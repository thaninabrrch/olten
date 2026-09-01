{{--
    Vitrine « Nos services ».

    Elle presente les services de la plateforme et, pour chacun, ses
    categories. Deux niveaux de clic :

        Voir le service          -> /vente
        Une categorie precise    -> /vente/telephones-tablettes

    Les compteurs annoncent TOUTES les offres d'un service — annonces,
    produits, et trajets pour le covoiturage — et non les seules annonces :
    un service qui ne vend que des produits affichait « 0 annonce ».
    Le calcul vit dans ServicePageController::index().
--}}
@extends('layouts.main')

@section('title', 'Nos services - Olten.fr')

@section('content')

<div class="sv-page">

    {{-- En-tete --}}
    <div class="sv-hero">
        <div class="sv-hero-veil"></div>

        <div class="sv-hero-inner">
            <nav class="sv-breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ route('home') }}" class="sv-breadcrumb-link">Accueil</a>
                <i class="fa-solid fa-chevron-right sv-breadcrumb-sep"></i>
                <span class="sv-breadcrumb-current">Nos services</span>
            </nav>

            <h1 class="sv-hero-title">Nos services</h1>

            <p class="sv-hero-subtitle">
                Olten réunit la vente, la location, le covoiturage, la livraison et les prestations
                de services au même endroit. Choisissez un service pour découvrir ses catégories
                et toutes ses annonces.
            </p>

            @if($services->isNotEmpty())
                <p class="olten-count">
                    <span class="olten-count-pill">
                        <i class="fa-solid fa-grip"></i>
                        {{ $services->count() }} service{{ $services->count() > 1 ? 's' : '' }}
                    </span>

                    <span class="olten-count-pill">
                        <i class="fa-solid fa-border-all"></i>
                        {{ $categoryTotal }} catégorie{{ $categoryTotal > 1 ? 's' : '' }}
                    </span>

                    <span class="olten-count-pill">
                        <i class="fa-solid fa-tag"></i>
                        {{ $offerTotal }} offre{{ $offerTotal > 1 ? 's' : '' }}
                    </span>

                    <span class="olten-count-note">
                        {{ $offerTotal > 0 ? 'en ligne en ce moment' : 'pour le moment' }}
                    </span>
                </p>
            @endif
        </div>
    </div>

    @if($services->isNotEmpty())

        <div class="sv-grid">
            @foreach($services as $service)
                @php
                    // Teinte de repli stable, quand aucune image n'a ete
                    // televersee pour le service depuis le back-office.
                    $tileHue = ($service->id * 47) % 360;
                    $shown   = $service->categories->take(5);
                    $rest    = $service->categories_count - $shown->count();
                    $offers  = $service->offers_count;
                @endphp

                <article class="sv-card">

                    {{-- Visuel du service : son image, sinon un aplat teinte
                         stable sur lequel se detache son glyphe. --}}
                    <a href="{{ route('services.show', $service->slug) }}"
                       class="sv-card-visual"
                       style="--tile-hue: {{ $tileHue }};@if($service->image) --tile-image: url('{{ asset('storage/' . $service->image) }}');@endif"
                       aria-label="Voir le service {{ $service->display_name }}">
                        <i class="{{ $service->icon_class }} sv-card-glyph" aria-hidden="true"></i>
                    </a>

                    <div class="sv-card-body">
                        <h2 class="sv-card-title">
                            <a href="{{ route('services.show', $service->slug) }}">{{ $service->display_name }}</a>
                        </h2>

                        <p class="sv-card-desc">
                            {{ $service->short_description
                                ?: ($service->description
                                    ?: 'Découvrez toutes les annonces disponibles pour ' . $service->display_name . '.') }}
                        </p>

                        <div class="sv-card-stats">
                            <span class="sv-card-stat {{ $service->categories_count ? '' : 'is-empty' }}">
                                <i class="fa-solid fa-border-all"></i>
                                {{ $service->categories_count }} catégorie{{ $service->categories_count > 1 ? 's' : '' }}
                            </span>

                            <span class="sv-card-stat {{ $offers ? '' : 'is-empty' }}">
                                <i class="fa-solid fa-tag"></i>
                                {{ $offers ? $offers . ' offre' . ($offers > 1 ? 's' : '') : 'Aucune offre' }}
                            </span>
                        </div>

                        @if($shown->isNotEmpty())
                            <div class="sv-card-tags">
                                @foreach($shown as $category)
                                    <a href="{{ route('services.category', [$service->slug, $category->slug]) }}"
                                       class="sv-tag">
                                        <i class="{{ $category->icon_class }}"></i>
                                        {{ $category->nom }}
                                    </a>
                                @endforeach

                                @if($rest > 0)
                                    <a href="{{ route('services.show', $service->slug) }}"
                                       class="sv-tag sv-tag--more"
                                       aria-label="Voir les {{ $rest }} autres catégories de {{ $service->display_name }}">+{{ $rest }}</a>
                                @endif
                            </div>
                        @endif
                    </div>

                    <a href="{{ route('services.show', $service->slug) }}" class="sv-card-cta">
                        Voir le service
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </article>
            @endforeach
        </div>

    @else
        <x-empty-state
            title="Aucun service disponible pour le moment"
            text="Les services de la plateforme apparaîtront ici dès qu'ils seront publiés."
            :action-url="route('home')"
            action-label="Retour à l'accueil" />
    @endif

</div>

@endsection
