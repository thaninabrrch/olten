{{--
    Rail de filtres de la recherche globale.

    Meme grammaire que celui des pages service (<x-services.filters />) et
    meme script (assets/js/facets.js) : sections depliables, curseur de prix
    a deux poignees, comptage en direct. Il porte en plus les deux criteres
    qui n'ont de sens que hors d'un service : le service lui-meme et sa
    categorie — la recherche part de tout le catalogue, il faut pouvoir le
    resserrer.

    Une section qui porte deja un critere s'ouvre au chargement : le rail des
    pages service demarre entierement replie, ce qui cachait au visiteur ce
    qu'il venait de choisir.

    Les criteres poses se relisent en haut du rail et se retirent un par un.
    Ces retraits sont de simples liens vers la meme URL privee du parametre :
    ils fonctionnent sans JavaScript et remettent la pagination a zero.
--}}
@props([
    'services'        => [],
    'categories'      => [],
    'selectedService' => null,
    'selectedCategory' => null,
    'serviceCounts'   => [],
    'categoryCounts'  => [],
    'cities'          => [],
    'total'           => 0,
    'priceBounds'     => ['min' => 0, 'max' => 1000],
    'priceBrackets'   => [],
])

@php
    $services   = collect($services);
    $categories = collect($categories);
    $cities     = collect($cities);

    $action = route('search');

    $search   = request('search');
    $location = request('location');
    $type     = request('type');
    $minPrice = request('min_price');
    $maxPrice = request('max_price');
    $period   = request('period');
    $sort     = request('sort');
    $delivery = request()->boolean('delivery');

    // Categories proposees : celles du service ouvert, sinon tout le
    // catalogue. Une liste de 60 categories tous services confondus ne se
    // lit pas — c'est le service qui la rend utilisable.
    $pickable = $selectedService
        ? $categories->where('service_id', $selectedService->id)
        : $categories;

    $typeLabels = [
        \App\Support\Listing::ANNONCE => 'Annonces',
        \App\Support\Listing::PRODUIT => 'Produits',
        \App\Support\Listing::TRAJET  => 'Trajets',
    ];

    $periodLabels = [
        '24h' => "Dernières 24 heures",
        '7d'  => '7 derniers jours',
        '30d' => '30 derniers jours',
    ];

    $sortLabels = [
        'relevance'  => 'Pertinence',
        'recent'     => 'Plus récentes',
        'price_asc'  => 'Prix croissant',
        'price_desc' => 'Prix décroissant',
        'popular'    => 'Les plus consultées',
    ];

    $euros = fn ($valeur) => number_format((float) $valeur, 0, ',', ' ') . ' €';

    $priceLabel = match (true) {
        filled($minPrice) && filled($maxPrice) => $euros($minPrice) . ' – ' . $euros($maxPrice),
        filled($minPrice)                      => 'Dès ' . $euros($minPrice),
        filled($maxPrice)                      => "Jusqu'à " . $euros($maxPrice),
        default                                => null,
    };

    // Un retrait enleve son parametre et repart de la premiere page.
    $sans = fn (array $cles) => request()->fullUrlWithoutQuery(array_merge($cles, ['page']));

    $tags = [];

    if (filled($search)) {
        $tags[] = ['label' => '« ' . $search . ' »', 'url' => $sans(['search'])];
    }

    if ($selectedService) {
        // Retirer le service retire aussi la categorie : gardee seule, elle
        // reimposerait le service au rechargement suivant.
        $tags[] = ['label' => $selectedService->display_name, 'url' => $sans(['service', 'category'])];
    }

    if ($selectedCategory) {
        $tags[] = ['label' => $selectedCategory->nom, 'url' => $sans(['category'])];
    }

    if (filled($location)) {
        $tags[] = ['label' => $location, 'url' => $sans(['location'])];
    }

    if (filled($type)) {
        $tags[] = ['label' => $typeLabels[$type] ?? $type, 'url' => $sans(['type'])];
    }

    if ($priceLabel) {
        $tags[] = ['label' => $priceLabel, 'url' => $sans(['min_price', 'max_price'])];
    }

    if ($delivery) {
        $tags[] = ['label' => 'Livraison possible', 'url' => $sans(['delivery'])];
    }

    if (filled($period) && isset($periodLabels[$period])) {
        $tags[] = ['label' => $periodLabels[$period], 'url' => $sans(['period'])];
    }

    if (filled($sort) && isset($sortLabels[$sort])) {
        $tags[] = ['label' => $sortLabels[$sort], 'url' => $sans(['sort'])];
    }

    // Une section ouverte au chargement quand elle porte deja un critere.
    $ouvertes = [
        'service'  => (bool) $selectedService,
        'category' => (bool) $selectedCategory,
        'ville'    => filled($location),
        'type'     => filled($type),
        'prix'     => (bool) $priceLabel,
        'livraison' => $delivery,
        'date'     => filled($period),
        'tri'      => filled($sort),
    ];
