@extends('layouts.connected')
@section('title', 'Statistiques')

@section('content')

<div class="breadcrumb">
    <a href="#">Accueil</a>
    <span>></span>
    <span>Statistiques</span>
</div>

<h1 class="page-title">Statistiques</h1>

<!-- SECTION ANALYTIQUES -->
<div class="stats-container">
    <div class="section-header">
        <h2 class="section-title">Analytique des annonces</h2>

        <div class="filters">

            <select class="filter-select" id="visitFilter">
                <option value="all">Toutes les visites</option>
                <option value="unique">Visites uniques</option>
                <option value="repeat">Visites répétées</option>
            </select>

            <select class="filter-select" id="annonceFilter">
                <option value="all">Toutes les annonces</option>
                <option value="active">Annonces actives</option>
                <option value="inactive">Annonces inactives</option>
            </select>

            @php
                use Carbon\Carbon;
                Carbon::setLocale('fr');

                $startOfWeek = Carbon::now()->subDays(6);
                $endOfWeek   = Carbon::now();

                $weekLabel = $startOfWeek->translatedFormat('d F Y')
                    . ' - ' .
                    $endOfWeek->translatedFormat('d F Y');
            @endphp

            <select class="filter-select" id="dateFilter">
                <option value="week">{{ $weekLabel }}</option>
                <option value="month">Ce mois</option>
                <option value="year">Cette année</option>
                <option value="custom">Période personnalisée</option>
            </select>

            <div id="customDateInputs" style="display:none; margin-top:10px;">
                <input type="date" id="customStart">
                <input type="date" id="customEnd">
                <button id="applyCustomDate" class="btn-save">Appliquer</button>
            </div>

        </div>
    </div>

    <div class="chart-container">
        <canvas id="analyticsChart"></canvas>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>

<script>
let analyticsChart;

document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('analyticsChart');

    if (ctx) {
        analyticsChart = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Nombre d’annonces',
                        data: [],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Nombre de vues',
                        data: [],
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.15)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    initFilters();
    updateChartData();
});

function initFilters() {

    const visitFilter = document.getElementById('visitFilter');
    const annonceFilter = document.getElementById('annonceFilter');
    const dateFilter = document.getElementById('dateFilter');
    const customBox = document.getElementById('customDateInputs');
    const applyBtn = document.getElementById('applyCustomDate');

    visitFilter.addEventListener('change', updateChartData);
    annonceFilter.addEventListener('change', updateChartData);

    dateFilter.addEventListener('change', function () {
        if (this.value === 'custom') {
            customBox.style.display = 'block';
        } else {
            customBox.style.display = 'none';
            updateChartData();
        }
    });

    applyBtn.addEventListener('click', updateChartData);
}

function updateChartData() {

    const visitFilter = document.getElementById('visitFilter').value;
    const annonceFilter = document.getElementById('annonceFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;

    let start = document.getElementById('customStart')?.value;
    let end = document.getElementById('customEnd')?.value;

    let url = `/stats/ads?period=${dateFilter}&visitFilter=${visitFilter}&annonceFilter=${annonceFilter}`;

    if (dateFilter === 'custom' && start && end) {
        url += `&start=${start}&end=${end}`;
    }

    fetch(url)
        .then(res => res.json())
        .then(data => {

            if (!analyticsChart) return;

            analyticsChart.data.labels = data.labels || [];

            analyticsChart.data.datasets[0].data = data.datasets?.[0]?.data || [];
            analyticsChart.data.datasets[1].data = data.datasets?.[1]?.data || [];

            analyticsChart.update();
        })
        .catch(err => console.error('Stats error:', err));
}

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '0';
            entry.target.style.transform = 'translateY(20px)';

            setTimeout(() => {
                entry.target.style.transition = 'all 0.6s ease';
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }, 100);

            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.1 });

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.stat-card').forEach(card => {
        observer.observe(card);
    });
});

</script>

@endsection