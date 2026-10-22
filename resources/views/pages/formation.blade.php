@extends('layouts.main')

@section('title', 'Formation - Bientôt disponible - Olten.fr')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,800&family=Caveat:wght@600&display=swap">
    <link rel="stylesheet" href="{{ asset('assets/css/formation.css') }}?v={{ @filemtime(public_path('assets/css/formation.css')) ?: 1 }}">
@endpush

@section('content')
@php
    // Exemples de cours, a titre d'illustration : le vrai catalogue viendra
    // avec le service. `band` colore le bandeau de la fiche, `tone` l'icone.
    $examples = [
        ['icon' => 'fa-guitar',      'title' => 'Guitare',              'topic' => 'Musique',           'band' => '#FF3C00', 'tone' => '#E13800', 'tilt' => -3],
        ['icon' => 'fa-utensils',    'title' => 'Cuisine du monde',     'topic' => 'Cuisine',           'band' => '#8E72D0', 'tone' => '#5B3E96', 'tilt' => 2.5],
        ['icon' => 'fa-book-open',   'title' => 'Soutien scolaire',     'topic' => 'Révisions',         'band' => '#5B3E96', 'tone' => '#5B3E96', 'tilt' => 1.5],
        ['icon' => 'fa-scissors',    'title' => 'Couture',              'topic' => 'Loisirs créatifs',  'band' => '#FF9A7A', 'tone' => '#E13800', 'tilt' => -2],
        ['icon' => 'fa-language',    'title' => 'Anglais',              'topic' => 'Langues',           'band' => '#FF3C00', 'tone' => '#E13800', 'tilt' => -1],
        ['icon' => 'fa-laptop-code', 'title' => 'Premiers pas en code', 'topic' => 'Numérique',         'band' => '#8E72D0', 'tone' => '#5B3E96', 'tilt' => 3],
    ];
@endphp

<section class="fm-page">
    <div class="fm-inner">

        <div class="fm-copy">
            <span class="fm-badge fm-reveal" style="--d: 0">
                <span class="fm-badge-dot" aria-hidden="true"></span>
                Bientôt disponible
            </span>

            <h1 class="fm-title fm-reveal" style="--d: 1">
                La formation arrive <mark>bientôt</mark> sur Olten.
            </h1>

            <p class="fm-lead fm-reveal" style="--d: 2">
                Apprendre la guitare avec un voisin, transmettre une recette de famille,
                aider un élève à réviser : nous préparons un espace pour partager ce que
                l'on sait faire, près de chez soi.
            </p>

            <div class="fm-actions fm-reveal" style="--d: 3">
                <a href="{{ route('services.index') }}" class="fm-btn fm-btn--primary">
                    Explorer nos services
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
                <a href="{{ route('home') }}" class="fm-btn fm-btn--ghost">Retour à l'accueil</a>
            </div>

            <p class="fm-note fm-reveal" style="--d: 4">
                Vous aimeriez donner des cours&nbsp;? <a href="{{ route('contact') }}">Écrivez-nous</a>
            </p>
        </div>

        <div class="fm-board">
            <p class="fm-annotation fm-reveal" style="--d: 3">
                Quelques idées de cours
                <svg viewBox="0 0 44 34" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 4c11 1 23 7 30 22" />
                    <path d="M25 23l8 4 2-8" />
                </svg>
            </p>

            <ul class="fm-cards" aria-label="Exemples de cours">
                @foreach($examples as $example)
                    <li style="--i: {{ $loop->index }}; --tilt: {{ $example['tilt'] }}deg; --band: {{ $example['band'] }}; --tone: {{ $example['tone'] }};">
                        <div class="fm-card">
                            <span class="fm-card-icon" aria-hidden="true">
                                <i class="fas {{ $example['icon'] }}"></i>
                            </span>
                            <span class="fm-card-text">
                                <strong>{{ $example['title'] }}</strong>
                                <span>{{ $example['topic'] }}</span>
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

    </div>
</section>
@endsection
