<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    // Colonnes assignables en masse
    protected $fillable = [
        'nom',
        'slug',
        'short_description',
        'description',
        'image',
    ];

    /**
     * Le slug identifie le service : c'est lui qui sépare les différents
     * services (/vente, /location, /covoiturage...) et qui permet au front
     * de choisir le design à afficher.
     */
    protected static function booted(): void
    {
        static::saving(function (Service $service) {
            $service->slug = Str::slug($service->slug ?: $service->nom);
        });
    }

    /**
     * Glyphes Font Awesome par famille de service.
     * La table `services` n'a pas de colonne `icon` : on associe ici un
     * glyphe à chaque mot-clé de slug, du plus spécifique au plus générique.
     */
    private const ICONS = [
        'covoiturage' => 'fas fa-car-side',
        'trajet'      => 'fas fa-car-side',
        'vtc'         => 'fas fa-taxi',
        'livraison'   => 'fas fa-truck-fast',
        'colis'       => 'fas fa-box',
        'location'    => 'fas fa-key',
        'vente'       => 'fas fa-bag-shopping',
        'achat'       => 'fas fa-bag-shopping',
        'appel'       => 'fas fa-file-signature',
        'offre'       => 'fas fa-file-signature',
        'prestation'  => 'fas fa-screwdriver-wrench',
        'service'     => 'fas fa-screwdriver-wrench',
    ];

    /**
     * Classe d'icône à afficher pour ce service (accueil, menus...).
     * Repli générique si aucun mot-clé ne correspond.
     */
    public function getIconClassAttribute(): string
    {
        $haystack = Str::slug($this->slug ?: $this->nom);

        foreach (self::ICONS as $keyword => $icon) {
            if (str_contains($haystack, $keyword)) {
                return $icon;
            }
        }

        return 'fas fa-layer-group';
    }

    /**
     * Relation : un Service a plusieurs Catégories (sous-services)
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Relation : toutes les annonces du service, via ses catégories
     */
    public function ads()
    {
        return $this->hasManyThrough(Ad::class, Category::class, 'service_id', 'category_id');
    }
}
