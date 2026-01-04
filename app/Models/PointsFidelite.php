<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointsFidelite extends Model
{
    protected $table = 'points_fidelite'; 
    protected $primaryKey = 'fidelite_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action_source', 'points_gagnes', 'points_utilises', 'date_operation'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
