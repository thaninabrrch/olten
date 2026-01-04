<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $primaryKey = 'transaction_id';
    protected $fillable = [
        'user_id','type_operation','service_type','service_id','montant','moyen_paiement','statut'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
