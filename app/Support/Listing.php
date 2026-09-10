<?php

namespace App\Support;

use App\Models\Ad;
use App\Models\Covoiturage;
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

    /**
     * Un trajet de covoiturage. Il ne vit ni dans `ads` ni dans `products`
     * mais remonte dans la recherche globale, qui balaie toute la
     * plateforme : sans lui, chercher « Lyon » ne trouvait rien alors que
     * des conducteurs y partent demain.
     */
    public const TRAJET = 'trajet';

    public static function fromAd(Ad $ad): array
    {
        // Une annonce de vente n'a pas de tarif journalier : le prix affiche
        // est ferme, et l'accroche « A partir de » n'a plus de sens.
        $vente = $ad->category?->isVente() ?? false;

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
            'price_label'  => $vente ? 'Prix' : 'À partir de',
            'price_suffix' => $vente ? '' : '/ jour',
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
     * Un trajet de covoiturage, ramene au meme jeu de champs.
     *
     * Il n'a ni categorie, ni stock, ni compteur de vues : ces champs restent
     * presents mais vides, la carte s'appuyant sur `type` pour savoir quoi
     * afficher. Le prix montre est celui d'une place — c'est ce que paie le
     * passager.
     */
    public static function fromTrip(Covoiturage $trip): array
    {
        return [
            'type'         => self::TRAJET,
            'type_label'   => 'Trajet',
            'id'           => $trip->covoiturage_id,
            'title'        => $trip->depart_ville . ' → ' . $trip->destination_ville,
            'image'        => RouteImage::for($trip->depart_ville, $trip->destination_ville),
            'category'     => null,
            'address'      => $trip->depart_ville,
            'views'        => 0,
            // La table `covoiturages` ne date pas ses lignes : c'est la date
            // de depart qui situe le trajet dans le temps.
            'created_at'   => $trip->date_depart,
            'price'        => (float) ($trip->prix_total_affiche ?: $trip->prix_place),
            'price_label'  => 'Par place',
            'price_suffix' => '',
            'delivery'     => false,
            'stock'        => null,
            'latitude'     => null,
            'longitude'    => null,
            'url'          => route('covoiturage.trip', $trip),
            'owner'        => $trip->conducteur?->name,
            'owner_photo'  => self::photo($trip->conducteur),
            // Un trajet ne se met pas en favori : le bouton coeur ne sait
            // traiter que les annonces et les produits.
            'favorite'     => null,
            'trip_date'    => $trip->date_depart,
            'trip_time'    => $trip->heure_depart,
            'seats'        => (int) $trip->nb_places,
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
