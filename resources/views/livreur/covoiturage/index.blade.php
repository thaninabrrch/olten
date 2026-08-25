@extends('layouts.connected')
@section('title', 'Mes trajets | ' . config('app.name'))

@php
    /*
     | $trajets = Covoiturage du conducteur connecte, deja tries par date de
     | depart decroissante. La cle primaire du modele est covoiturage_id.
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
    $aVenir    = $trajets->filter(fn ($t) => $t->date_depart && $t->date_depart->isFuture())->count();
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

        <a href="{{ route('covoiturage.create') }}" class="sp-btn-primary">
            Publier un trajet
        </a>
    </header>

    {{-- Indicateurs --}}
    <div class="sp-stats">
        <div class="sp-stat">
            <span class="sp-stat-icon is-brand"><i class="fa-solid fa-car-side"></i></span>
            <div>
                <span class="sp-stat-value">{{ $total }}</span>
                <span class="sp-stat-label">Trajet{{ $total > 1 ? 's' : '' }} publié{{ $total > 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-blue"><i class="fa-regular fa-clock"></i></span>
            <div>
                <span class="sp-stat-value">{{ $aVenir }}</span>
                <span class="sp-stat-label">À venir</span>
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
                <span class="sp-count">{{ $total }} trajet{{ $total > 1 ? 's' : '' }} au total</span>
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

                        $passe = $date && $date->isPast();
                    @endphp

                    <article class="sp-card sp-mission {{ $passe ? 'is-out' : '' }}">

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
                                    {{ $trajet->nb_places }} place{{ $trajet->nb_places > 1 ? 's' : '' }}
                                </span>

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
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="sp-empty">
                <x-empty-state
                    title="Aucun trajet pour le moment"
                    text="Partagez votre route et commencez à rentabiliser vos déplacements."
                    :action-url="route('covoiturage.create')"
                    action-label="Publier un trajet" />
            </div>
        @endif
    </section>
</div>
@endsection
