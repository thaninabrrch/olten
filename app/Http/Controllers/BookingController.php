<?php

namespace App\Http\Controllers;
use App\Models\Booking;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingRejectedMail;
use App\Mail\BookingAcceptedMail;
use App\Notifications\NewBookingNotification;

class BookingController extends Controller
{
    public function receivedBookings()
    {
        $bookings = Booking::with('ad', 'user')
                           ->whereHas('ad', function ($q) {
                                $q->where('user_id', auth()->id());
                            })
                           ->latest()
                           ->paginate(25);

        return view('pages.locateur.bookingReceived', compact('bookings'));
    }

    public function myBookings()
    {
        $bookings = Booking::with(['ad.category', 'ad.user'])
                           ->where('user_id', auth()->id())
                           ->latest()
                           ->paginate(25);
        return view('bookings.my', compact('bookings'));
    }

    public function accept(Booking $booking)
    {
        if ($booking->ad->user_id !== auth()->id()) {
            abort(403);
        }

        $booking->update([
            'booking_status' => 'confirmed',
        ]);

        Mail::to($booking->user->email)->send(
            new BookingAcceptedMail($booking)
        );

        return back()->with('success', 'Réservation acceptée avec succès.');
    }

    public function reject(Booking $booking)
    {
        if ($booking->ad->user_id !== auth()->id()) {
            abort(403);
        }

        if (in_array($booking->booking_status, ['cancelled', 'completed'])) {
            return back()->with('error', 'Action impossible sur cette réservation.');
        }

        if ($booking->status === 'paid' && $booking->payment_intent_id) {

            Stripe::setApiKey(config('services.stripe.secret'));

            Refund::create([
                'payment_intent' => $booking->payment_intent_id,
            ]);
        }

        $booking->update([
            'status' => 'refunded',
            'booking_status' => 'cancelled',
        ]);

        if ($booking->user && $booking->user->email) {
            Mail::to($booking->user->email)->send(
                new BookingRejectedMail($booking)
            );
        }

        return back()->with('success', 'Réservation refusée avec remboursement si nécessaire.');
    }

    public function store(Request $request, Ad $ad)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ], [
            'start_date.required' => 'La date de début est obligatoire.',
            'end_date.required'   => 'La date de fin est obligatoire.',
            'end_date.after_or_equal' => 'La date de fin doit être égale ou après la date de début.',
        ]);

        // Une annonce de vente n'a pas de periode de location : ses dates sont
        // nulles. Sans ce garde-fou, l'appel a format() sur null casserait la
        // page ; une annonce sans periode ne se reserve simplement pas.
        if (!$ad->available_from || !$ad->available_until) {
            return back()->withErrors(['dates' => "Cette annonce ne se réserve pas : elle n'a pas de période de location."]);
        }

        if ($validated['start_date'] < $ad->available_from->format('Y-m-d') ||
            $validated['end_date'] > $ad->available_until->format('Y-m-d')) {
            return back()->withErrors(['dates' => 'Les dates choisies ne sont pas disponibles pour cette annonce.']);
        }

        return redirect()->route('bookings.confirm')->with([
            'ad_id' => $ad->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
    }

    public function confirm()
    {
        if (!session()->has('start_date')) {
            return redirect()->back();
        }
        $ad = Ad::findOrFail(session('ad_id'));
        return view('pages.annonces_pages.confirm_booking', [
                                                                'ad' => $ad,
                                                                'start_date' => session('start_date'),
                                                                'end_date' => session('end_date'),
                                                            ]);
    }

    /**
     * Convertit un montant saisi ou formate en flottant.
     * Gere « 2200.00 », « 2,200.00 € » (milliers a l'anglaise) et
     * « 2 200,00 € » (ecriture francaise).
     */
    private function montant($valeur): float
    {
        $brut = preg_replace('/[^0-9.,]/', '', (string) $valeur);

        if (str_contains($brut, ',') && str_contains($brut, '.')) {
            // Les deux presents : la virgule ne peut etre que le separateur de milliers
            $brut = str_replace(',', '', $brut);
        } else {
            $brut = str_replace(',', '.', $brut);
        }

        return (float) $brut;
    }

    public function pay(Request $request)
    {
        $user = Auth::user();

        Stripe::setApiKey(config('services.stripe.secret'));

        $ad = Ad::findOrFail($request->ad_id);

        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $days = \Carbon\Carbon::parse($start_date)->diffInDays($end_date) + 1;
        // Le montant arrive du formulaire : selon le chemin emprunte il peut
        // porter un symbole et un separateur de milliers (« 2,200.00 € »), que
        // le cast direct tronquerait a 2. On le normalise avant conversion.
        $total = $this->montant($request->finalPrice);
        $intent = PaymentIntent::create([
            'amount' => (int) round($total * 100),
            'currency' => 'eur',
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);

        $intent = $intent->confirm([
            'payment_method' => $request->payment_method,
            'return_url' => route('bookings.confirm'),
        ]);
        if ($intent->status == 'succeeded') {
            $booking = new Booking();
            $booking->ad_id = $ad->id;
            $booking->user_id = $user->id;
            $booking->start_date = $start_date;
            $booking->end_date = $end_date;
            $booking->status = 'paid';
            $booking->booking_status = 'pending';
            $booking->payment_intent_id = $intent->id;
            $booking->address = $ad->address;

            $booking->delivery_requested = $request->boolean('delivery_requested');

            if ($booking->delivery_requested) {

                $booking->delivery_cost = $request->delivery_cost;
                $booking->delivery_distance_km = $request->delivery_distance;
                $booking->delivery_address = $request->delivery_address;
            }

            $booking->calculateTotalPrice();
            $booking->save();
            $owner = $ad->user;
            if ($owner && $owner->hasPremiumSubscription()) {
                $owner->notify(new NewBookingNotification($booking)); 
            }
        }

        return response()->json([
            'success' => true,
            'redirect' => url("/annonces/{$ad->id}/détails")
        ]);
    }

    public function show(Booking $booking)
    {
        $booking->load([
            'ad.images',
            'ad.category',
            'ad.user',
            'delivery.deliveryPerson',
            'delivery.reviews'
        ]);

        return view('bookings.show', compact('booking'));
    }
}