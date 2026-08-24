{{--
    Carte d'offre, partagee par la grille et le bloc « les plus consultees ».

    Elle recoit une offre normalisee (App\Support\Listing) et non un modele :
    annonces et produits vivent dans deux tables aux colonnes differentes,
    c'est le champ `type` qui porte la distinction ici.
--}}
@props([
    'listing',
    'badge' => null,
])

@php
    $isProduct = ($listing['type'] ?? null) === \App\Support\Listing::PRODUIT;
@endphp

<article class="cs-card">
    <div class="cs-card-media">
        <img src="{{ $listing['image'] }}" alt="{{ $listing['title'] }}" loading="lazy">

        <span class="cs-badge-type {{ $isProduct ? 'is-product' : 'is-ad' }}">
            <i class="fa-solid {{ $isProduct ? 'fa-bag-shopping' : 'fa-key' }}"></i>
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

        {{-- Classe et attributs attendus par le gestionnaire global (assets/js/script.js) --}}
        <button type="button" class="cs-favorite-btn favorite-btn"
                aria-label="Ajouter aux favoris"
                data-type="{{ $listing['favorite'] }}"
                data-id="{{ $listing['id'] }}">
            <i class="fa-regular fa-heart"></i>
        </button>
    </div>

    <div class="cs-card-body">
        <p class="cs-card-meta">
            @if($listing['category']?->icon)
                <i class="{{ $listing['category']->icon_class }}"></i>
            @endif
            {{ $listing['category']->nom ?? '' }}
        </p>

        <h3 class="cs-card-title">{{ $listing['title'] }}</h3>

        @if($listing['address'])
            <p class="cs-card-location">
                <i class="fa-solid fa-location-dot"></i>
                {{ $listing['address'] }}
            </p>
        @endif

        <p class="cs-card-stats">
            <span><i class="fa-regular fa-eye"></i> {{ $listing['views'] }} vue(s)</span>

            @if($isProduct && $listing['stock'] !== null)
                <span>
                    <i class="fa-solid fa-boxes-stacked"></i>
                    {{ $listing['stock'] > 0 ? $listing['stock'] . ' en stock' : 'Rupture' }}
                </span>
            @elseif($listing['created_at'])
                <span><i class="fa-regular fa-clock"></i> {{ $listing['created_at']->diffForHumans() }}</span>
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
            <a href="{{ $listing['url'] }}" class="cs-btn-details">Voir détails</a>
        </div>
    </div>
</article>
