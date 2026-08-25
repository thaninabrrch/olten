@extends('layouts.connected')
@section('title', 'Date et heure du trajet | ' . config('app.name'))

@php
    /*
     | Deux champs seulement : date_depart et heure_depart, lus par
     | updateDateTime. Les noms sont conserves tels quels.
     */
    $date  = old('date_depart', $covoiturage->date_depart?->format('Y-m-d'));
    $heure = old('heure_depart', \Illuminate\Support\Str::of((string) $covoiturage->heure_depart)->substr(0, 5));
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ route('covoiturage.index') }}">Mes trajets</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('covoiturage.edit', $covoiturage->covoiturage_id) }}">Modifier</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('covoiturage.edititen.edit', $covoiturage->covoiturage_id) }}">Itinéraire</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Date et heure</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Date et heure</h1>
            <p class="sp-subtitle">Quand partez-vous de {{ $covoiturage->depart ?: 'votre point de départ' }} ?</p>
        </div>

        <a href="{{ route('covoiturage.edititen.edit', $covoiturage->covoiturage_id) }}" class="sp-btn-primary">
            Retour à l'itinéraire
        </a>
    </header>

    <form action="{{ route('covoiturage.update-date-time', $covoiturage->covoiturage_id) }}" method="POST">
        @csrf

        @if ($errors->any())
            <div class="sp-alert">
                <strong>La date n'a pas pu être enregistrée.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="sp-form-section">
            <div class="sp-form-head">
                <span class="sp-step">1</span>
                <div>
                    <h2>Départ</h2>
                    <p>Les passagers verront cet horaire sur votre annonce.</p>
                </div>
            </div>

            <div class="sp-form-grid">
                <div class="sp-box">
                    <div class="sp-box-head">
                        <label class="sp-label" for="date_depart">Date du départ <span class="sp-req">*</span></label>
                        <span class="sp-help">Le jour où vous prenez la route.</span>
                    </div>

                    <input type="date" name="date_depart" id="date_depart" class="sp-input"
                           min="{{ now()->format('Y-m-d') }}" value="{{ $date }}" required>
                </div>

                <div class="sp-box">
                    <div class="sp-box-head">
                        <label class="sp-label" for="heure_depart">Heure du départ <span class="sp-req">*</span></label>
                        <span class="sp-help">Prévoyez une marge pour le point de rendez-vous.</span>
                    </div>

                    <input type="time" name="heure_depart" id="heure_depart" class="sp-input"
                           value="{{ $heure }}" required>
                </div>
            </div>
        </section>

        <div class="sp-form-actions">
            <a href="{{ route('covoiturage.edititen.edit', $covoiturage->covoiturage_id) }}" class="sp-act is-ghost">Annuler</a>
            <button type="submit" class="sp-btn-primary">Enregistrer</button>
        </div>
    </form>
</div>
@endsection
