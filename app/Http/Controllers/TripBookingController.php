<?php

namespace App\Http\Controllers;

use App\Models\Covoiturage;
use App\Models\TripBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;

class TripBookingController extends Controller
{
    private const LEGS = ['aller', 'retour'];

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /* ------------------------------------------------------------------
     | Calculs : tout est fait ici, jamais confié au navigateur.
     | On raisonne en centimes pour éviter les erreurs d'arrondi.
     ------------------------------------------------------------------ */

 /** Prix d'un sens en euros (0 = sens indisponible). Même source que la page de détail. */
private function legPrice(Covoiturage $trip, string $leg): float
{
    if ($leg === 'retour') {
        return $trip->retour
            ? (float) data_get($trip->return_trip_data, 'total', 0)
            : 0.0;
    }

    return (float) ($trip->prix_total_affiche ?: $trip->prix_place);
}

private function legPriceCents(Covoiturage $trip, string $leg): int
{
    return (int) round($this->legPrice($trip, $leg) * 100);
}

/** @return string[] uniquement les sens réellement disponibles */
private function resolveLegs(?string $raw, Covoiturage $trip): array
{
    $legs = array_values(array_unique(array_intersect(explode(',', (string) $raw), self::LEGS)));

    // On ignore les sens sans prix au lieu de tout bloquer.
    $legs = array_values(array_filter($legs, fn ($l) => $this->legPriceCents($trip, $l) > 0));

    abort_if(empty($legs), 422, 'Aucun sens disponible pour ce trajet.');

    return $legs;
}
    private function amounts(Covoiturage $trip, array $legs, int $seats): array
    {
        $rate        = Covoiturage::serviceRate();
        $seatCents   = collect($legs)->sum(fn ($l) => $this->legPriceCents($trip, $l));
        $driverCents = $seatCents * $seats;
        $commission  = Covoiturage::serviceFeeCents($driverCents);

        return [
            'rate'         => $rate,
            'seats'        => $seats,
            'seat'         => $seatCents / 100,
            'driver_cents' => $driverCents,
            'commission_c' => $commission,
            'total_cents'  => $driverCents + $commission,
            'driver'       => $driverCents / 100,
            'commission'   => $commission / 100,
            'total'        => ($driverCents + $commission) / 100,
        ];
    }

    /** Places restantes pour un sens donné. */
/** Places restantes pour un sens : même règle que la liste et la fiche du trajet. */
    private function remainingSeats(Covoiturage $trip, string $leg): int
    {
        // Recalcul frais : pour un paiement, on ne se fie jamais à une relation déjà chargée.
        return $trip->unsetRelation('paidBookings')->seatsLeft($leg);
    }

    /** Places réservables d'un coup : celles du sens choisi le plus rempli. */
    private function availableSeats(Covoiturage $trip, array $legs): int
    {
        return (int) collect($legs)->map(fn ($leg) => $this->remainingSeats($trip, $leg))->min();
    }

    private function hasSeats(Covoiturage $trip, array $legs, int $seats): bool
    {
        return $this->availableSeats($trip, $legs) >= $seats;
    }

    /* ------------------------------------------------------------------
     | Page de paiement
     ------------------------------------------------------------------ */

    public function checkout(Request $request, Covoiturage $trip)
    {
        abort_if($trip->conducteur_id === auth()->id(), 403, 'Vous ne pouvez pas réserver votre propre trajet.');

        $legs      = $this->resolveLegs($request->query('legs'), $trip);
        $available = $this->availableSeats($trip, $legs);

        abort_if($available < 1, 422, "Il n'y a plus de place disponible sur ce trajet.");

        // Le nombre de places vient de la fiche du trajet : on le ramène
        // dans les places encore libres plutôt que de refuser la page.
        $seats   = min(max(1, (int) $request->query('seats', 1)), $available);
        $amounts = $this->amounts($trip, $legs, $seats);

        // Détail affiché dans le récapitulatif
        $lines = collect($legs)->map(fn ($leg) => [
            'label' => $leg === 'retour' ? 'Retour' : 'Aller',
            'from'  => $leg === 'retour' ? $trip->destination : $trip->depart,
            'to'    => $leg === 'retour' ? $trip->depart      : $trip->destination,
            'price' => $this->legPriceCents($trip, $leg) / 100,
        ]);

        return view('trips.checkout', compact('trip', 'legs', 'amounts', 'lines', 'seats'));
    }
 
