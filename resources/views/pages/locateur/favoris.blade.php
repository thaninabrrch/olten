@extends('layouts.connected')
@section('title', 'Favoris - Olten')

@php
    /*
     | $favorites est une collection (annonces + produits) fournie telle quelle
     | par le controleur : les compteurs portent donc sur la totalite.
     | Les cartes gardent .favori-card / data-id / data-type / .btn-delete,
     | ce sont les points d'accroche de public/assets/js/favoris.js.
     */
    $ads      = $favorites->where('favorite_type', 'ad');
    $products = $favorites->where('favorite_type', 'product');

    $tabs = [
        ''        => ['Tous', $favorites->count()],
        'ad'      => ['Annonces', $ads->count()],
        'product' => ['Produits', $products->count()],
    ];
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Favoris</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Favoris</h1>
            <p class="sp-subtitle">Les annonces et produits que vous avez mis de côté.</p>
        </div>

        <a href="{{ route('home') }}" class="sp-btn-primary">
            Parcourir les annonces
        </a>
    </header>

    {{-- Indicateurs --}}
    <div class="sp-stats is-3">
        <div class="sp-stat">
            <span class="sp-stat-icon is-brand"><i class="fa-solid fa-heart"></i></span>
            <div>
                <span class="sp-stat-value">{{ $favorites->count() }}</span>
                <span class="sp-stat-label">Favori{{ $favorites->count() > 1 ? 's' : '' }} enregistré{{ $favorites->count() > 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-blue"><i class="fa-solid fa-bullhorn"></i></span>
            <div>
                <span class="sp-stat-value">{{ $ads->count() }}</span>
                <span class="sp-stat-label">Annonce{{ $ads->count() > 1 ? 's' : '' }} de location</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-green"><i class="fa-solid fa-box-open"></i></span>
            <div>
                <span class="sp-stat-value">{{ $products->count() }}</span>
                <span class="sp-stat-label">Produit{{ $products->count() > 1 ? 's' : '' }} à la vente</span>
            </div>
        </div>
    </div>

    {{-- Panneau --}}
    <section class="sp-panel">

        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">Mes enregistrements</h2>
                <span class="sp-count">
                    {{ $favorites->count() }} élément{{ $favorites->count() > 1 ? 's' : '' }} en favori
                </span>
            </div>
        </div>

        @if($favorites->count())
            <div class="sp-tabs">
                @foreach($tabs as $value => [$label, $count])
                    <button type="button" class="sp-tab {{ $value === '' ? 'is-active' : '' }}" data-sp-fav-filter="{{ $value }}">
                        {{ $label }} <span class="sp-tab-count">{{ $count }}</span>
                    </button>
                @endforeach
            </div>
        @endif

        <div class="sp-grid" id="favorisList"
             data-empty-image="{{ asset('assets/images/pasdead.png') }}"
             data-browse-url="{{ route('home') }}">

            @forelse ($favorites as $favorite)
                @php
                    $isProduct = $favorite->favorite_type === 'product';
                    $title     = $isProduct ? $favorite->name : $favorite->title;
                    $image     = $isProduct
                        ? $favorite->images->first()?->image
                        : $favorite->images->first()?->path;
                    $url       = $isProduct
                        ? route('products.show', $favorite->id)
                        : route('ads.show', $favorite->id);
                    $price     = $isProduct ? $favorite->price : $favorite->price_per_day;
                @endphp

                <article class="sp-card favori-card"
                         data-id="{{ $favorite->id }}"
                         data-type="{{ $favorite->favorite_type }}">

                    <a href="{{ $url }}" class="sp-media" title="Voir la fiche">
                        <img src="{{ $image ? asset('storage/' . $image) : asset('assets/images/no-image.jpg') }}"
                             alt="{{ $title }}" loading="lazy">

                        <div class="sp-media-badges">
                            <span class="sp-badge is-type">{{ $isProduct ? 'Produit' : 'Annonce' }}</span>
                        </div>
                    </a>

                    <div class="sp-body">
                        <div class="sp-list-main">
                            <span class="sp-chip">
                                <i class="fa-solid fa-tag"></i>
                                {{ $favorite->category->nom ?? 'Sans catégorie' }}
                            </span>

                            <a href="{{ $url }}" class="sp-name">{{ $title }}</a>

                            <div class="sp-fav-price">
                                {{ number_format((float) $price, 2, ',', ' ') }} €
                                <small>{{ $isProduct ? "l'unité" : '/ jour' }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="sp-actions">
                        <a href="{{ $url }}" class="sp-act is-edit">Voir</a>

                        <button type="button" class="sp-act is-cancel btn-delete" aria-label="Retirer des favoris">
                            Retirer
                        </button>
                    </div>
                </article>

            @empty
                <x-empty-state id="emptyState"
                    title="Aucun favori enregistré"
                    text="Les annonces et produits que vous ajoutez en favori apparaîtront ici."
                    :action-url="route('home')"
                    action-label="Parcourir les annonces" />
            @endforelse
        </div>

        <p class="sp-nores" data-sp-fav-nores>Aucun élément dans cette catégorie.</p>
    </section>
</div>

<script src="{{ asset('assets/js/favoris.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tri local Tous / Annonces / Produits sur les cartes deja affichees
        const tabs = document.querySelectorAll('[data-sp-fav-filter]');
        const list = document.getElementById('favorisList');
        const none = document.querySelector('[data-sp-fav-nores]');

        if (!tabs.length || !list) return;

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                const filter = tab.dataset.spFavFilter;
                let visible = 0;

                tabs.forEach(t => t.classList.toggle('is-active', t === tab));

                list.querySelectorAll('.favori-card').forEach(function (card) {
                    const match = !filter || card.dataset.type === filter;
                    card.classList.toggle('is-hidden', !match);
                    if (match) visible++;
                });

                if (none) none.classList.toggle('is-shown', visible === 0);
            });
        });
    });
</script>
@endsection
