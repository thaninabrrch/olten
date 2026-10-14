<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Proposition d'un prestataire sur une demande de prestation.
 *
 * La demande est une annonce publiée dans une catégorie du service
 * « Prestations de services » (Ad::isPrestation) : le client décrit son
 * besoin, les prestataires y répondent par un prix total, le client compare
 * puis en choisit une. Choisir une proposition décline les autres.
 *
 *   pending   : envoyée, en attente du choix du client
 *   accepted  : retenue par le client
 *   declined  : le client a choisi un autre prestataire
 *   withdrawn : retirée par le prestataire, invisible pour le client
 */
class ServiceProposal extends Model
{
    /** Service dont les annonces sont des demandes de prestation. */
    public const SERVICE_SLUG = 'prestations-de-services';

    public const PENDING   = 'pending';
    public const ACCEPTED  = 'accepted';
    public const DECLINED  = 'declined';
    public const WITHDRAWN = 'withdrawn';

    protected $fillable = ['ad_id', 'user_id', 'price', 'message', 'available_on', 'status', 'answered_at'];

    protected $casts = [
        'price'        => 'decimal:2',
        'available_on' => 'date',
        'answered_at'  => 'datetime',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Propositions que le client voit et compare : une proposition retirée
     * n'existe plus pour lui.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('status'), '!=', self::WITHDRAWN);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('status'), self::PENDING);
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::ACCEPTED;
    }

    /** [libellé, classe, icône] du statut, pour les badges. */
    public function statusMeta(): array
    {
        return match ($this->status) {
            self::ACCEPTED  => ['Retenue', 'is-ok', 'fa-circle-check'],
            self::DECLINED  => ['Non retenue', 'is-out', 'fa-circle-xmark'],
            self::WITHDRAWN => ['Retirée', 'is-muted', 'fa-rotate-left'],
            default         => ['En attente', 'is-wait', 'fa-hourglass-half'],
        };
    }
}
