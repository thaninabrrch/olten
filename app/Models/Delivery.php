<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable = [
        'booking_id',
        'product_sale_id',
        'delivery_person_id',
        'pickup_address',
        'delivery_address',
        'distance_km',
        'base_price',
        'platform_fee',
        'total_price',
        'status',
        'picked_up_at',
        'delivered_at',
    ];

    protected $casts = [
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function productSale()
    {
        return $this->belongsTo(ProductSale::class, 'product_sale_id');
    }

    public function deliveryPerson()
    {
        return $this->belongsTo(User::class, 'delivery_person_id');
    }

    public function reviews()
    {
        return $this->hasMany(DeliveryReview::class);
    }
}