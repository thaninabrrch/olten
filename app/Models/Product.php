<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'user_id',
        'price',
        'stock',
        'description',
        'is_active',
        'views',
        'delivery_available',
        'address',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'delivery_available' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Disponibilite et archives
    |--------------------------------------------------------------------------
    | Un produit n'a pas de date de fin : il quitte la plateforme quand il ne
    | peut plus etre achete, soit parce que son stock est epuise, soit parce
    | que le vendeur l'a mis hors ligne. Il rejoint alors les archives du
    | vendeur, qui le remet en vente en reajustant le stock ou en le
    | reactivant. C'est le pendant de l'expiration d'une annonce.
    */

    /**
     * Produits visibles par les visiteurs : en ligne et en stock. C'est le
     * seul point d'entree des listes publiques.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('is_active'), true)
                     ->where($query->qualifyColumn('stock'), '>', 0);
    }

    /** Produits retires de la plateforme : hors ligne ou epuises. */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q->where($q->qualifyColumn('is_active'), false)
                                                  ->orWhere($q->qualifyColumn('stock'), '<=', 0));
    }

    public function isArchived(): bool
    {
        return ! $this->is_active || (int) $this->stock <= 0;
    }

    /** Raison de l'archivage, telle qu'affichee au vendeur. */
    public function archiveReason(): ?string
    {
        return match (true) {
            (int) $this->stock <= 0 => 'Stock épuisé',
            ! $this->is_active      => 'Hors ligne',
            default                 => null,
        };
    }

    public function sales()
    {
        return $this->hasMany(ProductSale::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'product_id');
    }
}