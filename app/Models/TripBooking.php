<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Stripe\Refund;
use Stripe\Stripe;

class TripBooking extends Model
{
    protected $guarded = [];

    /**
     * Statuts d'une réservation :
     *   paid      confirmée ;
     *   pending   payée, en attente de l'accord du conducteur (trajet en
     *             validation manuelle) ;
     *   cancelled annulée par le passager ;
     *   refused   refusée par le conducteur ;
     *   expired   restée sans réponse au départ (covoiturage:expirer-demandes).
     * Les trois derniers sont remboursés.
     *
     * Confirmée ou en attente, une réservation tient sa place : l'argent est
     * encaissé et personne d'autre ne peut la prendre.
     */
    public const HOLDING = ['paid', 'pending'];

    /** Libellé et icône de chaque statut, pour les listes et les fiches. */
    public const STATUS = [
        'paid'      => ['Confirmée', 'fa-circle-check'],
        'pending'   => ["En attente d'accord", 'fa-hourglass-half'],
        'cancelled' => ['Annulée', 'fa-ban'],
        'refused'   => ['Refusée', 'fa-circle-xmark'],
        'expired'   => ['Sans réponse', 'fa-clock-rotate-left'],
    ];

    protected $casts = [
        'legs'         => 'array',
        'seats'        => 'integer',
        'seats_aller'  => 'integer',
        'seats_retour' => 'integer',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Les places réservées sont tenues à jour sur le trajet (compteurs de
     * `covoiturages`) : les listes filtrent et regroupent ainsi en SQL. Le
     * recomptage repart des réservations, il ne peut donc pas dériver.
     */
    protected static function booted(): void
    {
        // Places par sens : une réservation créée sans elles (ancien code,
        // données de test) les tient de `seats`, valable pour chacun de ses
        // sens. `seats` reste le plus grand des deux : le nombre de voyageurs.
        static::creating(function (TripBooking $booking) {
            if (! $booking->seats_aller && ! $booking->seats_retour) {
                $legs  = (array) $booking->legs;
                $seats = max(1, (int) $booking->seats);

                $booking->seats_aller  = in_array('aller', $legs, true) ? $seats : 0;
                $booking->seats_retour = in_array('retour', $legs, true) ? $seats : 0;
            }

            $booking->seats = max((int) $booking->seats_aller, (int) $booking->seats_retour, 1);
        });

        static::saved(fn (TripBooking $booking) => $booking->trip?->recountSeats());
        static::deleted(fn (TripBooking $booking) => $booking->trip?->recountSeats());
    }

    public function trip()
    {
        return $this->belongsTo(
            Covoiturage::class,
            'trip_id',
            'covoiturage_id'
        );
    }

    public function passenger()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Places prises sur un sens : 0 si la réservation ne le couvre pas. */
    public function seatsOn(string $leg): int
    {
        return (int) ($leg === 'retour' ? $this->seats_retour : $this->seats_aller);
    }

    /** Voir describeSeats() : « 2 places », ou « 2 places à l'aller · 1 au retour ». */
    public function seatsLabel(bool $short = false): string
    {
        $seats = array_filter(['aller' => $this->seatsOn('aller'), 'retour' => $this->seatsOn('retour')]);

        return self::describeSeats($seats ?: ['aller' => (int) $this->seats], $short);
    }

    /**
     * Places en toutes lettres : « 2 places », ou « 2 places à l'aller ·
     * 1 au retour » quand les deux sens diffèrent. En court : « 2 », ou
     * « 2 aller · 1 retour ».
     *
     * @param array<string, int> $seats places par sens réservé
     */
    public static function describeSeats(array $seats, bool $short = false): string
    {
        $seats = array_filter($seats);

        if (count(array_unique($seats)) <= 1) {
            $count = (int) (reset($seats) ?: 0);

            return $short ? (string) $count : $count . ' place' . ($count > 1 ? 's' : '');
        }

        return $short
            ? $seats['aller'] . ' aller · ' . $seats['retour'] . ' retour'
            : $seats['aller'] . ' place' . ($seats['aller'] > 1 ? 's' : '') . " à l'aller · " . $seats['retour'] . ' au retour';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Départ du premier sens réservé : une demande encore en attente à ce
     * moment-là est remboursée (covoiturage:expirer-demandes), et le
     * conducteur ne peut plus y répondre.
     */
    public function departsAt(): ?Carbon
    {
        $trip = $this->trip;
        $leg  = $this->seatsOn('aller') > 0 ? 'aller' : 'retour';
        $date = $leg === 'retour' ? $trip?->return_date : $trip?->date_depart;

        if (! $date) {
            return null;
        }

        $at   = Carbon::parse($date)->startOfDay();
        $time = (string) ($leg === 'retour' ? $trip->return_time : $trip->heure_depart);

        if (preg_match('/^(\d{1,2}):(\d{2})/', $time, $m)) {
            $at->setTime((int) $m[1], (int) $m[2]);
        }

        return $at;
    }

    /**
     * Rembourse la réservation sur Stripe, puis la clôt avec ce statut
     * (refused, expired) : sa place se libère. Si Stripe refuse le
     * remboursement, l'exception remonte et la réservation reste en l'état.
     */
    public function refundAndClose(string $status): void
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        Refund::create(['payment_intent' => $this->stripe_intent]);

        $this->update(['status' => $status, 'cancelled_at' => now()]);
    }

    /** Payées et non remboursées : confirmées, ou en attente de l'accord du conducteur. */
    public function scopePaid($query)
    {
        return $query->whereIn('status', self::HOLDING);
    }

    /** En attente de l'accord du conducteur. */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Payées (confirmées ou en attente), ou annulées (donc remboursées) : les
     * tentatives de paiement jamais abouties n'ont rien à faire dans les listes.
     */
    public function scopeSettled($query)
    {
        return $query->where(fn ($q) => $q->whereIn('status', self::HOLDING)->orWhereNotNull('cancelled_at'));
    }
}