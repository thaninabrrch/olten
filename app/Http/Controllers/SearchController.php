<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Category;
use App\Models\Covoiturage;
use App\Models\Product;
use App\Models\Service;
use App\Support\Listing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Recherche globale de la plateforme.
 *
 * La barre du header cherchait sur la page d'accueil : elle rejouait la
 * liste des annonces sans dire ce qu'elle avait cherche, sans compter les
 * resultats et sans moyen de les affiner. Elle arrive maintenant ici, sur
 * une page qui balaie les trois familles d'offres :
 *
 *      annonces (ads) + produits (products) + trajets (covoiturages)
 *
 * Les trois sont ramenees au meme jeu de champs par App\Support\Listing,
 * ce qui permet de les trier, de les compter et de les afficher ensemble.
 */
class SearchController extends Controller
{
    /** Nombre de resultats par page. */
    private const PER_PAGE = 12;

    /** Plafond de marqueurs envoyes a la carte. */
    private const MAP_LIMIT = 200;

    /** Plafond de trajets remontes : ils ne sont pas filtrables par categorie. */
    private const TRIP_LIMIT = 60;

    /** Nombre de suggestions par famille dans l'autocompletion. */
    private const SUGGEST_LIMIT = 5;

    /** Nombre d'offres du bloc « les plus consultees ». */
    private const POPULAR_LIMIT = 4;

    /**
     * Fenetres proposees par la facette « Date de publication ».
     * La valeur est le nombre de jours en arriere.
     */
    private const PERIODS = [
        '24h' => 1,
        '7d'  => 7,
        '30d' => 30,
    ];

    /**
     * Page de resultats.
     *
     * Elle repond aussi en JSON (`count_only=1`) : le rail de filtres
     * annonce en direct le nombre d'offres qu'un reglage donnerait, sans
     * recharger la page (voir assets/js/facets.js).
     */
    public function index(Request $request)
    {
        $services   = Service::orderBy('id')->get();
        $categories = Category::with('service')->orderBy('id')->get();

        $service  = $this->resolveService($request, $services);
        $category = $this->resolveCategory($request, $categories);

        // Une categorie appartient a un service : si les deux se contredisent
        // (lien profond, URL bricolee), c'est la categorie qui gagne, sinon
        // le fil d'Ariane annoncerait un service qui ne contient pas ce
        // qu'on affiche.
        if ($category?->service) {
            $service = $category->service;
        }

        $listings = $this->listings($request, $service, $category);

        if ($request->boolean('count_only')) {
            return response()->json(['total' => $listings->count()]);
        }

        // Volumes par categorie, calcules SANS les criteres de perimetre :
        // une facette doit montrer ou l'on peut aller, pas seulement la
        // case ou l'on se trouve deja.
        $categoryCounts = $this->categoryCounts($request);

        $counts = [
            'total'   => $listings->count(),
            'annonce' => $listings->where('type', Listing::ANNONCE)->count(),
            'produit' => $listings->where('type', Listing::PRODUIT)->count(),
            'trajet'  => $listings->where('type', Listing::TRAJET)->count(),
        ];

        $bounds = $this->priceBounds();

        return view('search.index', [
            'term'            => (string) $request->input('search'),
            'services'        => $services,
            'selectedService' => $service,
            'categories'      => $categories,
            'selectedCategory' => $category,
            'listings'        => $this->paginate($listings, self::PER_PAGE, $request),
            'counts'          => $counts,
            'typeCounts'      => $this->typeCounts($request, $service, $category),
            'serviceCounts'   => $this->serviceCounts($categories, $categoryCounts),
            'categoryCounts'  => $categoryCounts,
            'mapPoints'       => $this->mapPoints($listings),
            'cities'          => $this->cities(),
            'priceBounds'     => $bounds,
            'priceBrackets'   => $this->priceBrackets($bounds['max']),
            'related'         => $this->related($request, $categories, $services),
            'popular'         => $this->popular(),
            'hasFilters'      => $this->hasFilters($request),
        ]);
    }

