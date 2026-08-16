<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'ad_id',
        'user_id',
        'start_date',
        'end_date',
        'total_price',
        'delivery_cost',
        'delivery_requested',
        'delivery_distance_km',
        'delivery_address',
        'status',
        'booking_status',
        'address',
        'payment_intent_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    // public function livraison()
    // {
    //     return $this->hasOne(LivraisonColis::class, 'booking_id');
    // }
    
    public function delivery()
    {
        return $this->hasOne(Delivery::class, 'booking_id');
    }

    public function deliveryRequests()
    {
        return $this->hasMany(DeliveryRequest::class, 'booking_id');
    }

    public function calculateTotalPrice()
    {
        $start = $this->start_date instanceof Carbon ? $this->start_date : Carbon::parse($this->start_date);
        $end   = $this->end_date instanceof Carbon ? $this->end_date : Carbon::parse($this->end_date);

        $days = $start->diffInDays($end) + 1;
        $adPrice = $this->ad->price_per_day ?? 0;
        $delivery = $this->delivery_cost ?? 0;

        $this->total_price = ($days * $adPrice) + $delivery;

        return $this->total_price;
    }
}
