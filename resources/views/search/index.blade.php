{{--
    Page de resultats de la recherche globale.

    La barre du header postait sur l'accueil : elle rejouait la liste des
    annonces sans dire ce qu'elle avait cherche ni combien elle avait trouve.
    Elle arrive ici, sur une page qui balaie les trois familles d'offres de
    la plateforme — annonces, produits et trajets — et donne de quoi les
    resserrer : service, categorie, ville, type, prix, livraison, fraicheur.

    Le contenu est entierement dynamique : rien n'est ecrit en dur, tout
    vient de SearchController.
--}}
@extends('layouts.main')

@section('title', $term !== '' ? 'Recherche : ' . $term . ' - Olten.fr' : 'Recherche - Olten.fr')

@section('content')

@php
    // Liens des onglets et du tri : ils gardent tous les criteres en cours
    // et remettent seulement la pagination a zero, sinon on atterrirait
    // page 4 d'une liste qui n'en compte plus qu'une.
    $lienType = function (?string $valeur) {
        $params = request()->except(['page', 'type']);

        if ($valeur) {
            $params['type'] = $valeur;
        }

        return route('search', $params);
    };

    $typeActif = request('type');

    $onglets = [
        ['valeur' => null,                             'label' => 'Tout',      'icon' => 'fa-solid fa-border-all',    'count' => array_sum($typeCounts)],
        ['valeur' => \App\Support\Listing::ANNONCE,    'label' => 'Annonces',  'icon' => 'fa-solid fa-key',           'count' => $typeCounts['annonce']],
        ['valeur' => \App\Support\Listing::PRODUIT,    'label' => 'Produits',  'icon' => 'fa-solid fa-bag-shopping',  'count' => $typeCounts['produit']],
        ['valeur' => \App\Support\Listing::TRAJET,     'label' => 'Trajets',   'icon' => 'fa-solid fa-car-side',      'count' => $typeCounts['trajet']],
    ];

    $sortLabels = [
        ''           => filled($term) ? 'Pertinence' : 'Plus récentes',
        'relevance'  => 'Pertinence',
        'recent'     => 'Plus récentes',
        'price_asc'  => 'Prix croissant',
        'price_desc' => 'Prix décroissant',
        'popular'    => 'Les plus consultées',
    ];
@endphp

{{-- ─────────────────────────────────────────────────────────────────────
     Bandeau : ce qui a ete cherche, et de quoi le rechercher autrement.
     Il est pose hors de `.cs-page` pour occuper toute la largeur, mais
     reprend la meme gouttiere interne : le fil d'Ariane s'aligne donc sur
     les blocs qui le suivent.
     ───────────────────────────────────────────────────────────────────── --}}