    /**
     * Autocompletion de la barre de recherche.
     *
     * Deux champs l'appellent, distingues par `field` :
     *   - `search`   : services, categories, annonces, produits, trajets ;
     *   - `location` : villes ou une offre existe reellement.
     *
     * Sans saisie, elle repond quand meme : les categories les plus
     * fournies et les offres les plus vues. Un menu vide au premier clic
     * n'apprend rien au visiteur.
     */
    public function suggest(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q'));

        if ($request->input('field') === 'location') {
            return response()->json(['groups' => $this->cityGroup($term)]);
        }

        $groups = $term === ''
            ? $this->discoverGroups()
            : $this->matchGroups($term);

        return response()->json([
            'term'   => $term,
            'groups' => array_values(array_filter($groups, fn (array $g) => $g['items'] !== [])),
        ]);
    }

    /* ==================================================================
       Perimetre : service et categorie
       ================================================================== */

    /**
     * Le service demande, designe indifferemment par son slug (/recherche
     * ?service=location) ou par son identifiant : le menu du header a
     * longtemps envoye des identifiants, les liens partages le sont encore.
     */
    private function resolveService(Request $request, Collection $services): ?Service
    {
        $value = trim((string) $request->input('service'));

        if ($value === '') {
            return null;
        }

        return $services->first(fn (Service $s) => $s->slug === $value || (string) $s->id === $value);
    }

    private function resolveCategory(Request $request, Collection $categories): ?Category
    {
        $value = trim((string) $request->input('category'));

        if ($value === '') {
            return null;
        }

        return $categories->first(fn (Category $c) => $c->slug === $value || (string) $c->id === $value);
    }

    /**
     * Identifiants de categories couverts par le perimetre choisi.
     * `null` signifie « toute la plateforme » et non « aucune categorie » :
     * les deux ne donnent pas du tout le meme resultat.
     */
    private function scopeIds(?Service $service, ?Category $category): ?array
    {
        if ($category) {
            return [$category->id];
        }

        if ($service) {
            return $service->categories()->pluck('id')->all() ?: [0];
        }

        return null;
    }

    /* ==================================================================
       Resultats
       ================================================================== */

    /**
     * Les trois familles d'offres, filtrees, normalisees puis triees
     * ensemble.
     *
     * Le tri se fait en PHP : les tables n'ont ni les memes colonnes de prix
     * (`price_per_day`, `price`, `prix_place`) ni le meme modele, un ORDER BY
     * commun n'aurait pas de sens.
     */
    private function listings(Request $request, ?Service $service, ?Category $category): Collection
    {
        $type = $request->input('type');
        $ids  = $this->scopeIds($service, $category);

        $listings = collect();

        if ($type === null || $type === Listing::ANNONCE) {
            $listings = $listings->merge(
                $this->adQuery($request, $ids)
                     ->with(['images', 'category.service', 'user'])
                     ->get()
                     ->map(fn (Ad $ad) => Listing::fromAd($ad))
            );
        }

        if ($type === null || $type === Listing::PRODUIT) {
            $listings = $listings->merge(
                $this->productQuery($request, $ids)
                     ->with(['images', 'category.service', 'user'])
                     ->get()
                     ->map(fn (Product $product) => Listing::fromProduct($product))
            );
        }

        if ($type === null || $type === Listing::TRAJET) {
            $listings = $listings->merge(
                $this->tripQuery($request, $service, $category)
                     ->map(fn (Covoiturage $trip) => Listing::fromTrip($trip))
            );
        }

        return $this->sort($listings, $request);
    }

