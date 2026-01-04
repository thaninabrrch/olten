<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivraisonVtc extends Model
{
    protected $table = 'livraisons_vtc'; 
    protected $primaryKey = 'vtc_id';
    public $timestamps = false;
    protected $fillable = [
        'client_id', 'chauffeur_id', 'adresse_depart', 'adresse_arrivee',
        'distance_km', 'prix_base', 'commission_plateforme', 'prix_total_affiche',
        'statut', 'reglementation_transport', 'date_creation'
    ];

    public function chauffeur() {
        return $this->belongsTo(User::class, 'chauffeur_id');
    }

    public function client() {
        return $this->belongsTo(User::class, 'client_id');
    }
}