<div class="sr-hero">
    <div class="sr-hero-glow" aria-hidden="true"></div>

    <div class="sr-hero-inner">

        <nav class="cs-breadcrumb" aria-label="Fil d'Ariane">
            <a href="{{ route('home') }}" class="cs-breadcrumb-link">Accueil</a>
            <i class="fa-solid fa-chevron-right cs-breadcrumb-sep"></i>

            @if($selectedService)
                <a href="{{ route('services.show', $selectedService->slug) }}" class="cs-breadcrumb-link">{{ $selectedService->display_name }}</a>
                <i class="fa-solid fa-chevron-right cs-breadcrumb-sep"></i>
            @endif

            <span class="cs-breadcrumb-current">Recherche</span>
        </nav>

        <h1 class="sr-hero-title">
            @if($term !== '')
                Résultats pour <span class="sr-hero-term">« {{ $term }} »</span>
            @elseif($selectedCategory)
                {{ $selectedCategory->nom }}
            @elseif($selectedService)
                {{ $selectedService->display_name }}
            @else
                Rechercher sur Olten
            @endif
        </h1>

        <p class="sr-hero-subtitle">
            @if($counts['total'] > 0)
                @php $pluriel = $counts['total'] > 1; @endphp

                {{ number_format($counts['total'], 0, ',', ' ') }}
                offre{{ $pluriel ? 's' : '' }}
                @if($hasFilters)
                    {{ $pluriel ? 'correspondent' : 'correspond' }} à votre recherche.
                @else
                    en ligne sur la plateforme.
                @endif
            @else
                Aucune offre ne correspond à cette recherche pour le moment.
            @endif
        </p>

        {{-- Barre de recherche de la page : les memes criteres que celle du
             header, en plus grand, avec l'autocompletion branchee dessus. --}}
        <form method="GET" action="{{ route('search') }}" class="sr-bar" data-search-form>

            <div class="sr-bar-field sr-bar-field--wide">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" value="{{ $term }}"
                       placeholder="Que recherchez-vous ?" autocomplete="off"
                       aria-label="Mot-clé"
                       data-search-input data-search-field="search">
            </div>

            <span class="sr-bar-divider" aria-hidden="true"></span>

            <div class="sr-bar-field">
                <i class="fa-solid fa-location-dot"></i>
                <input type="text" name="location" value="{{ request('location') }}"
                       placeholder="Emplacement" autocomplete="off"
                       aria-label="Ville"
                       data-search-input data-search-field="location">
            </div>

            <span class="sr-bar-divider" aria-hidden="true"></span>

            <div class="sr-bar-field sr-bar-field--select">
                <select name="service" aria-label="Service">
                    <option value="">Tous les services</option>
                    @foreach($services as $service)
                        <option value="{{ $service->slug }}" @selected($selectedService?->id === $service->id)>
                            {{ $service->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="sr-bar-btn">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>Rechercher</span>
            </button>
        </form>

        {{-- Recherches recentes : tenues par le navigateur du visiteur
             (assets/js/search-suggest.js). Le bloc reste vide cote serveur. --}}
        <div class="sr-recent" data-search-recent hidden>
            <span class="sr-recent-label">Vos dernières recherches</span>
            <div class="sr-recent-list" data-search-recent-list></div>
            <button type="button" class="sr-recent-clear" data-search-recent-clear>Effacer</button>
        </div>

        @if($related)
            <div class="sr-related">
                <span class="sr-related-label">
                    {{ $term !== '' ? 'Recherches associées' : 'Idées de recherche' }}
                </span>

                <div class="sr-related-list">
                    @foreach($related as $piste)
                        <a href="{{ $piste['url'] }}" class="sr-related-chip">
                            <i class="{{ $piste['icon'] }}"></i>
                            {{ $piste['label'] }}
                            @if($piste['count'])<em>{{ $piste['count'] }}</em>@endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<div class="cs-page">

    {{-- Onglets : une famille d'offres a la fois. Chacun annonce son propre
         volume, calcule sans le filtre `type` — un onglet qui affiche le
         total de l'onglet ouvert ne sert a rien. --}}
    <nav class="sr-tabs" aria-label="Type de résultats">
        @foreach($onglets as $onglet)
            @php $actif = $typeActif === $onglet['valeur'] || (blank($typeActif) && $onglet['valeur'] === null); @endphp

            <a href="{{ $lienType($onglet['valeur']) }}"
               class="sr-tab {{ $actif ? 'is-active' : '' }} {{ $onglet['count'] ? '' : 'is-empty' }}"
               @if($actif) aria-current="page" @endif>
                <i class="{{ $onglet['icon'] }}"></i>
                {{ $onglet['label'] }}
                <em>{{ $onglet['count'] }}</em>
            </a>
        @endforeach
    </nav>

    <section class="cs-listings">

        <x-search.filters :services="$services"
                          :categories="$categories"
                          :selected-service="$selectedService"
                          :selected-category="$selectedCategory"
                          :service-counts="$serviceCounts"
                          :category-counts="$categoryCounts"
                          :cities="$cities"
                          :total="$counts['total']"
                          :price-bounds="$priceBounds"
                          :price-brackets="$priceBrackets" />

        <div class="cs-results">

            <div class="cs-listings-header">
                <div>
                    <h2 class="cs-listings-title">
                        {{ $hasFilters ? 'Résultats de votre recherche' : 'Toutes les offres' }}
                    </h2>

                    <p class="olten-count">
                        <span class="olten-count-pill">
                            <i class="fa-solid fa-layer-group"></i>
                            {{ $counts['total'] }} résultat{{ $counts['total'] > 1 ? 's' : '' }}
                        </span>

                        @if($counts['annonce'])
                            <span class="olten-count-pill">
                                <i class="fa-solid fa-key"></i>
                                {{ $counts['annonce'] }} annonce{{ $counts['annonce'] > 1 ? 's' : '' }}
                            </span>
                        @endif

                        @if($counts['produit'])
                            <span class="olten-count-pill">
                                <i class="fa-solid fa-bag-shopping"></i>
                                {{ $counts['produit'] }} produit{{ $counts['produit'] > 1 ? 's' : '' }}
                            </span>
                        @endif

                        @if($counts['trajet'])
                            <span class="olten-count-pill">
                                <i class="fa-solid fa-car-side"></i>
                                {{ $counts['trajet'] }} trajet{{ $counts['trajet'] > 1 ? 's' : '' }}
                            </span>
                        @endif
                    </p>
                </div>

                <div class="sr-tools">
                    {{-- Tri : un formulaire a part, qui rejoue les criteres en
                         cours en champs caches. Le meme reglage existe dans le
                         rail ; ici il est a portee de la grille. --}}
                    <form method="GET" action="{{ route('search') }}" class="sr-sort">
                        @foreach(request()->except(['sort', 'page']) as $cle => $valeur)
                            @if(is_scalar($valeur) && $valeur !== '')
                                <input type="hidden" name="{{ $cle }}" value="{{ $valeur }}">
                            @endif
                        @endforeach

                        <label for="srSort" class="sr-sort-label">Trier</label>

                        <select name="sort" id="srSort" onchange="this.form.submit()">
                            @foreach($sortLabels as $valeur => $libelle)
                                @continue($valeur === 'relevance' && blank($term))
                                <option value="{{ $valeur }}" @selected(request('sort', '') === $valeur)>{{ $libelle }}</option>
                            @endforeach
                        </select>
                    </form>

                    {{-- Liste et carte montrent les memes offres : deux vues
                         d'un seul jeu de resultats. --}}
                    <div class="cs-view-switch" role="tablist" aria-label="Affichage des offres">
                        <button type="button" class="cs-view-btn" role="tab"
                                id="cs-tab-liste" aria-controls="cs-panel-liste" aria-selected="true"
                                data-cs-view="liste">
                            <i class="fa-solid fa-bars"></i> Liste
                        </button>

                        <button type="button" class="cs-view-btn" role="tab"
                                id="cs-tab-carte" aria-controls="cs-panel-carte" aria-selected="false"
                                data-cs-view="carte">
                            <i class="fa-regular fa-map"></i> Carte
                        </button>
                    </div>
                </div>
            </div>

            <div class="cs-panel" id="cs-panel-liste" role="tabpanel"
                 aria-labelledby="cs-tab-liste" data-cs-panel="liste">

                @if($listings->isNotEmpty())
                    <div class="cs-cards-grid">
                        @foreach($listings as $listing)
                            @php
                                // La pastille nomme le service d'ou vient
                                // l'offre : la recherche melange tout le
                                // catalogue, sans elle on ne sait pas si une
                                // carte vient de la location ou de la vente.
                                $badge = $listing['type'] === \App\Support\Listing::TRAJET
                                    ? 'Covoiturage'
                                    : ($listing['category']?->service?->display_name);
                            @endphp

                            <x-services.listing-card :listing="$listing" :badge="$badge" />
                        @endforeach
                    </div>

                    @if($listings->hasPages())
                        <div class="cs-pagination">
                            {{ $listings->links() }}
                        </div>
                    @endif
                @else
                    {{-- Etat vide. Le message dit quoi relacher plutot que
                         « soyez le premier a publier », qui laisserait croire
                         que la plateforme est deserte — et l'action proposee
                         suit ce qui bloque reellement : des criteres trop
                         etroits, un mot-cle sans echo, ou un catalogue vide.
                         Un bouton qui renvoie a la page qu'on regarde deja
                         n'est pas une issue. --}}
                    @php
                        $autresFiltres = collect(['location', 'service', 'category', 'type', 'min_price', 'max_price', 'delivery', 'period'])
                            ->contains(fn ($cle) => request()->filled($cle));

                        $vide = match (true) {
                            $autresFiltres => [
                                'url'   => route('search', array_filter(['search' => $term])),
                                'label' => 'Relancer sans les filtres',
                                'text'  => "Vos critères sont peut-être trop étroits. Ils sont listés à gauche : retirez-en un pour élargir la recherche.",
                            ],
                            $term !== '' => [
                                'url'   => route('search'),
                                'label' => 'Voir toutes les offres',
                                'text'  => "Aucune annonce, aucun produit et aucun trajet ne porte ce mot. Essayez un mot-clé plus court, ou parcourez tout le catalogue.",
                            ],
                            default => [
                                'url'   => route('services.index'),
                                'label' => 'Explorer nos services',
                                'text'  => "Rien n'est en ligne pour le moment. Parcourez les services de la plateforme en attendant les premières publications.",
                            ],
                        };
                    @endphp

                    <x-empty-state
                        :action-url="$vide['url']"
                        :action-label="$vide['label']"
                        :text="$vide['text']"
                        title="Aucune offre ne correspond à votre recherche" />
                @endif
            </div>

            <div class="cs-panel" id="cs-panel-carte" role="tabpanel"
                 aria-labelledby="cs-tab-carte" data-cs-panel="carte" hidden>
                <x-services.map :points="$mapPoints" />
            </div>

        </div>{{-- /.cs-results --}}

    </section>

    {{-- Repli quand la recherche ne donne rien : plutot qu'une page vide,
         ce que la plateforme a de plus consulte. --}}
    @if($listings->isEmpty() && $popular->isNotEmpty())
        <section class="cs-popular">
            <div class="cs-section-head">
                <h2 class="cs-section-title">Les plus consultées</h2>
                <span class="cs-section-hint">Ce que les visiteurs regardent en ce moment sur Olten</span>
            </div>

            <div class="cs-cards-grid cs-cards-grid--four">
                @foreach($popular as $listing)
                    <x-services.listing-card :listing="$listing" badge="Populaire" />
                @endforeach
            </div>
        </section>
    @endif

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        /* ------------------------- Liste / Carte -------------------------
           La carte previent son composant quand son panneau devient visible :
           Leaflet ne sait pas mesurer un conteneur masque, il faut donc la
           construire (ou la reveiller) a ce moment-la. */
        const onglets  = document.querySelectorAll('[data-cs-view]');
        const panneaux = document.querySelectorAll('[data-cs-panel]');

        onglets.forEach(function (onglet) {
            onglet.addEventListener('click', function () {
                const vue = onglet.dataset.csView;

                onglets.forEach(function (autre) {
                    autre.setAttribute('aria-selected', autre.dataset.csView === vue ? 'true' : 'false');
                });

                panneaux.forEach(function (panneau) {
                    const actif = panneau.dataset.csPanel === vue;
                    panneau.hidden = ! actif;

                    if (actif) {
                        panneau.dispatchEvent(new CustomEvent('cs:shown', { bubbles: true }));
                    }
                });
            });
        });

        /* ------------------- Service et categorie liees -------------------
           La liste des categories est celle du service affiche. Changer de
           service sans relacher la categorie enverrait une categorie qui
           n'en fait pas partie — et le serveur, qui fait primer la categorie,
           ramenerait le visiteur sur le service qu'il vient de quitter. */
        const rail = document.querySelector('[data-cs-facets]');

        if (rail) {
            rail.querySelectorAll('input[name="service"]').forEach(function (choix) {
                choix.addEventListener('change', function () {
                    rail.querySelectorAll('input[name="category"]').forEach(function (categorie) {
                        categorie.checked = categorie.value === '';
                    });
                });
            });
        }
    });
</script>
@endpush

@endsection