@endphp

<form method="GET" action="{{ $action }}" class="cs-facets" data-cs-facets
      data-cs-count-url="{{ $action }}" data-cs-noun="résultat">

    <div class="cs-facets-head">
        <span class="cs-facets-head-title">
            <i class="fa-solid fa-sliders"></i>
            Affiner
        </span>

        @if($tags)
            <a href="{{ route('search', array_filter(['search' => $search])) }}" class="cs-tag-clear">Tout effacer</a>
        @endif
    </div>

    {{-- Repli mobile : sous 900px, le rail se range derriere ce bouton. --}}
    <button type="button" class="cs-facets-toggle" data-cs-rail-toggle
            aria-expanded="false" aria-controls="cs-facets-body">
        <span>
            <i class="fa-solid fa-sliders"></i>
            Filtres @if($tags)<span class="cs-facets-badge">{{ count($tags) }}</span>@endif
        </span>
        <i class="fa-solid fa-chevron-down cs-facet-caret"></i>
    </button>

    <div class="cs-facets-body" id="cs-facets-body" data-cs-rail-body>

        <div class="cs-facets-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Affiner par mot-clé" aria-label="Affiner par mot-clé">
        </div>

        @if($tags)
            <div class="cs-facets-active">
                @foreach($tags as $tag)
                    <a href="{{ $tag['url'] }}" class="cs-tag">
                        {{ $tag['label'] }}
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        <span class="cs-sr-only">Retirer ce filtre</span>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Service --}}
        @if($services->isNotEmpty())
            <section class="cs-facet {{ $ouvertes['service'] ? 'is-open' : '' }}" data-cs-facet>
                <button type="button" class="cs-facet-btn" data-cs-facet-btn
                        aria-expanded="{{ $ouvertes['service'] ? 'true' : 'false' }}" aria-controls="cs-facet-service">
                    <span class="cs-facet-name">Service</span>
                    @if($selectedService)<span class="cs-facet-value">{{ $selectedService->display_name }}</span>@endif
                    <i class="fa-solid fa-chevron-down cs-facet-caret"></i>
                </button>

                <div class="cs-facet-panel" id="cs-facet-service" @unless($ouvertes['service']) hidden @endunless>
                    <div class="cs-facet-radios">
                        <label class="cs-facet-radio">
                            <input type="radio" name="service" value="" @checked(! $selectedService)>
                            <span>Tous les services</span>
                        </label>

                        @foreach($services as $service)
                            <label class="cs-facet-radio">
                                <input type="radio" name="service" value="{{ $service->slug }}"
                                       @checked($selectedService?->id === $service->id)>
                                <span>{{ $service->display_name }}</span>
                                @isset($serviceCounts[$service->id])
                                    <em class="cs-facet-count">{{ $serviceCounts[$service->id] }}</em>
                                @endisset
                            </label>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Categorie --}}
        @if($pickable->isNotEmpty())
            <section class="cs-facet {{ $ouvertes['category'] ? 'is-open' : '' }}" data-cs-facet>
                <button type="button" class="cs-facet-btn" data-cs-facet-btn
                        aria-expanded="{{ $ouvertes['category'] ? 'true' : 'false' }}" aria-controls="cs-facet-categorie">
                    <span class="cs-facet-name">Catégorie</span>
                    @if($selectedCategory)<span class="cs-facet-value">{{ $selectedCategory->nom }}</span>@endif
                    <i class="fa-solid fa-chevron-down cs-facet-caret"></i>
                </button>

                <div class="cs-facet-panel" id="cs-facet-categorie" @unless($ouvertes['category']) hidden @endunless>
                    <div class="cs-facet-radios cs-facet-radios--scroll">
                        <label class="cs-facet-radio">
                            <input type="radio" name="category" value="" @checked(! $selectedCategory)>
                            <span>Toutes les catégories</span>
                        </label>

                        @foreach($pickable as $category)
                            <label class="cs-facet-radio">
                                <input type="radio" name="category" value="{{ $category->slug }}"
                                       @checked($selectedCategory?->id === $category->id)>
                                <span>{{ $category->nom }}</span>
                                @isset($categoryCounts[$category->id])
                                    <em class="cs-facet-count">{{ $categoryCounts[$category->id] }}</em>
                                @endisset
                            </label>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Ville --}}
        <section class="cs-facet {{ $ouvertes['ville'] ? 'is-open' : '' }}" data-cs-facet>
            <button type="button" class="cs-facet-btn" data-cs-facet-btn
                    aria-expanded="{{ $ouvertes['ville'] ? 'true' : 'false' }}" aria-controls="cs-facet-ville">
                <span class="cs-facet-name">Ville</span>
                @if(filled($location))<span class="cs-facet-value">{{ $location }}</span>@endif
                <i class="fa-solid fa-chevron-down cs-facet-caret"></i>
            </button>

            <div class="cs-facet-panel" id="cs-facet-ville" @unless($ouvertes['ville']) hidden @endunless>
                <div class="cs-facet-field">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="location" value="{{ $location }}"
                           placeholder="Toutes les villes" aria-label="Ville"
                           autocomplete="off" data-cs-city-input>
                </div>

                @if($cities->isNotEmpty())
                    <div class="cs-facet-options" data-cs-city-list>
                        @foreach($cities as $city)
                            <button type="button" class="cs-facet-option {{ $location === $city ? 'is-on' : '' }}"
                                    data-cs-city="{{ $city }}">
                                {{ $city }}
                            </button>
                        @endforeach
                    </div>
                @endif

                @if(filled($location))
                    <button type="button" class="cs-facet-clear" data-cs-clear="location">Effacer</button>
                @endif
            </div>
        </section>

        {{-- Type d'offre --}}
        <section class="cs-facet {{ $ouvertes['type'] ? 'is-open' : '' }}" data-cs-facet>
            <button type="button" class="cs-facet-btn" data-cs-facet-btn
                    aria-expanded="{{ $ouvertes['type'] ? 'true' : 'false' }}" aria-controls="cs-facet-type">
                <span class="cs-facet-name">Type d'offre</span>
                @if(filled($type))<span class="cs-facet-value">{{ $typeLabels[$type] ?? $type }}</span>@endif
                <i class="fa-solid fa-chevron-down cs-facet-caret"></i>
            </button>

            <div class="cs-facet-panel" id="cs-facet-type" @unless($ouvertes['type']) hidden @endunless>
                <div class="cs-facet-radios">
                    <label class="cs-facet-radio">
                        <input type="radio" name="type" value="" @checked(blank($type))>
                        <span>Tout</span>
                    </label>

                    @foreach($typeLabels as $valeur => $libelle)
                        <label class="cs-facet-radio">
                            <input type="radio" name="type" value="{{ $valeur }}" @checked($type === $valeur)>
                            <span>{{ $libelle }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Prix --}}
        <section class="cs-facet {{ $ouvertes['prix'] ? 'is-open' : '' }}" data-cs-facet>
            <button type="button" class="cs-facet-btn" data-cs-facet-btn
                    aria-expanded="{{ $ouvertes['prix'] ? 'true' : 'false' }}" aria-controls="cs-facet-prix">
                <span class="cs-facet-name">Prix</span>
                @if($priceLabel)<span class="cs-facet-value">{{ $priceLabel }}</span>@endif
                <i class="fa-solid fa-chevron-down cs-facet-caret"></i>
            </button>

            <div class="cs-facet-panel" id="cs-facet-prix" @unless($ouvertes['prix']) hidden @endunless
                 data-cs-price data-cs-price-min="{{ $priceBounds['min'] }}" data-cs-price-max="{{ $priceBounds['max'] }}">

                <div class="cs-price-inputs">
                    <input type="number" name="min_price" min="{{ $priceBounds['min'] }}" max="{{ $priceBounds['max'] }}"
                           step="1" value="{{ $minPrice }}" placeholder="{{ $priceBounds['min'] }}"
                           aria-label="Prix minimum" data-cs-price-from>

                    <span class="cs-price-sep" aria-hidden="true">—</span>

                    <input type="number" name="max_price" min="{{ $priceBounds['min'] }}" max="{{ $priceBounds['max'] }}"
                           step="1" value="{{ $maxPrice }}" placeholder="{{ $priceBounds['max'] }}"
                           aria-label="Prix maximum" data-cs-price-to>
                </div>

                {{-- Curseur a deux poignees : deux `input[range]` superposes.
                     Ils ne portent pas de `name`, ce sont les deux champs
                     nombre ci-dessus qui partent au serveur. --}}
                <div class="cs-price-slider">
                    <span class="cs-price-track"></span>
                    <span class="cs-price-fill" data-cs-price-fill></span>

                    <input type="range" min="{{ $priceBounds['min'] }}" max="{{ $priceBounds['max'] }}" step="1"
                           value="{{ filled($minPrice) ? $minPrice : $priceBounds['min'] }}"
                           aria-label="Prix minimum (curseur)" data-cs-range-from>

                    <input type="range" min="{{ $priceBounds['min'] }}" max="{{ $priceBounds['max'] }}" step="1"
                           value="{{ filled($maxPrice) ? $maxPrice : $priceBounds['max'] }}"
                           aria-label="Prix maximum (curseur)" data-cs-range-to>
                </div>

                @if(filled($priceBrackets))
                    <div class="cs-price-brackets">
                        @foreach($priceBrackets as $bracket)
                            <button type="button" class="cs-price-bracket"
                                    data-cs-bracket-min="{{ $bracket['min'] ?? '' }}"
                                    data-cs-bracket-max="{{ $bracket['max'] ?? '' }}">
                                {{ $bracket['label'] }}
                            </button>
                        @endforeach
                    </div>
                @endif

                @if($priceLabel)
                    <button type="button" class="cs-facet-clear" data-cs-clear="min_price,max_price">Effacer</button>
                @endif
            </div>
        </section>

        {{-- Livraison --}}
        <section class="cs-facet {{ $ouvertes['livraison'] ? 'is-open' : '' }}" data-cs-facet>
            <button type="button" class="cs-facet-btn" data-cs-facet-btn
                    aria-expanded="{{ $ouvertes['livraison'] ? 'true' : 'false' }}" aria-controls="cs-facet-livraison">
                <span class="cs-facet-name">Livraison</span>
                @if($delivery)<span class="cs-facet-value">Possible</span>@endif
                <i class="fa-solid fa-chevron-down cs-facet-caret"></i>
            </button>

            <div class="cs-facet-panel" id="cs-facet-livraison" @unless($ouvertes['livraison']) hidden @endunless>
                <label class="cs-facet-check">
                    <input type="checkbox" name="delivery" value="1" @checked($delivery)>
                    <span>
                        <i class="fa-solid fa-truck-fast"></i>
                        Uniquement les offres livrables
                    </span>
                </label>

                <p class="cs-facet-note">
                    Les trajets de covoiturage sortent des résultats : ils ne se livrent pas.
                </p>
            </div>
        </section>

        {{-- Date de publication --}}
        <section class="cs-facet {{ $ouvertes['date'] ? 'is-open' : '' }}" data-cs-facet>
            <button type="button" class="cs-facet-btn" data-cs-facet-btn
                    aria-expanded="{{ $ouvertes['date'] ? 'true' : 'false' }}" aria-controls="cs-facet-date">
                <span class="cs-facet-name">Publiée</span>
                @if(filled($period))<span class="cs-facet-value">{{ $periodLabels[$period] ?? $period }}</span>@endif
                <i class="fa-solid fa-chevron-down cs-facet-caret"></i>
            </button>

            <div class="cs-facet-panel" id="cs-facet-date" @unless($ouvertes['date']) hidden @endunless>
                <div class="cs-facet-radios">
                    <label class="cs-facet-radio">
                        <input type="radio" name="period" value="" @checked(blank($period))>
                        <span>N'importe quand</span>
                    </label>

                    @foreach($periodLabels as $valeur => $libelle)
                        <label class="cs-facet-radio">
                            <input type="radio" name="period" value="{{ $valeur }}" @checked($period === $valeur)>
                            <span>{{ $libelle }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Tri --}}
        <section class="cs-facet {{ $ouvertes['tri'] ? 'is-open' : '' }}" data-cs-facet>
            <button type="button" class="cs-facet-btn" data-cs-facet-btn
                    aria-expanded="{{ $ouvertes['tri'] ? 'true' : 'false' }}" aria-controls="cs-facet-tri">
                <span class="cs-facet-name">Trier par</span>
                @if(filled($sort))<span class="cs-facet-value">{{ $sortLabels[$sort] ?? $sort }}</span>@endif
                <i class="fa-solid fa-chevron-down cs-facet-caret"></i>
            </button>

            <div class="cs-facet-panel" id="cs-facet-tri" @unless($ouvertes['tri']) hidden @endunless>
                <div class="cs-facet-radios">
                    <label class="cs-facet-radio">
                        <input type="radio" name="sort" value="" @checked(blank($sort))>
                        <span>{{ filled($search) ? 'Pertinence' : 'Plus récentes' }}</span>
                    </label>

                    @foreach($sortLabels as $valeur => $libelle)
                        <label class="cs-facet-radio">
                            <input type="radio" name="sort" value="{{ $valeur }}" @checked($sort === $valeur)>
                            <span>{{ $libelle }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="cs-facets-foot">
            <button type="submit" class="cs-facet-apply" data-cs-apply>
                Afficher {{ number_format($total, 0, ',', ' ') }} résultat{{ $total > 1 ? 's' : '' }}
            </button>
        </div>
    </div>
</form>

@push('scripts')
    <script src="{{ asset('assets/js/facets.js') }}?v={{ @filemtime(public_path('assets/js/facets.js')) ?: 1 }}"></script>
@endpush
