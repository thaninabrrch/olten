<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Objet extends Model
{
    protected $primaryKey = 'objet_id';
    protected $fillable = [
        'proprietaire_id','titre','description','categorie','prix_jour',
        'disponible','localisation','condition_sanitaire','date_ajout'
    ];

    public function proprietaire() {
        return $this->belongsTo(User::class, 'proprietaire_id');
    }

    public function reservations() {
        return $this->hasMany(Reservation::class, 'objet_id');
    }
}