    /* ------------------------------------------------------------------
     | Paiement (gère aussi le 3D Secure en deux temps)
     ------------------------------------------------------------------ */
 
    public function pay(Request $request, Covoiturage $trip)
    {
        abort_if($trip->conducteur_id === auth()->id(), 403);
 
        $request->validate([
            'legs'  => 'required|string',
            'seats' => 'required|integer|min:1',
        ], [
            'seats.*' => 'Choisissez au moins une place.',
        ]);

        $legs    = $this->resolveLegs($request->input('legs'), $trip);
        $seats   = (int) $request->input('seats');
        $amounts = $this->amounts($trip, $legs, $seats);

        // La page de paiement appelle en fetch : la réponse doit rester en JSON.
        if (! $this->hasSeats($trip, $legs, $seats)) {
            $left = $this->availableSeats($trip, $legs);

            return response()->json([
                'success' => false,
                'message' => $left > 0
                    ? "Il ne reste que {$left} place" . ($left > 1 ? 's' : '') . ' sur ce trajet.'
                    : "Il n'y a plus de place disponible sur ce trajet.",
            ]);
        }
 
        try {
            if ($request->filled('payment_intent_id')) {
                // 2e appel, après authentification 3D Secure côté navigateur
                $intent = PaymentIntent::retrieve($request->input('payment_intent_id'));
                $this->assertIntentBelongsToRequest($intent, $trip, $legs, $amounts);
 
                if ($intent->status === 'requires_confirmation') {
                    $intent = $intent->confirm();
                }
            } else {
                $request->validate([
                    'payment_method' => 'required|string',
                    'phone'          => 'required|string|max:30',
                ]);
 
                $intent = PaymentIntent::create([
                    'amount'         => $amounts['total_cents'],
                    'currency'       => 'eur',
                    'payment_method' => $request->input('payment_method'),
                    'confirm'        => true,
                    'automatic_payment_methods' => ['enabled' => true, 'allow_redirects' => 'never'],
                    'metadata'       => [
                        'trip_id' => $trip->getKey(),
                        'user_id' => auth()->id(),
                        'legs'    => implode(',', $legs),
                        'seats'   => $seats,
                        'phone'   => $request->input('phone'),
                    ],
                ]);
            }
        } catch (ApiErrorException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
 
        if ($intent->status === 'requires_action') {
            return response()->json([
                'success'           => false,
                'requires_action'   => true,
                'client_secret'     => $intent->client_secret,
                'payment_intent_id' => $intent->id,
            ]);
        }
 
        if ($intent->status !== 'succeeded') {
            return response()->json(['success' => false, 'message' => "Le paiement n'a pas abouti."]);
        }
 
        return $this->finalize($trip, $legs, $amounts, $intent);
    }
 
    /** Un intent renvoyé par le navigateur doit correspondre exactement à cette réservation. */
    private function assertIntentBelongsToRequest(PaymentIntent $intent, Covoiturage $trip, array $legs, array $amounts): void
    {
        $meta = $intent->metadata;
 
        abort_unless(
            (int) $meta['trip_id'] === (int) $trip->getKey()
            && (int) $meta['user_id'] === (int) auth()->id()
            && $meta['legs'] === implode(',', $legs)
            && (int) ($meta['seats'] ?? 0) === $amounts['seats']
            && (int) $intent->amount === $amounts['total_cents'],
            403,
            'Paiement invalide.'
        );
    }
/** Enregistre la réservation une fois le paiement encaissé. */
private function finalize(Covoiturage $trip, array $legs, array $amounts, PaymentIntent $intent)
{
    try {
        $booking = DB::transaction(function () use ($trip, $legs, $amounts, $intent) {
            // 1. Verrou d'abord : deux paiements simultanés ne prennent pas la dernière place.
            Covoiturage::whereKey($trip->getKey())->lockForUpdate()->first();

            // 2. Puis seulement les lectures
            // Déjà enregistrée (double clic, rechargement) : on ne recrée rien.
            if ($existing = TripBooking::where('stripe_intent', $intent->id)->first()) {
                return $existing;
            }

            if (! $this->hasSeats($trip, $legs, $amounts['seats'])) {
                return null;
            }

            return TripBooking::create([
                'trip_id'         => $trip->getKey(),
                'user_id'         => auth()->id(),
                'legs'            => $legs,
                'seats'           => $amounts['seats'],
                'phone'           => $intent->metadata['phone'] ?? '',
                'driver_amount'   => $amounts['driver'],
                'commission'      => $amounts['commission'],
                'total_price'     => $amounts['total'],
                'commission_rate' => $amounts['rate'],
                'stripe_intent'   => $intent->id,
                'status'          => 'paid',
            ]);
        });
    } catch (\Throwable $e) {
        // Le paiement est encaissé mais la réservation n'a pas pu être enregistrée :
        // on rembourse pour ne jamais débiter sans réservation.
        report($e);

        return $this->refundAndFail(
            $intent,
            "Une erreur est survenue. Vous avez été remboursé."
        );
    }

    if (! $booking) {
        // Les places ont été prises entre-temps : remboursement immédiat.
        return $this->refundAndFail(
            $intent,
            $amounts['seats'] > 1
                ? "Les places demandées viennent d'être prises. Vous avez été remboursé."
                : "La dernière place vient d'être prise. Vous avez été remboursé."
        );
    }

    return response()->json([
        'success'  => true,
        'redirect' => route('bookings.showtripe', $booking),
    ]);
}

/** Rembourse le paiement, puis répond en JSON à la page de paiement. */
private function refundAndFail(PaymentIntent $intent, string $message)
{
    try {
        Refund::create(['payment_intent' => $intent->id]);
    } catch (\Throwable $e) {
        // Remboursement impossible : on le trace pour le faire à la main dans Stripe,
        // et on ne promet pas au passager qu'il a été remboursé.
        report($e);
        $message = "Une erreur est survenue. Contactez-nous : votre paiement sera remboursé.";
    }

    return response()->json(['success' => false, 'message' => $message]);
}
 
    /* ------------------------------------------------------------------
     | Confirmation et annulation
     ------------------------------------------------------------------ */
 
    public function showtripe(TripBooking $booking)
    {
        $this->assertParticipant($booking);
        $booking->load('trip.driver');
 
        return view('bookings.showtripe', compact('booking'));
    }
 
    public function cancel(TripBooking $booking)
    {
        // Seul le passager annule sa réservation : en publiant le trajet, le
        // conducteur s'est engagé envers lui.
        abort_unless((int) $booking->user_id === (int) auth()->id(), 403, 'Seul le passager peut annuler sa réservation.');
        abort_if($booking->status !== 'paid', 422, 'Cette réservation est déjà annulée.');
 
        try {
            Refund::create(['payment_intent' => $booking->stripe_intent]);
        } catch (ApiErrorException $e) {
            return back()->with('error', 'Le remboursement a échoué : ' . $e->getMessage());
        }
 
        // Le statut change : le gain sort tout seul du portefeuille du conducteur.
        $booking->update(['status' => 'cancelled', 'cancelled_at' => now()]);
 
        return back()->with('success', 'Réservation annulée. Vous êtes intégralement remboursé.');
    }
 
    /** Le passager ou le conducteur du trajet. */
    private function assertParticipant(TripBooking $booking): void
    {
        $uid = auth()->id();
        abort_unless($booking->user_id === $uid || $booking->trip->conducteur_id === $uid, 403);
    }
}
 