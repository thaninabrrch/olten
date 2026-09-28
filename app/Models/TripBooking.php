<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripBooking extends Model
{
    protected $guarded = [];

    protected $casts = [
        'legs'         => 'array',
        'seats'        => 'integer',
        'cancelled_at' => 'datetime',
    ];

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

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Payées, ou annulées (donc remboursées) : les tentatives de paiement
     * jamais abouties n'ont rien à faire dans les listes.
     */
    public function scopeSettled($query)
    {
        return $query->where(fn ($q) => $q->where('status', 'paid')->orWhereNotNull('cancelled_at'));
    }
}