<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Visuel d'un itineraire de covoiturage.
 *
 * Un trajet ne porte aucune image en base : la vignette est deduite de la
 * ville d'arrivee, avec la possibilite de deposer une photo locale pour
 * remplacer l'image distante (voir config/covoiturage.php).
 */
class RouteImage
{
    private const LOCAL_DIR = 'assets/images/villes';

    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public static function for(string $from, string $to): string
    {
        $slug = Str::slug($to);

        if ($local = self::local($slug)) {
            return $local;
        }

        if ($url = config('covoiturage.cities.' . $slug)) {
            return $url;
        }

        return self::fallback($from . '-' . $to);
    }

    /**
     * Photo deposee dans public/assets/images/villes/ : elle prime sur tout
     * le reste, ce qui permet de personnaliser une destination sans code.
     */
    private static function local(string $slug): ?string
    {
        foreach (self::EXTENSIONS as $ext) {
            $relative = self::LOCAL_DIR . '/' . $slug . '.' . $ext;

            if (is_file(public_path($relative))) {
                return asset($relative);
            }
        }

        return null;
    }

    /**
     * Image du pool generique. Le choix depend de l'itineraire et non du
     * hasard : une meme liaison garde toujours la meme vignette, d'une page
     * a l'autre comme d'une visite a l'autre.
     */
    private static function fallback(string $key): string
    {
        $pool = (array) config('covoiturage.fallback', []);

        if (empty($pool)) {
            return asset('assets/images/no-image.jpg');
        }

        return $pool[crc32(Str::slug($key)) % count($pool)];
    }
}
