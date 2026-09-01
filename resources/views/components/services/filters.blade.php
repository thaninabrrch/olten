{{--
    Rail de filtres de la page service.

    Une colonne a gauche des resultats : chaque critere est une section
    depliee par defaut, donc lisible sans ouvrir de menu. Les vrais champs du
    formulaire vivent dans les sections ; repliees, elles restent envoyables,
    il n'y a donc aucune valeur a recopier ailleurs.

    Les criteres poses se relisent en haut du rail et se retirent un par un.
    Ces retraits sont de simples liens vers la meme URL privee du parametre :
    ils fonctionnent sans JavaScript et remettent la pagination a zero.

    Sous 900px le rail passe au-dessus des resultats et se replie derriere un
    bouton « Filtres », pour ne pas repousser la grille hors de l'ecran.

    Le formulaire poste en GET sur l'URL courante du service : la categorie
    selectionnee reste dans le chemin, les filtres dans la query string.
--}}
@props([
    'action',
    'resetUrl',
    'cities'        => [],
    'total'         => 0,
    'priceBounds'   => ['min' => 0, 'max' => 1000],
    'priceBrackets' => [],
])

@php
    $cities = collect($cities);

    $search   = request('search');
    $location = request('location');
    $type     = request('type');
    $minPrice = request('min_price');
    $maxPrice = request('max_price');
    $sort     = request('sort');

    $typeLabels = [
        \App\Support\Listing::ANNONCE => 'Annonces',
        \App\Support\Listing::PRODUIT => 'Produits',
    ];

    $sortLabels = [
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
        $tags[] = ['label' => $search, 'url' => $sans(['search'])];
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

    if (filled($sort) && isset($sortLabels[$sort])) {
        $tags[] = ['label' => $sortLabels[$sort], 'url' => $sans(['sort'])];
    }

    // Toutes les sections demarrent repliees : le rail tient alors dans un
    // seul ecran, et le critere deja pose reste lisible sur l'en-tete de sa
    // section comme dans la liste des filtres actifs.
@endphp

<form method="GET" action="{{ $action }}" class="cs-facets" data-cs-facets
      data-cs-count-url="{{ $action }}">

    <div class="cs-facets-head">
        <span class="cs-facets-head-title">
            <i class="fa-solid fa-sliders"></i>
            Filtres
        </span>

        @if($tags)
            <a href="{{ $resetUrl }}" class="cs-tag-clear">Tout effacer</a>
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

        {{-- Ville --}}
        <section class="cs-facet" data-cs-facet>
            <button type="button" class="cs-facet-btn" data-cs-facet-btn
                    aria-expanded="false" aria-controls="cs-facet-ville">
                <span class="cs-facet-name">Ville</span>
                @if(filled($location))<span class="cs-facet-value">{{ $location }}</span>@endif
                <i class="fa-solid fa-chevron-down cs-facet-caret"></i>
            </button>

            <div class="cs-facet-panel" id="cs-facet-ville" hidden>
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

        {{-- Type --}}
        <section class="cs-facet" data-cs-facet>
            <button type="button" class="cs-facet-btn" data-cs-facet-btn
                    aria-expanded="false" aria-controls="cs-facet-type">
                <span class="cs-facet-name">Type d'offre</span>
                @if(filled($type))<span class="cs-facet-value">{{ $typeLabels[$type] ?? $type }}</span>@endif
                <i class="fa-solid fa-chevron-down cs-facet-caret"></i>
            </button>

            <div class="cs-facet-panel" id="cs-facet-type" hidden>
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
        <section class="cs-facet" data-cs-facet>
            <button type="button" class="cs-facet-btn" data-cs-facet-btn
                    aria-expanded="false" aria-controls="cs-facet-prix">
                <span class="cs-facet-name">Prix</span>
                @if($priceLabel)<span class="cs-facet-value">{{ $priceLabel }}</span>@endif
                <i class="fa-solid fa-chevron-down cs-facet-caret"></i>
            </button>

            <div class="cs-facet-panel" id="cs-facet-prix" hidden
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

        {{-- Tri --}}
        <section class="cs-facet" data-cs-facet>
            <button type="button" class="cs-facet-btn" data-cs-facet-btn
                    aria-expanded="false" aria-controls="cs-facet-tri">
                <span class="cs-facet-name">Trier par</span>
                <i class="fa-solid fa-chevron-down cs-facet-caret"></i>
            </button>

            <div class="cs-facet-panel" id="cs-facet-tri" hidden>
                <div class="cs-facet-radios">
                    <label class="cs-facet-radio">
                        <input type="radio" name="sort" value="" @checked(blank($sort))>
                        <span>Plus récentes</span>
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
                Afficher {{ number_format($total, 0, ',', ' ') }} offre{{ $total > 1 ? 's' : '' }}
            </button>
        </div>
    </div>
</form>

@push('scripts')
    <script src="{{ asset('assets/js/facets.js') }}?v={{ @filemtime(public_path('assets/js/facets.js')) ?: 1 }}"></script>
@endpush
