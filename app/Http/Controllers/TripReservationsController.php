<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsTripLegs;
use App\Models\Covoiturage;
use App\Models\TripBooking;
use App\Support\RouteImage;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Espace connecté : les réservations de trajets, des deux côtés.
 *
 *   index()    : côté passager, les trajets réservés (à venir / passés / annulés)
 *   received() : côté conducteur, les réservations reçues sur ses trajets
 *   show()     : le détail d'une réservation, pour le passager comme le conducteur
 */
class TripReservationsController extends Controller
{
    use BuildsTripLegs;

    /** Onglet de l'URL => état d'une réservation. */
    private const TABS = [
        'a-venir'  => 'upcoming',
        'passes'   => 'past',
        'annulees' => 'cancelled',
    ];

    /** Onglet de l'URL => état d'un trajet du conducteur. */
    private const RECEIVED_TABS = [
        'a-venir' => 'upcoming',
        'passes'  => 'past',
    ];

    public function index(Request $request)
    {
        $tab = array_key_exists($request->query('onglet'), self::TABS)
            ? $request->query('onglet')
            : 'a-venir';

        $items = TripBooking::query()
            ->where('user_id', auth()->id())
            ->settled()
            ->whereHas('trip')
            ->with('trip.conducteur.vehicle')
            ->get()
            ->map(fn (TripBooking $booking) => $this->present($booking));

        $counts = [];
        foreach (self::TABS as $slug => $state) {
            $counts[$slug] = $items->where('state', $state)->count();
        }

        $state = self::TABS[$tab];

        // À venir : le départ le plus proche d'abord. Passés et annulés :
        // le plus récent d'abord.
        $list = $items->where('state', $state);
        $list = $state === 'upcoming'
            ? $list->sortBy('sort')
            : $list->sortByDesc('sort');

        return view('trips.my-bookings', [
            'tab'    => $tab,
            'list'   => $list->values(),
            'counts' => $counts,
            'spent'  => $items->where('state', '!=', 'cancelled')
                              ->sum(fn (array $i) => (float) $i['booking']->total_price),
        ]);
    }

    /**
     * Côté conducteur : les réservations reçues sur ses trajets, regroupées
     * par trajet pour voir d'un coup d'œil qui voyage avec lui et combien de
     * places restent. Les réservations annulées restent visibles, barrées.
     */
    public function received(Request $request)
    {
        $tab = array_key_exists($request->query('onglet'), self::RECEIVED_TABS)
            ? $request->query('onglet')
            : 'a-venir';

        $trips = Covoiturage::query()
            ->where('conducteur_id', auth()->id())
            ->whereHas('bookings', fn ($q) => $q->settled())
            ->with(['bookings' => fn ($q) => $q->settled()->with('passenger')->latest()])
            ->get()
            ->map(fn (Covoiturage $trip) => $this->presentReceived($trip));

        $counts = [];
        foreach (self::RECEIVED_TABS as $slug => $state) {
            $counts[$slug] = $trips->where('state', $state)->count();
        }

        $state = self::RECEIVED_TABS[$tab];

        // À venir : les trajets qui ont des demandes à traiter d'abord, puis
        // le départ le plus proche. Passés : le plus récent d'abord.
        $list = $trips->where('state', $state);
        $list = $state === 'upcoming'
            ? $list->sortBy([['pending', 'desc'], ['sort', 'asc']])
            : $list->sortByDesc('sort');

        $upcoming = $trips->where('state', 'upcoming');

        return view('trips.received', [
            'tab'         => $tab,
            'list'        => $list->values(),
            'counts'      => $counts,
            'passengers'  => $upcoming->sum('passengers'),
            'seats'       => $upcoming->sum('seats'),
            'earnings'    => $trips->sum('earnings'),
            // Demandes qui attendent son accord, et le premier trajet concerné
            'pending'     => $trips->sum('pending'),
            'pendingTrip' => $trips->where('pending', '>', 0)->sortBy('sort')->first()['trip'] ?? null,
        ]);
    }

    /**
     * Détail d'une réservation : visible par le passager qui a réservé et
     * par le conducteur du trajet, personne d'autre.
     */
    public function show(TripBooking $booking)
    {
        $booking->load(['trip.conducteur.vehicle', 'passenger']);

        $trip = $booking->trip;

        abort_if($trip === null, 404);

        $userId      = auth()->id();
        $isPassenger = (int) $booking->user_id === (int) $userId;
        $isDriver    = (int) $trip->conducteur_id === (int) $userId;

        abort_unless($isPassenger || $isDriver, 403);

        // Seuls les sens réservés sont affichés ; à défaut de liste exploitable,
        // on montre tout le trajet plutôt qu'une page vide.
        $all  = $this->tripLegs($trip);
        $legs = collect($all)->only($booking->legs ?? [])->all() ?: $all;

        $state = $this->stateOf($booking, collect($legs)->pluck('date'));

        return view('trips.my-booking-show', [
            'booking'     => $booking,
            'trip'        => $trip,
            'legs'        => $legs,
            'state'       => $state,
            'isPassenger' => $isPassenger,
            'isDriver'    => $isDriver && ! $isPassenger,
            'image'       => RouteImage::for($trip->depart_ville, $trip->destination_ville),
        ]);
    }

