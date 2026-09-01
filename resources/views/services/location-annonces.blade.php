{{--
    Page du service Location.

    Le service tient en une seule page : ses categories sont des onglets, et
    la grille montre les annonces ET les produits publies de l'onglet ouvert.

        /location              ->  onglet « Toutes », tout le service
        /location/{categorie}  ->  onglet de la categorie

    Son contenu vient entierement de la base : les onglets sont les
    categories du service, la grille leurs offres publiees.
--}}
@extends('layouts.main')

@section('title', ($selectedCategory->nom ?? $service->display_name) . ' - Olten.fr')

@section('content')

@php
    // URL de la page courante : la recherche et la pagination y reviennent.
    $selfUrl = $selectedCategory
        ? route('services.category', [$service->slug, $selectedCategory->slug])
        : route('services.show', $service->slug);
@endphp

<div class="lv-page">

    {{-- Hero : la categorie ouverte, avec le volume d'offres qu'elle porte --}}
    <section class="lv-hero">
        <div class="lv-hero-bg">
            <img src="{{ $image }}" alt="{{ $selectedCategory->nom ?? $service->display_name }}">
            <div class="lv-hero-overlay"></div>
        </div>

        <div class="lv-hero-content">
            @if($selectedCategory)
                <a href="{{ route('services.show', $service->slug) }}" class="lv-hero-back">
                    <i class="fa-solid fa-arrow-left"></i> Toutes les offres
                </a>

                <span class="lv-hero-tag">— {{ $service->display_name }}</span>

                <h1 class="lv-hero-title">{{ $selectedCategory->nom }}</h1>

                <p class="lv-hero-subtitle">
                    {{ $selectedCategory->description
                        ?: 'Toutes les annonces et les produits publiés dans ' . $selectedCategory->nom . ', près de chez vous.' }}
                </p>
            @else
                <span class="lv-hero-tag">— Location de véhicules d'exception</span>

                <h1 class="lv-hero-title">Louez votre <span class="lv-hero-title-accent">liberté.</span></h1>

                <p class="lv-hero-subtitle">
                    {{ $service->short_description
                        ?: 'Découvrez une nouvelle façon de louer. Des véhicules premium, une sécurité totale et une expérience sans compromis.' }}
                </p>
            @endif

            @if($stats['total'])
                <ul class="lv-hero-stats">
                    <li>
                        <strong>{{ $stats['total'] }}</strong>
                        <span>offre{{ $stats['total'] > 1 ? 's' : '' }} publiée{{ $stats['total'] > 1 ? 's' : '' }}</span>
                    </li>
                    <li>
                        <strong>{{ $stats['ads'] }}</strong>
                        <span>annonce{{ $stats['ads'] > 1 ? 's' : '' }}</span>
                    </li>
                    <li>
                        <strong>{{ $stats['products'] }}</strong>
                        <span>produit{{ $stats['products'] > 1 ? 's' : '' }}</span>
                    </li>
                    <li>
                        <strong>{{ $stats['owners'] }}</strong>
                        <span>loueur{{ $stats['owners'] > 1 ? 's' : '' }}</span>
                    </li>
                </ul>
            @endif
        </div>
    </section>

    {{-- Filtres : les libelles suivent la categorie ouverte (`fields`), les
         champs sont ceux que la recherche sait reellement appliquer. --}}
    <section class="lv-search-section">
        @php
            // Les criteres secondaires ouvrent le volet d'eux-memes quand
            // ils sont poses : sinon le visiteur verrait des resultats
            // filtres sans voir par quoi.
            $extraOuvert = collect(['type', 'min_price', 'max_price', 'sort'])
                ->contains(fn ($cle) => request()->filled($cle));
        @endphp

        <form class="lv-search-form lv-search-form--filters" action="{{ $selfUrl }}" method="GET">

            {{-- Rangee unique : ce qu'on renseigne presque toujours. --}}
            <div class="lv-search-row">

            <div class="lv-search-field">
                <label for="lvSearch">Que recherchez-vous ?</label>
                <div class="lv-search-input">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="lvSearch" name="search" value="{{ request('search') }}"
                           placeholder="{{ $fields['search'] }}">
                </div>
            </div>

            <div class="lv-search-field">
                <label for="lvLocation">Lieu</label>
                <div class="lv-search-input">
                    <i class="fa-solid fa-location-dot"></i>
                    <input type="text" id="lvLocation" name="location" value="{{ request('location') }}"
                           placeholder="Ville" list="lvCities">
                    <datalist id="lvCities">
                        @foreach($cities as $city)
                            <option value="{{ $city }}"></option>
                        @endforeach
                    </datalist>
                </div>
            </div>

            <div class="lv-search-field">
                <label for="lvStartDate">{{ $fields['from'] }}</label>
                <div class="lv-search-input">
                    <input type="date" id="lvStartDate" name="start_date" value="{{ request('start_date') }}">
                </div>
            </div>

            <div class="lv-search-field">
                <label for="lvEndDate">{{ $fields['to'] }}</label>
                <div class="lv-search-input">
                    <input type="date" id="lvEndDate" name="end_date" value="{{ request('end_date') }}"
                           min="{{ request('start_date') }}">
                </div>
            </div>

                <button type="submit" class="lv-search-btn">Rechercher</button>
            </div>{{-- /.lv-search-row --}}

            {{-- Barre de service : deplier les criteres secondaires, et
                 repartir de zero quand une recherche est en cours. --}}
            <div class="lv-search-foot">
                <button type="button" class="lv-search-more" data-lv-more
                        aria-expanded="{{ $extraOuvert ? 'true' : 'false' }}" aria-controls="lvExtra">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Plus de filtres</span>
                    <i class="fa-solid fa-chevron-down lv-search-more-caret"></i>
                </button>

                @if($hasFilters)
                    <a href="{{ $selfUrl }}#offres" class="lv-search-reset">
                        <i class="fa-solid fa-rotate-left"></i> Réinitialiser
                    </a>
                @endif
            </div>

            <div class="lv-search-extra" id="lvExtra" data-lv-extra @unless($extraOuvert) hidden @endunless>

            <div class="lv-search-field">
                <label for="lvType">Type d'offre</label>
                <div class="lv-search-input">
                    <i class="fa-solid fa-tag"></i>
                    <select id="lvType" name="type">
                        <option value="">Tout</option>
                        <option value="{{ \App\Support\Listing::ANNONCE }}"
                                @selected(request('type') === \App\Support\Listing::ANNONCE)>Annonces de location</option>
                        <option value="{{ \App\Support\Listing::PRODUIT }}"
                                @selected(request('type') === \App\Support\Listing::PRODUIT)>Produits à vendre</option>
                    </select>
                </div>
            </div>

            <div class="lv-search-field">
                <label for="lvMinPrice">Prix minimum</label>
                <div class="lv-search-input">
                    <i class="fa-solid fa-euro-sign"></i>
                    <input type="number" id="lvMinPrice" name="min_price" min="0" step="1"
                           value="{{ request('min_price') }}" placeholder="0">
                </div>
            </div>

            <div class="lv-search-field">
                <label for="lvMaxPrice">Prix maximum</label>
                <div class="lv-search-input">
                    <i class="fa-solid fa-euro-sign"></i>
                    <input type="number" id="lvMaxPrice" name="max_price" min="0" step="1"
                           value="{{ request('max_price') }}" placeholder="Sans limite">
                </div>
            </div>

            <div class="lv-search-field">
                <label for="lvSort">Trier par</label>
                <div class="lv-search-input">
                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                    <select id="lvSort" name="sort">
                        <option value="">Plus récentes</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Prix croissant</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Prix décroissant</option>
                        <option value="popular" @selected(request('sort') === 'popular')>Les plus vues</option>
                    </select>
                </div>
            </div>

            </div>{{-- /.lv-search-extra --}}
        </form>
    </section>

    {{-- Les autres categories du service, pour passer de l'une a l'autre.
         Une categorie sans aucune offre part en gris : le visiteur voit
         avant de cliquer qu'il n'y trouvera rien. --}}
    @if($categories->isNotEmpty())
        @php $totalOffres = $categories->sum('ads_count') + $categories->sum('products_count'); @endphp

        <nav class="lv-chips" aria-label="Catégories de {{ $service->display_name }}">
            <a href="{{ route('services.show', $service->slug) }}"
               class="lv-chip {{ $selectedCategory ? '' : 'is-active' }} {{ $totalOffres ? '' : 'is-empty' }}"
               @unless($selectedCategory) aria-current="page" @endunless>
                <i class="fa-solid fa-border-all"></i> Toutes
            </a>

            @foreach($categories as $category)
                @php
                    // Annonces et produits partagent la meme grille : c'est
                    // leur somme qui dit si la categorie a quelque chose.
                    $nbOffres = ($category->ads_count ?? 0) + ($category->products_count ?? 0);
                    $estActive = $selectedCategory?->id === $category->id;
                @endphp

                <a href="{{ route('services.category', [$service->slug, $category->slug]) }}"
                   class="lv-chip {{ $estActive ? 'is-active' : '' }} {{ $nbOffres ? '' : 'is-empty' }}"
                   @if($estActive) aria-current="page" @endif>
                    <i class="{{ $category->icon_class }}"></i> {{ $category->nom }}
                </a>
            @endforeach
        </nav>
    @endif

    {{-- Les offres publiees --}}
    <section class="lv-recent" id="offres">
        <div class="lv-recent-header">
            <div>
                <h2 class="lv-section-title">
                    {{ $hasFilters ? 'Résultats de votre recherche' : 'Offres disponibles' }}
                </h2>
                <p class="olten-count">
                    <span class="olten-count-pill">
                        <i class="fa-solid fa-tag"></i>
                        {{ $listings->total() }} offre{{ $listings->total() > 1 ? 's' : '' }}
                    </span>
                    <span class="olten-count-note">
                        {{ $hasFilters
                            ? 'correspondant à votre recherche'
                            : 'publiée' . ($listings->total() > 1 ? 's' : '') . ' par les membres' }}
                    </span>
                </p>
            </div>

            @if($hasFilters)
                <a href="{{ $selfUrl }}#offres" class="lv-recent-link">
                    <i class="fa-solid fa-rotate-left"></i> Réinitialiser la recherche
                </a>
            @endif
        </div>

        @if($listings->isNotEmpty())
            {{-- Meme carte que les autres pages service : une offre se lit
                 partout de la meme facon sur la plateforme. --}}
            <div class="cs-cards-grid">
                @foreach($listings as $listing)
                    <x-services.listing-card :listing="$listing" :badge="$service->display_name" />
                @endforeach
            </div>

            @if($listings->hasPages())
                <div class="lv-pagination">
                    {{ $listings->links() }}
                </div>
            @endif
        @else
            <x-empty-state
                :title="$hasFilters
                            ? 'Aucune offre ne correspond à votre recherche'
                            : 'Aucune offre publiée pour le moment'"
                :text="$hasFilters
                            ? 'Élargissez vos dates, changez de ville ou retirez un filtre pour voir davantage d\'offres.'
                            : 'Les annonces et produits publiés dans ' . ($selectedCategory->nom ?? $service->display_name) . ' apparaîtront ici.'"
                :action-url="$hasFilters ? $selfUrl : route('ads.create')"
                :action-label="$hasFilters ? 'Voir toutes les offres' : 'Publier la première annonce'"
                :action-auth="! $hasFilters" />
        @endif
    </section>

    {{-- Banniere loueur --}}
    <section class="lv-banner">
        <div class="lv-banner-bg">
            <img src="{{ asset('assets/images/location-voiture.jpg.jpeg') }}" alt="Publier une annonce de location">
            <div class="lv-banner-overlay"></div>

            <div class="lv-banner-content">
                <span class="lv-banner-badge">Offre Loueur</span>
                <h2 class="lv-banner-title">
                    Rentabilisez ce qui <span class="lv-banner-title-accent">dort</span> chez vous.
                </h2>
                <p class="lv-banner-subtitle">
                    Une voiture, un utilitaire, du matériel ou une salle qui ne servent pas tous les jours ?
                    Publiez votre annonce en quelques minutes et louez-la aux membres près de chez vous.
                </p>
                <a href="{{ route('ads.create') }}" class="lv-banner-btn" data-auth-required>Publier une annonce</a>
            </div>
        </div>
    </section>

    {{-- Notre engagement --}}
    <section class="lv-engagement">
        <h2 class="lv-section-title">Notre Engagement</h2>

        <div class="lv-engagement-grid">
            <div class="lv-engagement-card">
                <span class="lv-engagement-icon"><i class="fa-solid fa-hand"></i></span>
                <h3 class="lv-engagement-title">La liberté du choix</h3>
                <p class="lv-engagement-desc">Louez à la journée, au week-end ou à la semaine, directement auprès des membres, sans agence ni horaires imposés.</p>
            </div>

            <div class="lv-engagement-card">
                <span class="lv-engagement-icon"><i class="fa-solid fa-dollar-sign"></i></span>
                <h3 class="lv-engagement-title">Le juste prix</h3>
                <p class="lv-engagement-desc">Des tarifs fixés par les loueurs eux-mêmes, affichés à la journée : vous savez exactement ce que vous payez avant de réserver.</p>
            </div>

            <div class="lv-engagement-card">
                <span class="lv-engagement-icon"><i class="fa-regular fa-circle-check"></i></span>
                <h3 class="lv-engagement-title">Sérénité certifiée</h3>
                <p class="lv-engagement-desc">Chaque annonce est validée avant publication et chaque profil est vérifié, pour que la remise des clés se passe sans mauvaise surprise.</p>
            </div>
        </div>
    </section>

</div>

@endsection

@push('scripts')
<script>
    // Volet des criteres secondaires de la barre de recherche : la rangee
    // visible reste sur une seule ligne, le reste se deplie a la demande.
    document.addEventListener('DOMContentLoaded', function () {
        const bouton = document.querySelector('[data-lv-more]');
        const volet  = document.querySelector('[data-lv-extra]');

        if (! bouton || ! volet) {
            return;
        }

        bouton.addEventListener('click', function () {
            const ouvrir = volet.hidden;

            volet.hidden = ! ouvrir;
            bouton.setAttribute('aria-expanded', String(ouvrir));
        });
    });
</script>
@endpush
