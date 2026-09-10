{{--
    Carte d'offre, partagee par la grille et le bloc « les plus consultees ».

    Elle recoit une offre normalisee (App\Support\Listing) et non un modele :
    annonces, produits et trajets vivent dans trois tables aux colonnes
    differentes, c'est le champ `type` qui porte la distinction ici.
--}}
@props([
    'listing',
    'badge' => null,
])

@php
    $isProduct = ($listing['type'] ?? null) === \App\Support\Listing::PRODUIT;
    $isTrip    = ($listing['type'] ?? null) === \App\Support\Listing::TRAJET;

    // Glyphe du bandeau de type : une cle pour une location, un sac pour un
    // achat, une voiture pour un trajet partage.
    $typeIcon = match (true) {
        $isTrip    => 'fa-car-side',
        $isProduct => 'fa-bag-shopping',
        default    => 'fa-key',
    };

    $typeClass = match (true) {
        $isTrip    => 'is-trip',
        $isProduct => 'is-product',
        default    => 'is-ad',
    };
@endphp

<article class="cs-card">
    <div class="cs-card-media">
        <img src="{{ $listing['image'] }}" alt="{{ $listing['title'] }}" loading="lazy">

        <span class="cs-badge-type {{ $typeClass }}">
            <i class="fa-solid {{ $typeIcon }}"></i>
            {{ $listing['type_label'] }}
        </span>

        @if($badge)
            <span class="cs-badge-location">{{ $badge }}</span>
        @endif

        @if($listing['delivery'])
            <span class="cs-badge-delivery">
                <i class="fa-solid fa-truck-fast"></i>
                Livraison disponible
            </span>
        @endif

        {{-- Classe et attributs attendus par le gestionnaire global
             (assets/js/script.js). Un trajet ne se met pas en favori : la
             table des favoris ne connait que les annonces et les produits,
             le bouton n'a donc rien a envoyer. --}}
        @if($listing['favorite'] ?? null)
            <button type="button" class="cs-favorite-btn favorite-btn"
                    aria-label="Ajouter aux favoris"
                    data-type="{{ $listing['favorite'] }}"
                    data-id="{{ $listing['id'] }}">
                <i class="fa-regular fa-heart"></i>
            </button>
        @endif
    </div>

    <div class="cs-card-body">
        <p class="cs-card-meta">
            @if($isTrip)
                <i class="fa-solid fa-car-side"></i>
                Covoiturage
            @else
                @if($listing['category']?->icon)
                    <i class="{{ $listing['category']->icon_class }}"></i>
                @endif
                {{ $listing['category']->nom ?? '' }}
            @endif
        </p>

        <h3 class="cs-card-title">{{ $listing['title'] }}</h3>

        @if($listing['address'])
            <p class="cs-card-location">
                <i class="fa-solid fa-location-dot"></i>
                {{ $listing['address'] }}
            </p>
        @endif

        <p class="cs-card-stats">
            @if($isTrip)
                {{-- Un trajet ne se compte pas en vues : ce qui compte est
                     quand il part et combien il reste de places. --}}
                <span>
                    <i class="fa-regular fa-calendar"></i>
                    {{ optional($listing['trip_date'] ?? null)->translatedFormat('D j M') }}
                    @if($listing['trip_time'] ?? null) · {{ \Illuminate\Support\Str::of($listing['trip_time'])->substr(0, 5) }} @endif
                </span>

                <span>
                    <i class="fa-solid fa-users"></i>
                    {{ $listing['seats'] ?? 0 }} place{{ ($listing['seats'] ?? 0) > 1 ? 's' : '' }}
                </span>
            @else
                <span><i class="fa-regular fa-eye"></i> {{ $listing['views'] }} vue{{ $listing['views'] > 1 ? 's' : '' }}</span>

                @if($isProduct && $listing['stock'] !== null)
                    <span>
                        <i class="fa-solid fa-boxes-stacked"></i>
                        {{ $listing['stock'] > 0 ? $listing['stock'] . ' en stock' : 'Rupture' }}
                    </span>
                @elseif($listing['created_at'])
                    <span><i class="fa-regular fa-clock"></i> {{ $listing['created_at']->diffForHumans() }}</span>
                @endif
            @endif
        </p>

        <div class="cs-card-footer">
            <div class="cs-card-price">
                <span class="cs-price-label">{{ $listing['price_label'] }}</span>
                <span class="cs-price-value">
                    {{ number_format($listing['price'], 2, ',', ' ') }}&nbsp;€
                    <small>{{ $listing['price_suffix'] }}</small>
                </span>
            </div>
            <a href="{{ $listing['url'] }}" class="cs-btn-details">{{ $isTrip ? 'Voir le trajet' : 'Voir détails' }}</a>
        </div>
    </div>
</article>
