@extends('layouts.main')

@section('title', $from . ' → ' . $to . ' en covoiturage - Olten.fr')

@section('content')

<div class="cv-page">

    {{-- Hero --}}
    <section class="cv-hero cv-hero--compact">
        <div class="cv-hero-bg">
            <img src="{{ $image }}" alt="{{ $from }} - {{ $to }}">
            <div class="cv-hero-overlay"></div>
        </div>

        <div class="cv-hero-content">
            <a href="{{ route('services.show', 'covoiturage') }}#cv-trajets" class="cv-hero-back">
                <i class="fa-solid fa-arrow-left"></i> Tous les itinéraires
            </a>
            <span class="cv-hero-tag">— Covoiturage</span>
            <h1 class="cv-hero-title">
                {{ $from }} <span class="cv-hero-title-accent">→ {{ $to }}</span>
            </h1>
            <p class="cv-hero-subtitle">
                {{ $total }} trajet{{ $total > 1 ? 's' : '' }} proposé{{ $total > 1 ? 's' : '' }} par nos conducteurs sur cette liaison.
            </p>
        </div>
    </section>

    {{-- Recherche --}}
    <section class="cv-search-section">
        <form class="cv-search-form" action="{{ route('covoiturage.trips') }}" method="GET">
            <input type="hidden" name="from" value="{{ $from }}">
            <input type="hidden" name="to" value="{{ $to }}">

            <div class="cv-search-field">
                <label for="cvStartDate">Départ à partir du</label>
                <div class="cv-search-input">
                    <input type="date" id="cvStartDate" name="start_date" value="{{ $filters['start_date'] }}">
                </div>
            </div>

            <div class="cv-search-field">
                <label for="cvEndDate">Départ jusqu'au</label>
                <div class="cv-search-input">
                    <input type="date" id="cvEndDate" name="end_date" value="{{ $filters['end_date'] }}">
                </div>
            </div>

            <div class="cv-search-field">
                <label for="cvPersons">Nombre de personnes</label>
                <div class="cv-search-input">
                    <i class="fa-solid fa-user-group"></i>
                    <select id="cvPersons" name="persons">
                        <option value="">Peu importe</option>
                        @for ($i = 1; $i <= 4; $i++)
                            <option value="{{ $i }}" @selected($filters['persons'] === $i)>
                                {{ $i }} personne{{ $i > 1 ? 's' : '' }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>

            <button type="submit" class="cv-search-btn">Rechercher</button>
        </form>
    </section>

    {{-- Liste des trajets --}}
    <section class="cv-trips">
        <div class="cv-trips-header">
            <h2 class="cv-trips-title">
                {{ $from }} <i class="fa-solid fa-arrow-right"></i> {{ $to }}
            </h2>
            <p class="cv-trips-count">
                {{ $total }} trajet{{ $total > 1 ? 's' : '' }} à afficher
            </p>
        </div>

        @if ($trips->isEmpty())
            <div class="cv-empty">
                <span class="cv-empty-icon"><i class="fa-solid fa-route"></i></span>
                <h3 class="cv-empty-title">Aucun trajet sur cette liaison</h3>
                <p class="cv-empty-text">
                    Aucun trajet ne correspond à ces dates. Élargissez la période ou consultez les autres itinéraires.
                </p>
                <a href="{{ route('services.show', 'covoiturage') }}#cv-trajets" class="cv-empty-btn">
                    Voir tous les itinéraires
                </a>
            </div>
        @else
            <div class="cv-trip-list">
                @foreach ($trips as $trip)
                    @php
                        $driver   = $trip->conducteur;
                        $vehicle  = $driver?->vehicle;
                        $avatar   = $trip->photo_conducteur
                            ?: ($driver?->profile_photo ? asset('storage/' . $driver->profile_photo) : null);

                        $departure = \Illuminate\Support\Str::substr((string) $trip->heure_depart, 0, 5);
                        $duration  = (int) ($trip->selected_route['duration'] ?? 0);
                        $distance  = (float) ($trip->selected_route['distance'] ?? 0);

                        $arrival = null;
                        if ($departure && $duration > 0) {
                            $arrival = \Illuminate\Support\Carbon::createFromFormat('H:i', $departure)
                                        ->addSeconds($duration)->format('H:i');
                        }

                        $price  = $trip->prix_total_affiche ?: $trip->prix_place;
                        $isFull = $trip->statut === 'complet' || $trip->nb_places < 1;

                        $modes = [
                            'womenOnly'     => ['fa-venus', 'Entre femmes'],
                            'maxBackSeats'  => ['fa-user-group', 'Max 2 à l\'arrière'],
                            'mixed'         => ['fa-users', 'Mixte'],
                        ];
                        $mode = $modes[$trip->passenger_mode] ?? null;
                    @endphp

                    <article class="cv-trip">
                        <span class="cv-trip-tag {{ $trip->retour ? 'is-return' : '' }}">
                            <i class="fa-solid {{ $trip->retour ? 'fa-arrow-right-arrow-left' : 'fa-arrow-right-long' }}"></i>
                            {{ $trip->retour ? 'Aller-retour' : 'Aller simple' }}
                        </span>

                        <div class="cv-trip-grid">

                            {{-- Itinéraire --}}
                            <div class="cv-trip-route">
                                <div class="cv-trip-stop">
                                    <span class="cv-trip-time">{{ $departure ?: '--:--' }}</span>
                                    <span class="cv-trip-dot is-start"></span>
                                    <span class="cv-trip-place">
                                        <strong>{{ $trip->depart_ville }}</strong>
                                        <small>{{ $trip->depart }}</small>
                                    </span>
                                </div>

                                <div class="cv-trip-line">
                                    <span class="cv-trip-line-rail"></span>
                                    <span class="cv-trip-line-meta">
                                        @if ($duration > 0)
                                            <span><i class="fa-regular fa-clock"></i> {{ intdiv($duration, 3600) }}h{{ str_pad((string) intdiv($duration % 3600, 60), 2, '0', STR_PAD_LEFT) }}</span>
                                        @endif
                                        @if ($distance > 0)
                                            <span><i class="fa-solid fa-road"></i> {{ number_format($distance / 1000, 0, ',', ' ') }} km</span>
                                        @endif
                                        @if (is_array($trip->segments) && count($trip->segments) > 1)
                                            <span><i class="fa-solid fa-location-dot"></i> {{ count($trip->segments) - 1 }} arrêt{{ count($trip->segments) > 2 ? 's' : '' }}</span>
                                        @endif
                                    </span>
                                </div>

                                <div class="cv-trip-stop">
                                    <span class="cv-trip-time">{{ $arrival ?: '--:--' }}</span>
                                    <span class="cv-trip-dot is-end"></span>
                                    <span class="cv-trip-place">
                                        <strong>{{ $trip->destination_ville }}</strong>
                                        <small>{{ $trip->destination }}</small>
                                    </span>
                                </div>
                            </div>

                            {{-- Conducteur & véhicule --}}
                            <div class="cv-trip-side">
                                <div class="cv-trip-driver">
                                    @if ($avatar)
                                        <img src="{{ $avatar }}" alt="{{ $driver->name ?? 'Conducteur' }}">
                                    @else
                                        <span class="cv-trip-driver-initial">
                                            {{ strtoupper(mb_substr($driver->name ?? '?', 0, 1)) }}
                                        </span>
                                    @endif
                                    <span class="cv-trip-driver-info">
                                        <strong>{{ $driver->name ?? 'Conducteur' }}</strong>
                                        @if ($driver?->is_approved)
                                            <small class="is-verified"><i class="fa-solid fa-circle-check"></i> Vérifié</small>
                                        @else
                                            <small>Conducteur</small>
                                        @endif
                                    </span>
                                </div>

                                <div class="cv-trip-vehicle">
                                    <span class="cv-trip-vehicle-icon"><i class="fa-solid fa-car-side"></i></span>
                                    <span class="cv-trip-vehicle-info">
                                        <strong>
                                            {{ $vehicle ? trim(\Illuminate\Support\Str::title($vehicle->marque . ' ' . $vehicle->modele)) : 'Véhicule non renseigné' }}
                                        </strong>
                                        <small>
                                            {{ $trip->nb_places }} place{{ $trip->nb_places > 1 ? 's' : '' }} disponible{{ $trip->nb_places > 1 ? 's' : '' }}
                                            @if ($vehicle?->couleur)
                                                · {{ $vehicle->couleur }}
                                            @endif
                                        </small>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="cv-trip-footer">
                            <div class="cv-trip-chips">
                                <span class="cv-trip-chip">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ $trip->date_depart?->translatedFormat('D d M Y') }}
                                </span>
                                @if ($mode)
                                    <span class="cv-trip-chip"><i class="fa-solid {{ $mode[0] }}"></i> {{ $mode[1] }}</span>
                                @endif
                                @if ($trip->booking_mode === 'instant')
                                    <span class="cv-trip-chip is-instant"><i class="fa-solid fa-bolt"></i> Réservation immédiate</span>
                                @endif
                                @if ($isFull)
                                    <span class="cv-trip-chip is-full"><i class="fa-solid fa-ban"></i> Complet</span>
                                @endif
                            </div>

                            <div class="cv-trip-action">
                                <span class="cv-trip-price">
                                    {{ number_format((float) $price, 2, ',', ' ') }}&nbsp;€
                                    <small>/ place</small>
                                </span>
                                <a href="{{ route('covoiturage.trip', $trip->covoiturage_id) }}" class="cv-trip-btn">
                                    Voir détails
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($trips->hasPages())
                <div class="cv-pagination">
                    {{ $trips->links() }}
                </div>
            @endif
        @endif
    </section>

</div>

@endsection
