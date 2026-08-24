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
@endphp

<div class="cs-page">

    {{-- Bloc 1 : titre du service et fil d'Ariane --}}
    <div class="cs-hero">
        <div class="cs-hero-inner">
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

            <h1 class="cs-hero-title">{{ $selectedCategory->nom ?? $service->display_name }}</h1>

            <p class="cs-hero-subtitle">
                {{ $selectedCategory?->description
                    ?: ($service->short_description
                        ?: ($service->description
                            ?: 'Découvrez toutes les annonces disponibles pour ' . $service->display_name . '.')) }}
            </p>
        </div>
    </div>

    {{-- Bloc 2 : les categories du service, cliquables --}}
    <x-services.categories :service="$service"
                           :categories="$categories"
                           :selected="$selectedCategory" />

    {{-- Bloc 3 : carte des annonces localisees --}}
    <x-services.map :points="$mapPoints" />

    {{-- Bloc 4 : filtres + grille d'annonces --}}
    <section class="cs-listings">

        <x-services.filters :action="$selfUrl"
                            :reset-url="$selfUrl"
                            :cities="$cities" />

        <div class="cs-listings-header">
            <div>
                <h2 class="cs-listings-title">
                    {{ $selectedCategory ? $selectedCategory->nom : 'Toutes les offres' }}
                </h2>
                <span class="cs-listings-count">
                    {{ $counts['total'] }} résultat(s)
                    @if($counts['annonce'] && $counts['produit'])
                        — {{ $counts['annonce'] }} annonce(s), {{ $counts['produit'] }} produit(s)
                    @endif
                </span>
            </div>
        </div>

        @if($listings->isNotEmpty())
            <div class="cs-cards-grid">
                @foreach($listings as $listing)
                    <x-services.listing-card :listing="$listing" :badge="$service->display_name" />
                @endforeach
            </div>

            <div class="cs-pagination">
                {{ $listings->links() }}
            </div>
        @else
            {{-- Etat vide de la grille --}}
            <x-empty-state
                :action-url="$selectedCategory || $hasFilters
                                ? route('services.show', $service->slug)
                                : route('ads.create')"
                :action-label="$selectedCategory || $hasFilters
                                ? 'Voir toutes les annonces'
                                : 'Publier la première annonce'"
                :action-auth="! $selectedCategory && ! $hasFilters"
                :text="$selectedCategory
                        ? 'Il n\'y a actuellement aucune annonce ni produit dans la catégorie ' . $selectedCategory->nom . '.'
                        : 'Soyez le premier à publier dans ' . $service->display_name . ' sur la plateforme Olten.'" />
        @endif

    </section>

    {{-- Bloc 5 : annonces populaires du service --}}
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

@endsection
