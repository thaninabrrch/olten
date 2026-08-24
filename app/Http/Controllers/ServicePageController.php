<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ServicePageController extends Controller
{
    /**
     * Designs specifiques : un slug de service => une vue dediee.
     * Tout service absent de cette liste utilise la page standard.
     */
    private const DESIGNS = [
        'covoiturage'         => 'services.covoiturage-service',
        'covoiturage-service' => 'services.covoiturage-service',
        'location'            => 'services.location-voiture',
        'location-voiture'    => 'services.location-voiture',
    ];

    /** Nombre d'annonces par page dans la grille. */
    private const PER_PAGE = 12;

    /** Nombre d'annonces mises en avant dans le bloc « populaires ». */
    private const POPULAR_LIMIT = 4;

    /** Plafond de marqueurs envoyes a la carte. */
    private const MAP_LIMIT = 200;

    /**
     * Vitrine « Nos services » : la liste complete des services de la
     * plateforme, chacun avec ses categories et son volume d'annonces.
     *
     * C'est la page d'atterrissage des liens « Explorer nos services » et
     * « Tous les services ». De la, un clic mene soit au service entier
     * (/vente), soit directement a l'une de ses categories
     * (/vente/telephones-tablettes).
     */
    public function index()
    {
        $services = Service::query()
            ->withCount([
                'categories',
                'ads as ads_count' => fn (Builder $q) => $q->where('ads.is_approved', true),
            ])
            ->with(['categories' => fn ($q) => $q->orderBy('id')])
            ->orderBy('id')
            ->get();

        return view('services.index', [
            'services'      => $services,
            'categoryTotal' => $services->sum('categories_count'),
            'adTotal'       => $services->sum('ads_count'),
        ]);
    }

    /**
     * Point d'entree unique des pages service.
     *
     * La separation entre les differents services se fait par leur slug :
     *   /covoiturage        -> design covoiturage
     *   /location           -> design location
     *   /vente, /livraison  -> page service standard
     *
     * La categorie se choisit indifferemment par le chemin ou par la query :
     *   /vente/telephones-tablettes
     *   /vente?category=telephones-tablettes
     *
     * Si aucun service ne porte ce slug, on retombe sur la page categorie.
     */
    public function show(Request $request, string $slug, ?string $category = null)
    {
        $service = Service::where('slug', $slug)->first();

        if (! $service) {
            abort_if($category !== null, 404);

            return app(HomeController::class)->show($slug);
        }

        if (isset(self::DESIGNS[$service->slug])) {
            return view(self::DESIGNS[$service->slug], [
                'service'    => $service,
                'categories' => $service->categories()->orderBy('id')->get(),
            ]);
        }

        return $this->standard($request, $service, $category);
    }

    /**
     * Page standard : titre du service, ses categories, la carte,
     * les filtres et la grille d'annonces.
     */
    private function standard(Request $request, Service $service, ?string $categorySlug)
    {
        $categories = $service->categories()
            ->withCount(['ads as ads_count' => fn (Builder $q) => $q->where('is_approved', true)])
            ->orderBy('id')
            ->get();

        // La categorie vient du chemin (/vente/vehicules) ou de la query (?category=vehicules)
        $categorySlug = $categorySlug ?: $request->input('category');

        $selectedCategory = $categorySlug
            ? $categories->firstWhere('slug', $categorySlug)
            : null;

        abort_if($categorySlug && ! $selectedCategory, 404);

        $categoryIds = $selectedCategory
            ? [$selectedCategory->id]
            : $categories->pluck('id')->all();

        // Grille filtree + paginee
        $ads = $this->filtered($request, $categoryIds)
                    ->with(['images', 'category'])
                    ->paginate(self::PER_PAGE)
                    ->withQueryString();

        // Marqueurs de la carte : memes filtres, mais sans pagination
        $mapPoints = $this->mapPoints($request, $categoryIds);

        // Villes proposees en autocompletion, deduites des annonces du service
        $cities = $this->cities($categoryIds);

        // Annonces populaires du service (independantes des filtres)
        $popularAds = $this->popular($categoryIds);

        return view('services.service-standard', compact(
            'service',
            'categories',
            'selectedCategory',
            'ads',
            'mapPoints',
            'cities',
            'popularAds'
        ));
    }

    /**
     * Requete de base : annonces publiees du service, filtrees par
     * recherche, ville, prix puis triees.
     */
    private function filtered(Request $request, array $categoryIds): Builder
    {
        $query = Ad::query()
            ->where('is_approved', true)
            ->whereIn('category_id', $categoryIds);

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('summary', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('location')) {
            $query->where('address', 'like', '%' . $request->input('location') . '%');
        }

        if ($request->filled('min_price')) {
            $query->where('price_per_day', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_day', '<=', (float) $request->input('max_price'));
        }

        match ($request->input('sort')) {
            'price_asc'  => $query->orderBy('price_per_day'),
            'price_desc' => $query->orderByDesc('price_per_day'),
            'popular'    => $query->orderByDesc('views'),
            default      => $query->latest(),
        };

        return $query;
    }

    /**
     * Annonces geolocalisees a afficher sur la carte, au format attendu
     * par Leaflet (le layout charge deja la librairie).
     */
    private function mapPoints(Request $request, array $categoryIds): Collection
    {
        return $this->filtered($request, $categoryIds)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with(['images', 'category'])
            ->limit(self::MAP_LIMIT)
            ->get()
            ->map(fn (Ad $ad) => [
                'id'       => $ad->id,
                'lat'      => (float) $ad->latitude,
                'lng'      => (float) $ad->longitude,
                'title'    => $ad->title,
                'category' => $ad->category->nom ?? '',
                'address'  => $ad->address,
                'price'    => number_format((float) $ad->price_per_day, 2, ',', ' ') . ' €',
                'image'    => $ad->images->first()
                                ? asset('storage/' . $ad->images->first()->path)
                                : asset('assets/images/no-image.jpg'),
                'url'      => route('ads.show', $ad),
            ])
            ->values();
    }

    /**
     * Villes distinctes des annonces du service.
     * Les adresses sont libres : on retient le dernier segment, qui porte
     * la ville dans la quasi-totalite des saisies ("12 rue X, Paris").
     */
    private function cities(array $categoryIds): Collection
    {
        return Ad::where('is_approved', true)
            ->whereIn('category_id', $categoryIds)
            ->whereNotNull('address')
            ->pluck('address')
            ->map(function (string $address) {
                $parts = array_filter(array_map('trim', explode(',', $address)));

                return $parts ? (string) end($parts) : null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * Annonces les plus consultees du service.
     * Le bloc reste vide tant qu'aucune annonce n'a ete vue : c'est
     * volontaire, l'etat vide a son propre message.
     */
    private function popular(array $categoryIds): Collection
    {
        return Ad::with(['images', 'category'])
            ->where('is_approved', true)
            ->whereIn('category_id', $categoryIds)
            ->where('views', '>', 0)
            ->orderByDesc('views')
            ->limit(self::POPULAR_LIMIT)
            ->get();
    }
}
