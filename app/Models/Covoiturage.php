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
        'places_reservees_aller' => 'integer',
        'places_reservees_retour' => 'integer',
    ];

    /**
     * Slug du service auquel tout trajet appartient.
     */
    public const SERVICE_SLUG = 'covoiturage';

    /**
     * Prix affiché d'une place en SQL : le total quand le conducteur en
     * publie un, le prix par place sinon (même règle que seat_price).
     */
    public const PRICE_SQL = 'COALESCE(NULLIF(prix_total_affiche, 0), prix_place)';

    /**
     * Places libres en SQL, sur le sens le moins rempli (même règle que
     * seatsLeft()). CASE plutôt que LEAST/GREATEST : SQLite ne les a pas.
     */
    public const SEATS_LEFT_SQL = 'nb_places - CASE WHEN retour AND places_reservees_retour < places_reservees_aller'
        . ' THEN places_reservees_retour ELSE places_reservees_aller END';

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

        // Ville de départ et d'arrivée en slug : les listes regroupent les
        // trajets par liaison et les alertes les comparent en SQL.
        static::saving(function (Covoiturage $covoiturage) {
            if ($covoiturage->isDirty('depart') || ! $covoiturage->depart_slug) {
                $covoiturage->depart_slug = self::citySlug($covoiturage->depart);
            }

            if ($covoiturage->isDirty('destination') || ! $covoiturage->destination_slug) {
                $covoiturage->destination_slug = self::citySlug($covoiturage->destination);
            }
        });

        // Nouveau trajet : les membres qui guettent cette liaison sont
        // prévenus (cloche du header). Un échec d'envoi ne doit pas faire
        // échouer la publication, le trajet est déjà enregistré.
        static::created(function (Covoiturage $covoiturage) {
            try {
                TripAlert::notifyFor($covoiturage);
            } catch (\Throwable $e) {
                report($e);
            }
        });

        // Trajet qui change de liaison ou de jour de départ, ou qui est remis
        // en ligne : c'est une offre nouvelle pour ceux qui guettent sa
        // liaison et ce jour-là, ils sont prévenus comme à la publication.
        // (Un trajet réservé ne peut plus changer de date ni d'itinéraire.)
        static::updated(function (Covoiturage $covoiturage) {
            $moved = $covoiturage->wasChanged(['depart_slug', 'destination_slug'])
                || $covoiturage->getOriginal('date_depart')?->toDateString() !== $covoiturage->date_depart?->toDateString()
                || ($covoiturage->wasChanged('statut') && $covoiturage->getOriginal('statut') === 'inactif');

            if (! $moved) {
                return;
            }

            try {
                TripAlert::notifyFor($covoiturage, updated: true);
            } catch (\Throwable $e) {
                report($e);
            }
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

    /** Trajets où il reste au moins $seats places sur un sens. */
    public function scopeWithSeats(Builder $query, int $seats = 1): Builder
    {
        return $query->whereRaw(self::SEATS_LEFT_SQL . ' >= ?', [max(1, $seats)]);
    }

    /** Trajets d'une liaison, comparée par ville (« Lyon » = « Lyon, Rhône, … »). */
    public function scopeOnRoute(Builder $query, string $from, string $to): Builder
    {
        return $query->where('depart_slug', self::citySlug($from))
                     ->where('destination_slug', self::citySlug($to));
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

    /** Clé de comparaison d'une ville : « Évry, Essonne, … » → « evry ». */
    public static function citySlug(?string $adresse): string
    {
        return Str::slug(self::villeCourte($adresse));
    }

    public function photoConducteur(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? asset('storage/' . $value) : null
        );
    }



    public function bookings() { return $this->hasMany(TripBooking::class, 'trip_id', 'covoiturage_id'); }
    public function driver()   { return $this->belongsTo(User::class, 'conducteur_id'); }
    /**
     * Réservations payées et non remboursées : confirmées, ou en attente de
     * l'accord du conducteur. Les unes comme les autres occupent leur place ;
     * une annulée, refusée ou expirée la libère.
     */
    public function paidBookings()
    {
        return $this->bookings()->whereIn('status', TripBooking::HOLDING);
    }

    /** Demandes en attente de l'accord du conducteur (validation manuelle). */
    public function pendingBookings()
    {
        return $this->bookings()->where('status', 'pending');
    }

    /** Validation manuelle : chaque réservation attend l'accord du conducteur. */
    public function isManual(): bool
    {
        return $this->booking_mode === 'manual';
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

    /**
     * Places libres en toutes lettres : « 3 places restantes », ou, quand les
     * deux sens diffèrent, « Aller complet · 1 place au retour ». Un trajet
     * reste en ligne tant qu'un de ses sens a une place (SEATS_LEFT_SQL).
     */
    public function seatsLeftLabel(): string
    {
        $aller  = $this->seatsLeft('aller');
        $retour = $this->retour ? $this->seatsLeft('retour') : $aller;

        if ($aller === $retour) {
            return $aller > 0 ? $aller . ' place' . ($aller > 1 ? 's' : '') . ' restante' . ($aller > 1 ? 's' : '') : 'Complet';
        }

        $leg = fn (int $left, string $where, string $name) => $left > 0
            ? $left . ' place' . ($left > 1 ? 's' : '') . ' ' . $where
            : $name . ' complet';

        return $leg($aller, "à l'aller", 'Aller') . ' · ' . $leg($retour, 'au retour', 'Retour');
    }

    /**
     * Places payées sur un sens, lues sur les compteurs du trajet : aucune
     * requête, et les listes peuvent filtrer dessus en SQL (SEATS_LEFT_SQL).
     */
    public function seatsBooked(string $leg): int
    {
        return (int) ($leg === 'retour' ? $this->places_reservees_retour : $this->places_reservees_aller);
    }

    /**
     * Places payées sur un sens, recomptées sur les réservations elles-mêmes.
     * C'est la source de vérité : le paiement s'y fie, jamais aux compteurs.
     * Chaque réservation porte ses places par sens (seats_aller,
     * seats_retour) : 2 à l'aller et 1 au retour se comptent chacune sur
     * son sens, et la somme se fait en SQL.
     */
    public function countPaidSeats(string $leg): int
    {
        return (int) $this->paidBookings()->sum($leg === 'retour' ? 'seats_retour' : 'seats_aller');
    }

    /**
     * Remet les compteurs d'accord avec les réservations. Appelé par
     * TripBooking à chaque réservation, annulation ou suppression ; la
     * commande `covoiturage:recompter-places` le rejoue sur tous les trajets.
     */
    public function recountSeats(): void
    {
        $this->forceFill([
            'places_reservees_aller'  => $this->countPaidSeats('aller'),
            'places_reservees_retour' => $this->countPaidSeats('retour'),
        ])->saveQuietly();
    }

    /*
    |--------------------------------------------------------------------------
    | Trajet réservé : ce qui ne bouge plus
    |--------------------------------------------------------------------------
    | Dès qu'une place est payée sur un sens, ce sens engage le conducteur :
    | le passager a payé pour une date, un itinéraire et un prix, qui ne
    | changent plus, et le trajet ne peut plus être annulé. Seul l'horaire
    | peut encore glisser, dans la marge de config('carpool.booked_time_shift')
    | minutes, et les passagers concernés en sont prévenus.
    */

    /**
     * Sens sur lesquels au moins une place est payée. Lecture fraîche en base
     * (et non la relation déjà chargée) : la réponse autorise ou refuse une
     * modification.
     *
     * @return string[]
     */
    public function bookedLegs(): array
    {
        return $this->paidBookings()->get(['legs'])
            ->flatMap(fn ($b) => (array) $b->legs)
            ->unique()
            ->values()
            ->all();
    }

    public function isBooked(?string $leg = null): bool
    {
        $legs = $this->bookedLegs();

        return $leg ? in_array($leg, $legs, true) : $legs !== [];
    }

    /** Décalage d'horaire accepté sur un sens réservé, en minutes. */
    public static function bookedTimeShift(): int
    {
        return (int) config('carpool.booked_time_shift', 30);
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
