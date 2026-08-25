@extends('layouts.connected')
@section('title', 'Tableau de bord | ' . config('app.name'))

@php
    /*
     | Toutes les valeurs viennent du controleur, calculees sur la base :
     |   - revenusMensuels : six mois glissants, trois sources de revenus
     |   - repartition     : total par source sur cette meme periode
     |   - septJours       : encaissements des sept derniers jours
     |
     | Un indicateur a zero signifie « aucune activite de ce type » : c'est une
     | information juste. Les anciennes pastilles de tendance (« +12% »,
     | « ★ 4.9 ») etaient ecrites en dur et ne mesuraient rien : elles ont ete
     | retirees plutot que d'afficher un chiffre invente.
     */
    $prenom = trim(explode(' ', trim($user->name ?? ''))[0] ?? '') ?: 'bienvenue';
    $roles  = $user->roles->pluck('display_name')->filter()->values();
    $note   = is_numeric($noteClient ?? null) ? round((float) $noteClient, 1) : null;

    $mensuel   = $revenusMensuels ?? ['labels' => [], 'locations' => [], 'ventes' => [], 'livraisons' => []];
    $sources   = collect($repartition ?? []);
    $periode   = (float) ($revenusPeriode ?? 0);
    $semaine   = collect($septJours ?? []);
    $maxJour   = (float) ($semaine->max() ?: 0);
    $totalSem  = (float) $semaine->sum();
    $aDesRevenus = $periode > 0;

    $jours = ['Mon' => 'Lun', 'Tue' => 'Mar', 'Wed' => 'Mer', 'Thu' => 'Jeu',
              'Fri' => 'Ven', 'Sat' => 'Sam', 'Sun' => 'Dim'];

    $raccourcis = [
        ['Mes annonces',     route('ads.index'),             'fa-bullhorn'],
        ['Mes produits',     route('seller.produits.index'), 'fa-box'],
        ['Mes réservations', url('/mes-reservations'),       'fa-calendar-check'],
        ['Mes commandes',    route('orders'),                'fa-bag-shopping'],
        ['Messages',         route('messages'),              'fa-comment-dots'],
        ['Portefeuille',     url('/portefeuille'),           'fa-wallet'],
    ];

    $teintes = ['Locations' => '#1d6ad4', 'Ventes' => '#ff3c00', 'Livraisons' => '#1a8245'];
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Tableau de bord</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Bonjour {{ $prenom }}</h1>
            <p class="sp-subtitle">Voici l'essentiel de votre activité sur Olten.</p>
        </div>

        @if($roles->count())
            <div class="sp-role-badges">
                @foreach($roles as $role)
                    <span class="sp-status is-confirmed">{{ $role }}</span>
                @endforeach
            </div>
        @endif
    </header>

    {{-- ══ Synthese des six derniers mois ══ --}}
    <div class="sp-balance">
        <div>
            <span class="sp-balance-label">Encaissé sur 6 mois</span>
            <span class="sp-balance-value">{{ number_format($periode, 2, ',', ' ') }} €</span>
            <span class="sp-balance-note">
                Paiements confirmés sur vos locations, vos ventes et vos livraisons,
                de {{ $mensuel['labels'][0] ?? '—' }} à {{ end($mensuel['labels']) ?: '—' }}.
            </span>
        </div>

        <div class="sp-balance-split">
            @foreach($sources as $nom => $montant)
                <div>
                    <span class="sp-split-dot" style="background: {{ $teintes[$nom] ?? '#fff' }}"></span>
                    <span class="sp-split-value">{{ number_format((float) $montant, 0, ',', ' ') }} €</span>
                    <span class="sp-split-label">{{ $nom }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ══ Audience ══ --}}
    <p class="sp-section-title">Mon audience</p>

    <div class="sp-stats">
        <div class="sp-stat">
            <span class="sp-stat-icon is-brand"><i class="fa-solid fa-bullhorn"></i></span>
            <div>
                <span class="sp-stat-value">{{ $activeAds }}</span>
                <span class="sp-stat-label">Annonce{{ $activeAds > 1 ? 's' : '' }} publiée{{ $activeAds > 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-blue"><i class="fa-regular fa-eye"></i></span>
            <div>
                <span class="sp-stat-value">{{ $totalViews }}</span>
                <span class="sp-stat-label">Vue{{ $totalViews > 1 ? 's' : '' }} cumulées</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-red"><i class="fa-solid fa-heart"></i></span>
            <div>
                <span class="sp-stat-value">{{ $favoritesCount }}</span>
                <span class="sp-stat-label">Favori{{ $favoritesCount > 1 ? 's' : '' }} enregistré{{ $favoritesCount > 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-green"><i class="fa-solid fa-star"></i></span>
            <div>
                <span class="sp-stat-value">{{ $note !== null ? $note : '—' }}</span>
                <span class="sp-stat-label">
                    Note moyenne
                    <small>sur 5</small>
                </span>
            </div>
        </div>
    </div>

    {{-- ══ Commerce ══ --}}
    <p class="sp-section-title">Mon activité commerciale</p>

    <div class="sp-stats">
        <div class="sp-stat">
            <span class="sp-stat-icon is-green"><i class="fa-solid fa-euro-sign"></i></span>
            <div>
                <span class="sp-stat-value">{{ number_format((float) $ventesTotal, 2, ',', ' ') }} €</span>
                <span class="sp-stat-label">Chiffre d'affaires produits</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-brand"><i class="fa-solid fa-bag-shopping"></i></span>
            <div>
                <span class="sp-stat-value">{{ $totalCommandes }}</span>
                <span class="sp-stat-label">Commande{{ $totalCommandes > 1 ? 's' : '' }} reçue{{ $totalCommandes > 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-blue"><i class="fa-solid fa-truck-fast"></i></span>
            <div>
                <span class="sp-stat-value">{{ $totalCourses }}</span>
                <span class="sp-stat-label">Course{{ $totalCourses > 1 ? 's' : '' }} de livraison</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-red"><i class="fa-solid fa-wallet"></i></span>
            <div>
                <span class="sp-stat-value">{{ number_format((float) $revenusTotal, 2, ',', ' ') }} €</span>
                <span class="sp-stat-label">Revenus de livraison</span>
            </div>
        </div>
    </div>

    {{-- ══ Graphiques ══ --}}
    <div class="sp-dash">

        <div class="sp-dash-main">

            {{-- Courbe des revenus --}}
            <section class="sp-panel">
                <div class="sp-toolbar">
                    <div>
                        <h2 class="sp-toolbar-title">Évolution des revenus</h2>
                        <span class="sp-count">Six derniers mois, par source</span>
                    </div>
                </div>

                @if($aDesRevenus)
                    <div class="sp-chart is-tall">
                        <canvas id="chartRevenus"></canvas>
                    </div>
                @else
                    <p class="sp-feed-empty">
                        Aucun encaissement sur les six derniers mois : la courbe apparaîtra dès votre premier paiement.
                    </p>
                @endif
            </section>

            {{-- Semaine --}}
            <section class="sp-panel">
                <div class="sp-toolbar">
                    <div>
                        <h2 class="sp-toolbar-title">Cette semaine</h2>
                        <span class="sp-count">
                            {{ number_format($totalSem, 2, ',', ' ') }} € sur les 7 derniers jours
                        </span>
                    </div>
                </div>

                <div class="sp-bars">
                    @foreach($semaine as $jour => $montant)
                        @php
                            $date    = \Carbon\Carbon::parse($jour);
                            $isToday = $date->isToday();
                            // Hauteur relative au meilleur jour, pas a un palier fixe
                            $hauteur = $maxJour > 0 ? max(($montant / $maxJour) * 100, 3) : 3;
                        @endphp

                        <div class="sp-bar {{ $isToday ? 'is-today' : '' }}">
                            <span class="sp-bar-value">{{ number_format((float) $montant, 0, ',', ' ') }} €</span>
                            <span class="sp-bar-fill" style="height: {{ $hauteur }}%"></span>
                            <span class="sp-bar-day">{{ $jours[$date->format('D')] ?? $date->format('d') }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Raccourcis --}}
            <section class="sp-panel">
                <div class="sp-toolbar">
                    <div>
                        <h2 class="sp-toolbar-title">Accès rapides</h2>
                        <span class="sp-count">Les pages que vous ouvrez le plus souvent</span>
                    </div>
                </div>

                <div class="sp-shortcuts">
                    @foreach($raccourcis as [$libelle, $url, $icone])
                        <a href="{{ $url }}" class="sp-shortcut">
                            <i class="fa-solid {{ $icone }}"></i>
                            <span>{{ $libelle }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="sp-dash-side">

            {{-- Repartition --}}
            <section class="sp-panel">
                <div class="sp-toolbar">
                    <div>
                        <h2 class="sp-toolbar-title">Répartition</h2>
                        <span class="sp-count">D'où viennent vos revenus</span>
                    </div>
                </div>

                @if($aDesRevenus)
                    <div class="sp-donut">
                        <canvas id="chartRepartition"></canvas>
                    </div>

                    <ul class="sp-legend">
                        @foreach($sources as $nom => $montant)
                            @php $part = $periode > 0 ? round(($montant / $periode) * 100) : 0; @endphp
                            <li>
                                <span class="sp-legend-dot" style="background: {{ $teintes[$nom] ?? '#adb5bd' }}"></span>
                                <span class="sp-legend-name">{{ $nom }}</span>
                                <span class="sp-legend-value">{{ number_format((float) $montant, 2, ',', ' ') }} €</span>
                                <span class="sp-legend-part">{{ $part }} %</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="sp-feed-empty">Rien à répartir pour l'instant.</p>
                @endif
            </section>

            {{-- Activité --}}
            <section class="sp-panel">
                <div class="sp-toolbar">
                    <div>
                        <h2 class="sp-toolbar-title">Activité récente</h2>
                        <span class="sp-count">{{ count($recentActivities ?? []) }} événement{{ count($recentActivities ?? []) > 1 ? 's' : '' }}</span>
                    </div>
                </div>

                @if(count($recentActivities ?? []))
                    <ul class="sp-feed">
                        @foreach($recentActivities as $activity)
                            <li>
                                <span class="sp-feed-dot"></span>
                                <div>
                                    <strong>{{ $activity['description'] }}</strong>
                                    <span>{{ $activity['time'] }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="sp-feed-empty">Aucune activité récente à afficher.</p>
                @endif
            </section>

            {{-- Dernière course --}}
            @if($derniereMission)
                <section class="sp-panel sp-last">
                    <span class="sp-last-label">Dernière course</span>

                    <span class="sp-last-value">
                        {{ number_format((float) $derniereMission->total_price, 2, ',', ' ') }} €
                    </span>

                    <span class="sp-last-route">
                        {{ $derniereMission->pickup_address ?: 'Départ non précisé' }}
                        →
                        {{ $derniereMission->delivery_address ?: 'Arrivée non précisée' }}
                    </span>

                    <a href="{{ route('liv_termine') }}" class="sp-last-link">Voir mes livraisons</a>
                </section>
            @endif
        </div>
    </div>
</div>

@if($aDesRevenus)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') return;

            const donnees = @json($mensuel);
            const sources = @json($sources);
            const teintes = @json($teintes);

            const euros = (v) => new Intl.NumberFormat('fr-FR', {
                style: 'currency', currency: 'EUR', maximumFractionDigits: 0,
            }).format(v);

            // ── Courbe des revenus par source ──
            const zone = document.getElementById('chartRevenus');

            if (zone) {
                const ctx = zone.getContext('2d');

                const degrade = (couleur) => {
                    const g = ctx.createLinearGradient(0, 0, 0, 300);
                    g.addColorStop(0, couleur + '38');
                    g.addColorStop(1, couleur + '00');
                    return g;
                };

                const series = [
                    ['Locations', donnees.locations, teintes.Locations],
                    ['Ventes', donnees.ventes, teintes.Ventes],
                    ['Livraisons', donnees.livraisons, teintes.Livraisons],
                ].filter(([, valeurs]) => (valeurs || []).some(v => Number(v) > 0));

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: donnees.labels,
                        datasets: series.map(([nom, valeurs, couleur]) => ({
                            label: nom,
                            data: valeurs,
                            borderColor: couleur,
                            backgroundColor: degrade(couleur),
                            pointBackgroundColor: couleur,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            borderWidth: 2,
                            tension: .35,
                            fill: true,
                        })),
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: {
                                align: 'end',
                                labels: {
                                    usePointStyle: true, pointStyle: 'circle',
                                    boxWidth: 8, padding: 18,
                                    color: '#6c757d', font: { size: 12, weight: '500' },
                                },
                            },
                            tooltip: {
                                backgroundColor: '#16191d',
                                padding: 12, cornerRadius: 10, usePointStyle: true,
                                callbacks: {
                                    label: (c) => ' ' + c.dataset.label + ' : ' + euros(c.parsed.y),
                                },
                            },
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: { color: '#adb5bd', font: { size: 11 } },
                            },
                            y: {
                                beginAtZero: true,
                                border: { display: false },
                                grid: { color: '#f0f1f3' },
                                ticks: {
                                    color: '#adb5bd', font: { size: 11 },
                                    callback: (v) => euros(v),
                                },
                            },
                        },
                    },
                });
            }

            // ── Répartition par source ──
            const part = document.getElementById('chartRepartition');

            if (part) {
                const noms = Object.keys(sources);

                new Chart(part.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: noms,
                        datasets: [{
                            data: noms.map(n => sources[n]),
                            backgroundColor: noms.map(n => teintes[n] || '#adb5bd'),
                            borderWidth: 0,
                            hoverOffset: 6,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#16191d',
                                padding: 12, cornerRadius: 10,
                                callbacks: { label: (c) => ' ' + c.label + ' : ' + euros(c.parsed) },
                            },
                        },
                    },
                });
            }
        });
    </script>
@endif
@endsection
