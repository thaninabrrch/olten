<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    /*
    |--------------------------------------------------------------------------
    | Trajets a venir et archives
    |--------------------------------------------------------------------------
    | Un trajet reste en ligne jusqu'au soir de son jour de depart. Des le
    | lendemain il est passe : il quitte la plateforme et rejoint les
    | archives du conducteur. La regle etait recopiee dans chaque requete
    | publique ; elle n'existe plus qu'ici.
    */

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate($query->qualifyColumn('date_depart'), '>=', today()->toDateString());
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->whereDate($query->qualifyColumn('date_depart'), '<', today()->toDateString());
    }

    public function isPast(): bool
    {
        return $this->date_depart !== null && $this->date_depart->copy()->startOfDay()->lt(today());
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



    public function bookings() { return $this->hasMany(TripBooking::class, 'trip_id', 'covoiturage_id'); }
    public function driver()   { return $this->belongsTo(User::class, 'conducteur_id'); }
    /** Seules les réservations payées occupent une place : une annulée la libère. */
    public function paidBookings()
    {
        return $this->bookings()->where('status', 'paid');
    }

    public function getSeatsLeftAttribute(): int
    {
        return $this->seatsLeft();
    }

    /** Places encore libres sur un sens, ou sur le sens le moins rempli. */
    public function seatsLeft(?string $leg = null): int
    {
        $legs = $leg ? [$leg] : $this->legKeys();

        return collect($legs)
            ->map(fn (string $l) => max(0, (int) $this->nb_places - $this->seatsBooked($l)))
            ->max();
    }

    /** Places payées sur un sens : une réservation peut en compter plusieurs. */
    public function seatsBooked(string $leg): int
    {
        return (int) $this->paidBookings
            ->filter(fn ($b) => in_array($leg, (array) $b->legs, true))
            ->sum('seats');
    }

    /**
     * Un trajet réservé engage le conducteur : il ne peut plus l'annuler, ni
     * en retirer un sens réservé. Seul le passager annule sa réservation.
     *
     * Lecture fraîche en base (et non la relation déjà chargée) : la réponse
     * décide d'une suppression. Le filtre sur le sens se fait en PHP, les
     * requêtes JSON n'étant pas portables entre MySQL, PostgreSQL et SQLite.
     */
    public function isBooked(?string $leg = null): bool
    {
        $bookings = $this->paidBookings()->get();

        return $leg
            ? $bookings->contains(fn ($b) => in_array($leg, (array) $b->legs, true))
            : $bookings->isNotEmpty();
    }

    /** @return string[] les sens proposés : l'aller, et le retour s'il existe */
    public function legKeys(): array
    {
        return $this->retour ? ['aller', 'retour'] : ['aller'];
    }

    /*
    |--------------------------------------------------------------------------
    | Prix d'une place et frais de service
    |--------------------------------------------------------------------------
    | Le conducteur fixe le prix d'une place ; la plateforme y ajoute ses
    | frais de service (config carpool.commission_rate, 20 % par défaut).
    | Le calcul se fait en centimes, comme au paiement : la liste, la fiche
    | et le tunnel de paiement affichent ainsi exactement le même montant.
    */

    /** Prix d'une place sur l'aller, en euros, hors frais de service. */
    public function getSeatPriceAttribute(): float
    {
        return (float) ($this->prix_total_affiche ?: $this->prix_place);
    }

    /** Taux des frais de service, en pourcentage. */
    public static function serviceRate(): float
    {
        return (float) config('carpool.commission_rate');
    }

    public static function serviceFeeCents(int $cents): int
    {
        return (int) round($cents * self::serviceRate() / 100);
    }

    public static function serviceFee(float $amount): float
    {
        return self::serviceFeeCents((int) round($amount * 100)) / 100;
    }
}
