@extends('layouts.connected')
@section('title', 'Mes trajets | ' . config('app.name'))

@php
    /*
     | $trajets = trajets a venir du conducteur connecte, deja tries par date
     | de depart decroissante. La cle primaire du modele est covoiturage_id.
     | Les trajets passes sont dans les archives ($archivedCount).
     |
     | Les etapes intermediaires se lisent sur « segments » (tableau de
     | from / to / price) : l'ancienne version interrogeait $trajet->steps,
     | une relation qui n'existe pas sur le modele — le bloc etait donc mort.
     */
    $statuts = [
        'actif'   => ['Actif',      'is-paid'],
        'validé'  => ['Validé',     'is-confirmed'],
        'pending' => ['En attente', 'is-pending'],
        'complet' => ['Complet',    'is-shipped'],
        'inactif' => ['Inactif',    'is-neutral'],
        'annulé'  => ['Annulé',     'is-cancelled'],
    ];

    $total     = $trajets->count();
    $places    = $trajets->sum(fn ($t) => (int) $t->nb_places);
    $recette   = $trajets->sum(fn ($t) => (float) ($t->prix_place ?? 0) * (int) $t->nb_places);

    $mois = [1 => 'janv', 'févr', 'mars', 'avr', 'mai', 'juin', 'juil', 'août', 'sept', 'oct', 'nov', 'déc'];
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Mes trajets</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Mes trajets</h1>
            <p class="sp-subtitle">Vos annonces de covoiturage, leurs places et leurs recettes.</p>
        </div>

        <div class="sp-head-actions">
            <a href="{{ route('trips.received') }}" class="sp-act">
                <i class="fa-solid fa-ticket"></i> Réservations reçues
            </a>
            <a href="{{ route('covoiturage.create') }}" class="sp-btn-primary">
                Publier un trajet
            </a>
        </div>
    </header>

    {{-- Indicateurs --}}
    <div class="sp-stats">
        <div class="sp-stat">
            <span class="sp-stat-icon is-brand"><i class="fa-solid fa-car-side"></i></span>
            <div>
                <span class="sp-stat-value">{{ $total }}</span>
                <span class="sp-stat-label">Trajet{{ $total > 1 ? 's' : '' }} à venir</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-blue"><i class="fa-solid fa-box-archive"></i></span>
            <div>
                <span class="sp-stat-value">{{ $archivedCount }}</span>
                <span class="sp-stat-label">
                    <a href="{{ route('archives', ['type' => 'trajet']) }}">Trajet{{ $archivedCount > 1 ? 's' : '' }} passé{{ $archivedCount > 1 ? 's' : '' }}</a>
                    <small>dans les archives</small>
                </span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-green"><i class="fa-solid fa-users"></i></span>
            <div>
                <span class="sp-stat-value">{{ $places }}</span>
                <span class="sp-stat-label">Place{{ $places > 1 ? 's' : '' }} proposée{{ $places > 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-red"><i class="fa-solid fa-euro-sign"></i></span>
            <div>
                <span class="sp-stat-value">{{ number_format($recette, 2, ',', ' ') }} €</span>
                <span class="sp-stat-label">
                    Recette potentielle
                    <small>toutes places vendues</small>
                </span>
            </div>
        </div>
    </div>

    {{-- Panneau --}}
    <section class="sp-panel">

        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">Mes publications</h2>
                <span class="sp-count">{{ $total }} trajet{{ $total > 1 ? 's' : '' }} à venir</span>
            </div>
        </div>

        @if($total)
            <div class="sp-grid">
                @foreach($trajets as $trajet)
                    @php
                        [$stLabel, $stClass] = $statuts[$trajet->statut] ?? [ucfirst((string) $trajet->statut ?: 'Inconnu'), 'is-neutral'];

                        $date = $trajet->date_depart;
                        $etapes = collect($trajet->segments ?? [])
                            ->pluck('to')
                            ->filter()
                            ->reject(fn ($v) => $v === $trajet->destination)
                            ->unique()
                            ->values();

                        // Places payées sur le sens le plus rempli, nombre de passagers
                        // confirmés et demandes qui attendent son accord (validation manuelle)
                        $reservees  = collect($trajet->legKeys())->map(fn ($l) => $trajet->seatsBooked($l))->max();
                        $restantes  = $trajet->seats_left;
                        $enAttente  = $trajet->paidBookings->where('status', 'pending')->count();
                        $passagers  = $trajet->paidBookings->count() - $enAttente;
                    @endphp

                    <article class="sp-card sp-mission">

                        <div class="sp-mission-head">
                            <div>
                                <span class="sp-status {{ $stClass }}">{{ $stLabel }}</span>

                                <span class="sp-mission-date">
                                    @if($date)
                                        {{ $date->format('d') }} {{ $mois[(int) $date->format('n')] }} {{ $date->format('Y') }}
                                        @if($trajet->heure_depart)
                                            · {{ \Illuminate\Support\Str::of($trajet->heure_depart)->substr(0, 5) }}
                                        @endif
                                    @else
                                        Date non définie
                                    @endif
                                </span>
                            </div>

                            <div class="sp-mission-price">
                                {{ number_format((float) $trajet->prix_place, 2, ',', ' ') }} €
                                <small>par place</small>
                            </div>
                        </div>

                        <div class="sp-mission-body">

                            {{-- Itineraire --}}
                            <div class="sp-trip">
                                <div class="sp-trip-step">
                                    <span class="sp-trip-dot"></span>
                                    <div>
                                        <span class="sp-trip-label">Départ</span>
                                        <span class="sp-trip-value">{{ $trajet->depart ?: 'Non précisé' }}</span>
                                    </div>
                                </div>

                                <div class="sp-trip-step is-end">
                                    <span class="sp-trip-dot"></span>
                                    <div>
                                        <span class="sp-trip-label">Arrivée</span>
                                        <span class="sp-trip-value">{{ $trajet->destination ?: 'Non précisée' }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($etapes->count())
                                <div class="sp-row-meta">
                                    @foreach($etapes as $etape)
                                        <span class="sp-tag">
                                            <i class="fa-solid fa-location-dot"></i>
                                            {{ $etape }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="sp-row-meta">
                                <span class="sp-tag">
                                    <i class="fa-solid fa-users"></i>
                                    @if ($restantes > 0)
                                        {{ $restantes }} place{{ $restantes > 1 ? 's' : '' }} restante{{ $restantes > 1 ? 's' : '' }}
                                    @else
                                        Complet
                                    @endif
                                </span>

                                @if ($reservees)
                                    <span class="sp-tag is-ok">
                                        <i class="fa-solid fa-ticket"></i>
                                        {{ $reservees }} réservée{{ $reservees > 1 ? 's' : '' }}
                                    </span>
                                @endif

                                @if($trajet->retour)
                                    <span class="sp-tag is-ok">
                                        <i class="fa-solid fa-rotate-left"></i>
                                        Retour prévu
                                    </span>
                                @endif

                                @if($trajet->prix_total_affiche)
                                    <span class="sp-tag">
                                        <i class="fa-solid fa-tag"></i>
                                        Trajet complet : {{ number_format((float) $trajet->prix_total_affiche, 2, ',', ' ') }} €
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="sp-actions">
                            <a href="{{ route('trajet.show', ['covoiturage' => $trajet->covoiturage_id]) }}"
                               class="sp-act is-edit">Détails</a>

                            <a href="{{ route('covoiturage.edit', $trajet->covoiturage_id) }}"
                               class="sp-act is-ghost">Modifier</a>

                            @if ($passagers || $enAttente)
                                <a href="{{ route('trips.received') }}#trajet-{{ $trajet->covoiturage_id }}"
                                   class="sp-act is-ghost">
                                    Réservations ({{ $passagers }}){{ $enAttente ? ' · ' . $enAttente . ' à approuver' : '' }}
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="sp-empty">
                <x-empty-state
                    title="Aucun trajet à venir"
                    text="Partagez votre route et commencez à rentabiliser vos déplacements. Vos trajets passés sont dans les archives."
                    :action-url="route('covoiturage.create')"
                    action-label="Publier un trajet" />
            </div>
        @endif
    </section>
</div>
@endsection