    /**
     * Annonces publiees correspondant aux criteres.
     *
     * `$ids` a null veut dire « toute la plateforme » : la recherche du
     * header n'impose aucun service tant que le visiteur n'en choisit pas.
     */
    private function adQuery(Request $request, ?array $ids): Builder
    {
        $query = Ad::query()->where('is_approved', true);

        if ($ids !== null) {
            $query->whereIn('category_id', $ids);
        }

        if ($search = $this->term($request)) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('summary', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhereHas('category', fn (Builder $c) => $c->where('nom', 'like', '%' . $search . '%'));
            });
        }

        if ($location = $this->text($request, 'location')) {
            $query->where('address', 'like', '%' . $location . '%');
        }

        if ($request->filled('min_price')) {
            $query->where('price_per_day', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_day', '<=', (float) $request->input('max_price'));
        }

        if ($request->boolean('delivery')) {
            $query->where('delivery_active', true);
        }

        if ($days = $this->periodDays($request)) {
            $query->where('created_at', '>=', now()->subDays($days));
        }

        return $query;
    }

    /**
     * Produits en ligne correspondant aux criteres.
     */
    private function productQuery(Request $request, ?array $ids): Builder
    {
        $query = Product::query()->where('is_active', true);

        if ($ids !== null) {
            $query->whereIn('category_id', $ids);
        }

        if ($search = $this->term($request)) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhereHas('category', fn (Builder $c) => $c->where('nom', 'like', '%' . $search . '%'));
            });
        }

        if ($location = $this->text($request, 'location')) {
            $query->where('address', 'like', '%' . $location . '%');
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        if ($request->boolean('delivery')) {
            $query->where('delivery_available', true);
        }

        if ($days = $this->periodDays($request)) {
            $query->where('created_at', '>=', now()->subDays($days));
        }

        return $query;
    }

    /**
     * Trajets a venir correspondant aux criteres.
     *
     * Un trajet n'appartient a aucune categorie et ne se livre pas : des que
     * la recherche porte sur l'un ou l'autre, il sort du perimetre plutot que
     * d'etre propose a tort. La table `covoiturages` n'est pas datee non
     * plus, un filtre sur la date de publication l'ecarte donc aussi.
     */
    private function tripQuery(Request $request, ?Service $service, ?Category $category): Collection
    {
        $horsPerimetre = $category
            || ($service && ! str_starts_with($service->slug, 'covoiturage'))
            || $request->boolean('delivery')
            || $this->periodDays($request);

        if ($horsPerimetre) {
            return collect();
        }

        $query = Covoiturage::query()
            ->where('statut', '!=', 'inactif')
            ->whereDate('date_depart', '>=', now()->startOfDay());

        if ($search = $this->term($request)) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('depart', 'like', '%' . $search . '%')
                  ->orWhere('destination', 'like', '%' . $search . '%')
                  ->orWhere('message_conducteur', 'like', '%' . $search . '%');
            });
        }

        if ($location = $this->text($request, 'location')) {
            $query->where(function (Builder $q) use ($location) {
                $q->where('depart', 'like', '%' . $location . '%')
                  ->orWhere('destination', 'like', '%' . $location . '%');
            });
        }

        // Prix affiche du trajet : le total quand le conducteur en publie un,
        // le prix par place sinon (meme regle que les pages covoiturage).
        $affiche = 'COALESCE(NULLIF(prix_total_affiche, 0), prix_place)';

        if ($request->filled('min_price')) {
            $query->whereRaw($affiche . ' >= ?', [(float) $request->input('min_price')]);
        }

        if ($request->filled('max_price')) {
            $query->whereRaw($affiche . ' <= ?', [(float) $request->input('max_price')]);
        }

        return $query->with('conducteur')
                     ->orderBy('date_depart')
                     ->limit(self::TRIP_LIMIT)
                     ->get();
    }

    /**
     * Ordre des resultats.
     *
     * Par defaut la pertinence quand un mot-cle est saisi, la fraicheur
     * sinon : sans mot-cle il n'y a rien a mesurer, tout se vaudrait et
     * l'ordre paraitrait aleatoire.
     */
    private function sort(Collection $listings, Request $request): Collection
    {
        $sort   = (string) $request->input('sort');
        $search = $this->term($request);

        if ($sort === '') {
            $sort = $search === null ? 'recent' : 'relevance';
        }

        return match ($sort) {
            'price_asc'  => $listings->sortBy('price')->values(),
            'price_desc' => $listings->sortByDesc('price')->values(),
            'popular'    => $listings->sortByDesc('views')->values(),
            'relevance'  => $listings
                ->sortByDesc(fn (array $l) => [
                    $this->score($l, (string) $search),
                    $l['views'],
                    $l['created_at']?->timestamp ?? 0,
                ])
                ->values(),
            default      => $listings->sortByDesc(fn (array $l) => $l['created_at']?->timestamp ?? 0)->values(),
        };
    }

    /**
     * Proximite entre une offre et le mot-cle cherche.
     *
     * Le titre pese plus que le reste : une annonce qui s'appelle « perceuse »
     * repond mieux a « perceuse » qu'une annonce de perceuse citee au detour
     * d'une description. A defaut de titre, la categorie rattrape le coup.
     */
    private function score(array $listing, string $search): int
    {
        if ($search === '') {
            return 0;
        }

        $title  = Str::lower((string) $listing['title']);
        $needle = Str::lower($search);

        return match (true) {
            $title === $needle                     => 100,
            str_starts_with($title, $needle)       => 80,
            str_contains($title, $needle)          => 60,
            Str::contains(Str::lower((string) ($listing['category']->nom ?? '')), $needle) => 40,
            default                                => 20,
        };
    }

    /* ==================================================================
       Facettes : volumes annonces sous chaque critere
       ================================================================== */

    /**
     * Nombre d'offres par categorie, tous criteres appliques SAUF le
     * perimetre (service et categorie) : c'est ce qui permet d'annoncer
     * « Location 42 » a cote d'une facette sur laquelle on n'est pas.
     *
     * @return array<int,int>
     */
    private function categoryCounts(Request $request): array
    {
        $ads = $this->adQuery($request, null)
            ->when($request->input('type') === Listing::PRODUIT, fn (Builder $q) => $q->whereRaw('1 = 0'))
            ->selectRaw('category_id, COUNT(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $products = $this->productQuery($request, null)
            ->when($request->input('type') === Listing::ANNONCE, fn (Builder $q) => $q->whereRaw('1 = 0'))
            ->selectRaw('category_id, COUNT(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $counts = [];

        foreach ([$ads, $products] as $source) {
            foreach ($source as $categoryId => $total) {
                $counts[(int) $categoryId] = ($counts[(int) $categoryId] ?? 0) + (int) $total;
            }
        }

        return $counts;
    }

    /**
     * Nombre d'offres par service, deduit des categories qu'il porte.
     * Le covoiturage n'en a pas : ses trajets se comptent a part.
     *
     * @return array<int,int>
     */
    private function serviceCounts(Collection $categories, array $categoryCounts): array
    {
        $counts = [];

        foreach ($categories as $category) {
            if (! $category->service_id) {
                continue;
            }

            $counts[$category->service_id] = ($counts[$category->service_id] ?? 0)
                + ($categoryCounts[$category->id] ?? 0);
        }

        return $counts;
    }

    /**
     * Volume de chaque famille d'offres, le filtre `type` mis de cote :
     * les onglets « Annonces / Produits / Trajets » doivent afficher leur
     * propre total, pas celui de l'onglet ouvert.
     *
     * @return array<string,int>
     */
    private function typeCounts(Request $request, ?Service $service, ?Category $category): array
    {
        // Une copie sans le critere `type` : `clone` ne suffirait pas, les
        // sacs de parametres sont partages par reference et retirer la cle
        // viderait aussi la requete d'origine.
        $sans = Request::create(
            $request->url(),
            'GET',
            collect($request->query())->except('type')->all()
        );

        $ids = $this->scopeIds($service, $category);

        return [
            'annonce' => $this->adQuery($sans, $ids)->count(),
            'produit' => $this->productQuery($sans, $ids)->count(),
            'trajet'  => $this->tripQuery($sans, $service, $category)->count(),
        ];
    }

    /* ==================================================================
       Elements d'habillage de la page
       ================================================================== */

    /**
     * Offres geolocalisees, au format attendu par Leaflet.
     */
    private function mapPoints(Collection $listings): Collection
    {
        return $listings
            ->filter(fn (array $l) => $l['latitude'] !== null && $l['longitude'] !== null)
            ->take(self::MAP_LIMIT)
            ->map(fn (array $l) => [
                'id'       => $l['id'],
                'type'     => $l['type'],
                'lat'      => (float) $l['latitude'],
                'lng'      => (float) $l['longitude'],
                'title'    => $l['title'],
                'category' => $l['category']->nom ?? '',
                'address'  => $l['address'],
                'price'    => number_format($l['price'], 2, ',', ' ') . ' €',
                'image'    => $l['image'],
                'url'      => $l['url'],
            ])
            ->values();
    }

    /**
     * Villes ou une offre existe, toutes familles confondues.
     *
     * Les adresses sont geocodees a l'envers, du plus precis au plus large
     * (« Lyon, Metropole de Lyon, Rhone, ..., France ») : c'est le PREMIER
     * segment qui approche la ville, le dernier donnant le pays. Un segment
     * purement numerique est un code postal, on passe au suivant.
     *
     * Meme regle que `Covoiturage::villeCourte()`, qui nomme deja les villes
     * sur les cartes de trajet : une meme adresse doit s'ecrire pareil
     * partout sur la plateforme.
     */
    private function cities(): Collection
    {
        $villes = Ad::where('is_approved', true)->whereNotNull('address')->pluck('address')
            ->merge(Product::where('is_active', true)->whereNotNull('address')->pluck('address'))
            ->map(fn (string $address) => $this->cityOf($address))
            ->merge(
                Covoiturage::where('statut', '!=', 'inactif')
                    ->whereDate('date_depart', '>=', now()->startOfDay())
                    ->get(['covoiturage_id', 'depart', 'destination'])
                    ->flatMap(fn (Covoiturage $t) => [$t->depart_ville, $t->destination_ville])
            );

        return $villes->filter()
                      ->unique(fn (string $ville) => Str::lower($ville))
                      ->sort(SORT_NATURAL | SORT_FLAG_CASE)
                      ->values();
    }

    /**
     * Nom de ville lisible tire d'une adresse geocodee.
     */
    private function cityOf(string $address): ?string
    {
        $parts = array_values(array_filter(array_map('trim', explode(',', $address))));

        if ($parts === []) {
            return null;
        }

        foreach ($parts as $part) {
            if (! ctype_digit($part)) {
                return $part;
            }
        }

        return $parts[0];
    }

    /**
     * Bornes du curseur de prix, calculees sur tout le catalogue.
     *
     * Volontairement independantes des filtres en cours : des bornes qui
     * suivraient la recherche se resserreraient autour du prix deja choisi,
     * et la poignee se retrouverait collee a son propre reglage.
     */
    private function priceBounds(): array
    {
        $max = max(
            (float) (Ad::where('is_approved', true)->max('price_per_day') ?? 0),
            (float) (Product::where('is_active', true)->max('price') ?? 0),
        );

        return [
            'min' => 0,
            'max' => $max > 0 ? (int) (ceil($max / 100) * 100) : 1000,
        ];
    }

    /**
     * Les quatre fourchettes proposees d'un clic sous le curseur de prix.
     */
    private function priceBrackets(int $max): array
    {
        $pas = $this->roundPrice(max($max / 4, 1));

        return [
            ['label' => 'Moins de ' . $this->formatPrice($pas), 'min' => null, 'max' => $pas],
            ['label' => $this->formatPrice($pas) . ' – ' . $this->formatPrice($pas * 2), 'min' => $pas, 'max' => $pas * 2],
            ['label' => $this->formatPrice($pas * 2) . ' – ' . $this->formatPrice($pas * 4), 'min' => $pas * 2, 'max' => $pas * 4],
            ['label' => 'Plus de ' . $this->formatPrice($pas * 4), 'min' => $pas * 4, 'max' => null],
        ];
    }

    /**
     * Arrondit a un palier « rond » (1, 2 ou 5 fois une puissance de dix) :
     * « Moins de 2 500 € » se lit mieux que « Moins de 2 437 € ».
     */
    private function roundPrice(float $valeur): int
    {
        $puissance = 10 ** max(0, (int) floor(log10(max($valeur, 1))));

        foreach ([5, 2, 1] as $facteur) {
            if ($valeur >= $facteur * $puissance) {
                return (int) ($facteur * $puissance);
            }
        }

        return (int) max(1, $puissance);
    }

    private function formatPrice(int $valeur): string
    {
        return number_format($valeur, 0, ',', ' ') . ' €';
    }

    /**
     * Pistes proposees sous la barre : les categories et les services dont
     * le nom repond au mot-cle. Sans mot-cle, les categories les mieux
     * fournies — il faut bien proposer un point de depart.
     */
    private function related(Request $request, Collection $categories, Collection $services): array
    {
        $term = $this->term($request);

        $volumes = $this->categoryCounts(new Request());

        $pistes = $categories
            ->when($term !== null, fn (Collection $c) => $c->filter(
                fn (Category $cat) => Str::contains(Str::lower($cat->nom), Str::lower((string) $term))
            ))
            ->sortByDesc(fn (Category $cat) => $volumes[$cat->id] ?? 0)
            ->take(8)
            ->map(fn (Category $cat) => [
                'label' => $cat->nom,
                'icon'  => $cat->icon_class,
                'count' => $volumes[$cat->id] ?? 0,
                'url'   => route('search', array_filter([
                    'search'   => $term,
                    'category' => $cat->slug,
                ])),
            ])
            ->values()
            ->all();

        // Un mot-cle qui ne ressemble a aucune categorie ne doit pas laisser
        // la ligne vide : on retombe sur les services, toujours pertinents.
        if ($pistes === []) {
            $pistes = $services->take(6)->map(fn (Service $s) => [
                'label' => $s->display_name,
                'icon'  => $s->icon_class,
                'count' => null,
                'url'   => route('search', ['service' => $s->slug]),
            ])->values()->all();
        }

        return $pistes;
    }

    /**
     * Les offres les plus consultees de la plateforme : elles remplissent
     * la page quand une recherche ne donne rien.
     */
    private function popular(): Collection
    {
        $ads = Ad::with(['images', 'category', 'user'])
            ->where('is_approved', true)
            ->where('views', '>', 0)
            ->orderByDesc('views')
            ->limit(self::POPULAR_LIMIT)
            ->get()
            ->map(fn (Ad $ad) => Listing::fromAd($ad));

        $products = Product::with(['images', 'category', 'user'])
            ->where('is_active', true)
            ->where('views', '>', 0)
            ->orderByDesc('views')
            ->limit(self::POPULAR_LIMIT)
            ->get()
            ->map(fn (Product $product) => Listing::fromProduct($product));

        return $ads->merge($products)->sortByDesc('views')->take(self::POPULAR_LIMIT)->values();
    }

    /* ==================================================================
       Autocompletion
       ================================================================== */

    /**
     * Menu affiche avant toute saisie : ce que la plateforme propose de
     * mieux, pour amorcer la recherche.
     */
    private function discoverGroups(): array
    {
        $services = Service::orderBy('id')->limit(6)->get()->map(fn (Service $s) => [
            'label' => $s->display_name,
            'sub'   => 'Service',
            'icon'  => $s->icon_class,
            'url'   => route('services.show', $s->slug),
            'term'  => null,
        ])->all();

        // Annonces ET produits : le bloc annonce « les plus consultees » de la
        // plateforme, il ne peut pas n'en montrer qu'une moitie.
        $vues = Ad::with(['images', 'category'])
            ->where('is_approved', true)
            ->where('views', '>', 0)
            ->orderByDesc('views')
            ->limit(self::SUGGEST_LIMIT)
            ->get()
            ->map(fn (Ad $ad) => ['views' => (int) $ad->views] + $this->adItem($ad))
            ->merge(
                Product::with(['images', 'category'])
                    ->where('is_active', true)
                    ->where('views', '>', 0)
                    ->orderByDesc('views')
                    ->limit(self::SUGGEST_LIMIT)
                    ->get()
                    ->map(fn (Product $p) => ['views' => (int) $p->views] + $this->productItem($p))
            )
            ->sortByDesc('views')
            ->take(self::SUGGEST_LIMIT)
            // `views` n'a servi qu'a departager les deux tables : elle n'a
            // rien a faire dans la reponse envoyee au navigateur.
            ->map(fn (array $item) => Arr::except($item, 'views'))
            ->values()
            ->all();

        return [
            ['title' => 'Explorer nos services', 'items' => $services],
            ['title' => 'Les plus consultées',   'items' => $vues],
        ];
    }

    /**
     * Menu affiche pendant la saisie, par familles : d'abord ce qui oriente
     * (service, categorie), ensuite ce qui se consulte directement.
     */
    private function matchGroups(string $term): array
    {
        $like = '%' . $term . '%';

        $services = Service::where('nom', 'like', $like)
            ->orWhere('slug', 'like', $like)
            ->orderBy('id')
            ->limit(3)
            ->get()
            ->map(fn (Service $s) => [
                'label' => $s->display_name,
                'sub'   => 'Service',
                'icon'  => $s->icon_class,
                'url'   => route('services.show', $s->slug),
                'term'  => null,
            ])->all();

        $categories = Category::with('service')
            ->where('nom', 'like', $like)
            ->orderBy('id')
            ->limit(4)
            ->get()
            ->map(fn (Category $c) => [
                'label' => $c->nom,
                'sub'   => $c->service?->display_name ?? 'Catégorie',
                'icon'  => $c->icon_class,
                'url'   => route('search', ['category' => $c->slug]),
                'term'  => null,
            ])->all();

        $ads = Ad::with(['images', 'category'])
            ->where('is_approved', true)
            ->where(fn (Builder $q) => $q->where('title', 'like', $like)->orWhere('summary', 'like', $like))
            ->orderByDesc('views')
            ->limit(self::SUGGEST_LIMIT)
            ->get()
            ->map(fn (Ad $ad) => $this->adItem($ad))
            ->all();

        $products = Product::with(['images', 'category'])
            ->where('is_active', true)
            ->where('name', 'like', $like)
            ->orderByDesc('views')
            ->limit(self::SUGGEST_LIMIT)
            ->get()
            ->map(fn (Product $p) => $this->productItem($p))
            ->all();

        $trips = Covoiturage::where('statut', '!=', 'inactif')
            ->whereDate('date_depart', '>=', now()->startOfDay())
            ->where(fn (Builder $q) => $q->where('depart', 'like', $like)->orWhere('destination', 'like', $like))
            ->orderBy('date_depart')
            ->limit(3)
            ->get()
            ->map(fn (Covoiturage $t) => [
                'label' => $t->depart_ville . ' → ' . $t->destination_ville,
                'sub'   => optional($t->date_depart)->translatedFormat('D j M') . ' · ' . $t->nb_places . ' place' . ($t->nb_places > 1 ? 's' : ''),
                'icon'  => 'fa-solid fa-car-side',
                'price' => number_format((float) ($t->prix_total_affiche ?: $t->prix_place), 0, ',', ' ') . ' €',
                'url'   => route('covoiturage.trip', $t),
                'term'  => null,
            ])->all();

        return [
            ['title' => 'Services',   'items' => $services],
            ['title' => 'Catégories', 'items' => $categories],
            ['title' => 'Annonces',   'items' => $ads],
            ['title' => 'Produits',   'items' => $products],
            ['title' => 'Trajets',    'items' => $trips],
        ];
    }

    private function productItem(Product $product): array
    {
        return [
            'label' => $product->name,
            'sub'   => trim(($product->category?->nom ?? 'Produit') . ($product->address ? ' · ' . $product->address : '')),
            'icon'  => 'fa-solid fa-bag-shopping',
            'image' => $product->images->first() ? asset('storage/' . $product->images->first()->image) : null,
            'price' => number_format((float) $product->price, 0, ',', ' ') . ' €',
            'url'   => route('products.show', $product),
            'term'  => null,
        ];
    }

    private function adItem(Ad $ad): array
    {
        return [
            'label' => $ad->title,
            'sub'   => trim(($ad->category?->nom ?? 'Annonce') . ($ad->address ? ' · ' . $ad->address : '')),
            'icon'  => 'fa-solid fa-key',
            'image' => $ad->images->first() ? asset('storage/' . $ad->images->first()->path) : null,
            'price' => number_format((float) $ad->price_per_day, 0, ',', ' ') . ' €',
            'url'   => route('ads.show', $ad),
            'term'  => null,
        ];
    }

    /**
     * Villes proposees sous le champ « Emplacement ». Elles sont tirees des
     * offres en ligne : on ne propose pas une ville ou il n'y a rien.
     */
    private function cityGroup(string $term): array
    {
        $villes = $this->cities()
            ->when($term !== '', fn (Collection $c) => $c->filter(
                fn (string $ville) => Str::contains(Str::lower($ville), Str::lower($term))
            ))
            ->take(8)
            ->map(fn (string $ville) => [
                'label' => $ville,
                'sub'   => null,
                'icon'  => 'fa-solid fa-location-dot',
                'url'   => null,
                // Une ville ne mene nulle part : elle remplit le champ.
                'term'  => $ville,
            ])
            ->values()
            ->all();

        return $villes === [] ? [] : [['title' => 'Villes', 'items' => $villes]];
    }

    /* ==================================================================
       Utilitaires
       ================================================================== */

    /** Le mot-cle cherche, ou null s'il est vide. */
    private function term(Request $request): ?string
    {
        return $this->text($request, 'search');
    }

    private function text(Request $request, string $key): ?string
    {
        $value = trim((string) $request->input($key));

        return $value === '' ? null : $value;
    }

    /** Nombre de jours couverts par la facette « Date de publication ». */
    private function periodDays(Request $request): ?int
    {
        return self::PERIODS[$request->input('period')] ?? null;
    }

    /** Un critere est-il pose ? Le tri n'en est pas un : il ne retire rien. */
    private function hasFilters(Request $request): bool
    {
        return collect(['search', 'location', 'service', 'category', 'type', 'min_price', 'max_price', 'delivery', 'period'])
            ->contains(fn (string $key) => $request->filled($key));
    }

    /**
     * Pagination d'une collection construite en memoire : le tri et la fusion
     * des trois familles ayant lieu en PHP, elle ne peut pas etre deleguee
     * a SQL.
     */
    private function paginate(Collection $items, int $perPage, Request $request): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
}
