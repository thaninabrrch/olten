<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeLivreur extends Model
{
    protected $table = 'demande_livreur';
    protected $primaryKey = 'id_demande';
    public $timestamps = true;

    protected $fillable = [
        'id_livreur', 'id_annonce', 'statut', 'date_demande', 'booking_id'
    ];
    public function livreur() {
        return $this->belongsTo(User::class, 'id_livreur');
    }
    public function ad() {
        return $this->belongsTo(Ad::class, 'id_annonce', 'id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
