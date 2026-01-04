<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Covoiturage extends Model
{
    protected $table = 'covoiturages';
    protected $primaryKey = 'covoiturage_id';
    public $timestamps = false; 
    protected $fillable = [
        'conducteur_id', 'depart', 'destination', 'date_depart',
        'nb_places', 'prix_place', 'commission_plateforme', 'statut', 'reglementation_applicable', 'date_creation'
    ];

    public function conducteur() {
        return $this->belongsTo(User::class, 'conducteur_id');
    }
}
