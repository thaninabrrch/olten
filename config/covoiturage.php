<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Visuels des itineraires
    |--------------------------------------------------------------------------
    |
    | Les trajets n'ont pas de photo en base : la vignette d'un itineraire est
    | donc choisie ici, d'apres sa ville d'arrivee.
    |
    | Ordre de resolution (voir App\Support\RouteImage) :
    |   1. un fichier local public/assets/images/villes/{slug}.{jpg,jpeg,png,webp}
    |   2. l'URL declaree pour cette ville dans `cities` ci-dessous
    |   3. a defaut, une image du pool `fallback`, stable pour un itineraire donne
    |
    | Deposer une photo dans public/assets/images/villes/ suffit donc a
    | personnaliser une destination, sans toucher au code.
    |
    | Les URL par defaut pointent vers Unsplash (licence Unsplash : usage
    | commercial autorise, sans attribution obligatoire).
    |
    */

    'cities' => [
        'paris'      => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=900&q=80',
        'lyon'       => 'https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?w=900&q=80',
        'nice'       => 'https://images.unsplash.com/photo-1491166617655-0723a0999cfc?w=900&q=80',
        'lille'      => 'https://images.unsplash.com/photo-1560969184-10fe8719e047?w=900&q=80',
        'bordeaux'   => 'https://images.unsplash.com/photo-1589994965851-a8f479c573a9?w=900&q=80',
        'toulouse'   => 'https://images.unsplash.com/photo-1572252821143-035a024857ac?w=900&q=80',
        'strasbourg' => 'https://images.unsplash.com/photo-1569949381669-ecf31ae8e613?w=900&q=80',
        'bruxelles'  => 'https://images.unsplash.com/photo-1559113202-c916b8e44373?w=900&q=80',
        'geneve'     => 'https://images.unsplash.com/photo-1527866959252-deab85ef7d1b?w=900&q=80',
    ],

    'fallback' => [
        'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=900&q=80',
        'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=900&q=80',
        'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=900&q=80',
        'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=900&q=80',
        'https://images.unsplash.com/photo-1502224562085-639556652f33?w=900&q=80',
        'https://images.unsplash.com/photo-1470770903676-69b98201ea1c?w=900&q=80',
    ],

];
