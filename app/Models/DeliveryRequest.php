<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryRequest extends Model
{
    protected $fillable = [
        'delivery_person_id',
        'booking_id',
        'product_sale_id',
        'status',
        'requested_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
    ];

    public function deliveryPerson()
    {
        return $this->belongsTo(User::class, 'delivery_person_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function productSale()
    {
        return $this->belongsTo(ProductSale::class);
    }
}