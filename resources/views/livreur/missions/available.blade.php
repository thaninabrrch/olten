@extends('layouts.connected')
@section('title', 'Missions disponibles | ' . config('app.name'))

@php
    /*
     | $missions melange des locations (Booking) et des ventes (ProductSale)
     | deja triees par le controleur. Les deux exposent address,
     | delivery_address et delivery_cost ; c'est ad_id qui les distingue.
     */
    $total    = $missions->count();
    $earnings = $missions->sum(fn ($m) => (float) $m->delivery_cost);
    $rentals  = $missions->filter(fn ($m) => (bool) $m->ad_id)->count();
    $sales    = $total - $rentals;
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Missions disponibles</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Missions disponibles</h1>
            <p class="sp-subtitle">Parcourez les courses ouvertes et proposez vos services de livraison.</p>
        </div>

        <a href="{{ route('liv_termine') }}" class="sp-btn-primary">
            Mes livraisons terminées
        </a>
    </header>

    {{-- Indicateurs --}}
    <div class="sp-stats">
        <div class="sp-stat">
            <span class="sp-stat-icon is-brand"><i class="fa-solid fa-route"></i></span>
            <div>
                <span class="sp-stat-value">{{ $total }}</span>
                <span class="sp-stat-label">Mission{{ $total > 1 ? 's' : '' }} ouverte{{ $total > 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-green"><i class="fa-solid fa-euro-sign"></i></span>
            <div>
                <span class="sp-stat-value">{{ number_format($earnings, 2, ',', ' ') }} €</span>
                <span class="sp-stat-label">Rémunération cumulée</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-blue"><i class="fa-solid fa-key"></i></span>
            <div>
                <span class="sp-stat-value">{{ $rentals }}</span>
                <span class="sp-stat-label">Location{{ $rentals > 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-red"><i class="fa-solid fa-box-open"></i></span>
            <div>
                <span class="sp-stat-value">{{ $sales }}</span>
                <span class="sp-stat-label">Vente{{ $sales > 1 ? 's' : '' }}</span>
            </div>
        </div>
    </div>

    {{-- Panneau --}}
    <section class="sp-panel">

        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">Flux des missions</h2>
                <span class="sp-count">{{ $total }} mission{{ $total > 1 ? 's' : '' }} à prendre</span>
            </div>

            <div class="sp-toolbar-actions">
                <button type="button" class="sp-search-submit" onclick="window.location.reload()">Actualiser</button>
            </div>
        </div>

        {{-- Navigation de l'espace livreur --}}
        <div class="sp-tabs">
            <a href="{{ route('livreur.missions') }}" class="sp-tab {{ request()->routeIs('livreur.missions') ? 'is-active' : '' }}">Disponibles</a>
            <a href="{{ route('livreur.demandes') }}" class="sp-tab {{ request()->routeIs('livreur.demandes') ? 'is-active' : '' }}">En attente</a>
            <a href="{{ route('livreur.livraisons') }}" class="sp-tab {{ request()->routeIs('livreur.livraisons') ? 'is-active' : '' }}">En cours</a>
        </div>

        @if($total)
            <div class="sp-grid">
                @foreach ($missions as $mission)
                    @php
                        $isRental = (bool) $mission->ad_id;
                        $sender   = $mission->ad?->user?->name ?? $mission->product?->user?->name ?? 'Client';
                        $title    = $mission->ad?->title ?? $mission->product?->name ?? 'Mission';
                        $initials = strtoupper(mb_substr($sender, 0, 2));
                    @endphp

                    <article class="sp-card sp-mission">

                        <div class="sp-mission-head">
                            <div class="sp-mission-sender">
                                <span class="sp-avatar is-dark">{{ $initials }}</span>
                                <div>
                                    <span class="sp-mission-role">Expéditeur</span>
                                    <span class="sp-mission-name">{{ \Illuminate\Support\Str::limit($sender, 22) }}</span>
                                </div>
                            </div>

                            <div class="sp-mission-price">
                                {{ number_format((float) $mission->delivery_cost, 2, ',', ' ') }} €
                                <small>net</small>
                            </div>
                        </div>

                        <div class="sp-mission-body">
                            <span class="sp-chip">
                                <i class="fa-solid {{ $isRental ? 'fa-key' : 'fa-box-open' }}"></i>
                                {{ $isRental ? 'Location' : 'Vente' }}
                            </span>

                            <h3 class="sp-mission-title">{{ \Illuminate\Support\Str::limit($title, 60) }}</h3>

                            {{-- Itineraire --}}
                            <div class="sp-trip">
                                <div class="sp-trip-step">
                                    <span class="sp-trip-dot"></span>
                                    <div>
                                        <span class="sp-trip-label">Point de départ</span>
                                        <span class="sp-trip-value">{{ $mission->address ?: 'Non précisé' }}</span>
                                    </div>
                                </div>

                                <div class="sp-trip-step is-end">
                                    <span class="sp-trip-dot"></span>
                                    <div>
                                        <span class="sp-trip-label">Arrivée</span>
                                        <span class="sp-trip-value">{{ $mission->delivery_address ?: 'Non précisée' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="sp-actions">
                            <form action="{{ route('delivery.ads.request', ['ad' => $mission->id, 'type' => $isRental ? 'ad' : 'product']) }}"
                                  method="POST" class="sp-mission-form">
                                @csrf
                                <button type="submit" class="sp-act is-edit">Prendre la mission</button>
                            </form>

                            <button type="button" class="sp-act is-ghost" onclick='openMissionModal(@json($mission))'>Détails</button>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="sp-empty">
                <x-empty-state
                    title="Aucune mission disponible"
                    text="Aucune course n'est ouverte pour le moment. Revenez dans quelques minutes." />
            </div>
        @endif
    </section>
</div>

{{-- Detail d'une mission --}}
<div class="sp-modal" id="missionModal" hidden>
    <div class="sp-modal-backdrop" onclick="closeMissionModal()"></div>

    <div class="sp-modal-box" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="sp-modal-head">
            <div>
                <span class="sp-modal-kicker">Mission</span>
                <h2 class="sp-modal-title" id="modalTitle"></h2>
            </div>

            <button type="button" class="sp-act is-ghost" onclick="closeMissionModal()">Fermer</button>
        </div>

        <div class="sp-modal-body">
            <div class="sp-modal-grid">
                <div class="sp-modal-cell">
                    <span class="sp-modal-label">Type</span>
                    <strong id="modalType"></strong>
                </div>

                <div class="sp-modal-cell">
                    <span class="sp-modal-label">Rémunération</span>
                    <strong class="sp-modal-price" id="modalPrice"></strong>
                </div>
            </div>

            <div class="sp-modal-cell">
                <span class="sp-modal-label">Expéditeur</span>
                <strong id="modalSender"></strong>
            </div>

            <div class="sp-modal-cell">
                <span class="sp-modal-label">Adresse de départ</span>
                <strong id="modalPickup"></strong>
            </div>

            <div class="sp-modal-cell">
                <span class="sp-modal-label">Adresse de livraison</span>
                <strong id="modalDelivery"></strong>
            </div>

            <div class="sp-modal-grid" id="datesBlock" hidden>
                <div class="sp-modal-cell">
                    <span class="sp-modal-label">Début</span>
                    <strong id="modalStartDate"></strong>
                </div>

                <div class="sp-modal-cell">
                    <span class="sp-modal-label">Fin</span>
                    <strong id="modalEndDate"></strong>
                </div>
            </div>

            <div class="sp-modal-cell">
                <span class="sp-modal-label">Description</span>
                <div id="modalDescription" class="sp-modal-text"></div>
            </div>
        </div>
    </div>
</div>

<script>
    function openMissionModal(mission) {
        const set = (id, value) => document.getElementById(id).textContent = value;

        set('modalTitle', mission.product?.name ?? mission.ad?.title ?? 'Mission');
        set('modalPrice', (mission.delivery_cost ?? 0) + ' €');
        set('modalSender', mission.ad?.user?.name ?? mission.product?.user?.name ?? 'Client');
        set('modalPickup', mission.address ?? '—');
        set('modalDelivery', mission.delivery_address ?? '—');
        set('modalType', mission.ad_id ? 'Location' : 'Vente');

        // La description vient de la base : on l'insere en texte, jamais en HTML
        document.getElementById('modalDescription').textContent =
            mission.product?.description ?? mission.ad?.description ?? 'Aucune description';

        const dates = document.getElementById('datesBlock');

        if (mission.start_date) {
            dates.hidden = false;
            set('modalStartDate', new Date(mission.start_date).toLocaleDateString('fr-FR'));
            set('modalEndDate', new Date(mission.end_date).toLocaleDateString('fr-FR'));
        } else {
            dates.hidden = true;
        }

        document.getElementById('missionModal').hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeMissionModal() {
        document.getElementById('missionModal').hidden = true;
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeMissionModal();
    });
</script>
@endsection
