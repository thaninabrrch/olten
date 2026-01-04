<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivraisonRepas extends Model
{
    protected $table = 'livraisons_repas';
    protected $primaryKey = 'livraison_repas_id';
    public $timestamps = false; 
    protected $fillable = [
        'client_id','livreur_id','restaurant_nom','adresse_depart','adresse_arrivee',
        'distance_km','prix_base','commission_plateforme','prix_total_affiche','statut','reglementation_sanitaire','date_creation'
    ];

    public function livreur() {
        return $this->belongsTo(User::class, 'livreur_id');
    }

    public function client() {
        return $this->belongsTo(User::class, 'client_id');
    }
}

