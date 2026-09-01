@extends('layouts.connected')
@section('title', 'Tableau de bord | ' . config('app.name'))

@php
    /*
     | Toutes les valeurs viennent du controleur, calculees sur la base :
     |   - repartition     : encaisse par source sur six mois glissants
     |   - revenusMensuels : sert ici aux seules bornes de periode du bandeau
     |
     | Un indicateur a zero signifie « aucune activite de ce type » : c'est une
     | information juste. Aucune pastille de tendance n'est ecrite en dur.
     */
    $prenom = trim(explode(' ', trim($user->name ?? ''))[0] ?? '') ?: 'bienvenue';
    $roles  = $user->roles->pluck('display_name')->filter()->values();
    $note   = is_numeric($noteClient ?? null) ? round((float) $noteClient, 1) : null;

    $mensuel   = $revenusMensuels ?? ['labels' => [], 'locations' => [], 'ventes' => [], 'livraisons' => []];
    $sources   = collect($repartition ?? []);
    $periode   = (float) ($revenusPeriode ?? 0);

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

    {{-- ══ Statistiques detaillees ══ --}}
    @php
        /*
         | Palette validee pour la lisibilite daltonienne : sur les paires
         | voisines, l'ecart OKLab reste au-dessus du seuil en protanopie comme
         | en deuteranopie. Chaque couleur suit une entite, jamais un rang :
         | retirer une serie ne repeint pas les autres.
         */
        $viz = [
            'bleu'   => '#2a78d6',
            'orange' => '#ff3c00',
            'aqua'   => '#1baf7a',
            'jaune'  => '#eda100',
            'violet' => '#7c3aed',
            'rouge'  => '#e34948',
        ];

        // Palette d'etat, reservee : elle ne sert jamais de couleur de serie.
        $etats  = ['Validé' => '#0ca30c', 'En attente' => '#fab219', 'Refusé' => '#d03b3b'];
        $icones = ['Validé' => 'fa-circle-check', 'En attente' => 'fa-clock', 'Refusé' => 'fa-circle-xmark'];

        $catalogue      = collect($catalogue ?? []);
        $verification   = collect($verification ?? []);
        $entonnoir      = collect($entonnoir ?? []);
        $recetteTrajets = $recetteTrajets ?? ['labels' => [], 'valeurs' => []];
        $trafic         = $trafic ?? ['labels' => [], 'valeurs' => [], 'total' => 0];
        $affluence      = $affluence ?? [];

        $teintesCatalogue = ['Annonces' => $viz['bleu'], 'Produits' => $viz['orange'], 'Trajets' => $viz['aqua']];
        $totalCatalogue   = (int) $catalogue->sum();
        $totalDocs        = (int) $verification->sum();
        $totalRecette     = array_sum($recetteTrajets['valeurs']);
        $aDesTrajets      = count($recetteTrajets['valeurs']) > 0;
        $aDuTrafic        = (int) ($trafic['total'] ?? 0) > 0;
        $sommetEntonnoir  = (float) ($entonnoir->first() ?: 0);
    @endphp

    <p class="sp-section-title">Statistiques détaillées</p>

    <div class="sp-viz-grid">

        {{-- ── Composition du catalogue ── --}}
        <section class="sp-panel sp-viz">
            <div class="sp-toolbar">
                <div>
                    <h2 class="sp-toolbar-title">Mon catalogue</h2>
                    <span class="sp-count">Ce que vous avez publié</span>
                </div>
            </div>

            @if($totalCatalogue > 0)
                <div class="sp-viz-canvas is-ring">
                    <canvas id="vizCatalogue"
                            data-labels='@json($catalogue->keys())'
                            data-valeurs='@json($catalogue->values())'
                            data-teintes='@json($catalogue->keys()->map(fn ($n) => $teintesCatalogue[$n] ?? "#adb5bd"))'></canvas>

                    <div class="sp-viz-center">
                        <strong>{{ $totalCatalogue }}</strong>
                        <span>publication{{ $totalCatalogue > 1 ? 's' : '' }}</span>
                    </div>
                </div>

                <ul class="sp-legend">
                    @foreach($catalogue as $nom => $nombre)
                        @php $part = $totalCatalogue > 0 ? round(($nombre / $totalCatalogue) * 100) : 0; @endphp
                        <li>
                            <span class="sp-legend-dot" style="background: {{ $teintesCatalogue[$nom] ?? '#adb5bd' }}"></span>
                            <span class="sp-legend-name">{{ $nom }}</span>
                            <span class="sp-legend-value">{{ $nombre }}</span>
                            <span class="sp-legend-part">{{ $part }} %</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="sp-feed-empty">Publiez une annonce, un produit ou un trajet pour voir la répartition.</p>
            @endif
        </section>

        {{-- ── Dossier de verification ── --}}
        <section class="sp-panel sp-viz">
            <div class="sp-toolbar">
                <div>
                    <h2 class="sp-toolbar-title">Vérification du compte</h2>
                    <span class="sp-count">Mes pièces justificatives</span>
                </div>
            </div>

            @if($totalDocs > 0)
                <div class="sp-viz-canvas is-ring">
                    <canvas id="vizVerification"
                            data-labels='@json($verification->keys())'
                            data-valeurs='@json($verification->values())'
                            data-teintes='@json($verification->keys()->map(fn ($n) => $etats[$n]))'></canvas>

                    <div class="sp-viz-center">
                        <strong>{{ $totalDocs }}</strong>
                        <span>document{{ $totalDocs > 1 ? 's' : '' }}</span>
                    </div>
                </div>

                {{-- Une couleur d'etat ne porte jamais le sens seule : icone + libelle --}}
                <ul class="sp-legend">
                    @foreach($verification as $nom => $nombre)
                        <li>
                            <i class="fa-solid {{ $icones[$nom] }} sp-legend-icon" style="color: {{ $etats[$nom] }}"></i>
                            <span class="sp-legend-name">{{ $nom }}</span>
                            <span class="sp-legend-value">{{ $nombre }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="sp-feed-empty">Aucun document déposé. Déposez vos pièces pour activer tous les services.</p>
            @endif
        </section>

        {{-- ── Affluence par jour de la semaine ── --}}
        <section class="sp-panel sp-viz">
            <div class="sp-toolbar">
                <div>
                    <h2 class="sp-toolbar-title">Affluence par jour</h2>
                    <span class="sp-count">Quand vos annonces sont consultées</span>
                </div>

                <span class="sp-viz-tag" style="--tag: {{ $viz['bleu'] }}">
                    <i class="fa-solid fa-chart-simple"></i> Visites
                </span>
            </div>

            @if(array_sum($affluence) > 0)
                <div class="sp-viz-canvas">
                    <canvas id="vizAffluence" data-valeurs='@json(array_values($affluence))'></canvas>
                </div>
            @else
                <p class="sp-feed-empty">Pas encore assez de visites pour dégager une tendance.</p>
            @endif
        </section>

        {{-- ── Recette potentielle des trajets ── --}}
        <section class="sp-panel sp-viz">
            <div class="sp-toolbar">
                <div>
                    <h2 class="sp-toolbar-title">Recette de mes trajets</h2>
                    <span class="sp-count">
                        Places × prix, soit {{ number_format((float) $totalRecette, 0, ',', ' ') }} € au complet
                    </span>
                </div>
            </div>

            @if($aDesTrajets)
                <div class="sp-viz-canvas is-list">
                    <canvas id="vizTrajets"
                            data-labels='@json($recetteTrajets["labels"])'
                            data-valeurs='@json($recetteTrajets["valeurs"])'></canvas>
                </div>
            @else
                <p class="sp-feed-empty">Aucun trajet de covoiturage publié pour l'instant.</p>
            @endif
        </section>

        {{-- ── Trafic sur trente jours ── --}}
        <section class="sp-panel sp-viz sp-viz--wide">
            <div class="sp-toolbar">
                <div>
                    <h2 class="sp-toolbar-title">Trafic sur mes annonces</h2>
                    <span class="sp-count">
                        {{ $trafic['total'] }} visite{{ $trafic['total'] > 1 ? 's' : '' }} sur les 30 derniers jours
                    </span>
                </div>

                <span class="sp-viz-tag" style="--tag: {{ $viz['orange'] }}">
                    <i class="fa-regular fa-eye"></i> Visites
                </span>
            </div>

            @if($aDuTrafic)
                <div class="sp-viz-canvas is-tall">
                    <canvas id="vizTrafic"
                            data-labels='@json($trafic["labels"])'
                            data-valeurs='@json($trafic["valeurs"])'></canvas>
                </div>
            @else
                <p class="sp-feed-empty">Aucune visite enregistrée sur cette période.</p>
            @endif
        </section>

        {{-- ── Entonnoir de conversion ── --}}
        <section class="sp-panel sp-viz sp-viz--wide">
            <div class="sp-toolbar">
                <div>
                    <h2 class="sp-toolbar-title">Ce que deviennent mes visites</h2>
                    <span class="sp-count">De la consultation au paiement</span>
                </div>
            </div>

            @if($sommetEntonnoir > 0)
                <div class="sp-viz-canvas is-list">
                    <canvas id="vizEntonnoir"
                            data-labels='@json($entonnoir->keys())'
                            data-valeurs='@json($entonnoir->values())'></canvas>
                </div>

                <ul class="sp-funnel">
                    @foreach($entonnoir as $etape => $nombre)
                        @php
                            $part = $sommetEntonnoir > 0 ? round(($nombre / $sommetEntonnoir) * 100, 1) : 0;
                            $part = rtrim(rtrim(number_format($part, 1, ',', ' '), '0'), ',');
                        @endphp
                        <li>
                            <span class="sp-funnel-value">{{ $nombre }}</span>
                            <span class="sp-funnel-name">{{ $etape }}</span>
                            <span class="sp-funnel-part">{{ $part }} %</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="sp-feed-empty">L'entonnoir apparaîtra dès les premières visites sur vos annonces.</p>
            @endif
        </section>
    </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') return;

        /* ══════════════════════════════════════════════════════════════════
           Reglages communs
           ══════════════════════════════════════════════════════════════════
           Encre et grille restent en retrait : ce sont les barres et les arcs
           qui portent l'information, pas le decor. Les valeurs et les
           libelles gardent une couleur de texte, jamais celle de la serie.
        */
        Chart.defaults.font.family = "system-ui, -apple-system, 'Segoe UI', sans-serif";
        Chart.defaults.font.size   = 11;
        Chart.defaults.color       = '#8b9199';

        const GRILLE = '#eef0f2';
        const ENCRE  = '#343a40';

        const VIZ = {
            bleu:   '#2a78d6',
            orange: '#ff3c00',
            aqua:   '#1baf7a',
            jaune:  '#eda100',
            violet: '#7c3aed',
            rouge:  '#e34948',
        };

        // Rampe ordinale : une seule teinte, du fonce au clair. Elle ne sert
        // qu'aux etapes ordonnees de l'entonnoir, jamais a des categories.
        const RAMPE = ['#1c5cab', '#2a78d6', '#5598e7', '#86b6ef'];

        const euros = (v) => new Intl.NumberFormat('fr-FR', {
            style: 'currency', currency: 'EUR', maximumFractionDigits: 0,
        }).format(v);

        const bulle = (formate) => ({
            backgroundColor: '#16191d',
            padding: 12,
            cornerRadius: 10,
            usePointStyle: true,
            titleFont: { size: 12, weight: '600' },
            bodyFont: { size: 12 },
            displayColors: true,
            callbacks: formate ? { label: formate } : {},
        });

        const lire = (canvas, cle, defaut) => {
            try { return JSON.parse(canvas.dataset[cle]); } catch (e) { return defaut; }
        };

        const raccourcir = (texte, max) =>
            String(texte).length > max ? String(texte).slice(0, max - 1) + '…' : String(texte);

        /* Etiquette posee au bout de chaque barre horizontale : sans elle, les
           teintes les plus claires passeraient sous le seuil de contraste. */
        const etiquetteBout = (formate) => ({
            id: 'etiquetteBout',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;
                ctx.save();
                ctx.font = '600 11px ' + Chart.defaults.font.family;
                ctx.fillStyle = ENCRE;
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';

                chart.getDatasetMeta(0).data.forEach((barre, i) => {
                    const valeur = chart.data.datasets[0].data[i];
                    ctx.fillText(formate(valeur), barre.x + 8, barre.y);
                });

                ctx.restore();
            },
        });

        /* ══ 1. Trafic sur trente jours — barres verticales ══ */
        const zoneTrafic = document.getElementById('vizTrafic');

        if (zoneTrafic) {
            new Chart(zoneTrafic.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: lire(zoneTrafic, 'labels', []),
                    datasets: [{
                        label: 'Visites',
                        data: lire(zoneTrafic, 'valeurs', []),
                        backgroundColor: VIZ.orange,
                        hoverBackgroundColor: '#d63200',
                        borderRadius: { topLeft: 4, topRight: 4 },
                        borderSkipped: 'bottom',
                        barPercentage: .72,
                        categoryPercentage: .88,
                        maxBarThickness: 24,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: bulle((c) => ' ' + c.parsed.y + ' visite' + (c.parsed.y > 1 ? 's' : '')),
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            border: { display: false },
                            ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 10 },
                        },
                        y: {
                            beginAtZero: true,
                            border: { display: false },
                            grid: { color: GRILLE },
                            ticks: { precision: 0, maxTicksLimit: 5 },
                        },
                    },
                },
            });
        }

        /* ══ 2. Affluence par jour de la semaine — radar ══ */
        const zoneAffluence = document.getElementById('vizAffluence');

        if (zoneAffluence) {
            new Chart(zoneAffluence.getContext('2d'), {
                type: 'radar',
                data: {
                    labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                    datasets: [{
                        label: 'Visites',
                        data: lire(zoneAffluence, 'valeurs', []),
                        borderColor: VIZ.bleu,
                        backgroundColor: 'rgba(42, 120, 214, .16)',
                        borderWidth: 2,
                        pointBackgroundColor: VIZ.bleu,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: bulle((c) => ' ' + c.formattedValue + ' visite' + (c.parsed.r > 1 ? 's' : '')),
                    },
                    scales: {
                        r: {
                            beginAtZero: true,
                            angleLines: { color: GRILLE },
                            grid: { color: GRILLE },
                            pointLabels: { color: '#6c757d', font: { size: 11, weight: '600' } },
                            ticks: { display: false, precision: 0 },
                        },
                    },
                },
            });
        }

        /* ══ 3 & 4. Anneaux : catalogue et verification ══ */
        ['vizCatalogue', 'vizVerification'].forEach((id) => {
            const zone = document.getElementById(id);
            if (!zone) return;

            new Chart(zone.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: lire(zone, 'labels', []),
                    datasets: [{
                        data: lire(zone, 'valeurs', []),
                        backgroundColor: lire(zone, 'teintes', []),
                        borderColor: '#fff',
                        borderWidth: 2,
                        hoverOffset: 8,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: bulle((c) => ' ' + c.label + ' : ' + c.formattedValue),
                    },
                },
            });
        });

        /* ══ 5. Recette potentielle des trajets — barres horizontales ══ */
        const zoneTrajets = document.getElementById('vizTrajets');

        if (zoneTrajets) {
            new Chart(zoneTrajets.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: lire(zoneTrajets, 'labels', []),
                    datasets: [{
                        label: 'Recette au complet',
                        data: lire(zoneTrajets, 'valeurs', []),
                        backgroundColor: VIZ.aqua,
                        hoverBackgroundColor: '#149268',
                        borderRadius: { topRight: 4, bottomRight: 4 },
                        borderSkipped: 'left',
                        barPercentage: .7,
                        categoryPercentage: .84,
                        maxBarThickness: 22,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: { right: 64 } },
                    plugins: {
                        legend: { display: false },
                        tooltip: bulle((c) => ' ' + euros(c.parsed.x)),
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            border: { display: false },
                            grid: { color: GRILLE },
                            ticks: { maxTicksLimit: 4, callback: (v) => euros(v) },
                        },
                        y: {
                            grid: { display: false },
                            border: { display: false },
                            ticks: {
                                color: '#495057',
                                font: { size: 11, weight: '600' },
                                callback(v) { return raccourcir(this.getLabelForValue(v), 24); },
                            },
                        },
                    },
                },
                plugins: [etiquetteBout(euros)],
            });
        }

        /* ══ 6. Entonnoir de conversion — etapes ordonnees ══ */
        const zoneEntonnoir = document.getElementById('vizEntonnoir');

        if (zoneEntonnoir) {
            const valeurs = lire(zoneEntonnoir, 'valeurs', []);

            new Chart(zoneEntonnoir.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: lire(zoneEntonnoir, 'labels', []),
                    datasets: [{
                        label: 'Étapes',
                        data: valeurs,
                        backgroundColor: valeurs.map((_, i) => RAMPE[Math.min(i, RAMPE.length - 1)]),
                        borderRadius: { topRight: 4, bottomRight: 4 },
                        borderSkipped: 'left',
                        barPercentage: .68,
                        categoryPercentage: .86,
                        maxBarThickness: 24,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: { right: 48 } },
                    plugins: {
                        legend: { display: false },
                        tooltip: bulle((c) => ' ' + c.formattedValue),
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            border: { display: false },
                            grid: { color: GRILLE },
                            ticks: { precision: 0, maxTicksLimit: 5 },
                        },
                        y: {
                            grid: { display: false },
                            border: { display: false },
                            ticks: { color: '#495057', font: { size: 11, weight: '600' } },
                        },
                    },
                },
                plugins: [etiquetteBout((v) => String(v))],
            });
        }
    });
</script>
@endsection
