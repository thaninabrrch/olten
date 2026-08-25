@extends('layouts.connected')
@section('title', 'Mode de réservation | ' . config('app.name'))

@php
    /*
     | Le champ envoye s'appelle booking_type (c'est ce que lit updateMode),
     | alors que la valeur enregistree se lit sur booking_mode : les deux
     | noms sont conserves tels quels.
     */
    $modes = [
        [
            'value' => 'instant',
            'titre' => 'Réservation instantanée',
            'texte' => 'Attirez jusqu\'à deux fois plus de passagers : les réservations sont validées automatiquement.',
            'icone' => 'fa-bolt',
            'phare' => true,
            'atouts' => ['Places remplies plus vite', 'Aucune action de votre part', 'Paiement garanti à la réservation'],
        ],
        [
            'value' => 'manual',
            'titre' => 'Validation manuelle',
            'texte' => 'Vous consultez chaque demande avant qu\'elle n\'expire, pour accepter ou refuser.',
            'icone' => 'fa-user-check',
            'phare' => false,
            'atouts' => ['Vous choisissez vos passagers', 'Échange possible avant l\'accord', '24 h pour répondre'],
        ],
    ];

    $actuel = old('booking_type', $covoiturage->booking_mode);
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ route('covoiturage.index') }}">Mes trajets</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('covoiturage.edit', $covoiturage->covoiturage_id) }}">Modifier</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Mode de réservation</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Mode de réservation</h1>
            <p class="sp-subtitle">Choisissez comment les passagers réservent une place sur ce trajet.</p>
        </div>

        <a href="{{ route('covoiturage.edit', $covoiturage->covoiturage_id) }}" class="sp-btn-primary">
            Retour au trajet
        </a>
    </header>

    <form action="{{ route('covoiturage.updateMode', $covoiturage->covoiturage_id) }}" method="POST">
        @csrf

        @if ($errors->any())
            <div class="sp-alert">
                <strong>Le mode n'a pas pu être enregistré.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="sp-grid is-flat">
            @foreach($modes as $mode)
                <label class="sp-card sp-pick">
                    <input type="radio" name="booking_type" value="{{ $mode['value'] }}"
                           @checked($actuel === $mode['value'])>

                    <span class="sp-pick-body">
                        <span class="sp-pick-head">
                            <span class="sp-stat-icon is-brand"><i class="fa-solid {{ $mode['icone'] }}"></i></span>

                            @if($mode['phare'])
                                <span class="sp-pick-flag">Recommandé</span>
                            @endif

                            <span class="sp-pick-check"><i class="fa-solid fa-check"></i></span>
                        </span>

                        <span class="sp-pick-title">{{ $mode['titre'] }}</span>
                        <span class="sp-pick-text">{{ $mode['texte'] }}</span>

                        <span class="sp-pick-list">
                            @foreach($mode['atouts'] as $atout)
                                <span><i class="fa-solid fa-check"></i> {{ $atout }}</span>
                            @endforeach
                        </span>
                    </span>
                </label>
            @endforeach
        </div>

        <div class="sp-form-actions">
            <a href="{{ route('covoiturage.edit', $covoiturage->covoiturage_id) }}" class="sp-act is-ghost">Annuler</a>
            <button type="submit" class="sp-btn-primary">Enregistrer le mode</button>
        </div>
    </form>
</div>
@endsection
