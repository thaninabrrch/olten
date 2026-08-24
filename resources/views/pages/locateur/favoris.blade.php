@extends('layouts.connected')
@section('title', 'Favoris - Olten')

@section('content')
<div class="breadcrumb">
    <a href="#">Accueil</a>
    <span>></span>
    <span>Favoris</span>
</div>

<h1 class="page-title">Favoris</h1>

<!-- SECTION ANNONCES ENREGISTRÉES -->
<div class="favoris-container">
    <div class="section-header">
        <h2 class="section-title">Annonces enregistrées</h2>
    </div>

    <div class="favoris-list" id="favorisList"
         data-empty-image="{{ asset('assets/images/pasdead.png') }}"
         data-browse-url="{{ route('home') }}">

        @forelse ($favorites as $favorite)

            @php
                $isProduct = $favorite->favorite_type === 'product';

                $title = $isProduct
                    ? $favorite->name
                    : $favorite->title;

                $image = $isProduct
                    ? $favorite->images->first()?->image
                    : $favorite->images->first()?->path;

                $url = $isProduct
                    ? route('products.show', $favorite->id)
                    : route('ads.show', $favorite->id);
            @endphp

            <div
                class="favori-card"
                data-id="{{ $favorite->id }}"
                data-type="{{ $favorite->favorite_type }}"
            >

                <a href="{{ $url }}" class="favori-link">

                    <div class="favori-image">
                        <img
                            src="{{ $image
                                ? asset('storage/' . $image)
                                : asset('assets/images/no-image.jpg') }}"
                            alt="{{ $title }}"
                        >
                    </div>

                    <div class="favori-content">
                        <span class="favorite-type">
                            {{ $isProduct ? 'Produit' : 'Annonce' }}
                        </span>

                        <h3 class="favori-title">
                            {{ $title }}
                        </h3>

                        @if($isProduct)
                            <p class="favori-price">
                                {{ number_format($favorite->price, 2) }} €
                            </p>
                        @else
                            <p class="favori-price">
                                {{ number_format($favorite->price_per_day, 2) }} € / jour
                            </p>
                        @endif
                    </div>

                </a>

                <button
                    type="button"
                    class="btn-delete"
                    aria-label="Supprimer des favoris"
                >
                    <i class="fa-solid fa-heart-circle-minus"></i>
                    Supprimer
                </button>

            </div>

        @empty

            <x-empty-state id="emptyState"
                title="Aucun favori enregistré"
                text="Les annonces et produits que vous ajoutez en favori apparaîtront ici."
                :action-url="route('home')"
                action-label="Parcourir les annonces" />

        @endforelse

    </div>

</div>

<script src="{{ asset('assets/js/favoris.js') }}"></script>
@endsection