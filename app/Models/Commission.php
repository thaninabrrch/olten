<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $primaryKey = 'commission_id';
    protected $fillable = [
        'type_service','taux','date_modification'
    ];
}
