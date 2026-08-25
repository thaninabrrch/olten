@extends('layouts.connected')
@section('title', 'Demandes de livraison | ' . config('app.name'))

@php
    /*
     | $requests est une collection (non paginee) fournie par le controleur :
     | ce sont les livreurs qui se proposent sur vos locations et vos ventes.
     | Les compteurs portent donc sur la totalite.
     */
    $pending  = $requests->where('status', 'pending')->count();
    $accepted = $requests->where('status', 'accepted')->count();
    $refused  = $requests->where('status', 'refused')->count();

    $tabs = [
        ''         => ['Toutes', $requests->count()],
        'pending'  => ['En attente', $pending],
        'accepted' => ['Acceptées', $accepted],
        'refused'  => ['Refusées', $refused],
    ];

    $statusMeta = [
        'pending'  => ['En attente', 'is-pending',   'fa-hourglass-half'],
        'accepted' => ['Acceptée',   'is-confirmed', 'fa-circle-check'],
        'refused'  => ['Refusée',    'is-cancelled', 'fa-circle-xmark'],
    ];
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Demandes de livraison</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Demandes de livraison</h1>
            <p class="sp-subtitle">Les livreurs qui se proposent pour acheminer vos locations et vos ventes.</p>
        </div>
    </header>

    {{-- Indicateurs --}}
    <div class="sp-stats">
        <div class="sp-stat">
            <span class="sp-stat-icon is-brand"><i class="fa-solid fa-inbox"></i></span>
            <div>
                <span class="sp-stat-value">{{ $requests->count() }}</span>
                <span class="sp-stat-label">Demande{{ $requests->count() > 1 ? 's' : '' }} reçue{{ $requests->count() > 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-red"><i class="fa-solid fa-bell"></i></span>
            <div>
                <span class="sp-stat-value">{{ $pending }}</span>
                <span class="sp-stat-label">À traiter</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-green"><i class="fa-solid fa-handshake"></i></span>
            <div>
                <span class="sp-stat-value">{{ $accepted }}</span>
                <span class="sp-stat-label">Livreur{{ $accepted > 1 ? 's' : '' }} recruté{{ $accepted > 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-blue"><i class="fa-solid fa-xmark"></i></span>
            <div>
                <span class="sp-stat-value">{{ $refused }}</span>
                <span class="sp-stat-label">Refusée{{ $refused > 1 ? 's' : '' }}</span>
            </div>
        </div>
    </div>

    {{-- Panneau --}}
    <section class="sp-panel">

        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">Propositions des livreurs</h2>
                <span class="sp-count">
                    {{ $requests->count() }} proposition{{ $requests->count() > 1 ? 's' : '' }} au total
                </span>
            </div>
        </div>

        @if($requests->count())
            <div class="sp-tabs" data-sp-filters>
                @foreach($tabs as $value => [$label, $count])
                    <button type="button" class="sp-tab {{ $value === '' ? 'is-active' : '' }}" data-sp-filter="{{ $value }}">
                        {{ $label }} <span class="sp-tab-count">{{ $count }}</span>
                    </button>
                @endforeach
            </div>

            <div class="sp-rows" data-sp-list>
                @foreach($requests as $request)
                    @php
                        $isBooking = (bool) $request->booking_id;
                        $title     = $request->booking?->ad?->title
                                     ?? $request->productSale?->product?->name
                                     ?? 'Élément supprimé';
                        $from      = $request->booking?->address ?? $request->productSale?->address;
                        $to        = $request->booking?->delivery_address ?? $request->productSale?->delivery_address;
                        $cost      = $request->booking?->delivery_cost ?? $request->productSale?->delivery_cost;
                        $person    = $request->deliveryPerson;
                        $initials  = strtoupper(mb_substr($person->name ?? '?', 0, 2));
                        [$stLabel, $stClass, $stIcon] = $statusMeta[$request->status]
                            ?? [ucfirst((string) $request->status), 'is-neutral', 'fa-circle-info'];
                    @endphp

                    <article class="sp-row {{ $request->status === 'refused' ? 'is-cancelled' : '' }}"
                             data-status="{{ $request->status }}">

                        {{-- Livreur --}}
                        <div class="sp-row-person">
                            <span class="sp-avatar is-dark">{{ $initials }}</span>
                        </div>

                        <div class="sp-row-main">
                            <div class="sp-row-head">
                                <h3 class="sp-row-title">{{ $person->name ?? 'Livreur' }}</h3>
                                <span class="sp-status {{ $stClass }}">
                                    <i class="fa-solid {{ $stIcon }}"></i> {{ $stLabel }}
                                </span>
                            </div>

                            <div class="sp-row-meta">
                                <span class="sp-chip">
                                    <i class="fa-solid {{ $isBooking ? 'fa-key' : 'fa-box-open' }}"></i>
                                    {{ $isBooking ? 'Location' : 'Vente' }}
                                </span>

                                <span class="sp-tag">
                                    <i class="fa-solid fa-cube"></i>
                                    {{ \Illuminate\Support\Str::limit($title, 40) }}
                                </span>
                            </div>

                            @if($from || $to)
                                <div class="sp-route">
                                    <span class="sp-route-step">{{ $from ?: 'Départ non précisé' }}</span>
                                    <span class="sp-route-arrow">→</span>
                                    <span class="sp-route-step">{{ $to ?: 'Arrivée non précisée' }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="sp-row-side">
                            <div class="sp-amount">
                                {{ number_format((float) $cost, 2, ',', ' ') }} €
                                <small>Coût de la livraison</small>
                            </div>

                            <div class="sp-row-actions">
                                @if($request->status === 'pending')
                                    <form action="{{ route('delivery.request.accept', $request) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="sp-act is-success">Recruter</button>
                                    </form>

                                    <form action="{{ route('delivery.request.refuse', $request) }}" method="POST"
                                          data-sp-confirm
                                          data-title="Refuser ce livreur ?"
                                          data-text="{{ $person->name ?? 'Ce livreur' }} ne sera pas retenu pour cette livraison."
                                          data-confirm-label="Oui, refuser">
                                        @csrf
                                        <button type="submit" class="sp-act is-cancel">Refuser</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <p class="sp-nores" data-sp-nores>Aucune demande dans cette catégorie.</p>
        @else
            <div class="sp-empty">
                <x-empty-state
                    title="Aucune demande reçue"
                    text="Les livreurs qui se proposent pour vos livraisons apparaîtront ici." />
            </div>
        @endif
    </section>
</div>

@include('partials.confirm-script')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('[data-sp-filter]');
        const list = document.querySelector('[data-sp-list]');
        const none = document.querySelector('[data-sp-nores]');

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
