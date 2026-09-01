{{--
    Page standard d'un service.

    Elle sert tous les services qui n'ont pas de design dedie : c'est le slug
    qui decide (voir ServicePageController::DESIGNS). Son contenu est
    entierement dynamique :

        SERVICE  ->  ses categories  ->  ses annonces ET ses produits filtres
--}}
@extends('layouts.main')

@section('title', ($selectedCategory->nom ?? $service->display_name) . ' - Olten.fr')

@section('content')

@php
    // URL de la page service, avec ou sans categorie dans le chemin.
    $selfUrl = $selectedCategory
        ? route('services.category', [$service->slug, $selectedCategory->slug])
        : route('services.show', $service->slug);

    $hasFilters = collect(['search', 'location', 'type', 'min_price', 'max_price', 'sort'])
        ->contains(fn ($key) => request()->filled($key));

    // Volume total du perimetre affiche (service entier ou categorie), avant
    // filtres : c'est ce que le visiteur trouvera ici s'il ne cherche rien.
    $heroOffers = $selectedCategory
        ? (($selectedCategory->ads_count ?? 0) + ($selectedCategory->products_count ?? 0))
        : ($categories->sum('ads_count') + $categories->sum('products_count'));

    // Photo de fond du hero : celle de la categorie ouverte, sinon celle du
    // service. Les deux sont facultatives en base — sans image, la banniere
    // bascule sur son degrade et le glyphe du service.
    $heroFile  = $selectedCategory?->image ?: $service->image;
    $heroImage = $heroFile && file_exists(storage_path('app/public/' . $heroFile))
        ? asset('storage/' . $heroFile)
        : null;
@endphp

{{-- Bloc 1 : titre du service et fil d'Ariane.
     Il est pose hors de `.cs-page` : le hero occupe toute la largeur de
     l'ecran alors que le reste de la page est contenu, mais les deux
     partagent la meme gouttiere, donc le fil d'Ariane s'aligne sur les
     blocs qui le suivent. --}}
<div class="cs-hero {{ $heroImage ? 'has-image' : '' }}">
    @if($heroImage)
        <div class="cs-hero-bg">
            <img src="{{ $heroImage }}" alt="" aria-hidden="true">
        </div>
    @endif

    <div class="cs-hero-veil"></div>

    <div class="cs-hero-inner">
        @unless($heroImage)
            <i class="{{ $service->icon_class }} cs-hero-glyph" aria-hidden="true"></i>
        @endunless

        <nav class="cs-breadcrumb" aria-label="Fil d'Ariane">
            <a href="{{ route('home') }}" class="cs-breadcrumb-link">Accueil</a>
            <i class="fa-solid fa-chevron-right cs-breadcrumb-sep"></i>

            @if($selectedCategory)
                <a href="{{ route('services.show', $service->slug) }}" class="cs-breadcrumb-link">{{ $service->display_name }}</a>
                <i class="fa-solid fa-chevron-right cs-breadcrumb-sep"></i>
                <span class="cs-breadcrumb-current">{{ $selectedCategory->nom }}</span>
            @else
                <span class="cs-breadcrumb-current">{{ $service->display_name }}</span>
            @endif
        </nav>

        <span class="cs-hero-tag">
            <i class="{{ $service->icon_class }}"></i>
            {{ $service->display_name }}
        </span>

        <h1 class="cs-hero-title">{{ $selectedCategory->nom ?? $service->display_name }}</h1>

        <p class="cs-hero-subtitle">
            {{ $selectedCategory?->description
                ?: ($service->short_description
                    ?: ($service->description
                        ?: 'Découvrez toutes les annonces disponibles pour ' . $service->display_name . '.')) }}
        </p>

        <p class="olten-count">
            <span class="olten-count-pill">
                <i class="fa-solid fa-tag"></i>
                {{ $heroOffers }} offre{{ $heroOffers > 1 ? 's' : '' }}
            </span>

            @if(! $selectedCategory && $categories->isNotEmpty())
                <span class="olten-count-pill">
                    <i class="fa-solid fa-border-all"></i>
                    {{ $categories->count() }} catégorie{{ $categories->count() > 1 ? 's' : '' }}
                </span>
            @endif

            <span class="olten-count-note">
                {{ $heroOffers > 0
                    ? 'disponible' . ($heroOffers > 1 ? 's' : '') . ' en ce moment'
                    : 'pour le moment' }}
            </span>
        </p>
    </div>
</div>