    /**
     * Une réservation mise en forme pour la liste : sens réservés avec leurs
     * dates, état (à venir / passé / annulé), conducteur et véhicule.
     */
    private function present(TripBooking $booking): array
    {
        $trip = $booking->trip;
        $from = Covoiturage::villeCourte($trip->depart);
        $to   = Covoiturage::villeCourte($trip->destination);

        $legs = collect($booking->legs)->map(function ($leg) use ($trip, $from, $to) {
            $isReturn = $leg === 'retour';

            return [
                'key'   => $isReturn ? 'retour' : 'aller',
                'label' => $isReturn ? 'Retour' : 'Aller',
                'from'  => $isReturn ? $to : $from,
                'to'    => $isReturn ? $from : $to,
                'date'  => $isReturn ? $trip->return_date : $trip->date_depart,
                'time'  => Str::substr((string) ($isReturn ? $trip->return_time : $trip->heure_depart), 0, 5) ?: null,
            ];
        })->values();

        $days = $legs->pluck('date')->filter()
            ->map(fn ($date) => $date->copy()->startOfDay()->timestamp);

        return [
            'booking' => $booking,
            'trip'    => $trip,
            'from'    => $from,
            'to'      => $to,
            'image'   => RouteImage::for($from, $to),
            'legs'    => $legs,
            'driver'  => $trip->conducteur,
            'vehicle' => $trip->conducteur?->vehicle,
            'state'   => $this->stateOf($booking, $legs->pluck('date')),
            'sort'    => (int) ($days->min() ?? 0),
            'url'     => route('trips.myBookings.show', $booking),
        ];
    }

    /**
     * Un trajet du conducteur mis en forme pour la page des réservations
     * reçues : sens avec leurs places réservées, réservations, gains.
     */
    private function presentReceived(Covoiturage $trip): array
    {
        $bookings = $trip->bookings;
        $paid     = $bookings->where('status', 'paid');
        $pending  = $bookings->where('status', 'pending');

        $from = Covoiturage::villeCourte($trip->depart);
        $to   = Covoiturage::villeCourte($trip->destination);

        $legs = collect($trip->legKeys())->map(function (string $leg) use ($trip, $from, $to) {
            $isReturn = $leg === 'retour';

            return [
                'key'    => $leg,
                'label'  => $isReturn ? 'Retour' : 'Aller',
                'from'   => $isReturn ? $to : $from,
                'to'     => $isReturn ? $from : $to,
                'date'   => $isReturn ? $trip->return_date : $trip->date_depart,
                'time'   => Str::substr((string) ($isReturn ? $trip->return_time : $trip->heure_depart), 0, 5) ?: null,
                'booked' => $trip->seatsBooked($leg),
            ];
        });

        $days = $legs->pluck('date')->filter()
            ->map(fn ($date) => $date->copy()->startOfDay()->timestamp);

        return [
            'trip'       => $trip,
            'from'       => $from,
            'to'         => $to,
            'image'      => RouteImage::for($from, $to),
            'legs'       => $legs,
            // Les demandes à traiter d'abord, puis les réservations en cours,
            // les annulées ensuite
            'bookings'   => $bookings->sortBy(fn (TripBooking $b) => ['pending' => 0, 'paid' => 1][$b->status] ?? 2)->values(),
            'passengers' => $paid->count(),
            'pending'    => $pending->count(),
            'seats'      => (int) $legs->max('booked'),
            'earnings'   => (float) $paid->sum('driver_amount'),
            'state'      => $days->isNotEmpty() && $days->max() < today()->timestamp ? 'past' : 'upcoming',
            'sort'       => (int) ($days->min() ?? 0),
        ];
    }

    /**
     * upcoming | past | cancelled.
     * Passé : tous les sens réservés sont antérieurs à aujourd'hui.
     */
    private function stateOf(TripBooking $booking, Collection $dates): string
    {
        // Confirmée ou en attente de l'accord du conducteur : la réservation
        // tient sa place, elle se range avec les trajets à venir ou passés.
        if (! in_array($booking->status, TripBooking::HOLDING, true) || $booking->cancelled_at !== null) {
            return 'cancelled';
        }

        $days = $dates->filter()->map(fn ($date) => $date->copy()->startOfDay()->timestamp);

        return $days->isNotEmpty() && $days->max() < today()->timestamp ? 'past' : 'upcoming';
    }
}