@extends('layouts.connected')
@section('title', 'Itinéraire du trajet | ' . config('app.name'))

@php
    /*
     | Recapitulatif de l'itineraire : date, heure et parcours.
     |
     | Les escales etaient lues sur $etapes, une variable que le controleur
     | ne fournit pas — le bloc ne s'affichait donc jamais. Elles viennent
     | desormais de segments, le tableau from / to / price reellement stocke.
     */
    $date  = $covoiturage->date_depart ? \Carbon\Carbon::parse($covoiturage->date_depart) : null;
    $heure = $covoiturage->heure_depart ? \Illuminate\Support\Str::of($covoiturage->heure_depart)->substr(0, 5) : null;

    $escales = collect($covoiturage->segments ?? [])
        ->pluck('to')
        ->filter()
        ->reject(fn ($v) => $v === $covoiturage->destination)
        ->unique()
        ->values();

    $mois = [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
             'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ route('covoiturage.index') }}">Mes trajets</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('covoiturage.edit', $covoiturage->covoiturage_id) }}">Modifier</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Itinéraire</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Itinéraire</h1>
            <p class="sp-subtitle">Le parcours, la date et l'heure de départ de ce trajet.</p>
        </div>

        <a href="{{ route('covoiturage.edit', $covoiturage->covoiturage_id) }}" class="sp-btn-primary">
            Retour au trajet
        </a>
    </header>

    {{-- Date et heure --}}
    <div class="sp-grid is-flat">
        <a href="{{ route('covoiturage.edit-date-time', $covoiturage->covoiturage_id) }}" class="sp-card sp-tile">
            <span class="sp-stat-icon is-brand"><i class="fa-solid fa-calendar-days"></i></span>

            <div class="sp-tile-body">
                <div class="sp-tile-head">
                    <h3>Date du départ</h3>
                </div>
                <p>
                    @if($date)
                        {{ $date->format('d') }} {{ $mois[(int) $date->format('n')] }} {{ $date->format('Y') }}
                    @else
                        Non définie
                    @endif
                </p>
            </div>

            <i class="fa-solid fa-chevron-right sp-tile-arrow"></i>
        </a>

        <a href="{{ route('covoiturage.edit-date-time', $covoiturage->covoiturage_id) }}" class="sp-card sp-tile">
            <span class="sp-stat-icon is-blue"><i class="fa-regular fa-clock"></i></span>

            <div class="sp-tile-body">
                <div class="sp-tile-head">
                    <h3>Heure de départ</h3>
                </div>
                <p>{{ $heure ?: 'Non définie' }}</p>
            </div>

            <i class="fa-solid fa-chevron-right sp-tile-arrow"></i>
        </a>
    </div>

    {{-- Parcours --}}
    <section class="sp-panel">
        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">Parcours</h2>
                <span class="sp-count">
                    {{ $escales->count() }} escale{{ $escales->count() > 1 ? 's' : '' }} entre le départ et l'arrivée
                </span>
            </div>

            <div class="sp-toolbar-actions">
                <a href="{{ route('covoiturage.edit-route', $covoiturage->covoiturage_id) }}" class="sp-act is-edit">
                    Gérer les étapes
                </a>
            </div>
        </div>

        <div class="sp-route-list">
            <a href="{{ route('covoiturage.edit-route', $covoiturage->covoiturage_id) }}" class="sp-route-point is-start">
                <span class="sp-route-dot"></span>
                <div>
                    <span class="sp-route-kind">Point de départ</span>
                    <span class="sp-route-name">{{ $covoiturage->depart ?: 'Non précisé' }}</span>
                </div>
            </a>

            @foreach($escales as $escale)
                <div class="sp-route-point">
                    <span class="sp-route-dot"></span>
                    <div>
                        <span class="sp-route-kind">Escale</span>
                        <span class="sp-route-name">{{ $escale }}</span>
                    </div>
                </div>
            @endforeach

            <a href="{{ route('covoiturage.edit-route', $covoiturage->covoiturage_id) }}" class="sp-route-point is-end">
                <span class="sp-route-dot"></span>
                <div>
                    <span class="sp-route-kind">Destination</span>
                    <span class="sp-route-name">{{ $covoiturage->destination ?: 'Non précisée' }}</span>
                </div>
            </a>
        </div>
    </section>
</div>
@endsection
