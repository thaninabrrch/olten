{{--
    Vitrine « Nos services ».

    Elle presente les services de la plateforme et, pour chacun, ses
    categories. Deux niveaux de clic :

        Voir le service          -> /vente
        Une categorie precise    -> /vente/telephones-tablettes
--}}
@extends('layouts.main')

@section('title', 'Nos services - Olten.fr')

@section('content')

<div class="sv-page">

    {{-- En-tete --}}
    <div class="sv-hero">
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
                <div class="sv-hero-stats">
                    <div class="sv-stat">
                        <span class="sv-stat-value">{{ $services->count() }}</span>
                        <span class="sv-stat-label">service(s)</span>
                    </div>
                    <div class="sv-stat">
                        <span class="sv-stat-value">{{ $categoryTotal }}</span>
                        <span class="sv-stat-label">catégorie(s)</span>
                    </div>
                    <div class="sv-stat">
                        <span class="sv-stat-value">{{ $adTotal }}</span>
                        <span class="sv-stat-label">annonce(s) en ligne</span>
                    </div>
                </div>
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
                @endphp

                <article class="sv-card">

                    {{-- Visuel du service : uniquement son image. Les services
                         sans image tombent sur un dégradé teinté, stable. --}}
                    <a href="{{ route('services.show', $service->slug) }}"
                       class="sv-card-visual"
                       style="--tile-hue: {{ $tileHue }};@if($service->image) --tile-image: url('{{ asset('storage/' . $service->image) }}');@endif"
                       aria-label="Voir le service {{ $service->display_name }}"></a>

                    <div class="sv-card-body">
                        <h2 class="sv-card-title">
                            <a href="{{ route('services.show', $service->slug) }}">{{ $service->display_name }}</a>
                        </h2>

                        <p class="sv-card-desc">
                            {{ $service->short_description
                                ?: ($service->description
                                    ?: 'Découvrez toutes les annonces disponibles pour ' . $service->display_name . '.') }}
                        </p>

                        <p class="sv-card-stats">
                            <span><i class="fa-solid fa-layer-group"></i> {{ $service->categories_count }} catégorie(s)</span>
                            <span><i class="fa-solid fa-bullhorn"></i> {{ $service->ads_count }} annonce(s)</span>
                        </p>

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
                                       class="sv-tag sv-tag--more">+{{ $rest }}</a>
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
