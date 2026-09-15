<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $table = 'ads';

    protected $fillable = [
        'title',
        'category_id',
        'address',
        'longitude',
        'latitude',
        'price_per_day',
        'delivery_active',
        'client_address',
        'price_per_km',
        'distance_km',
        'delivery_cost',
        'image',
        'user_id',
        'summary',
        'description',
        'available_from',
        'available_until',
        'expires_at',
        'is_approved',
        'views',
        'rejected_at'
    ];

    protected $casts = [
        'available_from' => 'date',
        'available_until' => 'date',
        'expires_at' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Expiration
    |--------------------------------------------------------------------------
    | `expires_at` reprend la fin de la periode de disponibilite. Le dernier
    | jour reste reservable : une annonce n'est expiree qu'a partir du
    | lendemain. Une annonce sans echeance (une vente) n'expire jamais.
    |
    | Cette regle vivait recopiee dans les vues et le controleur, avec des
    | ecarts (`isPast()` rendait une annonce expiree des le matin de son
    | dernier jour). Elle n'existe plus qu'ici.
    */

    /**
     * Annonces encore en cours : sans echeance, ou echeance non depassee.
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        $column = $query->qualifyColumn('expires_at');

        return $query->where(fn (Builder $q) => $q->whereNull($column)
                                                  ->orWhere($column, '>=', today()->toDateString()));
    }

    /**
     * Annonces dont la periode est terminee : elles quittent la plateforme
     * et sont rangees dans les archives de leur proprietaire.
     */
    public function scopeExpired(Builder $query): Builder
    {
        $column = $query->qualifyColumn('expires_at');

        return $query->whereNotNull($column)
                     ->where($column, '<', today()->toDateString());
    }

    /**
     * Annonces visibles par les visiteurs : validees par l'admin et non
     * expirees. C'est le seul point d'entree des listes publiques.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('is_approved'), true)
                     ->notExpired();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->lt(today());
    }

    /*
    |--------------------------------------------------------------------------
    | Prix
    |--------------------------------------------------------------------------
    | La colonne s'appelle `price_per_day` pour des raisons historiques, mais
    | une annonce de vente y porte un prix ferme : ni « / jour », ni
    | « a partir de ».
    */

    public function isVente(): bool
    {
        return $this->category?->isVente() ?? false;
    }

    /** Accroche placee avant le prix (« A partir de » ou « Prix »). */
    public function priceLabel(): string
    {
        return $this->isVente() ? 'Prix' : 'À partir de';
    }

    /** Unite placee apres le prix, vide pour une vente. */
    public function priceSuffix(): string
    {
        return $this->isVente() ? '' : '/ jour';
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function images()
    {
        return $this->hasMany(AdImage::class);
    }
    public function demandes()
    {
        return $this->hasMany(DemandeLivreur::class, 'id_annonce', 'id')
                    ->with('livreur');
    }


    public function reports()
    {
        return $this->hasMany(AdReport::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'ad_id', 'id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}
