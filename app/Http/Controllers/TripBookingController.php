<?php

namespace App\Http\Controllers;

use App\Models\Covoiturage;
use App\Models\TripBooking;
use App\Notifications\TripBookingAnswered;
use App\Notifications\TripBookingRequested;
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
    /**
     * Montants d'une réservation : chaque sens compte ses propres places
     * (2 à l'aller, 1 au retour...), au prix de ce sens.
     *
     * @param array<string, int> $seats places par sens choisi
     */
    private function amounts(Covoiturage $trip, array $seats): array
    {
        $driverCents = 0;
        foreach ($seats as $leg => $count) {
            $driverCents += $this->legPriceCents($trip, $leg) * $count;
        }

        $rate       = Covoiturage::serviceRate();
        $commission = Covoiturage::serviceFeeCents($driverCents);

        return [
            'rate'         => $rate,
            'seats'        => $seats,
            'driver_cents' => $driverCents,
            'commission_c' => $commission,
            'total_cents'  => $driverCents + $commission,
            'driver'       => $driverCents / 100,
            'commission'   => $commission / 100,
            'total'        => ($driverCents + $commission) / 100,
        ];
    }

    /**
     * Places restantes pour un sens. Pour un paiement, on recompte sur les
     * réservations elles-mêmes (source de vérité) et jamais sur une relation
     * déjà chargée ni sur les compteurs d'affichage du trajet.
     */
    private function remainingSeats(Covoiturage $trip, string $leg): int
    {
        return max(0, (int) $trip->nb_places - $trip->countPaidSeats($leg));
    }

    /** Le premier sens demandé qui n'a plus assez de places libres, ou null. */
    private function shortLeg(Covoiturage $trip, array $seats): ?string
    {
        foreach ($seats as $leg => $count) {
            if ($this->remainingSeats($trip, $leg) < $count) {
                return $leg;
            }
        }

        return null;
    }

    /** Pourquoi la demande ne passe pas (« Il ne reste que 1 place au retour. »), ou null. */
    private function shortageMessage(Covoiturage $trip, array $seats): ?string
    {
        $leg = $this->shortLeg($trip, $seats);

        if ($leg === null) {
            return null;
        }

        $left  = $this->remainingSeats($trip, $leg);
        // Sur un aller-retour, on dit quel sens manque, meme s'il est reserve seul.
        $where = $trip->retour ? ($leg === 'retour' ? ' au retour' : " à l'aller") : ' sur ce trajet';

        return $left > 0
            ? "Il ne reste que {$left} place" . ($left > 1 ? 's' : '') . $where . '.'
            : "Il n'y a plus de place disponible{$where}.";
    }

    /**
     * Places demandées pour chaque sens choisi, une au moins. Un lien
     * d'avant le choix par sens n'a qu'un nombre, `seats`, valable pour
     * chaque sens.
     *
     * @return array<string, int> ['aller' => 2, 'retour' => 1]
     */
    private function requestedSeats(Request $request, array $legs): array
    {
        $seats = [];
        foreach ($legs as $leg) {
            $seats[$leg] = max(1, (int) $request->input('seats_' . $leg, $request->input('seats', 1)));
        }

        return $seats;
    }

    /** Places par sens pour les métadonnées Stripe : « aller:2,retour:1 ». */
    private function seatsKey(array $seats): string
    {
        return collect($seats)->map(fn ($count, $leg) => $leg . ':' . $count)->implode(',');
    }

    /* ------------------------------------------------------------------
     | Page de paiement
     ------------------------------------------------------------------ */

    public function checkout(Request $request, Covoiturage $trip)
    {
        abort_if($trip->conducteur_id === auth()->id(), 403, 'Vous ne pouvez pas réserver votre propre trajet.');

        $legs = $this->resolveLegs($request->query('legs'), $trip);

        // Un sens choisi déjà complet : la page de paiement n'a pas lieu d'être.
        $full = $this->shortageMessage($trip, array_fill_keys($legs, 1));
        abort_if($full !== null, 422, (string) $full);

        // Le nombre de places de chaque sens vient de la fiche du trajet : on
        // le ramène dans les places encore libres de ce sens plutôt que de
        // refuser la page.
        $seats = [];
        foreach ($this->requestedSeats($request, $legs) as $leg => $count) {
            $seats[$leg] = min($count, $this->remainingSeats($trip, $leg));
        }
        $amounts = $this->amounts($trip, $seats);

        // Détail affiché dans le récapitulatif
        $lines = collect($legs)->map(fn ($leg) => [
            'label' => $leg === 'retour' ? 'Retour' : 'Aller',
            'from'  => $leg === 'retour' ? $trip->destination : $trip->depart,
            'to'    => $leg === 'retour' ? $trip->depart      : $trip->destination,
            'price' => $this->legPriceCents($trip, $leg) / 100,
            'seats' => $seats[$leg],
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
            'legs'         => 'required|string',
            'seats'        => 'nullable|integer|min:1',
            'seats_aller'  => 'nullable|integer|min:1',
            'seats_retour' => 'nullable|integer|min:1',
        ], [
            'seats.*'        => 'Choisissez au moins une place.',
            'seats_aller.*'  => 'Choisissez au moins une place à l\'aller.',
            'seats_retour.*' => 'Choisissez au moins une place au retour.',
        ]);

        $legs    = $this->resolveLegs($request->input('legs'), $trip);
        $seats   = $this->requestedSeats($request, $legs);
        $amounts = $this->amounts($trip, $seats);

        // La page de paiement appelle en fetch : la réponse doit rester en JSON.
        if ($message = $this->shortageMessage($trip, $seats)) {
            return response()->json(['success' => false, 'message' => $message]);
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
                        'seats'   => $this->seatsKey($seats),
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
            && (string) ($meta['seats'] ?? '') === $this->seatsKey($amounts['seats'])
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
            $locked = Covoiturage::whereKey($trip->getKey())->lockForUpdate()->first();

            // 2. Puis seulement les lectures
            // Déjà enregistrée (double clic, rechargement) : on ne recrée rien.
            if ($existing = TripBooking::where('stripe_intent', $intent->id)->first()) {
                return $existing;
            }

            // Places relues sur la ligne verrouillée (le conducteur a pu en
            // changer le nombre entre-temps) ; un trajet supprimé n'en a plus.
            if (! $locked || $this->shortLeg($locked, $amounts['seats']) !== null) {
                return null;
            }

            return TripBooking::create([
                'trip_id'         => $trip->getKey(),
                'user_id'         => auth()->id(),
                'legs'            => $legs,
                'seats'           => max($amounts['seats']),
                'seats_aller'     => $amounts['seats']['aller'] ?? 0,
                'seats_retour'    => $amounts['seats']['retour'] ?? 0,
                'phone'           => $intent->metadata['phone'] ?? '',
                'driver_amount'   => $amounts['driver'],
                'commission'      => $amounts['commission'],
                'total_price'     => $amounts['total'],
                'commission_rate' => $amounts['rate'],
                'stripe_intent'   => $intent->id,
                // Validation manuelle : payée, la place est tenue, mais le
                // conducteur doit encore l'accepter.
                'status'          => $locked->isManual() ? 'pending' : 'paid',
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
            array_sum($amounts['seats']) > 1
                ? "Les places demandées viennent d'être prises. Vous avez été remboursé."
                : "La dernière place vient d'être prise. Vous avez été remboursé."
        );
    }

    // Nouvelle demande à valider : le conducteur est prévenu (cloche et
    // e-mail). Un envoi raté ne doit pas faire échouer une réservation payée.
    if ($booking->wasRecentlyCreated && $booking->isPending()) {
        try {
            $trip->conducteur?->notify(new TripBookingRequested($booking));
        } catch (\Throwable $e) {
            report($e);
        }
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
        // Confirmée ou encore en attente de l'accord du conducteur
        abort_unless(in_array($booking->status, TripBooking::HOLDING, true), 422, 'Cette réservation est déjà annulée.');
 
        try {
            Refund::create(['payment_intent' => $booking->stripe_intent]);
        } catch (ApiErrorException $e) {
            return back()->with('error', 'Le remboursement a échoué : ' . $e->getMessage());
        }
 
        // Le statut change : le gain sort tout seul du portefeuille du conducteur.
        $booking->update(['status' => 'cancelled', 'cancelled_at' => now()]);
 
        return back()->with('success', 'Réservation annulée. Vous êtes intégralement remboursé.');
    }
 
    /* ------------------------------------------------------------------
     | Validation manuelle : le conducteur accepte ou refuse une demande
     ------------------------------------------------------------------ */

    public function approve(TripBooking $booking)
    {
        if ($blocked = $this->answerBlocked($booking)) {
            return back()->with('error', $blocked);
        }

        // Mise à jour conditionnelle : un double clic, ou un refus parti d'un
        // autre onglet, ne peut pas croiser cette acceptation. La place est
        // tenue depuis le paiement : il n'y a rien à recompter.
        $accepted = TripBooking::whereKey($booking->getKey())
            ->where('status', 'pending')
            ->update(['status' => 'paid', 'updated_at' => now()]);

        if ($accepted === 0) {
            return back()->with('error', 'Cette demande a déjà reçu une réponse.');
        }

        $this->tellPassenger($booking->refresh(), 'accepted');

        return back()->with('success', 'Réservation acceptée : ' . ($booking->passenger?->public_name ?? 'le passager') . ' est prévenu.');
    }

    public function refuse(TripBooking $booking)
    {
        if ($blocked = $this->answerBlocked($booking)) {
            return back()->with('error', $blocked);
        }

        try {
            $booking->refundAndClose('refused');
        } catch (ApiErrorException $e) {
            return back()->with('error', 'Le remboursement a échoué : ' . $e->getMessage());
        }

        $this->tellPassenger($booking, 'refused');

        return back()->with('success', 'Demande refusée : le passager est intégralement remboursé.');
    }

    /**
     * Seul le conducteur du trajet répond, à une demande encore en attente,
     * avant le départ (après, covoiturage:expirer-demandes la rembourse).
     * Renvoie la raison d'un refus, ou null.
     */
    private function answerBlocked(TripBooking $booking): ?string
    {
        abort_unless((int) $booking->trip?->conducteur_id === (int) auth()->id(), 403);

        if (! $booking->isPending()) {
            return 'Cette demande a déjà reçu une réponse.';
        }

        if ($booking->departsAt()?->isPast()) {
            return 'Le départ est passé : cette demande va être remboursée automatiquement.';
        }

        return null;
    }

    /** Un envoi raté ne doit pas faire échouer la réponse du conducteur. */
    private function tellPassenger(TripBooking $booking, string $answer): void
    {
        try {
            $booking->passenger?->notify(new TripBookingAnswered($booking, $answer));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Le passager ou le conducteur du trajet. */
    private function assertParticipant(TripBooking $booking): void
    {
        $uid = auth()->id();
        abort_unless($booking->user_id === $uid || $booking->trip->conducteur_id === $uid, 403);
    }
}
 