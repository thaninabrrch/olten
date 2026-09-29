<?php

namespace App\Support;

use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

/**
 * Photos deposees par les membres (annonces, produits).
 *
 * Le fichier recu n'est jamais stocke tel quel : une photo de telephone pese
 * plusieurs Mo pour s'afficher dans une vignette. Elle est ramenee a une
 * taille d'affichage et reencodee en WebP avant d'arriver sur le disque
 * public, ce qui permet d'accepter des photos lourdes sans alourdir les pages.
 */
class ImageUpload
{
    /** Plus grand cote de l'image stockee, en pixels. */
    private const MAX_SIDE = 1920;

    private const QUALITY = 80;

    /** Poids maximal du fichier envoye, en Ko (regle `max` de Laravel). */
    private const MAX_UPLOAD_KB = 20480;

    /**
     * GD decompresse l'image entiere en memoire (environ 4 octets par pixel)
     * avant de la reduire : c'est le nombre de pixels, et non le poids du
     * fichier, qui fixe la memoire necessaire. Un PNG de quelques Ko peut
     * annoncer 30 000 x 30 000 pixels : il est refuse avant d'etre decode.
     */
    private const MAX_PIXELS = 50_000_000;

    private const MEMORY_LIMIT = '512M';

    /**
     * Regles de validation d'un fichier photo, a placer sur `images.*`.
     */
    public static function rules(): array
    {
        return [
            'bail',
            'image',
            'mimes:jpeg,jpg,png,gif,webp',
            'max:' . self::MAX_UPLOAD_KB,
            function (string $attribute, mixed $value, Closure $fail) {
                [$width, $height] = @getimagesize($value->getRealPath()) ?: [0, 0];

                if ($width * $height > self::MAX_PIXELS) {
                    $fail("Une photo dépasse 50 mégapixels : réduisez sa résolution avant de l'envoyer.");
                }
            },
        ];
    }

    public static function messages(string $field): array
    {
        return [
            // Fichier bloque par PHP (upload_max_filesize) avant d'atteindre Laravel
            "$field.uploaded" => "Une photo n'a pas pu être envoyée : elle dépasse la taille acceptée par le serveur.",
            "$field.image"    => 'Chaque fichier doit être une image.',
            "$field.mimes"    => 'Les photos doivent être au format JPEG, PNG, GIF ou WebP.',
            "$field.max"      => 'Chaque photo ne peut pas dépasser 20 Mo.',
        ];
    }

    /**
     * Reduit, redresse et convertit la photo, puis l'ecrit dans $directory sur
     * le disque public. Retourne le chemin a enregistrer en base.
     */
    public static function store(UploadedFile $file, string $directory): string
    {
        self::raiseMemoryLimit();

        // Le redressement EXIF se fait apres la reduction et non au decodage :
        // tourner l'original doublerait la memoire occupee. Le cadre etant
        // carre, l'ordre ne change pas la taille finale.
        // Un GIF anime n'est pas conserve : seule sa premiere image est lue.
        $encoded = ImageManager::gd(autoOrientation: false, decodeAnimation: false)
            ->read($file)
            ->scaleDown(self::MAX_SIDE, self::MAX_SIDE)
            ->orient()
            ->toWebp(quality: self::QUALITY);

        $path = $directory . '/' . Str::random(40) . '.webp';

        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    private static function raiseMemoryLimit(): void
    {
        $current = ini_parse_quantity(ini_get('memory_limit'));

        if ($current !== -1 && $current < ini_parse_quantity(self::MEMORY_LIMIT)) {
            ini_set('memory_limit', self::MEMORY_LIMIT);
        }
    }
}
