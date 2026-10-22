<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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
        return $this->forFamily(self::ICONS) ?? 'fas fa-layer-group';
    }

    /**
     * Photo par défaut de chaque famille de service (copies allégées dans
     * public/assets/images/services), tant qu'aucune image n'a été
     * téléversée depuis le back-office.
     */
    private const COVERS = [
        'covoiturage' => 'assets/images/services/covoiturage.jpg',
        'livraison'   => 'assets/images/services/livraison.jpg',
        'location'    => 'assets/images/services/location.jpg',
        'vente'       => 'assets/images/services/vente.jpg',
        'appel'       => 'assets/images/services/appels-offres.jpg',
        'prestation'  => 'assets/images/services/prestations.jpg',
    ];

    /**
     * Image de fond du service : celle du back-office, sinon la photo de
     * sa famille, sinon null (la carte garde alors sa seule couleur).
     * Le fichier téléversé est vérifié : une image supprimée du disque
     * laissait une carte vide.
     */
    public function getCoverUrlAttribute(): ?string
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return asset('storage/' . $this->image);
        }

        $cover = $this->forFamily(self::COVERS);

        return $cover ? asset($cover) : null;
    }

    /**
     * Accroches par défaut par famille de service, pour les cartes de
     * l'accueil tant qu'aucune accroche n'a été saisie en back-office.
     */
    private const TAGLINES = [
        'covoiturage' => 'Partagez vos trajets',
        'livraison'   => 'Colis & courses',
        'location'    => 'Courte & longue durée',
        'vente'       => 'Acheter & vendre',
        'appel'       => 'Publiez votre besoin',
        'prestation'  => 'Pros qualifiés',
    ];

    /**
     * Accroche du service : celle du back-office, sinon celle de sa famille.
     */
    public function getTaglineAttribute(): ?string
    {
        return filled($this->short_description)
            ? $this->short_description
            : $this->forFamily(self::TAGLINES);
    }

    /**
     * Valeur associée au premier mot-clé de la table trouvé dans le slug
     * du service (les tables vont du plus spécifique au plus générique).
     */
    private function forFamily(array $values): ?string
    {
        $haystack = Str::slug($this->slug ?: $this->nom);

        foreach ($values as $keyword => $value) {
            if (str_contains($haystack, $keyword)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Nom affiché. Les noms saisis en back-office ne sont pas toujours
     * capitalisés (« covoiturage », « appels d'offres ») : on relève la
     * première lettre sans toucher au reste — contrairement à ucwords(),
     * qui donnerait « Appels D'offres ».
     */
    public function getDisplayNameAttribute(): string
    {
        return Str::ucfirst($this->nom);
    }

    /**
     * Relation : un Service a plusieurs Catégories (sous-services)
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Relation : les trajets rattachés au service (covoiturage)
     */
    public function covoiturages()
    {
        return $this->hasMany(Covoiturage::class);
    }

    /**
     * Relation : toutes les annonces du service, via ses catégories
     */
    public function ads()
    {
        return $this->hasManyThrough(Ad::class, Category::class, 'service_id', 'category_id');
    }

    /**
     * Relation : tous les produits du service, via ses catégories.
     *
     * Annonces et produits vivent dans deux tables distinctes mais se
     * présentent côte à côte sur la plateforme : compter les unes sans les
     * autres annonçait « 0 annonce » sur un service qui vendait des produits.
     */
    public function products()
    {
        return $this->hasManyThrough(Product::class, Category::class, 'service_id', 'category_id');
    }
}
