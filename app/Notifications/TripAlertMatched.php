<?php

namespace App\Notifications;

use App\Models\Covoiturage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Un trajet vient d'être publié sur une liaison que le membre guette
 * (TripAlert), ou un trajet existant vient d'y arriver : nouvelle date,
 * nouvel itinéraire, remise en ligne ($updated). Elle s'affiche dans la
 * cloche du header : canal base de données seul, envoyé immédiatement et non
 * mis en file d'attente, pour apparaître même quand aucun worker ne tourne.
 */
class TripAlertMatched extends Notification
{
    public function __construct(public Covoiturage $trip, public bool $updated = false)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $trip  = $this->trip;
        $heure = Str::substr((string) $trip->heure_depart, 0, 5);
        $price = $trip->seat_price;

        return [
            'icon'  => 'fa-route',
            'title' => ($this->updated ? 'Trajet mis à jour · ' : 'Nouveau trajet ')
                . $trip->depart_ville . ' → ' . $trip->destination_ville,
            'text'  => 'Départ le ' . $trip->date_depart?->translatedFormat('D d M')
                . ($heure ? ' à ' . $heure : '')
                . ($price > 0 ? ' · ' . number_format($price + Covoiturage::serviceFee($price), 2, ',', ' ') . ' € la place' : ''),
            'url'   => route('covoiturage.trip', $trip->covoiturage_id),
        ];
    }
}
