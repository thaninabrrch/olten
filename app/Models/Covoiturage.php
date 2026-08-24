<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Covoiturage extends Model
{
    use HasFactory;

    protected $table = 'covoiturages';
    protected $primaryKey = 'covoiturage_id';
    public $timestamps = false;

    protected $fillable = [
        'conducteur_id',
        'service_id',
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

    /**
     * Slug du service auquel tout trajet appartient.
     */
    public const SERVICE_SLUG = 'covoiturage';

    /**
     * Un trajet est toujours rattache au service « covoiturage ». Le lien est
     * pose ici plutot que dans le controleur pour couvrir tous les chemins de
     * creation (publication, duplication, seeds, back-office).
     */
    protected static function booted(): void
    {
        static::creating(function (Covoiturage $covoiturage) {
            $covoiturage->service_id ??= Service::where('slug', self::SERVICE_SLUG)->value('id');
        });
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function conducteur()
    {
        return $this->belongsTo(User::class, 'conducteur_id');
    }

    /**
     * Les champs `depart` et `destination` contiennent l'adresse geocodee
     * complete (« Lyon, Metropole de Lyon, Rhone, ... »), utile pour la carte
     * mais illisible sur une vignette : on n'en garde que la ville.
     */
    public function departVille(): Attribute
    {
        return Attribute::get(fn () => self::villeCourte($this->depart));
    }

    public function destinationVille(): Attribute
    {
        return Attribute::get(fn () => self::villeCourte($this->destination));
    }

    public static function villeCourte(?string $adresse): string
    {
        $adresse = trim((string) $adresse);

        return trim(Str::before($adresse, ',')) ?: $adresse;
    }

    public function photoConducteur(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? asset('storage/' . $value) : null
        );
    }
}
