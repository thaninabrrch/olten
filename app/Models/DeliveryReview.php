<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryReview extends Model
{
    protected $fillable = [
        'delivery_id',
        'user_id',
        'rating',
        'comment',
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}