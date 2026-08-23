@extends('layouts.main')

@section('title', $service->nom . ' - Olten.fr')

@section('content')

    <div class="cs-page">

    {{-- Hero section --}}
    <div class="cs-hero">
        <div class="cs-hero-inner">
            <nav class="cs-breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ route('home') }}" class="cs-breadcrumb-link">Accueil</a>
                <i class="fa-solid fa-chevron-right cs-breadcrumb-sep"></i>

                @if($selectedCategory)
                    <a href="{{ route('services.show', $service->slug) }}" class="cs-breadcrumb-link">{{ $service->nom }}</a>
                    <i class="fa-solid fa-chevron-right cs-breadcrumb-sep"></i>
                    <span class="cs-breadcrumb-current">{{ $selectedCategory->nom }}</span>
                @else
                    <span class="cs-breadcrumb-current">{{ $service->nom }}</span>
                @endif
            </nav>

            <h1 class="cs-hero-title">{{ $selectedCategory->nom ?? $service->nom }}</h1>

            <p class="cs-hero-subtitle">
                {{ $selectedCategory?->description
                    ?: ($service->description ?: 'Découvrez toutes les annonces disponibles pour ' . $service->nom . '.') }}
            </p>
        </div>
    </div>

    {{-- Categories du service (sous-services) --}}
    @if($categories->isNotEmpty())
        <div class="cs-categories">
            <a href="{{ route('services.show', $service->slug) }}"
               class="cs-cat-item {{ $selectedCategory ? '' : 'is-active' }}">
                <span class="cs-cat-icon"><i class="fa-solid fa-border-all"></i></span>
                <span class="cs-cat-label">Toutes les catégories</span>
            </a>

            @foreach($categories as $category)
                <a href="{{ route('services.show', [$service->slug, 'category' => $category->slug]) }}"
                   class="cs-cat-item {{ $selectedCategory && $selectedCategory->id === $category->id ? 'is-active' : '' }}">
                    <span class="cs-cat-icon">
                        <i class="{{ $category->icon ?: 'fa-solid fa-tag' }}"></i>
                    </span>
                    <span class="cs-cat-label">{{ $category->nom }}</span>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Carte / localisation --}}
    <div class="cs-map-card">
        <div class="cs-map-header">
            <p class="cs-map-title">Visualisez instantanément les annonces disponibles autour de vous</p>
            <span class="cs-map-badge">
                <i class="fa-solid fa-location-dot"></i>
                {{ $locatedCount }} position(s) active(s)
            </span>
        </div>
        <div class="cs-map-frame">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d674378.7410253617!2d2.50266365!3d48.68078245!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e5e1c403a68c17%3A0x10b82c3688b2570!2s%C3%8Ele-de-France%2C%20France!5e0!3m2!1sfr!2sdz!4v1787262706689!5m2!1sfr!2sdz" width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
        </div>
    </div>

    {{-- Annonces --}}
    <div class="cs-listings">

        {{-- Filtres : recherche, ville, prix, tri --}}
        <form method="GET" action="{{ route('services.show', $service->slug) }}" class="cs-filters">
            @if($selectedCategory)
                <input type="hidden" name="category" value="{{ $selectedCategory->slug }}">
            @endif

            <div class="cs-search-input">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Que recherchez-vous ?">
            </div>

            <div class="cs-search-input">
                <i class="fa-solid fa-location-dot"></i>
                <input type="text" name="location" value="{{ request('location') }}"
                       placeholder="Ville">
            </div>

            <div class="cs-search-input">
                <span class="cs-filter-prefix">Prix min</span>
                <input type="number" name="min_price" min="0" step="1"
                       value="{{ request('min_price') }}" placeholder="0">
            </div>

            <div class="cs-search-input">
                <span class="cs-filter-prefix">Prix max</span>
                <input type="number" name="max_price" min="0" step="1"
                       value="{{ request('max_price') }}" placeholder="100000">
            </div>

            <select name="sort" class="cs-sort-select">
                <option value="">Trier par</option>
                <option value="recent" @selected(request('sort') === 'recent')>Plus récent</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>Prix croissant</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>Prix décroissant</option>
            </select>

            <button type="submit" class="cs-btn-filter">Appliquer les filtres</button>

            @if(request()->hasAny(['search', 'location', 'min_price', 'max_price', 'sort']))
                <a href="{{ route('services.show', $selectedCategory ? [$service->slug, 'category' => $selectedCategory->slug] : $service->slug) }}"
                   class="cs-btn-reset">Réinitialiser</a>
            @endif
        </form>

        <div class="cs-listings-header">
            <div>
                <h2 class="cs-listings-title">Annonces vérifiées</h2>
                <span class="cs-listings-count">{{ $ads->total() }} résultat(s)</span>
            </div>
        </div>

        @if($ads->isNotEmpty())
            <div class="cs-cards-grid">
                @foreach($ads as $ad)
                    <article class="cs-card">
                        <div class="cs-card-media">
                            <img src="{{ $ad->images->first()
                                        ? asset('storage/' . $ad->images->first()->path)
                                        : asset('assets/images/no-image.jpg') }}"
                                 alt="{{ $ad->title }}">

                            <span class="cs-badge-location">{{ $service->nom }}</span>

                            @if($ad->delivery_active)
                                <span class="cs-badge-delivery">
                                    <i class="fa-solid fa-truck-fast"></i>
                                    Livraison disponible
                                </span>
                            @endif

                            <button type="button" class="cs-favorite-btn"
                                    aria-label="Ajouter aux favoris" data-ad-id="{{ $ad->id }}">
                                <i class="fa-regular fa-heart"></i>
                            </button>
                        </div>

                        <div class="cs-card-body">
                            <p class="cs-card-meta">{{ $ad->category->nom ?? '' }}</p>
                            <h3 class="cs-card-title">{{ $ad->title }}</h3>

                            @if($ad->address)
                                <p class="cs-card-location">
                                    <i class="fa-solid fa-location-dot"></i>
                                    {{ $ad->address }}
                                </p>
                            @endif

                            <div class="cs-card-footer">
                                <div class="cs-card-price">
                                    <span class="cs-price-label">À partir de</span>
                                    <span class="cs-price-value">
                                        {{ number_format((float) $ad->price_per_day, 2, ',', ' ') }}&nbsp;€
                                    </span>
                                </div>
                                <a href="{{ route('ads.show', $ad) }}" class="cs-btn-details">Voir détails</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="cs-pagination">
                {{ $ads->links() }}
            </div>
        @else
            {{-- État vide --}}
            <div class="cs-empty">
                <span class="cs-empty-icon"><i class="fa-solid fa-box-open"></i></span>

                <h3 class="cs-empty-title">Aucune annonce disponible</h3>

                <p class="cs-empty-text">
                    @if($selectedCategory)
                        Il n'y a actuellement aucune annonce dans la catégorie
                        <strong>{{ $selectedCategory->nom }}</strong>.
                    @else
                        Il n'y a actuellement aucune annonce pour ce service.
                    @endif
                </p>

                @if($selectedCategory || request()->hasAny(['search', 'location', 'min_price', 'max_price']))
                    <a href="{{ route('services.show', $service->slug) }}" class="cs-empty-btn">
                        Voir toutes les annonces
                    </a>
                @endif
            </div>
        @endif

    </div>

</div>

@endsection
