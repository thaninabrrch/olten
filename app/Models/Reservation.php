<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $primaryKey = 'reservation_id';
    protected $fillable = [
        'objet_id','locataire_id','date_debut','date_fin','montant_total',
        'commission_plateforme','statut','reglementation_applicable','date_creation'
    ];

    public function objet() {
        return $this->belongsTo(Objet::class, 'objet_id');
    }

    public function locataire() {
        return $this->belongsTo(User::class, 'locataire_id');
    }
}
