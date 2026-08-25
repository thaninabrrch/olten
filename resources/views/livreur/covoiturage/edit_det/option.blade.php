@extends('layouts.connected')
@section('title', 'Places et confort | ' . config('app.name'))

@php
    /*
     | updateOptions attend quatre champs, tous obligatoires sauf le message :
     | nb_places (1 a 10), booking_mode, passenger_mode et message_conducteur.
     | Le mode de reservation est donc repris ici, meme s'il a sa propre page.
     |
     | max_arriere et entre_femmes ne sont pas envoyes : le controleur les
     | deduit de passenger_mode. L'ancien script pilotait un badge « Premium »
     | depuis une case #max_arriere qui n'existait pas dans la page.
     */
    $reglages = json_decode($covoiturage->passenger_mode);
    $modePassagers = old('passenger_mode', $reglages->passenger_mode ?? 'mixed');
    $modeReservation = old('booking_mode', $covoiturage->booking_mode ?: 'instant');
    $places = (int) old('nb_places', $covoiturage->nb_places ?: 1);

    $publics = [
        [
            'value' => 'mixed',
            'titre' => 'Tous publics',
            'texte' => 'Votre trajet est ouvert à tout le monde, sans restriction.',
            'icone' => 'fa-users',
        ],
        [
            'value' => 'womenOnly',
            'titre' => 'Entre femmes',
            'texte' => 'Seules les passagères peuvent réserver une place.',
            'icone' => 'fa-venus',
        ],
        [
            'value' => 'maxBackSeats',
            'titre' => 'Deux à l\'arrière',
            'texte' => 'Jamais trois passagers sur la banquette : plus de place pour chacun.',
            'icone' => 'fa-couch',
        ],
    ];

    $reservations = [
        ['value' => 'instant', 'titre' => 'Réservation instantanée', 'texte' => 'Validée automatiquement.', 'icone' => 'fa-bolt'],
        ['value' => 'manual',  'titre' => 'Validation manuelle',     'texte' => 'Vous acceptez chaque demande.', 'icone' => 'fa-user-check'],
    ];
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ route('covoiturage.index') }}">Mes trajets</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('covoiturage.edit', $covoiturage->covoiturage_id) }}">Modifier</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Places et confort</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Places et confort</h1>
            <p class="sp-subtitle">Le nombre de passagers, les conditions à bord et votre message d'accueil.</p>
        </div>

        <a href="{{ route('covoiturage.edit', $covoiturage->covoiturage_id) }}" class="sp-btn-primary">
            Retour au trajet
        </a>
    </header>

    <form method="POST" action="{{ route('covoiturage.options.update', $covoiturage->covoiturage_id) }}">
        @csrf

        @if ($errors->any())
            <div class="sp-alert">
                <strong>Les options n'ont pas pu être enregistrées.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 1. Places --}}
        <section class="sp-form-section">
            <div class="sp-form-head">
                <span class="sp-step">1</span>
                <div>
                    <h2>Nombre de places</h2>
                    <p>Combien de passagers pouvez-vous accueillir sur ce trajet ?</p>
                </div>
            </div>

            <div class="sp-box is-row">
                <div class="sp-box-head">
                    <span class="sp-label">Places proposées</span>
                    <span class="sp-help">Entre 1 et 10, sans compter le conducteur.</span>
                </div>

                <div class="sp-stepper">
                    <button type="button" onclick="decrementPlaces()" aria-label="Retirer une place">−</button>
                    <span class="sp-stepper-value" id="display_nb_places">{{ $places }}</span>
                    <button type="button" onclick="incrementPlaces()" aria-label="Ajouter une place">+</button>

                    <input type="hidden" name="nb_places" id="input_nb_places" value="{{ $places }}">
                </div>
            </div>
        </section>

        {{-- 2. Reservation --}}
        <section class="sp-form-section">
            <div class="sp-form-head">
                <span class="sp-step">2</span>
                <div>
                    <h2>Mode de réservation</h2>
                    <p>Comment les passagers obtiennent leur place.</p>
                </div>
            </div>

            <div class="sp-form-grid">
                @foreach($reservations as $mode)
                    <label class="sp-card sp-pick is-compact">
                        <input type="radio" name="booking_mode" value="{{ $mode['value'] }}"
                               @checked($modeReservation === $mode['value'])>

                        <span class="sp-pick-body">
                            <span class="sp-pick-head">
                                <span class="sp-stat-icon is-brand"><i class="fa-solid {{ $mode['icone'] }}"></i></span>
                                <span class="sp-pick-check"><i class="fa-solid fa-check"></i></span>
                            </span>

                            <span class="sp-pick-title">{{ $mode['titre'] }}</span>
                            <span class="sp-pick-text">{{ $mode['texte'] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </section>

        {{-- 3. Conditions a bord --}}
        <section class="sp-form-section">
            <div class="sp-form-head">
                <span class="sp-step">3</span>
                <div>
                    <h2>Conditions à bord</h2>
                    <p>À qui s'adresse ce trajet et dans quelles conditions de confort.</p>
                </div>
            </div>

            <div class="sp-form-grid is-three">
                @foreach($publics as $public)
                    <label class="sp-card sp-pick is-compact">
                        <input type="radio" name="passenger_mode" value="{{ $public['value'] }}"
                               @checked($modePassagers === $public['value'])>

                        <span class="sp-pick-body">
                            <span class="sp-pick-head">
                                <span class="sp-stat-icon is-blue"><i class="fa-solid {{ $public['icone'] }}"></i></span>
                                <span class="sp-pick-check"><i class="fa-solid fa-check"></i></span>
                            </span>

                            <span class="sp-pick-title">{{ $public['titre'] }}</span>
                            <span class="sp-pick-text">{{ $public['texte'] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </section>

        {{-- 4. Message --}}
        <section class="sp-form-section">
            <div class="sp-form-head">
                <span class="sp-step">4</span>
                <div>
                    <h2>Message aux passagers</h2>
                    <p>Point de rendez-vous précis, bagages acceptés, habitudes de conduite...</p>
                </div>
            </div>

            <div class="sp-field">
                <label class="sp-label" for="message_conducteur">Votre message</label>
                <textarea name="message_conducteur" id="message_conducteur" rows="4" maxlength="500"
                          placeholder="Ex. Rendez-vous devant la gare, côté sortie nord. Un bagage cabine par personne.">{{ old('message_conducteur', $covoiturage->message_conducteur) }}</textarea>
                <span class="sp-help">500 caractères maximum.</span>
            </div>
        </section>

        <div class="sp-form-actions">
            <a href="{{ route('covoiturage.edit', $covoiturage->covoiturage_id) }}" class="sp-act is-ghost">Annuler</a>
            <button type="submit" class="sp-btn-primary">Enregistrer les options</button>
        </div>
    </form>
</div>

<script>
    // Le controleur accepte de 1 a 10 places
    function ajustePlaces(delta) {
        const input = document.getElementById('input_nb_places');
        const display = document.getElementById('display_nb_places');

        const valeur = (parseInt(input.value, 10) || 1) + delta;
        if (valeur < 1 || valeur > 10) return;

        input.value = valeur;
        display.innerText = valeur;
    }

    function incrementPlaces() { ajustePlaces(1); }
    function decrementPlaces() { ajustePlaces(-1); }
</script>
@endsection
