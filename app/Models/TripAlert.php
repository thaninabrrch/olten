<?php

namespace App\Models;

use App\Notifications\TripAlertMatched;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

/**
 * Alerte trajet : « prévenez-moi quand un Paris → Lyon est publié ».
 *
 * La liaison se compare en slug (depart_slug / destination_slug), comme les
 * trajets eux-mêmes : « Lyon » saisi par le membre correspond à un départ
 * géocodé « Lyon, Métropole de Lyon, Rhône, … ». Une date restreint l'alerte
 * aux trajets de ce jour-là ; sans date, tous les jours conviennent.
 */
class TripAlert extends Model
{
    protected $fillable = ['user_id', 'depart', 'destination', 'depart_slug', 'destination_slug', 'date'];

    protected $casts = [
        'date' => 'date',
    ];

    /** Plafond d'alertes par membre : au-delà, la cloche deviendrait du bruit. */
    public const MAX_PER_USER = 5;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Une alerte datée dont le jour est passé ne peut plus rien trouver. */
    public function isExpired(): bool
    {
        return $this->date !== null && $this->date->lt(today());
    }

    /**
     * Prévient les membres qui guettent la liaison d'un trajet qui vient
     * d'être publié ($updated = false), ou qui vient de changer de liaison,
     * de jour ou d'être remis en ligne ($updated = true). Le conducteur n'est
     * pas prévenu de son propre trajet, et un membre qui a deux alertes
     * correspondantes (une datée, une sans date) ne reçoit qu'une
     * notification.
     */
    public static function notifyFor(Covoiturage $trip, bool $updated = false): void
    {
        if ($trip->statut === 'inactif' || $trip->isPast() || ! $trip->depart_slug || ! $trip->destination_slug) {
            return;
        }

        $users = static::query()
            ->where('depart_slug', $trip->depart_slug)
            ->where('destination_slug', $trip->destination_slug)
            ->where('user_id', '!=', $trip->conducteur_id)
            ->where(fn ($q) => $q->whereNull('date')->orWhereDate('date', $trip->date_depart))
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id');

        if ($users->isNotEmpty()) {
            Notification::send($users, new TripAlertMatched($trip, $updated));
        }
    }
}
