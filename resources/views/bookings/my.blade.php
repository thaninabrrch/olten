@extends('layouts.connected')
@section('title', 'Mes réservations - Olten')

@php
    /*
     | Le controleur ne fournit que le paginateur $bookings (sans filtre) :
     | les onglets ci-dessous trient donc l'affichage cote navigateur, sur les
     | reservations de la page en cours. La mention est affichee des qu'il y a
     | plusieurs pages pour que ce soit sans ambiguite.
     */
    $onPage     = $bookings->getCollection();
    $today      = \Carbon\Carbon::today();
    $upcoming   = $onPage->filter(fn ($b) => \Carbon\Carbon::parse($b->start_date)->gte($today)
                                            && $b->booking_status !== 'cancelled')->count();
    $confirmed  = $onPage->where('booking_status', 'confirmed')->count();
    $spent      = $onPage->filter(fn ($b) => $b->booking_status !== 'cancelled')
                         ->sum(fn ($b) => (float) $b->total_price);
    $scopeLabel = $bookings->hasPages() ? 'sur cette page' : null;

    $statusMeta = [
        'pending'   => ['En attente', 'is-pending',   'fa-hourglass-half'],
        'confirmed' => ['Confirmée',  'is-confirmed', 'fa-circle-check'],
        'cancelled' => ['Annulée',    'is-cancelled', 'fa-circle-xmark'],
        'completed' => ['Terminée',   'is-delivered', 'fa-flag-checkered'],
    ];

    $tabs = [
        ''          => 'Toutes',
        'pending'   => 'En attente',
        'confirmed' => 'Confirmées',
        'completed' => 'Terminées',
        'cancelled' => 'Annulées',
    ];

    $mois = [1 => 'janv', 'févr', 'mars', 'avr', 'mai', 'juin', 'juil', 'août', 'sept', 'oct', 'nov', 'déc'];
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Mes réservations</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Mes réservations</h1>
            <p class="sp-subtitle">Vos locations en cours, à venir et passées, en un seul endroit.</p>
        </div>

        <a href="{{ route('home') }}" class="sp-btn-primary">
            Parcourir les annonces
        </a>
    </header>

    {{-- Indicateurs --}}
    <div class="sp-stats">
        <div class="sp-stat">
            <span class="sp-stat-icon is-brand"><i class="fa-solid fa-calendar-days"></i></span>
            <div>
                <span class="sp-stat-value">{{ $bookings->total() }}</span>
                <span class="sp-stat-label">Réservation{{ $bookings->total() > 1 ? 's' : '' }} au total</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-blue"><i class="fa-solid fa-clock"></i></span>
            <div>
                <span class="sp-stat-value">{{ $upcoming }}</span>
                <span class="sp-stat-label">
                    À venir
                    @if($scopeLabel)<small>{{ $scopeLabel }}</small>@endif
                </span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-green"><i class="fa-solid fa-circle-check"></i></span>
            <div>
                <span class="sp-stat-value">{{ $confirmed }}</span>
                <span class="sp-stat-label">
                    Confirmée{{ $confirmed > 1 ? 's' : '' }}
                    @if($scopeLabel)<small>{{ $scopeLabel }}</small>@endif
                </span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-red"><i class="fa-solid fa-euro-sign"></i></span>
            <div>
                <span class="sp-stat-value">{{ number_format($spent, 2, ',', ' ') }} €</span>
                <span class="sp-stat-label">
                    Total hors annulations
                    @if($scopeLabel)<small>{{ $scopeLabel }}</small>@endif
                </span>
            </div>
        </div>
    </div>

    {{-- Panneau --}}
    <section class="sp-panel">

        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">Historique des réservations</h2>
                <span class="sp-count">
                    {{ $onPage->count() }} réservation{{ $onPage->count() > 1 ? 's' : '' }} affichée{{ $onPage->count() > 1 ? 's' : '' }} sur {{ $bookings->total() }}
                </span>
            </div>
        </div>

        @if($bookings->count())
            {{-- Onglets : tri cote navigateur sur les reservations affichees --}}
            <div class="sp-tabs" data-sp-filters>
                @foreach($tabs as $value => $label)
                    @php
                        $tabCount = $value === ''
                            ? $onPage->count()
                            : $onPage->where('booking_status', $value)->count();
                    @endphp
                    <button type="button" class="sp-tab {{ $value === '' ? 'is-active' : '' }}" data-sp-filter="{{ $value }}">
                        {{ $label }} <span class="sp-tab-count">{{ $tabCount }}</span>
                    </button>
                @endforeach

                @if($scopeLabel)
                    <span class="sp-tabs-note">Tri sur les réservations de cette page</span>
                @endif
            </div>

            <div class="sp-rows" data-sp-list>
                @foreach ($bookings as $booking)
                    @php
                        $ad    = $booking->ad;
                        $start = \Carbon\Carbon::parse($booking->start_date);
                        $end   = \Carbon\Carbon::parse($booking->end_date);
                        $nights = max(1, $start->diffInDays($end));
                        [$stLabel, $stClass, $stIcon] = $statusMeta[$booking->booking_status]
                            ?? [ucfirst((string) $booking->booking_status), 'is-neutral', 'fa-circle-info'];
                        $owner = $ad?->user;
                    @endphp

                    <article class="sp-row {{ $booking->booking_status === 'cancelled' ? 'is-cancelled' : '' }}"
                             data-status="{{ $booking->booking_status }}">

                        {{-- Date de debut --}}
                        <div class="sp-row-date">
                            <span class="sp-date-day">{{ $start->format('d') }}</span>
                            <span class="sp-date-month">{{ $mois[(int) $start->format('n')] }}</span>
                            <span class="sp-date-year">{{ $start->format('Y') }}</span>
                            <span class="sp-date-sep"></span>
                            <span class="sp-date-end">au {{ $end->format('d/m/Y') }}</span>
                        </div>

                        <div class="sp-row-main">
                            <div class="sp-row-head">
                                @if($ad)
                                    <a href="{{ route('ads.show', $ad) }}" class="sp-row-title">{{ $ad->title }}</a>
                                @else
                                    <h3 class="sp-row-title">Annonce supprimée</h3>
                                @endif
                                <span class="sp-ref">#{{ $booking->id }}</span>
                                <span class="sp-status {{ $stClass }}">
                                    <i class="fa-solid {{ $stIcon }}"></i> {{ $stLabel }}
                                </span>
                            </div>

                            <span class="sp-chip">
                                <i class="fa-solid fa-tag"></i>
                                {{ $ad->category->nom ?? 'Sans catégorie' }}
                            </span>

                            <div class="sp-row-meta">
                                <span class="sp-tag">
                                    <i class="fa-solid fa-user"></i>
                                    Propriétaire : {{ trim(($owner->firstname ?? '') . ' ' . ($owner->name ?? '')) ?: ($owner->email ?? 'Inconnu') }}
                                </span>

                                <span class="sp-tag">
                                    <i class="fa-regular fa-moon"></i>
                                    {{ $nights }} jour{{ $nights > 1 ? 's' : '' }}
                                </span>

                                <span class="sp-tag">
                                    <i class="fa-regular fa-clock"></i>
                                    Réservée le {{ $booking->created_at->format('d/m/Y à H:i') }}
                                </span>

                                @if($booking->delivery_requested)
                                    <span class="sp-tag is-ok">
                                        <i class="fa-solid fa-truck-fast"></i> Livraison demandée
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="sp-row-side">
                            <div class="sp-amount">
                                {{ number_format((float) $booking->total_price, 2, ',', ' ') }} €
                                <small>Total de la réservation</small>
                            </div>

                            <div class="sp-row-actions">
                                @if($booking->delivery_requested)
                                    <a href="{{ route('bookings.show', $booking->id) }}" class="sp-act is-edit">
                                        Suivre
                                    </a>
                                @endif

                                @if($ad)
                                    <a href="{{ route('ads.show', $ad) }}" class="sp-act is-ghost">
                                        Voir l'annonce
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <p class="sp-nores" data-sp-nores>Aucune réservation dans cette catégorie sur la page affichée.</p>

            @if($bookings->hasPages())
                <div class="sp-pagination">
                    {{ $bookings->withQueryString()->links() }}
                </div>
            @endif
        @else
            <div class="sp-empty">
                <x-empty-state
                    title="Aucune réservation pour le moment"
                    text="Vos réservations apparaîtront ici dès que vous en aurez effectué une."
                    :action-url="route('home')"
                    action-label="Parcourir les annonces" />
            </div>
        @endif
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs  = document.querySelectorAll('[data-sp-filter]');
        const list  = document.querySelector('[data-sp-list]');
        const none  = document.querySelector('[data-sp-nores]');

        if (!tabs.length || !list) return;

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                const filter = tab.dataset.spFilter;
                let visible = 0;

                tabs.forEach(t => t.classList.toggle('is-active', t === tab));

                list.querySelectorAll('.sp-row').forEach(function (row) {
                    const match = !filter || row.dataset.status === filter;
                    row.classList.toggle('is-hidden', !match);
                    if (match) visible++;
                });

                if (none) none.classList.toggle('is-shown', visible === 0);
            });
        });
    });
</script>
@endsection
