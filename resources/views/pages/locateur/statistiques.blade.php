@extends('layouts.connected')
@section('title', 'Statistiques - Olten')

@php
    use Carbon\Carbon;

    Carbon::setLocale('fr');

    $weekLabel = Carbon::now()->subDays(6)->translatedFormat('d F Y')
        . ' – ' . Carbon::now()->translatedFormat('d F Y');
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Statistiques</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Statistiques</h1>
            <p class="sp-subtitle">L'audience de vos annonces, période par période.</p>
        </div>

        <a href="{{ route('ads.index') }}" class="sp-btn-primary">
            Mes annonces
        </a>
    </header>

    {{-- Indicateurs de la periode affichee (calcules a partir du graphique) --}}
    <div class="sp-stats is-3">
        <div class="sp-stat">
            <span class="sp-stat-icon is-brand"><i class="fa-solid fa-bullhorn"></i></span>
            <div>
                <span class="sp-stat-value" data-stat="ads">—</span>
                <span class="sp-stat-label">
                    Annonces
                    <small>sur la période affichée</small>
                </span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-blue"><i class="fa-regular fa-eye"></i></span>
            <div>
                <span class="sp-stat-value" data-stat="views">—</span>
                <span class="sp-stat-label">
                    Vues
                    <small>sur la période affichée</small>
                </span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-green"><i class="fa-solid fa-chart-line"></i></span>
            <div>
                <span class="sp-stat-value" data-stat="ratio">—</span>
                <span class="sp-stat-label">
                    Vues par annonce
                    <small>moyenne sur la période</small>
                </span>
            </div>
        </div>
    </div>

    {{-- Panneau --}}
    <section class="sp-panel">

        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">Audience des annonces</h2>
                <span class="sp-count" data-stat="period">{{ $weekLabel }}</span>
            </div>

            <div class="sp-toolbar-actions">
                <select class="sp-select" id="visitFilter" aria-label="Type de visites">
                    <option value="all">Toutes les visites</option>
                    <option value="unique">Visites uniques</option>
                    <option value="repeat">Visites répétées</option>
                </select>

                <select class="sp-select" id="annonceFilter" aria-label="Annonces prises en compte">
                    <option value="all">Toutes les annonces</option>
                    <option value="active">Annonces actives</option>
                    <option value="inactive">Annonces inactives</option>
                </select>

                <select class="sp-select" id="dateFilter" aria-label="Période">
                    <option value="week">{{ $weekLabel }}</option>
                    <option value="month">Ce mois</option>
                    <option value="year">Cette année</option>
                    <option value="custom">Période personnalisée</option>
                </select>
            </div>
        </div>

        {{-- Periode personnalisee --}}
        <div class="sp-daterange" id="customDateInputs" hidden>
            <label>
                <span>Du</span>
                <input type="date" id="customStart" class="sp-select">
            </label>

            <label>
                <span>Au</span>
                <input type="date" id="customEnd" class="sp-select">
            </label>

            <button type="button" id="applyCustomDate" class="sp-act is-edit">Appliquer</button>
        </div>

        <div class="sp-chart">
            <canvas id="analyticsChart"></canvas>
        </div>

        <p class="sp-chart-note" id="chartNote" hidden>Aucune donnée sur cette période.</p>
    </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
let analyticsChart;

document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('analyticsChart');

    if (canvas && typeof Chart !== 'undefined') {
        const ctx = canvas.getContext('2d');

        // Degrades aux couleurs de la plateforme
        const orange = ctx.createLinearGradient(0, 0, 0, 320);
        orange.addColorStop(0, 'rgba(255, 60, 0, .22)');
        orange.addColorStop(1, 'rgba(255, 60, 0, 0)');

        const ink = ctx.createLinearGradient(0, 0, 0, 320);
        ink.addColorStop(0, 'rgba(29, 106, 212, .20)');
        ink.addColorStop(1, 'rgba(29, 106, 212, 0)');

        analyticsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Annonces',
                        data: [],
                        borderColor: '#ff3c00',
                        backgroundColor: orange,
                        pointBackgroundColor: '#ff3c00',
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        borderWidth: 2,
                        tension: .35,
                        fill: true,
                    },
                    {
                        label: 'Vues',
                        data: [],
                        borderColor: '#1d6ad4',
                        backgroundColor: ink,
                        pointBackgroundColor: '#1d6ad4',
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        borderWidth: 2,
                        tension: .35,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 8,
                            padding: 18,
                            color: '#6c757d',
                            font: { size: 12, weight: '500' },
                        },
                    },
                    tooltip: {
                        backgroundColor: '#16191d',
                        padding: 12,
                        cornerRadius: 10,
                        titleFont: { size: 12 },
                        bodyFont: { size: 13 },
                        displayColors: true,
                        usePointStyle: true,
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
                        ticks: { color: '#adb5bd', font: { size: 11 }, precision: 0 },
                    },
                },
            },
        });
    }

    initFilters();
    updateChartData();
});

function initFilters() {
    const dateFilter = document.getElementById('dateFilter');
    const customBox = document.getElementById('customDateInputs');

    document.getElementById('visitFilter').addEventListener('change', updateChartData);
    document.getElementById('annonceFilter').addEventListener('change', updateChartData);

    dateFilter.addEventListener('change', function () {
        const custom = this.value === 'custom';
        customBox.hidden = !custom;
        if (!custom) updateChartData();
    });

    document.getElementById('applyCustomDate').addEventListener('click', updateChartData);
}

// Les compteurs du haut resument ce que le graphique affiche
function refreshTotals(data) {
    const sum = (arr) => (arr || []).reduce((t, v) => t + (Number(v) || 0), 0);

    const ads = sum(data.datasets?.[0]?.data);
    const views = sum(data.datasets?.[1]?.data);
    const ratio = ads > 0 ? (views / ads) : 0;

    const set = (key, value) => {
        const el = document.querySelector(`[data-stat="${key}"]`);
        if (el) el.textContent = value;
    };

    set('ads', ads);
    set('views', views);
    set('ratio', ads > 0 ? ratio.toFixed(1).replace('.', ',') : '—');

    const note = document.getElementById('chartNote');
    if (note) note.hidden = (data.labels || []).length > 0;
}

function updateChartData() {
    const visitFilter = document.getElementById('visitFilter').value;
    const annonceFilter = document.getElementById('annonceFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;

    const start = document.getElementById('customStart')?.value;
    const end = document.getElementById('customEnd')?.value;

    let url = `/stats/ads?period=${dateFilter}&visitFilter=${visitFilter}&annonceFilter=${annonceFilter}`;

    if (dateFilter === 'custom' && start && end) {
        url += `&start=${start}&end=${end}`;
    }

    fetch(url)
        .then(res => res.json())
        .then(data => {
            refreshTotals(data);

            if (!analyticsChart) return;

            analyticsChart.data.labels = data.labels || [];
            analyticsChart.data.datasets[0].data = data.datasets?.[0]?.data || [];
            analyticsChart.data.datasets[1].data = data.datasets?.[1]?.data || [];
            analyticsChart.update();
        })
        .catch(err => console.error('Stats error:', err));
}
</script>
@endsection
