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
