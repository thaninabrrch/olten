<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivraisonColis extends Model
{
    protected $table = 'livraisons_colis'; 
    protected $primaryKey = 'colis_id';
    public $timestamps = false;
    protected $fillable = [
        'expediteur_id','livreur_id','objet_description','adresse_depart','adresse_arrivee',
        'distance_km','prix_base','commission_plateforme','prix_total_affiche','statut','reglementation_transport','date_creation'
    ];

    public function livreur() {
        return $this->belongsTo(User::class, 'livreur_id');
    }

    public function expediteur() {
        return $this->belongsTo(User::class, 'expediteur_id');
    }
}
