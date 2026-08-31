@extends('layouts.main')

@section('title', 'Covoiturage - Olten.fr')

@section('content')

<div class="cv-page">

    {{-- Hero --}}
    <section class="cv-hero">
        <div class="cv-hero-bg">
            <img src="{{ asset('assets/images/location-voiture.jpg.jpeg') }}" alt="Covoiturage">
            <div class="cv-hero-overlay"></div>
        </div>

        <div class="cv-hero-content">
            <span class="cv-hero-tag">— Axé sur le trajet personnalisé</span>
            <h1 class="cv-hero-title">Réservez votre <span class="cv-hero-title-accent">trajet.</span></h1>
            <p class="cv-hero-subtitle">Profitez d'une expérience de covoiturage unique. Confort premium, sécurité totale et flexibilité à la demande.</p>

            @if ($stats['trips'])
                <ul class="cv-hero-stats">
                    <li>
                        <strong>{{ $stats['trips'] }}</strong>
                        <span>trajet{{ $stats['trips'] > 1 ? 's' : '' }} à venir</span>
                    </li>
                    <li>
                        <strong>{{ $stats['routes'] }}</strong>
                        <span>itinéraire{{ $stats['routes'] > 1 ? 's' : '' }}</span>
                    </li>
                    <li>
                        <strong>{{ $stats['cities'] }}</strong>
                        <span>destination{{ $stats['cities'] > 1 ? 's' : '' }}</span>
                    </li>
                    <li>
                        <strong>{{ $stats['drivers'] }}</strong>
                        <span>conducteur{{ $stats['drivers'] > 1 ? 's' : '' }}</span>
                    </li>
                </ul>
            @endif
        </div>
    </section>

    {{-- Search bar — section séparée, chevauche le bas du hero.
         Villes, places et prix viennent des trajets publiés : la barre ne
         propose que des critères qui ont des résultats. --}}
    <section class="cv-search-section">
        <form class="cv-search-form cv-search-form--wide" action="{{ route('services.show', 'covoiturage') }}" method="GET">
            <div class="cv-search-field">
                <label for="cvDeparture">Lieu de départ</label>
                <div class="cv-search-input">
                    <i class="fa-solid fa-location-dot"></i>
                    <input type="text" id="cvDeparture" name="departure" list="cvDepartureCities"
                           value="{{ $filters['departure'] }}" placeholder="Ville de départ">
                </div>
            </div>

            <div class="cv-search-field">
                <label for="cvArrival">Lieu de fin</label>
                <div class="cv-search-input">
                    <i class="fa-solid fa-location-dot"></i>
                    <input type="text" id="cvArrival" name="arrival" list="cvArrivalCities"
                           value="{{ $filters['arrival'] }}" placeholder="Ville d'arrivée">
                </div>
            </div>

            <div class="cv-search-field">
                <label for="cvStartDate">Départ à partir du</label>
                <div class="cv-search-input">
                    <input type="date" id="cvStartDate" name="start_date"
                           value="{{ $filters['start_date'] }}" min="{{ now()->toDateString() }}">
                </div>
            </div>

            <div class="cv-search-field">
                <label for="cvEndDate">Départ jusqu'au</label>
                <div class="cv-search-input">
                    <input type="date" id="cvEndDate" name="end_date"
                           value="{{ $filters['end_date'] }}"
                           min="{{ $filters['start_date'] ?: now()->toDateString() }}">
                </div>
            </div>

            <div class="cv-search-field">
                <label for="cvPersons">Nombre de personnes</label>
                <div class="cv-search-input">
                    <i class="fa-solid fa-user-group"></i>
                    <select id="cvPersons" name="persons">
                        <option value="">Peu importe</option>
                        @for ($i = 1; $i <= max($criteria['seats'], 1); $i++)
                            <option value="{{ $i }}" @selected($filters['persons'] === $i)>
                                {{ $i }} personne{{ $i > 1 ? 's' : '' }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="cv-search-field">
                <label for="cvMaxPrice">Prix maximum</label>
                <div class="cv-search-input">
                    <i class="fa-solid fa-euro-sign"></i>
                    <input type="number" id="cvMaxPrice" name="max_price" min="0" step="1"
                           max="{{ $criteria['max_price'] ? ceil($criteria['max_price']) : null }}"
                           value="{{ $filters['max_price'] ?: '' }}"
                           placeholder="{{ $criteria['max_price']
                                ? "Jusqu'à " . number_format((float) $criteria['max_price'], 0, ',', ' ') . " €"
                                : 'Sans limite' }}">
                </div>
            </div>

            <div class="cv-search-field">
                <label for="cvSort">Trier par</label>
                <div class="cv-search-input">
                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                    <select id="cvSort" name="sort">
                        <option value="">Départ le plus tôt</option>
                        <option value="price" @selected($filters['sort'] === 'price')>Prix le plus bas</option>
                    </select>
                </div>
            </div>

            <div class="cv-search-actions">
                @if ($hasFilters)
                    <a href="{{ route('services.show', 'covoiturage') }}#cv-trajets" class="cv-search-reset">
                        <i class="fa-solid fa-rotate-left"></i> Réinitialiser
                    </a>
                @endif

                <button type="submit" class="cv-search-btn">Rechercher</button>
            </div>
        </form>

        <datalist id="cvDepartureCities">
            @foreach ($cities['from'] as $city)
                <option value="{{ $city }}"></option>
            @endforeach
        </datalist>

        <datalist id="cvArrivalCities">
            @foreach ($cities['to'] as $city)
                <option value="{{ $city }}"></option>
            @endforeach
        </datalist>
    </section>

    {{-- Itinéraires disponibles --}}
    <section class="cv-destinations" id="cv-trajets">
        <div class="cv-destinations-header">
            <div>
                <h2 class="cv-section-title">Destinations disponibles</h2>
                <p class="olten-count">
                    <span class="olten-count-pill">
                        <i class="fa-solid fa-route"></i>
                        {{ $routes->total() }} itinéraire{{ $routes->total() > 1 ? 's' : '' }}
                    </span>
                    <span class="olten-count-pill">
                        <i class="fa-solid fa-car-side"></i>
                        {{ $tripTotal }} trajet{{ $tripTotal > 1 ? 's' : '' }}
                    </span>
                    <span class="olten-count-note">
                        {{ $hasFilters ? 'correspondant à votre recherche' : 'à venir' }}
                    </span>
                </p>
            </div>

            @if ($hasFilters)
                <a href="{{ route('services.show', 'covoiturage') }}#cv-trajets" class="cv-destinations-link">
                    <i class="fa-solid fa-rotate-left"></i> Voir tous les itinéraires
                </a>
            @endif
        </div>

        @if ($routes->isEmpty())
            <div class="cv-empty">
                <span class="cv-empty-icon"><i class="fa-solid fa-route"></i></span>
                <h3 class="cv-empty-title">
                    {{ $hasFilters ? 'Aucun trajet ne correspond à votre recherche' : 'Aucun trajet publié pour le moment' }}
                </h3>
                <p class="cv-empty-text">
                    {{ $hasFilters
                        ? 'Élargissez vos dates ou modifiez les villes pour voir davantage de trajets.'
                        : 'Les prochains trajets publiés par nos conducteurs apparaîtront ici.' }}
                </p>
                @if ($hasFilters)
                    <a href="{{ route('services.show', 'covoiturage') }}#cv-trajets" class="cv-empty-btn">
                        Voir tous les itinéraires
                    </a>
                @endif
            </div>
        @else
            <div class="cv-destinations-grid">
                @foreach ($routes as $route)
                    @php
                        $link = route('covoiturage.trips', array_filter([
                            'from'       => $route['from'],
                            'to'         => $route['to'],
                            'start_date' => $filters['start_date'],
                            'end_date'   => $filters['end_date'],
                            'persons'    => $filters['persons'] ?: null,
                        ]));
                    @endphp

                    <a href="{{ $link }}" class="cv-card cv-card--route">
                        <div class="cv-card-media">
                            <img src="{{ $route['image'] }}" alt="{{ $route['from'] }} - {{ $route['to'] }}" loading="lazy">
                            <span class="cv-card-count">
                                {{ $route['count'] }} trajet{{ $route['count'] > 1 ? 's' : '' }}
                            </span>
                        </div>

                        <div class="cv-card-body">
                            <h3 class="cv-card-route">
                                {{ $route['from'] }} <i class="fa-solid fa-arrow-right"></i> {{ $route['to'] }}
                            </h3>
                            <p class="cv-card-date">
                                Prochain départ le {{ $route['next']?->translatedFormat('d F Y') }}
                            </p>

                            <div class="cv-card-top">
                                <span class="cv-card-price">
                                    @if ($route['min_price'])
                                        <small>dès</small> {{ number_format((float) $route['min_price'], 2, ',', ' ') }}&nbsp;€
                                    @else
                                        <small>Prix sur demande</small>
                                    @endif
                                </span>
                                <span class="cv-card-btn">
                                    Voir les trajets
                                </span>
                            </div>

                            <div class="cv-card-footer">
                                <div class="cv-card-avatars">
                                    @foreach ($route['drivers'] as $driver)
                                        @if ($driver->profile_photo)
                                            <img src="{{ asset('storage/' . $driver->profile_photo) }}" alt="{{ $driver->name }}">
                                        @else
                                            <span class="cv-card-avatar-initial">
                                                {{ strtoupper(mb_substr($driver->name ?? '?', 0, 1)) }}
                                            </span>
                                        @endif
                                    @endforeach
                                    <span class="cv-card-drivers-label">
                                        {{ $route['drivers']->count() > 1
                                            ? $route['drivers']->count() . ' conducteurs'
                                            : ($route['drivers']->first()->name ?? 'Conducteur') }}
                                    </span>
                                </div>

                                <span class="cv-card-status">
                                    {{ $route['seats'] }} place{{ $route['seats'] > 1 ? 's' : '' }}
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            @if ($routes->hasPages())
                <div class="cv-pagination">
                    {{ $routes->links() }}
                </div>
            @endif
        @endif
    </section>

    {{-- Bannière conducteur --}}
    <section class="cv-driver-banner">
            <div class="cv-driver-bg">
                <img src="{{ asset('assets/images/conducteurtz.jpg.jpeg') }}" alt="Devenir conducteur">
                <div class="cv-driver-overlay"></div>

                <div class="cv-driver-content">
                    <span class="cv-driver-badge">Offre Conducteur</span>
                    <h2 class="cv-driver-title">Récupérez <span class="cv-driver-title-accent">90&nbsp;€</span> par trajet.</h2>
                    <p class="cv-driver-subtitle">Vous avez une voiture ? Faites-la travailler pour vous (et pas l'inverse). Récupérez jusqu'à 90&nbsp;€ en covoiturage sur un trajet de 300&nbsp;km avec 3 passagers.</p>
                    <a href="{{ route('covoiturage.create') }}" class="cv-driver-btn">Publier un trajet</a>
                </div>
            </div>
    </section>

    {{-- Notre engagement --}}
    <section class="cv-engagement">
        <h2 class="cv-section-title">Notre Engagement</h2>

        <div class="cv-engagement-grid">
            <div class="cv-engagement-card">
                <span class="cv-engagement-icon"><i class="fa-solid fa-hand"></i></span>
                <h3 class="cv-engagement-title">L'autonomie absolue</h3>
                <p class="cv-engagement-desc">Libérez-vous des contraintes horaires. Organisez le trajet selon vos propres règles, sans jamais compromettre votre confort.</p>
            </div>

            <div class="cv-engagement-card">
                <span class="cv-engagement-icon"><i class="fa-solid fa-dollar-sign"></i></span>
                <h3 class="cv-engagement-title">Le luxe de l'épargne</h3>
                <p class="cv-engagement-desc">Ne renoncez plus jamais au confort par souci de budget. Accédez à un partage des dépenses qui préserve votre fin de mois tout en offrant tout le confort recherché.</p>
            </div>

            <div class="cv-engagement-card">
                <span class="cv-engagement-icon"><i class="fa-regular fa-circle-check"></i></span>
                <h3 class="cv-engagement-title">Sérénité certifiée</h3>
                <p class="cv-engagement-desc">Nous sélectionnons rigoureusement nos partenaires et vérifions chaque profil pour garantir votre sécurité à chaque instant, sur chaque trajet.</p>
            </div>
        </div>
    </section>

</div>

@endsection
