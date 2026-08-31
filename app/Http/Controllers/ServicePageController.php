<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Category;
use App\Models\Covoiturage;
use App\Models\Product;
use App\Models\Service;
use App\Support\Listing;
use App\Support\RouteImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ServicePageController extends Controller
{
    /** Nombre d'annonces par page dans la grille. */
    private const PER_PAGE = 12;

    /** Nombre d'annonces mises en avant dans le bloc « populaires ». */
    private const POPULAR_LIMIT = 4;

    /**
     * Libelles de la barre de filtres de la page location, par categorie.
     *
     * On ne loue pas une salle comme une voiture : « date de depart » n'a
     * de sens que pour un vehicule, et le mot-cle attendu n'est pas le meme
     * selon qu'on cherche une berline, un studio ou une camera. Les
     * categories absentes de cette liste prennent les libelles generiques
     * de `locationFields()`.
     */
    private const LOCATION_FIELDS = [
        'voitures'             => ['search' => 'Modèle, marque, mot-clé',            'from' => 'Date de départ',      'to' => 'Date de retour'],
        'utilitaires'          => ['search' => 'Fourgon, camionnette, volume…',      'from' => 'Date de départ',      'to' => 'Date de retour'],
        'motos-scooters'       => ['search' => 'Modèle, cylindrée, marque…',         'from' => 'Date de départ',      'to' => 'Date de retour'],
        'bateaux'              => ['search' => 'Bateau, jet-ski, type de permis…',   'from' => 'Date de départ',      'to' => 'Date de retour'],
        'logements'            => ['search' => 'Ville, quartier, type de logement…', 'from' => "Date d'arrivée",      'to' => 'Date de départ'],
        'salles-evenements'    => ['search' => "Salle, capacité, type d'événement…", 'from' => "Date de l'événement", 'to' => "Fin de l'événement"],
        'materiel-de-chantier' => ['search' => 'Engin, outillage, marque…',          'from' => 'Début du chantier',   'to' => 'Fin du chantier'],
        'materiel-audiovisuel' => ['search' => 'Caméra, micro, éclairage…',          'from' => 'Début de la location', 'to' => 'Fin de la location'],
        'sport-plein-air'      => ['search' => 'Vélo, ski, équipement…',             'from' => 'Début de la location', 'to' => 'Fin de la location'],
    ];


    /** Nombre d'itineraires par page sur la page covoiturage. */
    private const ROUTES_PER_PAGE = 9;

    /** Plafond de points d'un trace envoye a la carte. */
    private const PATH_POINTS = 400;

    /** Nombre de trajets par page sur la liste d'un itineraire. */
    private const TRIPS_PER_PAGE = 10;

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

        // Le covoiturage a sa propre page : ses trajets vivent dans la table
        // `covoiturages` et non dans `ads`, la grille se construit donc a part.
        if (str_starts_with($service->slug, 'covoiturage')) {
            return $this->covoiturage($request, $service);
        }

        // La location garde son design dedie, mais s'alimente comme la page
        // standard : ses categories et ses annonces viennent de la base.
        if (str_starts_with($service->slug, 'location')) {
            return $this->location($request, $service, $category);
        }

        return $this->standard($request, $service, $category);
    }

    /**
     * Page covoiturage : les trajets a venir, regroupes par liaison.
     *
     * Une carte = un itineraire (Paris -> Lyon) et non un trajet : les
     * conducteurs publient souvent la meme liaison a des dates differentes,
     * les empiler ferait une grille de doublons. Le detail d'une liaison est
     * sur `covoiturage.trips`.
     */
    private function covoiturage(Request $request, Service $service)
    {
        $filters = $this->tripFilters($request);

        $trips = $this->tripQuery($filters)->with('conducteur')->get();

        $routes = $this->groupByRoute($trips, $filters['sort']);

        return view('services.covoiturage-service', [
            'service'    => $service,
            'categories' => $service->categories()->orderBy('id')->get(),
            'routes'     => $this->paginate($routes, self::ROUTES_PER_PAGE, $request),
            'tripTotal'  => $trips->count(),
            'filters'    => $filters,
            'stats'      => $this->tripStats(),
            'cities'     => $this->tripCities(),
            'criteria'   => $this->tripCriteria(),
            'hasFilters' => $this->hasTripFilters($filters),
        ]);
    }

    /**
     * Liste des trajets d'une liaison : /covoiturage/trajets?from=Paris&to=Lyon
     *
     * La liaison est designee par ses villes et non par un identifiant : les
     * trajets d'une meme liaison sont publies independamment les uns des
     * autres, il n'existe aucune entite « ligne » a referencer.
     */
    public function trips(Request $request)
    {
        $from = Covoiturage::villeCourte($request->input('from'));
        $to   = Covoiturage::villeCourte($request->input('to'));

        abort_if($from === '' || $to === '', 404);

        $filters = $this->tripFilters($request);

        // L'offre de la liaison, hors recherche : elle nomme la page, donne
        // ses chiffres et borne la barre de recherche. Elle ne doit pas
        // bouger avec les criteres saisis, sinon un filtre trop etroit
        // retirerait du formulaire les valeurs permettant d'en sortir.
        $all = $this->onRoute($this->tripQuery()->get(), $from, $to);

        $trips = $this->sortTrips(
            $this->onRoute($this->tripQuery($filters)->with(['conducteur.vehicle'])->get(), $from, $to),
            $filters['sort']
        );

        return view('services.covoiturage-trajets', [
            'from'       => $all->first()?->depart_ville ?: $from,
            'to'         => $all->first()?->destination_ville ?: $to,
            'trips'      => $this->paginate($trips, self::TRIPS_PER_PAGE, $request),
            'total'      => $trips->count(),
            'routeTotal' => $all->count(),
            'image'      => RouteImage::for($from, $to),
            'filters'    => $filters,
            'criteria'   => $this->tripCriteria($all),
            'hasFilters' => $this->hasTripFilters($filters),
        ]);
    }

    /**
     * Les trajets d'une liaison. Le rapprochement se fait sur la ville et
     * non sur l'adresse complete : les conducteurs saisissent des adresses
     * geocodees, « Lyon » et « Lyon, Metropole de Lyon » sont la meme ville.
     */
    private function onRoute(Collection $trips, string $from, string $to): Collection
    {
        return $trips
            ->filter(fn (Covoiturage $trip) => Str::slug($trip->depart_ville) === Str::slug($from)
                                            && Str::slug($trip->destination_ville) === Str::slug($to))
            ->values();
    }

    /**
     * Tri de la liste d'une liaison. Par defaut l'ordre de la requete (le
     * depart le plus proche) ; au prix, le trajet le moins cher d'abord.
     */
    private function sortTrips(Collection $trips, string $sort): Collection
    {
        return $sort === 'price'
            ? $trips->sortBy(fn (Covoiturage $t) => (float) ($t->prix_total_affiche ?: $t->prix_place))->values()
            : $trips;
    }

    /**
     * Detail public d'un trajet : carte de l'itineraire, recapitulatif
     * tarifaire, segments aller / retour et informations pratiques.
     *
     * Les donnees exposees sont celles de la fiche conducteur
     * (CovoiturageController::show) : segments, escales, distance, duree et
     * geometrie de chaque sens, y compris le retour dont le trace complet
     * est stocke dans `return_trip_data.trajet.geometry`.
     *
     * Un trajet desactive n'est plus expose : son lien ne doit pas rester
     * valable une fois le trajet retire de la plateforme.
     */
    public function trip(Covoiturage $covoiturage)
    {
        abort_if($covoiturage->statut === 'inactif', 404);

        $covoiturage->load('conducteur.vehicle');

        $return = $covoiturage->return_trip_data ?? [];
        $returnTrip = $return['trajet'] ?? [];

        $legs = [
            'aller' => $this->leg(
                key: 'aller',
                label: 'Segment aller',
                from: $covoiturage->depart,
                to: $covoiturage->destination,
                date: $covoiturage->date_depart,
                time: $covoiturage->heure_depart,
                metrics: $covoiturage->selected_route ?? [],
                segments: $covoiturage->segments ?? [],
                total: (float) ($covoiturage->prix_total_affiche ?: $covoiturage->prix_place),
                path: $this->routePath($covoiturage->selected_route, $covoiturage->itineraire),
            ),
        ];

        if ($covoiturage->retour) {
            $legs['retour'] = $this->leg(
                key: 'retour',
                label: 'Segment retour',
                from: $covoiturage->destination,
                to: $covoiturage->depart,
                date: $covoiturage->return_date,
                time: $covoiturage->return_time,
                metrics: $returnTrip,
                segments: $return['pricing'] ?? [],
                total: (float) ($return['total'] ?? 0),
                path: $this->routePath($returnTrip, $covoiturage->return_itinerary),
            );
        }

        return view('services.covoiturage-detail', [
            'trip'  => $covoiturage,
            'legs'  => $legs,
            'total' => collect($legs)->sum('total'),
            'image' => RouteImage::for($covoiturage->depart_ville, $covoiturage->destination_ville),
        ]);
    }

    /**
     * Un sens du trajet, mis en forme pour la vue : villes, adresses
     * completes, horaires de depart et d'arrivee, distance, duree, escales
     * et trace.
     */
    private function leg(
        string $key,
        string $label,
        ?string $from,
        ?string $to,
        $date,
        ?string $time,
        array $metrics,
        array $segments,
        float $total,
        array $path
    ): array {
        $departure = Str::substr((string) $time, 0, 5);
        $duration = (int) round((float) ($metrics['duration'] ?? 0));

        $arrival = null;

        if ($departure !== '' && $duration > 0) {
            $arrival = Carbon::createFromFormat('H:i', $departure)->addSeconds($duration);
        }

        return [
            'key'       => $key,
            'label'     => $label,
            'from'      => Covoiturage::villeCourte($from),
            'to'        => Covoiturage::villeCourte($to),
            'address'   => ['from' => $from, 'to' => $to],
            'date'      => $date,
            'time'      => $departure ?: null,
            'arrival'   => $arrival?->format('H:i'),
            // Une arrivee au dela de minuit tombe le lendemain : sans ce
            // repere, un depart a 23h03 et une arrivee a 08h16 se lisent
            // comme un trajet de la veille.
            'next_day'  => $arrival && $departure !== '' && $arrival->format('H:i') < $departure,
            'distance'  => (float) ($metrics['distance'] ?? 0),
            'duration'  => $duration,
            'segments'  => array_values($segments),
            'total'     => $total,
            'path'      => $path,
        ];
    }

    /**
     * Trace a dessiner sur la carte, en [latitude, longitude].
     *
     * On prefere la geometrie calculee par le moteur d'itineraire ; a defaut
     * (trajet retour, trajet ancien), on relie les points saisis, ce qui
     * donne un trace approximatif mais toujours coherent.
     */
    private function routePath(?array $metrics, ?array $itineraire): array
    {
        // Aller comme retour, le moteur d'itineraire stocke son trace au meme
        // endroit : `geometry.coordinates` (voir la fiche conducteur).
        $coordinates = $metrics['geometry']['coordinates'] ?? null;

        if (is_array($coordinates) && count($coordinates) > 1) {
            // GeoJSON stocke [longitude, latitude] : Leaflet attend l'inverse.
            $path = array_values(array_map(
                fn ($point) => [(float) ($point[1] ?? 0), (float) ($point[0] ?? 0)],
                $coordinates
            ));

            return $this->simplify($path);
        }

        return collect($itineraire ?? [])
            ->map(fn ($point) => isset($point['latlng'][0], $point['latlng'][1])
                ? [(float) $point['latlng'][0], (float) $point['latlng'][1]]
                : null)
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Allege un trace avant de l'envoyer au navigateur.
     *
     * Le moteur d'itineraire renvoie plusieurs milliers de points pour un
     * long trajet : les integrer tels quels ferait plusieurs centaines de
     * kilo-octets dans la page pour un rendu identique a l'ecran. On garde
     * un point sur N, plus les deux extremites.
     */
    private function simplify(array $path, int $max = self::PATH_POINTS): array
    {
        $count = count($path);

        if ($count <= $max) {
            return $path;
        }

        $step = (int) ceil($count / $max);

        $simplified = [];

        for ($i = 0; $i < $count; $i += $step) {
            $simplified[] = $path[$i];
        }

        $simplified[] = $path[$count - 1];

        return $simplified;
    }

    /**
     * Criteres de la barre de recherche, communs aux deux pages.
     */
    private function tripFilters(Request $request): array
    {
        return [
            'departure'  => trim((string) $request->input('departure')),
            'arrival'    => trim((string) $request->input('arrival')),
            'start_date' => $this->asDate($request->input('start_date')),
            'end_date'   => $this->asDate($request->input('end_date')),
            'persons'    => max(0, (int) $request->input('persons')),
            'max_price'  => max(0.0, (float) $request->input('max_price')),
            'sort'       => $request->input('sort') === 'price' ? 'price' : '',
        ];
    }

    /**
     * Une recherche est-elle en cours ? Le tri n'en fait pas partie : il
     * ordonne les liaisons, il n'en retire aucune.
     */
    private function hasTripFilters(array $filters): bool
    {
        return collect($filters)->except('sort')->filter()->isNotEmpty();
    }

    /**
     * Bornes reelles de l'offre, pour que la barre de recherche ne propose
     * que des criteres qui existent : on ne demande pas 6 places quand le
     * trajet le plus large en compte 4, ni un prix plafond hors de portee.
     *
     * Sans argument, elle decrit tout le service ; avec, la liaison ouverte.
     */
    private function tripCriteria(?Collection $trips = null): array
    {
        $trips ??= $this->tripQuery()->get(['covoiturage_id', 'nb_places', 'prix_place', 'prix_total_affiche']);

        $prices = $trips->map(fn (Covoiturage $t) => (float) ($t->prix_total_affiche ?: $t->prix_place))
                        ->filter(fn (float $price) => $price > 0);

        return [
            'seats'     => (int) $trips->max('nb_places'),
            'min_price' => $prices->min(),
            'max_price' => $prices->max(),
        ];
    }

    /**
     * Trajets exposes au public : a venir et non desactives. Un trajet dont
     * la date est passee n'a plus a etre reservable.
     */
    private function tripQuery(array $filters = []): Builder
    {
        return Covoiturage::query()
            ->where('statut', '!=', 'inactif')
            ->whereDate('date_depart', '>=', now()->startOfDay())
            ->when($filters['departure'] ?? null, fn (Builder $q, $v) => $q->where('depart', 'like', '%' . $v . '%'))
            ->when($filters['arrival'] ?? null, fn (Builder $q, $v) => $q->where('destination', 'like', '%' . $v . '%'))
            ->when($filters['start_date'] ?? null, fn (Builder $q, $v) => $q->whereDate('date_depart', '>=', $v))
            ->when($filters['end_date'] ?? null, fn (Builder $q, $v) => $q->whereDate('date_depart', '<=', $v))
            ->when(($filters['persons'] ?? 0) > 0, fn (Builder $q) => $q->where('nb_places', '>=', $filters['persons']))
            // Prix affiche du trajet : le total quand le conducteur en publie
            // un, le prix par place sinon (meme regle que les cartes).
            ->when(($filters['max_price'] ?? 0) > 0, fn (Builder $q) => $q->whereRaw(
                'COALESCE(NULLIF(prix_total_affiche, 0), prix_place) <= ?',
                [$filters['max_price']]
            ))
            ->orderBy('date_depart')
            ->orderBy('heure_depart');
    }

    /**
     * Regroupe les trajets par liaison (ville de depart -> ville d'arrivee).
     * Le regroupement se fait en PHP : les colonnes stockent l'adresse
     * geocodee complete, un GROUP BY SQL separerait « Lyon » de
     * « Lyon, Metropole de Lyon, ... ».
     */
    private function groupByRoute(Collection $trips, string $sort = ''): Collection
    {
        return $trips
            ->groupBy(fn (Covoiturage $trip) => Str::slug($trip->depart_ville) . '::' . Str::slug($trip->destination_ville))
            ->map(function (Collection $group) {
                $first = $group->first();

                $prices = $group->map(fn (Covoiturage $t) => (float) ($t->prix_total_affiche ?: $t->prix_place))
                                ->filter(fn ($p) => $p > 0);

                return [
                    'from'      => $first->depart_ville,
                    'to'        => $first->destination_ville,
                    'image'     => RouteImage::for($first->depart_ville, $first->destination_ville),
                    'count'     => $group->count(),
                    'seats'     => $group->sum('nb_places'),
                    'min_price' => $prices->min(),
                    'next'      => $group->min('date_depart'),
                    'drivers'   => $group->map(fn (Covoiturage $t) => $t->conducteur)
                                         ->filter()
                                         ->unique('id')
                                         ->take(3)
                                         ->values(),
                ];
            })
            // Par defaut la liaison qui part le plus tot ; au prix, celle dont
            // le trajet le moins cher est le moins cher. Une liaison sans
            // prix affiche passe en dernier plutot que de sortir en tete.
            ->sortBy($sort === 'price'
                ? fn (array $route) => $route['min_price'] ?? INF
                : 'next')
            ->values();
    }

    /**
     * Chiffres de l'entete : ils decrivent l'offre du service et non la
     * recherche en cours, ils ignorent donc les filtres.
     */
    private function tripStats(): array
    {
        $trips = $this->tripQuery()->get(['covoiturage_id', 'depart', 'destination', 'conducteur_id']);

        return [
            'trips'   => $trips->count(),
            'routes'  => $trips->map(fn (Covoiturage $t) => Str::slug($t->depart_ville) . '::' . Str::slug($t->destination_ville))
                               ->unique()->count(),
            'cities'  => $trips->map(fn (Covoiturage $t) => Str::slug($t->destination_ville))->filter()->unique()->count(),
            'drivers' => $trips->pluck('conducteur_id')->unique()->count(),
        ];
    }

    /**
     * Villes proposees en autocompletion des champs de recherche.
     */
    private function tripCities(): array
    {
        $trips = $this->tripQuery()->get(['covoiturage_id', 'depart', 'destination']);

        // Depart et arrivee sont proposes separement : toutes les villes ne
        // sont pas des villes de depart, et l'inverse est vrai aussi.
        return [
            'from' => $trips->map(fn (Covoiturage $t) => $t->depart_ville)->filter()->unique()->sort()->values(),
            'to'   => $trips->map(fn (Covoiturage $t) => $t->destination_ville)->filter()->unique()->sort()->values(),
        ];
    }

    /**
     * Pagination d'une collection construite en memoire (liaisons, trajets
     * filtres sur la ville) : le tri et le regroupement ayant lieu en PHP,
     * la pagination ne peut pas etre deleguee a SQL.
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

    /**
     * Date de filtre saisie par l'utilisateur, ignoree si elle est illisible :
     * la page de recherche ne doit jamais tomber en erreur de validation.
     */
    private function asDate($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Page du service Location : son design dedie, alimente par la base.
     *
     * Le service n'a qu'une page, quel que soit le chemin : les categories
     * (voitures, utilitaires, logements, salles...) y sont des onglets et
     * non une grille intermediaire a traverser.
     *
     *   /location              -> onglet « Toutes », les offres du service
     *   /location/{categorie}  -> onglet de la categorie, ses offres
     */
    private function location(Request $request, Service $service, ?string $categorySlug)
    {
        return $this->locationListings(
            $request,
            $service,
            $this->serviceCategories($service),
            $categorySlug ?: $request->input('category')
        );
    }

    /**
     * Page des offres publiees d'une categorie de location.
     *
     * La location a son propre design, distinct de la page service
     * standard : les categories y sont des onglets, et la grille montre les
     * annonces ET les produits publies de l'onglet ouvert.
     *
     * Sans categorie (onglet « Toutes »), la page couvre tout le service.
     */
    private function locationListings(Request $request, Service $service, Collection $categories, ?string $categorySlug)
    {
        $selectedCategory = $this->selectedCategory($request, $categories, $categorySlug);

        $categoryIds = $selectedCategory
            ? [$selectedCategory->id]
            : $categories->pluck('id')->all();

        $listings = $this->listings($request, $categoryIds);

        // Les chiffres de l'entete decrivent l'offre de la categorie et non
        // la recherche en cours : ils sont comptes avant filtrage.
        $all = $this->listings(new Request(), $categoryIds);

        return view('services.location-annonces', [
            'service'          => $service,
            'categories'       => $categories,
            'selectedCategory' => $selectedCategory,
            'listings'         => $this->paginate($listings, self::PER_PAGE, $request),
            'stats'            => [
                'total'    => $all->count(),
                'ads'      => $all->where('type', Listing::ANNONCE)->count(),
                'products' => $all->where('type', Listing::PRODUIT)->count(),
                'owners'   => $all->pluck('owner')->filter()->unique()->count(),
            ],
            'cities'           => $this->cities($categoryIds),
            'fields'           => $this->locationFields($selectedCategory),
            'hasFilters'       => $this->hasSearch($request),
            'image'            => $this->categoryImage($selectedCategory),
        ]);
    }

    /**
     * Image de fond de l'entete d'une categorie de location.
     *
     * Toutes les categories n'ont pas de visuel, et un visuel enregistre en
     * base peut ne plus exister sur le disque : l'entete etant plein cadre,
     * une image manquante se verrait immediatement. On repli donc sur le
     * visuel du service.
     */
    private function categoryImage(?Category $category): string
    {
        $image = $category?->image;

        if ($image && file_exists(storage_path('app/public/' . $image))) {
            return asset('storage/' . $image);
        }

        return asset('assets/images/location-voiture.jpg.jpeg');
    }

    /**
     * Libelles de la barre de filtres pour la categorie ouverte.
     */
    private function locationFields(?Category $category): array
    {
        return array_merge([
            'search' => 'Véhicule, logement, matériel…',
            'from'   => 'Disponible à partir du',
            'to'     => "Jusqu'au",
        ], self::LOCATION_FIELDS[$category?->slug] ?? []);
    }

    /**
     * Une recherche est-elle en cours ? L'entete de la page et son etat vide
     * ne disent pas la meme chose selon la reponse.
     *
     * Le tri n'en fait pas partie : il ordonne les offres, il n'en retire
     * aucune, et le lien « reinitialiser » n'a donc pas a l'effacer.
     */
    private function hasSearch(Request $request): bool
    {
        return collect(['search', 'location', 'start_date', 'end_date', 'min_price', 'max_price', 'type'])
            ->contains(fn (string $key) => $request->filled($key));
    }

    /**
     * Les categories d'un service avec leur volume d'offres publiees.
     *
     * Une categorie porte des annonces (location) et des produits (vente) :
     * les deux comptent dans le total affiche sur sa carte.
     */
    private function serviceCategories(Service $service): Collection
    {
        return $service->categories()
            ->withCount([
                'ads as ads_count' => fn (Builder $q) => $q->where('is_approved', true),
                'products as products_count' => fn (Builder $q) => $q->where('is_active', true),
            ])
            ->orderBy('id')
            ->get();
    }

    /**
     * La categorie choisie, designee indifferemment par le chemin
     * (/location/voitures) ou par la barre de recherche (?category=voitures).
     * Un slug inconnu est une 404 : la page ne doit pas repondre « tout le
     * service » a une categorie qui n'existe pas.
     */
    private function selectedCategory(Request $request, Collection $categories, ?string $categorySlug): ?Category
    {
        $categorySlug = $categorySlug ?: $request->input('category');

        if (! $categorySlug) {
            return null;
        }

        $selected = $categories->firstWhere('slug', $categorySlug);

        abort_if(! $selected, 404);

        return $selected;
    }

    /**
     * Page standard : titre du service, ses categories, la carte,
     * les filtres et la grille d'annonces.
     */
    private function standard(Request $request, Service $service, ?string $categorySlug)
    {
        $categories = $this->serviceCategories($service);

        $selectedCategory = $this->selectedCategory($request, $categories, $categorySlug);

        $categoryIds = $selectedCategory
            ? [$selectedCategory->id]
            : $categories->pluck('id')->all();

        // Grille : annonces et produits ramenes au meme format, tries ensemble
        $all = $this->listings($request, $categoryIds);

        return view('services.service-standard', [
            'service'          => $service,
            'categories'       => $categories,
            'selectedCategory' => $selectedCategory,
            'listings'         => $this->paginate($all, self::PER_PAGE, $request),
            'counts'           => [
                'total'   => $all->count(),
                'annonce' => $all->where('type', Listing::ANNONCE)->count(),
                'produit' => $all->where('type', Listing::PRODUIT)->count(),
            ],
            'mapPoints'        => $this->mapPoints($request, $categoryIds),
            'cities'           => $this->cities($categoryIds),
            'popular'          => $this->popular($categoryIds),
        ]);
    }

    /**
     * Annonces et produits du service, ramenes au meme jeu de champs puis
     * tries ensemble.
     *
     * Le tri se fait en PHP et non en SQL : les deux tables n'ont ni les
     * memes colonnes de prix (`price_per_day` contre `price`) ni le meme
     * modele, un ORDER BY commun n'aurait pas de sens.
     */
    private function listings(Request $request, array $categoryIds): Collection
    {
        $type = $request->input('type');

        $listings = collect();

        if ($type !== Listing::PRODUIT) {
            $listings = $listings->merge(
                $this->filtered($request, $categoryIds)
                     ->with(['images', 'category', 'user'])
                     ->get()
                     ->map(fn (Ad $ad) => Listing::fromAd($ad))
            );
        }

        if ($type !== Listing::ANNONCE) {
            $listings = $listings->merge(
                $this->filteredProducts($request, $categoryIds)
                     ->with(['images', 'category', 'user'])
                     ->get()
                     ->map(fn (Product $product) => Listing::fromProduct($product))
            );
        }

        return $this->sortListings($listings, $request->input('sort'));
    }

    /**
     * Meme tri que la grille d'annonces, applique a l'ensemble melange.
     */
    private function sortListings(Collection $listings, ?string $sort): Collection
    {
        return match ($sort) {
            'price_asc'  => $listings->sortBy('price')->values(),
            'price_desc' => $listings->sortByDesc('price')->values(),
            'popular'    => $listings->sortByDesc('views')->values(),
            default      => $listings->sortByDesc(fn ($l) => $l['created_at']?->timestamp ?? 0)->values(),
        };
    }

    /**
     * Produits en ligne du service, filtres comme les annonces.
     */
    private function filteredProducts(Request $request, array $categoryIds): Builder
    {
        $query = Product::query()
            ->where('is_active', true)
            ->whereIn('category_id', $categoryIds);

        // Un produit se vend a l'unite : il n'a pas de periode de mise a
        // disposition et ne peut donc pas repondre a une recherche par
        // dates. On l'ecarte plutot que de le proposer a tort.
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $query->whereRaw('1 = 0');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('location')) {
            $query->where('address', 'like', '%' . $request->input('location') . '%');
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        return $query;
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

        // Disponibilite (barre de recherche de la page location) : une
        // annonce ne ressort que si sa periode de mise a disposition
        // recouvre les dates demandees. Une periode non renseignee est
        // consideree comme toujours disponible.
        if ($start = $this->asDate($request->input('start_date'))) {
            $query->where(fn (Builder $q) => $q->whereNull('available_until')
                                               ->orWhereDate('available_until', '>=', $start));
        }

        if ($end = $this->asDate($request->input('end_date'))) {
            $query->where(fn (Builder $q) => $q->whereNull('available_from')
                                               ->orWhereDate('available_from', '<=', $end));
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
        $points = $this->listings($request, $categoryIds)
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

        return $points;
    }

    /**
     * Villes distinctes du service, annonces et produits confondus.
     * Les adresses sont libres : on retient le dernier segment, qui porte
     * la ville dans la quasi-totalite des saisies ("12 rue X, Paris").
     */
    private function cities(array $categoryIds): Collection
    {
        $adresses = Ad::where('is_approved', true)
            ->whereIn('category_id', $categoryIds)
            ->whereNotNull('address')
            ->pluck('address')
            ->merge(
                Product::where('is_active', true)
                    ->whereIn('category_id', $categoryIds)
                    ->whereNotNull('address')
                    ->pluck('address')
            );

        return $adresses
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
     * Les offres les plus consultees du service, annonces et produits
     * confondus. Le bloc reste vide tant que rien n'a ete vu : c'est
     * volontaire, l'etat vide a son propre message.
     */
    private function popular(array $categoryIds): Collection
    {
        $ads = Ad::with(['images', 'category', 'user'])
            ->where('is_approved', true)
            ->whereIn('category_id', $categoryIds)
            ->where('views', '>', 0)
            ->orderByDesc('views')
            ->limit(self::POPULAR_LIMIT)
            ->get()
            ->map(fn (Ad $ad) => Listing::fromAd($ad));

        $products = Product::with(['images', 'category', 'user'])
            ->where('is_active', true)
            ->whereIn('category_id', $categoryIds)
            ->where('views', '>', 0)
            ->orderByDesc('views')
            ->limit(self::POPULAR_LIMIT)
            ->get()
            ->map(fn (Product $product) => Listing::fromProduct($product));

        return $ads->merge($products)
                   ->sortByDesc('views')
                   ->take(self::POPULAR_LIMIT)
                   ->values();
    }
}
