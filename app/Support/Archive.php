<?php

namespace App\Support;

use App\Models\Ad;
use App\Models\Covoiturage;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Archives d'un membre : tout ce qu'il a publie et qui a quitte la
 * plateforme.
 *
 *   - une annonce dont la periode de disponibilite est terminee (Ad::scopeExpired)
 *   - un produit epuise ou mis hors ligne (Product::scopeArchived)
 *   - un trajet dont le jour de depart est passe (Covoiturage::scopePast)
 *
 * Les trois vivent dans des tables differentes ; comme Listing pour la
 * grille publique, cette classe les ramene au meme jeu de champs pour une
 * page unique, et porte pour chacun le moyen de le remettre en ligne.
 */
class Archive
{
    public const TYPES = [
        Listing::ANNONCE => 'Annonces',
        Listing::PRODUIT => 'Produits',
        Listing::TRAJET  => 'Trajets',
    ];

    /**
     * Nombre d'elements archives par type, et leur total.
     *
     * @return array{annonce:int, produit:int, trajet:int, total:int}
     */
    public static function counts(User $user): array
    {
        $counts = [
            Listing::ANNONCE => Ad::where('user_id', $user->id)->expired()->count(),
            Listing::PRODUIT => Product::where('user_id', $user->id)->archived()->count(),
            Listing::TRAJET  => Covoiturage::where('conducteur_id', $user->id)->past()->count(),
        ];

        return $counts + ['total' => array_sum($counts)];
    }

    /**
     * Elements archives du membre, du plus recemment archive au plus ancien.
     *
     * @param  string|null  $type  un des TYPES, ou null pour tout
     */
    public static function items(User $user, ?string $type = null, string $search = ''): Collection
    {
        $like  = '%' . $search . '%';
        $items = collect();

        if ($type === null || $type === Listing::ANNONCE) {
            $items = $items->merge(
                Ad::where('user_id', $user->id)
                    ->expired()
                    ->with(['images', 'category.service'])
                    ->when($search !== '', fn ($q) => $q->where('title', 'like', $like))
                    ->get()
                    ->map(fn (Ad $ad) => self::fromAd($ad))
            );
        }

        if ($type === null || $type === Listing::PRODUIT) {
            $items = $items->merge(
                Product::where('user_id', $user->id)
                    ->archived()
                    ->with(['images', 'category'])
                    ->when($search !== '', fn ($q) => $q->where('name', 'like', $like))
                    ->get()
                    ->map(fn (Product $product) => self::fromProduct($product))
            );
        }

        if ($type === null || $type === Listing::TRAJET) {
            $items = $items->merge(
                Covoiturage::where('conducteur_id', $user->id)
                    ->past()
                    ->when($search !== '', fn ($q) => $q->where(
                        fn ($w) => $w->where('depart', 'like', $like)->orWhere('destination', 'like', $like)
                    ))
                    ->get()
                    ->map(fn (Covoiturage $trip) => self::fromTrip($trip))
            );
        }

        return $items
            ->sortByDesc(fn (array $item) => $item['archived_at']?->timestamp ?? 0)
            ->values();
    }

    private static function fromAd(Ad $ad): array
    {
        return [
            'type'          => Listing::ANNONCE,
            'type_label'    => 'Annonce',
            'type_icon'     => 'fa-solid fa-bullhorn',
            'key'           => Listing::ANNONCE . '-' . $ad->id,
            'title'         => $ad->title,
            'image'         => $ad->images->first()
                                 ? asset('storage/' . $ad->images->first()->path)
                                 : asset('assets/images/no-image.jpg'),
            'category'      => $ad->category?->nom ?? 'Sans catégorie',
            'price'         => (float) $ad->price_per_day,
            'price_suffix'  => $ad->priceSuffix(),
            'reason'        => 'Expirée',
            'detail'        => $ad->available_from
                                 ? 'Du ' . $ad->available_from->format('d/m/Y') . ' au ' . $ad->expires_at->format('d/m/Y')
                                 : "Jusqu'au " . $ad->expires_at->format('d/m/Y'),
            'detail_icon'   => 'fa-regular fa-calendar-xmark',
            'views'         => (int) $ad->views,
            'archived_at'   => $ad->expires_at,
            'show_url'      => route('ads.show', $ad),
            'restore_url'   => route('ads.edit', $ad),
            'restore_hint'  => "Choisir de nouvelles dates pour remettre l'annonce en ligne",
            'delete_url'    => route('ads.destroy', $ad),
        ];
    }

    private static function fromProduct(Product $product): array
    {
        $epuise = (int) $product->stock <= 0;

        return [
            'type'          => Listing::PRODUIT,
            'type_label'    => 'Produit',
            'type_icon'     => 'fa-solid fa-bag-shopping',
            'key'           => Listing::PRODUIT . '-' . $product->id,
            'title'         => $product->name,
            'image'         => $product->images->first()
                                 ? asset('storage/' . $product->images->first()->image)
                                 : asset('assets/images/no-image.jpg'),
            'category'      => $product->category?->nom ?? 'Sans catégorie',
            'price'         => (float) $product->price,
            'price_suffix'  => "l'unité",
            'reason'        => $product->archiveReason(),
            'detail'        => $epuise ? 'Stock : 0' : 'Mis hors ligne',
            'detail_icon'   => $epuise ? 'fa-solid fa-cubes' : 'fa-solid fa-eye-slash',
            'views'         => (int) $product->views,
            // Un produit n'a pas de date de fin : sa derniere modification
            // (stock tombe a zero, passage hors ligne) est ce qui l'en
            // approche le plus.
            'archived_at'   => $product->updated_at,
            'show_url'      => route('products.show', $product),
            'restore_url'   => route('seller.produits.edit', $product),
            'restore_hint'  => $epuise
                                 ? 'Réajuster le stock pour remettre le produit en vente'
                                 : 'Réactiver le produit pour le remettre en vente',
            'delete_url'    => route('seller.produits.destroy', $product),
        ];
    }

    private static function fromTrip(Covoiturage $trip): array
    {
        $heure = $trip->heure_depart ? ' à ' . substr((string) $trip->heure_depart, 0, 5) : '';

        return [
            'type'          => Listing::TRAJET,
            'type_label'    => 'Trajet',
            'type_icon'     => 'fa-solid fa-car-side',
            'key'           => Listing::TRAJET . '-' . $trip->covoiturage_id,
            'title'         => $trip->depart_ville . ' → ' . $trip->destination_ville,
            'image'         => RouteImage::for($trip->depart_ville, $trip->destination_ville),
            'category'      => $trip->nb_places . ' place' . ($trip->nb_places > 1 ? 's' : ''),
            'price'         => (float) ($trip->prix_total_affiche ?: $trip->prix_place),
            'price_suffix'  => 'par place',
            'reason'        => 'Trajet passé',
            'detail'        => 'Parti le ' . $trip->date_depart->format('d/m/Y') . $heure,
            'detail_icon'   => 'fa-regular fa-calendar-xmark',
            'views'         => null,
            'archived_at'   => $trip->date_depart,
            'show_url'      => route('trajet.show', ['covoiturage' => $trip->covoiturage_id]),
            'restore_url'   => route('covoiturage.edit-date-time', $trip->covoiturage_id),
            'restore_hint'  => 'Choisir une nouvelle date de départ pour republier le trajet',
            'delete_url'    => route('covoiturage.destroy', $trip->covoiturage_id),
        ];
    }
}
