@extends('layouts.connected')
@section('title', 'Missions en cours | ' . config('app.name'))

@php
    /*
     | $missions = Delivery attribuees au livreur connecte et non terminees.
     |
     | Machine a etats, une seule action possible a la fois :
     |   pending    -> livreur.livraison.pickup     (« J'ai récupéré le colis »)
     |   picked_up  -> livreur.livraison.start      (« Démarrer la livraison »)
     |   in_transit -> livreur.livraison.finaliser  (confirmation puis POST)
     |
     | Cote vente, l'expediteur se lit sur productSale->product->user :
     | ProductSale n'a pas de relation « ad ».
     */
    $etapes = [
        'pending'    => ['À récupérer',    'is-pending',   'fa-box',        1],
        'picked_up'  => ['Colis récupéré', 'is-confirmed', 'fa-box-open',   2],
        'in_transit' => ['En route',       'is-shipped',   'fa-truck-fast', 3],
        'accepted'   => ['Acceptée',       'is-neutral',   'fa-circle-check', 1],
    ];

    $total    = $missions->count();
    $aRecup   = $missions->where('status', 'pending')->count();
    $enRoute  = $missions->where('status', 'in_transit')->count();
    $gains    = $missions->sum(fn ($m) => (float) ($m->booking?->delivery_cost ?? $m->productSale?->delivery_cost ?? 0));
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('livreur.missions') }}">Espace livreur</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Missions en cours</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Missions en cours</h1>
            <p class="sp-subtitle">Vos courses acceptées : récupérez le colis, démarrez, puis clôturez la livraison.</p>
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
                <span class="sp-stat-label">Course{{ $total > 1 ? 's' : '' }} en cours</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-red"><i class="fa-solid fa-box"></i></span>
            <div>
                <span class="sp-stat-value">{{ $aRecup }}</span>
                <span class="sp-stat-label">Colis à récupérer</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-blue"><i class="fa-solid fa-truck-fast"></i></span>
            <div>
                <span class="sp-stat-value">{{ $enRoute }}</span>
                <span class="sp-stat-label">En route</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-green"><i class="fa-solid fa-euro-sign"></i></span>
            <div>
                <span class="sp-stat-value">{{ number_format($gains, 2, ',', ' ') }} €</span>
                <span class="sp-stat-label">Rémunération engagée</span>
            </div>
        </div>
    </div>

    {{-- Panneau --}}
    <section class="sp-panel">

        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">Courses en cours</h2>
                <span class="sp-count">{{ $total }} mission{{ $total > 1 ? 's' : '' }} à mener à terme</span>
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
                        $isRental = (bool) $mission->booking_id;
                        $source   = $isRental ? $mission->booking : $mission->productSale;
                        $titre    = $isRental
                            ? ($mission->booking?->ad?->title ?? 'Annonce supprimée')
                            : ($mission->productSale?->product?->name ?? 'Produit supprimé');
                        $sender   = $isRental
                            ? ($mission->booking?->ad?->user?->name ?? 'Client')
                            : ($mission->productSale?->product?->user?->name ?? 'Client');
                        $initials = strtoupper(mb_substr($sender, 0, 2));
                        $cout     = (float) ($source?->delivery_cost ?? 0);

                        [$stLabel, $stClass, $stIcon, $etape] = $etapes[$mission->status]
                            ?? [ucfirst((string) $mission->status), 'is-neutral', 'fa-circle-info', 1];

                        $depart  = $mission->pickup_address ?: $source?->address;
                        $arrivee = $mission->delivery_address ?: $source?->delivery_address;

                        /* Seuls les champs affiches partent dans la modale :
                           serialiser la Delivery entiere exposait le telephone
                           du client et les identifiants de paiement. */
                        $detail = [
                            'title'   => $titre,
                            'type'    => $isRental ? 'Location' : 'Vente',
                            'sender'  => $sender,
                            'cost'    => $cout,
                            'pickup'  => $depart,
                            'dropoff' => $arrivee,
                            'status'  => $stLabel,
                            'start'   => $isRental ? optional($mission->booking?->start_date)->format('d/m/Y') : null,
                            'end'     => $isRental ? optional($mission->booking?->end_date)->format('d/m/Y') : null,
                            'desc'    => $isRental
                                ? strip_tags((string) $mission->booking?->ad?->description)
                                : strip_tags((string) $mission->productSale?->product?->description),
                        ];
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
                                {{ number_format($cout, 2, ',', ' ') }} €
                                <small>net</small>
                            </div>
                        </div>

                        <div class="sp-mission-body">
                            <div class="sp-row-head">
                                <span class="sp-chip">
                                    <i class="fa-solid {{ $isRental ? 'fa-key' : 'fa-box-open' }}"></i>
                                    {{ $isRental ? 'Location' : 'Vente' }}
                                </span>

                                <span class="sp-status {{ $stClass }}">
                                    <i class="fa-solid {{ $stIcon }}"></i> {{ $stLabel }}
                                </span>
                            </div>

                            <h3 class="sp-mission-title">{{ \Illuminate\Support\Str::limit($titre, 60) }}</h3>

                            {{-- Avancement de la course --}}
                            <ol class="sp-steps" aria-label="Avancement de la mission">
                                <li class="{{ $etape >= 1 ? 'is-done' : '' }}"><span>Acceptée</span></li>
                                <li class="{{ $etape >= 2 ? 'is-done' : '' }}"><span>Récupérée</span></li>
                                <li class="{{ $etape >= 3 ? 'is-done' : '' }}"><span>En route</span></li>
                                <li><span>Livrée</span></li>
                            </ol>

                            <div class="sp-trip">
                                <div class="sp-trip-step">
                                    <span class="sp-trip-dot"></span>
                                    <div>
                                        <span class="sp-trip-label">Point de départ</span>
                                        <span class="sp-trip-value">{{ $depart ?: 'Non précisé' }}</span>
                                    </div>
                                </div>

                                <div class="sp-trip-step is-end">
                                    <span class="sp-trip-dot"></span>
                                    <div>
                                        <span class="sp-trip-label">Destination</span>
                                        <span class="sp-trip-value">{{ $arrivee ?: 'Non précisée' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="sp-actions">
                            {{-- Une seule action possible, dictee par le statut --}}
                            @if($mission->status === 'pending')
                                <form action="{{ route('livreur.livraison.pickup', $mission) }}" method="POST" class="sp-mission-form">
                                    @csrf
                                    <button type="submit" class="sp-act is-edit">J'ai récupéré le colis</button>
                                </form>
                            @elseif($mission->status === 'picked_up')
                                <form action="{{ route('livreur.livraison.start', $mission) }}" method="POST" class="sp-mission-form">
                                    @csrf
                                    <button type="submit" class="sp-act is-edit">Démarrer la livraison</button>
                                </form>
                            @elseif($mission->status === 'in_transit')
                                <button type="button" class="sp-act is-success sp-mission-form"
                                        onclick="openConfirmModal('{{ route('livreur.livraison.finaliser', $mission) }}')">
                                    Terminer la livraison
                                </button>
                            @endif

                            <button type="button" class="sp-act is-ghost" onclick='openMissionModal(@json($detail))'>Détails</button>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="sp-empty">
                <x-empty-state
                    title="Aucune course en cours"
                    text="Vos missions acceptées s'afficheront ici, avec l'action à effectuer à chaque étape."
                    :action-url="route('livreur.missions')"
                    action-label="Voir les missions disponibles" />
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
                    <span class="sp-modal-label">Statut</span>
                    <strong id="modalStatus"></strong>
                </div>

                <div class="sp-modal-cell">
                    <span class="sp-modal-label">Rémunération</span>
                    <strong class="sp-modal-price" id="modalPrice"></strong>
                </div>
            </div>

            <div class="sp-modal-grid">
                <div class="sp-modal-cell">
                    <span class="sp-modal-label">Type</span>
                    <strong id="modalType"></strong>
                </div>

                <div class="sp-modal-cell">
                    <span class="sp-modal-label">Expéditeur</span>
                    <strong id="modalSender"></strong>
                </div>
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

{{-- Confirmation de fin de course --}}
<div class="sp-modal" id="confirmDeliveryModal" hidden>
    <div class="sp-modal-backdrop" onclick="closeConfirmModal()"></div>

    <div class="sp-modal-box is-small" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
        <div class="sp-modal-head">
            <div>
                <span class="sp-modal-kicker">Dernière étape</span>
                <h2 class="sp-modal-title" id="confirmTitle">Terminer la livraison ?</h2>
            </div>
        </div>

        <div class="sp-modal-body">
            <p class="sp-modal-text">
                Confirmez que le colis a bien été remis au destinataire. La course passera en
                « terminée » et sa rémunération sera comptabilisée.
            </p>
        </div>

        <div class="sp-modal-foot">
            <button type="button" class="sp-act is-ghost" onclick="closeConfirmModal()">Annuler</button>

            {{-- L'action est renseignee au clic depuis route() : plus d'URL en dur --}}
            <form method="POST" id="confirmDeliveryForm">
                @csrf
                <button type="submit" class="sp-act is-success">Oui, c'est livré</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openMissionModal(detail) {
        const set = (id, value) => document.getElementById(id).textContent = value;

        set('modalTitle', detail.title || 'Mission');
        set('modalStatus', detail.status || '—');
        set('modalType', detail.type || '—');
        set('modalPrice', (detail.cost ?? 0).toFixed(2).replace('.', ',') + ' €');
        set('modalSender', detail.sender || 'Client');
        set('modalPickup', detail.pickup || '—');
        set('modalDelivery', detail.dropoff || '—');
        // Texte brut : la description vient de la base, jamais injectée en HTML
        set('modalDescription', detail.desc || 'Aucune description');

        const dates = document.getElementById('datesBlock');

        if (detail.start) {
            dates.hidden = false;
            set('modalStartDate', detail.start);
            set('modalEndDate', detail.end || '—');
        } else {
            dates.hidden = true;
        }

        openModal('missionModal');
    }

    function closeMissionModal() {
        closeModal('missionModal');
    }

    function openConfirmModal(actionUrl) {
        document.getElementById('confirmDeliveryForm').action = actionUrl;
        openModal('confirmDeliveryModal');
    }

    function closeConfirmModal() {
        closeModal('confirmDeliveryModal');
    }

    function openModal(id) {
        document.getElementById(id).hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        document.getElementById(id).hidden = true;
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        closeMissionModal();
        closeConfirmModal();
    });
</script>
@endsection
