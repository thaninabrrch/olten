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
        // Une offre de livraison ne se reserve pas sur des dates : le client
        // saisit son trajet sur la page de paiement dediee.
        if ($ad->isDelivery()) {
            return redirect()->route('bookings.confirm.livraison', $ad);
        }
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

        // La page masque deja le formulaire d'une annonce expiree ; le
        // controle est refait ici pour une requete envoyee sans passer par elle.
        if ($ad->isExpired()) {
            return back()->withErrors(['dates' => "Cette annonce a expiré : elle n'est plus réservable."]);
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

    public function confirmLivraison(Ad $ad)
    {
        if (! $ad->isDelivery()) {
            abort(404);
        }

        // Meme garde-fou que l'encart de l'annonce : on ne paie pas sa propre
        // offre, ni une offre retiree ou expiree.
        if ($ad->user_id === Auth::id()) {
            return redirect()->route('ads.show', $ad)
                             ->withErrors(['dates' => 'Vous ne pouvez pas réserver votre propre offre de livraison.']);
        }

        if ($ad->isExpired()) {
            return redirect()->route('ads.show', $ad)
                             ->withErrors(['dates' => "Cette offre a expiré : elle n'est plus réservable."]);
        }

        $ad->load('images', 'user');

        return view('bookings.confirm-livraison', compact('ad'));
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

        $ad = Ad::with('category.service')->findOrFail($request->ad_id);

        // Offre de livraison : un trajet paye au kilometre, sans dates.
        $isDelivery = $ad->isDelivery();

        $start_date = null;
        $end_date = null;
        $distance = null;

        if ($isDelivery) {
            $request->validate([
                'departure_address' => 'required|string|max:255',
                'arrival_address'   => 'required|string|max:255',
                'delivery_distance' => 'required|numeric|min:0.01|max:5000',
            ], [
                'departure_address.required' => "L'adresse de départ est obligatoire.",
                'arrival_address.required'   => "L'adresse d'arrivée est obligatoire.",
                'delivery_distance.*'        => 'La distance du trajet est invalide : sélectionnez à nouveau les deux adresses.',
            ]);

            if ($ad->user_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas réserver votre propre offre de livraison.',
                ], 422);
            }

            // Le prix se recalcule ici, au tarif de l'annonce : le montant
            // envoye par la page n'est pas une source fiable. Meme arrondi
            // que la page, pour que le client paie ce qu'il a lu.
            $distance = round((float) $request->delivery_distance, 2);
            $total = round($distance * $ad->deliveryRate(), 2);

            // Stripe refuse un paiement en euros inferieur a 50 centimes.
            if ($total < 0.5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce trajet est trop court pour être payé en ligne (minimum 0,50 €).',
                ], 422);
            }
        } else {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $start_date = $request->start_date;
            $end_date = $request->end_date;

            // Le montant arrive du formulaire : selon le chemin emprunte il peut
            // porter un symbole et un separateur de milliers (« 2,200.00 € »), que
            // le cast direct tronquerait a 2. On le normalise avant conversion.
            $total = $this->montant($request->finalPrice);
        }

        // Carte refusee, cle invalide... : Stripe leve une exception. Sans ce
        // filet, la page recevait une erreur 500 et un message generique.
        try {
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
        } catch (\Stripe\Exception\CardException $e) {
            // Stripe repond en anglais : les cas courants sont traduits, les
            // autres retombent sur un message generique.
            $refus = [
                'insufficient_funds' => 'Provision insuffisante sur cette carte.',
                'expired_card'       => 'Cette carte a expiré.',
                'incorrect_cvc'      => 'Le code de sécurité (CVC) est incorrect.',
                'incorrect_number'   => 'Le numéro de carte est incorrect.',
                'processing_error'   => 'Erreur de traitement de la carte. Réessayez.',
            ];

            return response()->json([
                'success' => false,
                'message' => $refus[$e->getDeclineCode()] ?? $refus[$e->getStripeCode()]
                    ?? 'Votre carte a été refusée. Vérifiez vos informations ou essayez une autre carte.',
            ], 422);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => "Le paiement n'a pas pu aboutir. Réessayez dans un instant.",
            ], 422);
        }

        if ($intent->status !== 'succeeded') {
            return response()->json([
                'success' => false,
                'message' => 'Le paiement nécessite une authentification ou n’a pas abouti.',
                'status' => $intent->status,
            ], 422);
        }

        $booking = new Booking();

        $booking->ad_id = $ad->id;
        $booking->user_id = $user->id;
        $booking->start_date = $start_date;
        $booking->end_date = $end_date;
        $booking->status = 'paid';
        $booking->booking_status = 'pending';
        $booking->payment_intent_id = $intent->id;

        if ($isDelivery) {
            // `address` : point de retrait ; `delivery_address` : destination.
            // `delivery_requested` reste faux : l'annonceur EST le livreur, la
            // reservation ne doit pas partir chez les autres livreurs comme
            // une mission a prendre (DeliveryAdController).
            $booking->address = $request->departure_address;
            $booking->delivery_address = $request->arrival_address;
            $booking->delivery_distance_km = $distance;
            $booking->delivery_cost = $total;
            $booking->delivery_requested = false;
        } else {
            // Location : livraison en option, choisie sur la page de paiement.
            $booking->address = $ad->address;
            $booking->delivery_requested = $request->boolean('delivery_requested');

            if ($booking->delivery_requested) {
                $booking->delivery_cost = $request->delivery_cost;
                $booking->delivery_distance_km = $request->delivery_distance;
                $booking->delivery_address = $request->delivery_address;
            }
        }

        $booking->calculateTotalPrice();
        $booking->save();

        $owner = $ad->user;

        if ($owner && $owner->hasPremiumSubscription()) {
            $owner->notify(new NewBookingNotification($booking));
        }

        return response()->json([
            'success' => true,
            // Une livraison se suit dans « Mes réservations » ; une location
            // revient sur son annonce, comme avant.
            'redirect' => $isDelivery
                ? route('bookings.show', $booking)
                : route('ads.show', $ad),
        ]);
    }

    public function show(Booking $booking)
    {
        // Le suivi expose les adresses du client : seuls le client et
        // l'annonceur y ont acces, pas n'importe quel membre connecte.
        if ($booking->user_id !== Auth::id() && $booking->ad?->user_id !== Auth::id()) {
            abort(403);
        }

        $booking->load([
            'user',
            'ad.images',
            'ad.category.service',
            'ad.user',
            'delivery.deliveryPerson',
            'delivery.reviews'
        ]);

        return view('bookings.show', compact('booking'));
    }
}