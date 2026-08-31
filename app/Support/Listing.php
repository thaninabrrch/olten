<?php

namespace App\Support;

use App\Models\Ad;
use App\Models\Product;
use App\Models\User;

/**
 * Annonces et produits partagent les memes pages service, mais vivent dans
 * deux tables aux colonnes differentes : une annonce se loue a la journee
 * (`price_per_day`), un produit se vend a l'unite (`price`, `stock`).
 *
 * Cette classe ramene les deux au meme jeu de champs pour la grille, et
 * surtout porte le `type` : c'est lui qui permet de les distinguer partout
 * ensuite (badge, libelle de prix, lien de detail, favori).
 */
class Listing
{
    public const ANNONCE = 'annonce';

    public const PRODUIT = 'produit';

    public static function fromAd(Ad $ad): array
    {
        return [
            'type'         => self::ANNONCE,
            'type_label'   => 'Annonce',
            'id'           => $ad->id,
            'title'        => $ad->title,
            'image'        => $ad->images->first()
                                ? asset('storage/' . $ad->images->first()->path)
                                : asset('assets/images/no-image.jpg'),
            'category'     => $ad->category,
            'address'      => $ad->address,
            'views'        => (int) $ad->views,
            'created_at'   => $ad->created_at,
            'price'        => (float) $ad->price_per_day,
            'price_label'  => 'À partir de',
            'price_suffix' => '/ jour',
            'delivery'     => (bool) $ad->delivery_active,
            'stock'        => null,
            'latitude'     => $ad->latitude,
            'longitude'    => $ad->longitude,
            'url'          => route('ads.show', $ad),
            'owner'        => $ad->user?->name,
            'owner_photo'  => self::photo($ad->user),
            // Type attendu par le gestionnaire de favoris (assets/js/script.js)
            'favorite'     => 'ad',
        ];
    }

    public static function fromProduct(Product $product): array
    {
        return [
            'type'         => self::PRODUIT,
            'type_label'   => 'Produit',
            'id'           => $product->id,
            'title'        => $product->name,
            'image'        => $product->images->first()
                                ? asset('storage/' . $product->images->first()->image)
                                : asset('assets/images/no-image.jpg'),
            'category'     => $product->category,
            'address'      => $product->address,
            'views'        => (int) $product->views,
            'created_at'   => $product->created_at,
            'price'        => (float) $product->price,
            'price_label'  => 'Prix',
            'price_suffix' => "l'unité",
            'delivery'     => (bool) $product->delivery_available,
            'stock'        => (int) $product->stock,
            'latitude'     => $product->latitude,
            'longitude'    => $product->longitude,
            'url'          => route('products.show', $product),
            'owner'        => $product->user?->name,
            'owner_photo'  => self::photo($product->user),
            'favorite'     => 'product',
        ];
    }

    /**
     * Photo du proprietaire de l'offre, avec l'avatar par defaut de la
     * plateforme : une carte qui montre qui loue affiche toujours un
     * visage, meme quand le membre n'a pas renseigne de photo.
     */
    private static function photo(?User $user): string
    {
        return $user?->profile_photo
            ? asset('storage/' . $user->profile_photo)
            : asset('assets/images/user-profile.webp');
    }
}
