<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenuPlateforme extends Model
{
    protected $primaryKey = 'revenu_id';
    protected $fillable = [
        'transaction_id','montant_commission','date_reception'
    ];

    public function transaction() {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
