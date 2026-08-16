<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'user_id',
        'marque',
        'modele',
        'immatriculation',
        'annee',
        'couleur',
        'places',
        'type',
        'photo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
