@extends('layouts.connected')
@section('title', 'Missions en attente | ' . config('app.name'))

@php
    /*
     | $missions = DeliveryRequest deja filtrees par le controleur sur le
     | livreur connecte et sur status = 'pending' : rien a refiltrer ici.
     |
     | Le discriminant location / vente est booking_id (l'ancienne version
     | testait un champ absent, ce qui affichait toujours le meme libelle).
     | Cote vente, l'expediteur se lit sur productSale->product->user :
     | ProductSale n'a pas de relation « ad ».
     */
    $total    = $missions->count();
    $attendus = $missions->sum(fn ($m) => (float) ($m->booking?->delivery_cost ?? $m->productSale?->delivery_cost ?? 0));
    $rentals  = $missions->filter(fn ($m) => (bool) $m->booking_id)->count();
    $sales    = $total - $rentals;
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('livreur.missions') }}">Espace livreur</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Missions en attente</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Missions en attente</h1>
            <p class="sp-subtitle">Les courses sur lesquelles vous vous êtes positionné, en attente de réponse.</p>
        </div>

        <a href="{{ route('livreur.missions') }}" class="sp-btn-primary">
            Trouver des missions
        </a>
    </header>

    {{-- Indicateurs --}}
    <div class="sp-stats">
        <div class="sp-stat">
            <span class="sp-stat-icon is-brand"><i class="fa-solid fa-hourglass-half"></i></span>
            <div>
                <span class="sp-stat-value">{{ $total }}</span>
                <span class="sp-stat-label">Candidature{{ $total > 1 ? 's' : '' }} en attente</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-green"><i class="fa-solid fa-euro-sign"></i></span>
            <div>
                <span class="sp-stat-value">{{ number_format($attendus, 2, ',', ' ') }} €</span>
                <span class="sp-stat-label">Rémunération espérée</span>
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
                <h2 class="sp-toolbar-title">Mes candidatures</h2>
                <span class="sp-count">{{ $total }} demande{{ $total > 1 ? 's' : '' }} sans réponse</span>
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

                        /* Seuls les champs affiches sont transmis a la modale :
                           serialiser le modele entier exposait le telephone du
                           client et les identifiants de paiement dans le HTML. */
                        $detail = [
                            'title'    => $titre,
                            'type'     => $isRental ? 'Location' : 'Vente',
                            'sender'   => $sender,
                            'cost'     => (float) ($source?->delivery_cost ?? 0),
                            'pickup'   => $source?->address,
                            'dropoff'  => $source?->delivery_address,
                            'start'    => $isRental ? optional($mission->booking?->start_date)->format('d/m/Y') : null,
                            'end'      => $isRental ? optional($mission->booking?->end_date)->format('d/m/Y') : null,
                            'desc'     => $isRental
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
                                {{ number_format((float) ($source?->delivery_cost ?? 0), 2, ',', ' ') }} €
                                <small>net</small>
                            </div>
                        </div>

                        <div class="sp-mission-body">
                            <div class="sp-row-head">
                                <span class="sp-chip">
                                    <i class="fa-solid {{ $isRental ? 'fa-key' : 'fa-box-open' }}"></i>
                                    {{ $isRental ? 'Location' : 'Vente' }}
                                </span>

                                <span class="sp-status is-pending">
                                    <i class="fa-solid fa-hourglass-half"></i> En attente de réponse
                                </span>
                            </div>

                            <h3 class="sp-mission-title">{{ \Illuminate\Support\Str::limit($titre, 60) }}</h3>

                            <div class="sp-trip">
                                <div class="sp-trip-step">
                                    <span class="sp-trip-dot"></span>
                                    <div>
                                        <span class="sp-trip-label">Point de départ</span>
                                        <span class="sp-trip-value">{{ $source?->address ?: 'Non précisé' }}</span>
                                    </div>
                                </div>

                                <div class="sp-trip-step is-end">
                                    <span class="sp-trip-dot"></span>
                                    <div>
                                        <span class="sp-trip-label">Arrivée</span>
                                        <span class="sp-trip-value">{{ $source?->delivery_address ?: 'Non précisée' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="sp-actions">
                            <button type="button" class="sp-act is-edit" onclick='openMissionModal(@json($detail))'>Voir le détail</button>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="sp-empty">
                <x-empty-state
                    title="Aucune demande en attente"
                    text="Positionnez-vous sur une mission disponible : elle apparaîtra ici jusqu'à la réponse du client."
                    :action-url="route('livreur.missions')"
                    action-label="Voir les missions disponibles" />
            </div>
        @endif
    </section>
</div>

{{-- Detail d'une candidature --}}
<div class="sp-modal" id="missionModal" hidden>
    <div class="sp-modal-backdrop" onclick="closeMissionModal()"></div>

    <div class="sp-modal-box" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="sp-modal-head">
            <div>
                <span class="sp-modal-kicker">Candidature</span>
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
    function openMissionModal(detail) {
        const set = (id, value) => document.getElementById(id).textContent = value;

        set('modalTitle', detail.title || 'Mission');
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
