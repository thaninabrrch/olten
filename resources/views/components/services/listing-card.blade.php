{{--
    Carte d'annonce, partagee par la grille et le bloc « annonces populaires ».
--}}
@props([
    'ad',
    'badge' => null,
])

@php
    $image = $ad->images->first()
        ? asset('storage/' . $ad->images->first()->path)
        : asset('assets/images/no-image.jpg');
@endphp

<article class="cs-card">
    <div class="cs-card-media">
        <img src="{{ $image }}" alt="{{ $ad->title }}" loading="lazy">

        @if($badge)
            <span class="cs-badge-location">{{ $badge }}</span>
        @endif

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
        <p class="cs-card-meta">
            @if($ad->category?->icon)
                <i class="{{ $ad->category->icon_class }}"></i>
            @endif
            {{ $ad->category->nom ?? '' }}
        </p>

        <h3 class="cs-card-title">{{ $ad->title }}</h3>

        @if($ad->address)
            <p class="cs-card-location">
                <i class="fa-solid fa-location-dot"></i>
                {{ $ad->address }}
            </p>
        @endif

        <p class="cs-card-stats">
            <span><i class="fa-regular fa-eye"></i> {{ $ad->views }} vue(s)</span>

            @if($ad->created_at)
                <span><i class="fa-regular fa-clock"></i> {{ $ad->created_at->diffForHumans() }}</span>
            @endif
        </p>

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