<div class="cs-page">

    {{-- Bloc 2 : les categories du service, cliquables --}}
    <x-services.categories :service="$service"
                           :categories="$categories"
                           :selected="$selectedCategory" />

    {{-- Bloc 3 : filtres, puis les offres en liste ou sur la carte --}}
    <section class="cs-listings">

        <x-services.filters :action="$selfUrl"
                            :reset-url="$selfUrl"
                            :cities="$cities"
                            :total="$counts['total']"
                            :price-bounds="$priceBounds"
                            :price-brackets="$priceBrackets" />

        <div class="cs-results">

        <div class="cs-listings-header">
            <div>
                <h2 class="cs-listings-title">
                    {{ $hasFilters
                        ? 'Résultats de votre recherche'
                        : ($selectedCategory ? $selectedCategory->nom : 'Toutes les offres') }}
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

                    <span class="olten-count-note">
                        {{ $hasFilters ? 'correspondant à votre recherche' : 'publiés par les membres' }}
                    </span>
                </p>
            </div>

            {{-- Liste et carte montrent les memes offres : deux vues d'un
                 seul jeu de resultats, pas deux blocs empiles. --}}
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

        <div class="cs-panel" id="cs-panel-liste" role="tabpanel"
             aria-labelledby="cs-tab-liste" data-cs-panel="liste">

            @if($listings->isNotEmpty())
                <div class="cs-cards-grid">
                    @foreach($listings as $listing)
                        <x-services.listing-card :listing="$listing" :badge="$service->display_name" />
                    @endforeach
                </div>

                @if($listings->hasPages())
                    <div class="cs-pagination">
                        {{ $listings->links() }}
                    </div>
                @endif
            @else
                {{-- Etat vide de la grille.
                     Le message suit la raison du vide : une recherche sans
                     resultat n'appelle pas « soyez le premier a publier »,
                     qui laissait croire que le service etait desert. --}}
                @php
                    $videTitre = $hasFilters
                        ? 'Aucune offre ne correspond à votre recherche'
                        : ($selectedCategory
                            ? 'Aucune offre dans cette catégorie'
                            : "Aucune annonce n'a été publiée pour le moment");

                    $videTexte = $hasFilters
                        ? "Essayez d'élargir vos critères : une fourchette de prix plus large, une autre ville, ou tous les types d'offres."
                        : ($selectedCategory
                            ? "Il n'y a actuellement aucune annonce ni produit dans la catégorie " . $selectedCategory->nom . '.'
                            : 'Soyez le premier à publier dans ' . $service->display_name . ' sur la plateforme Olten.');
                @endphp

                <x-empty-state
                    :action-url="$selectedCategory || $hasFilters
                                    ? route('services.show', $service->slug)
                                    : route('ads.create')"
                    :action-label="$selectedCategory || $hasFilters
                                    ? 'Voir toutes les annonces'
                                    : 'Publier la première annonce'"
                    :action-auth="! $selectedCategory && ! $hasFilters"
                    :title="$videTitre"
                    :text="$videTexte" />
            @endif
        </div>

        <div class="cs-panel" id="cs-panel-carte" role="tabpanel"
             aria-labelledby="cs-tab-carte" data-cs-panel="carte" hidden>
            <x-services.map :points="$mapPoints" />
        </div>

        </div>{{-- /.cs-results --}}

    </section>

    {{-- Bloc 4 : annonces populaires du service --}}
    <section class="cs-popular">
        <div class="cs-section-head">
            <h2 class="cs-section-title">Les plus consultées</h2>
            <span class="cs-section-hint">Annonces et produits les plus vus de {{ $service->display_name }}</span>
        </div>

        @if($popular->isNotEmpty())
            <div class="cs-cards-grid cs-cards-grid--four">
                @foreach($popular as $listing)
                    <x-services.listing-card :listing="$listing" badge="Populaire" />
                @endforeach
            </div>
        @else
            <x-empty-state
                compact
                title="Aucune offre populaire"
                text="Les annonces et produits les plus consultés apparaîtront ici." />
        @endif
    </section>

</div>

@push('scripts')
<script>
    // Bascule Liste / Carte. La carte previent son composant quand son
    // panneau devient visible : Leaflet ne sait pas mesurer un conteneur
    // masque, il faut donc la construire (ou la reveiller) a ce moment-la.
    document.addEventListener('DOMContentLoaded', function () {
        const onglets  = document.querySelectorAll('[data-cs-view]');
        const panneaux = document.querySelectorAll('[data-cs-panel]');

        if (! onglets.length) {
            return;
        }

        function afficher(vue) {
            onglets.forEach(function (onglet) {
                onglet.setAttribute('aria-selected', onglet.dataset.csView === vue ? 'true' : 'false');
            });

            panneaux.forEach(function (panneau) {
                const actif = panneau.dataset.csPanel === vue;
                panneau.hidden = ! actif;

                if (actif) {
                    panneau.dispatchEvent(new CustomEvent('cs:shown', { bubbles: true }));
                }
            });
        }

        onglets.forEach(function (onglet) {
            onglet.addEventListener('click', function () {
                afficher(onglet.dataset.csView);
            });
        });
    });
</script>
@endpush

@endsection
