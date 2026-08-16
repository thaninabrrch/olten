<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Covoiturage extends Model
{
    use HasFactory;

    protected $table = 'covoiturages';
    protected $primaryKey = 'covoiturage_id';
    public $timestamps = false;

    protected $fillable = [
        'conducteur_id',
        'depart',
        'destination',
        'date_depart',
        'heure_depart',
        'nb_places',
        'prix_place',
        'commission_plateforme',
        'statut',
        'reglementation_applicable',
        'prix_total_affiche',
        'retour',
        'itineraire',
        'segments',
        'photo_conducteur',
        'message_conducteur',
        'passenger_mode',
        'selected_route',
        'selected_route_index',
        'return_trip_data',
        'return_date',
        'return_time',
        'return_itinerary',
        'booking_mode'
    ];

    protected $casts = [
        'date_depart' => 'datetime',
        'retour' => 'boolean',
        'itineraire' => 'array',
        'segments' => 'array',
        'prix_place' => 'float',
        'commission_plateforme' => 'float',
        'prix_total_affiche' => 'float',
        'nb_places' => 'integer',
        'selected_route' => 'array',
        'retour' => 'boolean',
        'return_trip_data' => 'array',
        'return_date' => 'datetime',
        'return_time' => 'string',
        'return_itinerary' => 'array',
    ];

    public function conducteur()
    {
        return $this->belongsTo(User::class, 'conducteur_id');
    }

    public function photoConducteur(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? asset('storage/' . $value) : null
        );
    }
}
