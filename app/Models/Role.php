<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Role extends Model
{
    protected $fillable = ['name', 'display_name'];

    /**
     * Libelles montres aux membres, quand ils different du display_name.
     *
     * Le role technique « chauffeur_vtc » est celui des conducteurs de
     * covoiturage : le membre lit « Conducteur », comme a l'inscription. Son
     * display_name n'est pas renomme en base : le back-office le distingue
     * ainsi du role « conducteur », qui porte deja ce libelle.
     */
    public const PUBLIC_LABELS = [
        'chauffeur_vtc' => 'Conducteur',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /** Libelle du role dans l'espace membre (tableau de bord). */
    public function publicLabel(): Attribute
    {
        return Attribute::get(fn () => self::PUBLIC_LABELS[$this->name]
            ?? ($this->display_name ?: Str::ucfirst((string) $this->name)));
    }
}
