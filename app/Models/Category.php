<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'description', 'image', 'service_id', 'slug', 'icon'];

    /**
     * Une categorie est une sous-partie d'un service : c'est son slug qui
     * la designe dans l'URL (/vente/vehicules ou /vente?category=vehicules).
     * Le slug est donc toujours normalise, meme saisi a la main dans l'admin.
     */
    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            $category->slug = Str::slug($category->slug ?: $category->nom);
        });
    }

    /**
     * Glyphe par defaut si l'admin n'a pas renseigne d'icone.
     * Les deux librairies (Font Awesome et Bootstrap Icons) sont chargees
     * par le layout : la valeur stockee est utilisee telle quelle.
     */
    public function getIconClassAttribute(): string
    {
        return $this->icon ?: 'fa-solid fa-tag';
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Categories proposables au depot d'une offre (annonce ou produit).
     *
     * Le covoiturage a son propre parcours — on publie un trajet depuis
     * « Mes trajets », avec ses places, son itineraire et ses horaires — et
     * n'a donc rien a faire dans le selecteur d'une annonce ou d'un produit.
     * Les categories sans service restent proposees : les exclure ferait
     * disparaitre le groupe « Autres ».
     */
    public function scopePublishable($query)
    {
        return $query->whereDoesntHave('service', function ($q) {
            $q->where('slug', 'covoiturage');
        });
    }

    /**
     * Une categorie de vente ne se loue pas : pas de periode de disponibilite,
     * et un prix ferme au lieu d'un tarif journalier.
     */
    public function isVente(): bool
    {
        return $this->service?->slug === 'vente';
    }

    public function objets()
    {
        return $this->hasMany(Objet::class, 'categorie_id');
    }

    public function ads()
    {
        return $this->hasMany(Ad::class, 'category_id');
    }

    /**
     * Annonces publiees (visibles par les visiteurs).
     */
    public function approvedAds()
    {
        return $this->ads()->where('is_approved', true);
    }

    /**
     * Une categorie porte aussi des produits a la vente : ils appartiennent
     * au meme service qu'elle et s'affichent dans la meme grille.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Produits en ligne (visibles par les visiteurs).
     */
    public function activeProducts()
    {
        return $this->products()->where('is_active', true);
    }
}
