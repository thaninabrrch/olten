<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'file_path',
        'status',
        'identifier',
        'rejection_reason'
    ];

    /*
    |--------------------------------------------------------------------------
    | Pieces reglementaires du chauffeur
    |--------------------------------------------------------------------------
    |
    | La liste vivait auparavant en quatre exemplaires : le formulaire du
    | chauffeur, la validation du controleur, le filtre du back-office et le
    | libelle affiche a l'administrateur. Ajouter une piece obligeait a penser
    | aux quatre. Elle est desormais decrite ici, une fois.
    |
    | L'ordre du tableau est celui de l'affichage.
    |
    | La cle « for » dit a quels profils la piece est demandee : un livreur
    | n'a pas a transmettre une carte VTC, un chauffeur VTC si.
    */
    public const TYPES = [
        'identity_card' => [
            'label' => "Pièce d'identité",
            'desc'  => 'CNI ou passeport en cours de validité (recto/verso).',
            'icon'  => 'fa-id-card',
            'for'   => ['livreur', 'vtc'],
        ],
        'driver_license' => [
            'label' => 'Permis de conduire',
            'desc'  => 'Permis B en cours de validité, recto et verso.',
            'icon'  => 'fa-id-card-clip',
            'for'   => ['livreur', 'vtc'],
        ],
        'vtc_card' => [
            'label' => 'Carte VTC',
            'desc'  => 'Carte VTC délivrée par la préfecture.',
            'icon'  => 'fa-address-card',
            'for'   => ['vtc'],
        ],
    ];

    /*
    | Pieces qui doivent etre validees par l'administrateur avant d'ouvrir une
    | activite. La piece d'identite reste collectee sans rien conditionner.
    */
    public const REQUIRED_TO_PUBLISH = ['driver_license', 'vtc_card']; // publier un trajet
    public const REQUIRED_TO_DELIVER = ['driver_license'];             // accepter des livraisons

    /* Une reference n'est generee que pour la carte professionnelle */
    public const IDENTIFIED_TYPES = ['vtc_card'];

    public static function types(): array
    {
        return array_keys(self::TYPES);
    }

    /**
     * Profils d'un utilisateur, au sens des pieces a fournir.
     */
    public static function profilesOf($user): array
    {
        $profils = [];

        if ($user->hasRole('livreur')) {
            $profils[] = 'livreur';
        }

        if ($user->hasRole('chauffeur_vtc')) {
            $profils[] = 'vtc';
        }

        return $profils;
    }

    /**
     * Types de pieces demandees a cet utilisateur.
     *
     * Un compte qui n'est ni livreur ni chauffeur voit toute la liste plutot
     * qu'une page vide : il peut basculer a tout moment depuis son profil.
     */
    public static function typesFor($user): array
    {
        $profils = self::profilesOf($user);

        if (! $profils) {
            return self::types();
        }

        return array_keys(array_filter(
            self::TYPES,
            fn ($type) => (bool) array_intersect($type['for'], $profils)
        ));
    }

    /**
     * Pieces qui verrouillent une activite pour cet utilisateur, indexees par
     * type : ['driver_license' => ['accepter des livraisons', 'publier un trajet']]
     */
    public static function gatesFor($user): array
    {
        $profils = self::profilesOf($user);
        $portes  = [];

        if (in_array('livreur', $profils, true)) {
            foreach (self::REQUIRED_TO_DELIVER as $name) {
                $portes[$name][] = 'accepter des livraisons';
            }
        }

        if (in_array('vtc', $profils, true)) {
            foreach (self::REQUIRED_TO_PUBLISH as $name) {
                $portes[$name][] = 'publier un trajet';
            }
        }

        return $portes;
    }

    public static function label(string $name): string
    {
        return self::TYPES[$name]['label'] ?? $name;
    }

    public static function icon(string $name): string
    {
        return self::TYPES[$name]['icon'] ?? 'fa-file-lines';
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
