@extends('layouts.connected')
@section('title', 'Date et heure du trajet | ' . config('app.name'))

@php
    /*
     | Date et heure de l'aller (date_depart, heure_depart) et, s'il existe,
     | du retour (return_date, return_time), lus par updateDateTime.
     |
     | Un sens reserve ($bookedLegs) garde sa date : le champ est en lecture
     | seule. Son horaire ne peut glisser que de $shift minutes, et les
     | passagers de ce sens sont prevenus du decalage.
     */
    $allerBooked  = in_array('aller', $bookedLegs, true);
    $retourBooked = in_array('retour', $bookedLegs, true);

    $heureAller  = \Illuminate\Support\Str::of((string) $covoiturage->heure_depart)->substr(0, 5);
    $heureRetour = \Illuminate\Support\Str::of((string) $covoiturage->return_time)->substr(0, 5);

    $date  = $allerBooked ? $covoiturage->date_depart?->format('Y-m-d') : old('date_depart', $covoiturage->date_depart?->format('Y-m-d'));
    $heure = old('heure_depart', $heureAller);

    $dateRetour  = $retourBooked ? $covoiturage->return_date?->format('Y-m-d') : old('return_date', $covoiturage->return_date?->format('Y-m-d'));
    $heureRetourSaisie = old('return_time', $heureRetour);
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

        {{-- Ouvert depuis les archives : une nouvelle date republie le trajet,
             sauf s'il a eu des passagers (on ne deplace pas un trajet effectue) --}}
        @if ($covoiturage->isPast())
            <div class="sp-note">
                <i class="fa-solid fa-box-archive"></i>
                @if ($allerBooked)
                    Ce trajet est parti le {{ $covoiturage->date_depart->format('d/m/Y') }} avec des passagers : sa date
                    ne peut plus changer. Pour le reproposer, dupliquez-le depuis la page « Modifier ».
                @else
                    Ce trajet est archivé : il est parti le {{ $covoiturage->date_depart->format('d/m/Y') }} et n'est
                    plus visible sur la plateforme. Choisissez une nouvelle date de départ pour le republier.
                @endif
            </div>
        @elseif ($allerBooked || $retourBooked)
            <div class="sp-note">
                <i class="fa-solid fa-lock"></i>
                Des passagers ont réservé {{ $allerBooked && $retourBooked ? 'l\'aller et le retour' : ($allerBooked ? 'l\'aller' : 'le retour') }} :
                la date ne change plus, et l'horaire ne peut être décalé que de {{ $shift }} minutes au plus.
                Les passagers concernés sont prévenus du nouvel horaire, dans leur cloche et par e-mail.
            </div>
        @endif

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
                    <h2>{{ $covoiturage->retour ? 'Aller' : 'Départ' }}</h2>
                    <p>Les passagers verront cet horaire sur votre annonce.</p>
                </div>
            </div>

            <div class="sp-form-grid">
                <div class="sp-box">
                    <div class="sp-box-head">
                        <label class="sp-label" for="date_depart">Date du départ <span class="sp-req">*</span></label>
                        <span class="sp-help">
                            {{ $allerBooked ? 'Réservé : la date ne change plus.' : 'Le jour où vous prenez la route.' }}
                        </span>
                    </div>

                    <input type="date" name="date_depart" id="date_depart" class="sp-input"
                           min="{{ now()->format('Y-m-d') }}" value="{{ $date }}" required @readonly($allerBooked)>
                </div>

                <div class="sp-box">
                    <div class="sp-box-head">
                        <label class="sp-label" for="heure_depart">Heure du départ <span class="sp-req">*</span></label>
                        <span class="sp-help">
                            {{ $allerBooked && $heureAller !== ''
                                ? 'Réservé : entre ' . $shift . ' minutes avant et après ' . $heureAller . '.'
                                : 'Prévoyez une marge pour le point de rendez-vous.' }}
                        </span>
                    </div>

                    <input type="time" name="heure_depart" id="heure_depart" class="sp-input"
                           value="{{ $heure }}" required>
                </div>
            </div>
        </section>

        @if ($covoiturage->retour)
            <section class="sp-form-section">
                <div class="sp-form-head">
                    <span class="sp-step">2</span>
                    <div>
                        <h2>Retour</h2>
                        <p>Le trajet inverse, de {{ $covoiturage->destination_ville }} à {{ $covoiturage->depart_ville }}.</p>
                    </div>
                </div>

                <div class="sp-form-grid">
                    <div class="sp-box">
                        <div class="sp-box-head">
                            <label class="sp-label" for="return_date">Date du retour <span class="sp-req">*</span></label>
                            <span class="sp-help">
                                {{ $retourBooked ? 'Réservé : la date ne change plus.' : 'Le même jour que l\'aller, ou après.' }}
                            </span>
                        </div>

                        <input type="date" name="return_date" id="return_date" class="sp-input"
                               min="{{ now()->format('Y-m-d') }}" value="{{ $dateRetour }}" required @readonly($retourBooked)>
                    </div>

                    <div class="sp-box">
                        <div class="sp-box-head">
                            <label class="sp-label" for="return_time">Heure du retour <span class="sp-req">*</span></label>
                            <span class="sp-help">
                                {{ $retourBooked && $heureRetour !== ''
                                    ? 'Réservé : entre ' . $shift . ' minutes avant et après ' . $heureRetour . '.'
                                    : 'L\'heure à laquelle vous repartez.' }}
                            </span>
                        </div>

                        <input type="time" name="return_time" id="return_time" class="sp-input"
                               value="{{ $heureRetourSaisie }}" required>
                    </div>
                </div>
            </section>
        @endif

        <div class="sp-form-actions">
            <a href="{{ route('covoiturage.edititen.edit', $covoiturage->covoiturage_id) }}" class="sp-act is-ghost">Annuler</a>
            <button type="submit" class="sp-btn-primary">Enregistrer</button>
        </div>
    </form>
</div>
@endsection
