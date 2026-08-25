@extends('layouts.connected')
@section('title', 'Livraisons terminées | ' . config('app.name'))

@php
    /*
     | $livraisonsTerminees, $totalLivres et $revenusCumules viennent du
     | controleur. La relation cote vente s'appelle productSale (et non
     | « order ») : c'est elle qui porte le produit livre.
     */
    $notes    = $livraisonsTerminees->flatMap(fn ($l) => $l->reviews->pluck('rating'));
    $moyenne  = $notes->count() ? round($notes->avg(), 1) : null;
    $moisFr   = [1 => 'janv', 'févr', 'mars', 'avr', 'mai', 'juin', 'juil', 'août', 'sept', 'oct', 'nov', 'déc'];
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Livraisons terminées</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Livraisons terminées</h1>
            <p class="sp-subtitle">L'historique de vos courses achevées et de ce qu'elles vous ont rapporté.</p>
        </div>

        <a href="{{ route('livreur.missions') }}" class="sp-btn-primary">
            Voir les missions
        </a>
    </header>

    {{-- Indicateurs --}}
    <div class="sp-stats">
        <div class="sp-stat">
            <span class="sp-stat-icon is-green"><i class="fa-solid fa-circle-check"></i></span>
            <div>
                <span class="sp-stat-value">{{ $totalLivres }}</span>
                <span class="sp-stat-label">Livraison{{ $totalLivres > 1 ? 's' : '' }} terminée{{ $totalLivres > 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-brand"><i class="fa-solid fa-euro-sign"></i></span>
            <div>
                <span class="sp-stat-value">{{ number_format($revenusCumules, 2, ',', ' ') }} €</span>
                <span class="sp-stat-label">Revenus cumulés</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-blue"><i class="fa-solid fa-star"></i></span>
            <div>
                <span class="sp-stat-value">{{ $moyenne ? $moyenne . ' / 5' : '—' }}</span>
                <span class="sp-stat-label">
                    Note moyenne
                    <small>{{ $notes->count() }} avis reçu{{ $notes->count() > 1 ? 's' : '' }}</small>
                </span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-red"><i class="fa-solid fa-chart-line"></i></span>
            <div>
                <span class="sp-stat-value">
                    {{ $totalLivres ? number_format($revenusCumules / $totalLivres, 2, ',', ' ') : '0,00' }} €
                </span>
                <span class="sp-stat-label">Revenu moyen par course</span>
            </div>
        </div>
    </div>

    {{-- Panneau --}}
    <section class="sp-panel">

        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">Historique</h2>
                <span class="sp-count">{{ $totalLivres }} course{{ $totalLivres > 1 ? 's' : '' }} achevée{{ $totalLivres > 1 ? 's' : '' }}</span>
            </div>
        </div>

        @if($livraisonsTerminees->count())
            <div class="sp-grid">
                @foreach ($livraisonsTerminees as $livraison)
                    @php
                        $client = $livraison->booking?->ad?->user ?? $livraison->productSale?->product?->user;
                        $titre  = $livraison->booking?->ad?->title
                                  ?? $livraison->productSale?->product?->name
                                  ?? 'Livraison';
                        $date   = $livraison->delivered_at ?? $livraison->created_at;
                        $note   = $livraison->reviews->count() ? round($livraison->reviews->avg('rating'), 1) : null;
                        $initiales = strtoupper(mb_substr($client?->name ?? 'C', 0, 2));
                    @endphp

                    <article class="sp-card sp-mission">

                        <div class="sp-mission-head">
                            <div>
                                <span class="sp-status is-delivered">
                                    <i class="fa-solid fa-circle-check"></i> Terminée
                                </span>
                                <span class="sp-mission-date">
                                    @if($date)
                                        {{ $date->format('d') }} {{ $moisFr[(int) $date->format('n')] }} {{ $date->format('Y') }}
                                    @else
                                        Date inconnue
                                    @endif
                                </span>
                            </div>

                            <div class="sp-mission-price">
                                {{ number_format((float) $livraison->total_price, 2, ',', ' ') }} €
                                <small>perçu</small>
                            </div>
                        </div>

                        <div class="sp-mission-body">
                            <h3 class="sp-mission-title">{{ \Illuminate\Support\Str::limit($titre, 60) }}</h3>

                            <div class="sp-trip">
                                <div class="sp-trip-step">
                                    <span class="sp-trip-dot"></span>
                                    <div>
                                        <span class="sp-trip-label">Départ</span>
                                        <span class="sp-trip-value">{{ $livraison->pickup_address ?: 'Non précisé' }}</span>
                                    </div>
                                </div>

                                <div class="sp-trip-step is-end">
                                    <span class="sp-trip-dot"></span>
                                    <div>
                                        <span class="sp-trip-label">Arrivée</span>
                                        <span class="sp-trip-value">{{ $livraison->delivery_address ?: 'Non précisée' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="sp-mission-client">
                                <span class="sp-avatar is-dark">{{ $initiales }}</span>
                                <div>
                                    <span class="sp-mission-role">Client</span>
                                    <span class="sp-mission-name">{{ $client?->name ?? 'Anonyme' }}</span>
                                </div>

                                @if($note)
                                    <span class="sp-rating" title="{{ $livraison->reviews->count() }} avis">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fa-star {{ $i <= round($note) ? 'fas' : 'far' }}"></i>
                                        @endfor
                                        <strong>{{ $note }}</strong>
                                    </span>
                                @endif
                            </div>

                            @if($livraison->reviews->count())
                                @foreach($livraison->reviews->take(1) as $review)
                                    @if($review->comment)
                                        <blockquote class="sp-review">{{ $review->comment }}</blockquote>
                                    @endif
                                @endforeach
                            @endif
                        </div>

                        <div class="sp-actions">
                            <button type="button" class="sp-act is-edit"
                                    onclick='openHistoryModal(@json($livraison))'>Voir le détail</button>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="sp-empty">
                <x-empty-state
                    title="Aucune livraison terminée"
                    text="Vos courses achevées et leurs revenus s'afficheront ici."
                    :action-url="route('livreur.missions')"
                    action-label="Voir les missions disponibles" />
            </div>
        @endif
    </section>
</div>

{{-- Detail d'une livraison --}}
<div class="sp-modal" id="historyModal" hidden>
    <div class="sp-modal-backdrop" onclick="closeHistoryModal()"></div>

    <div class="sp-modal-box" role="dialog" aria-modal="true" aria-labelledby="historyTitle">
        <div class="sp-modal-head">
            <div>
                <span class="sp-modal-kicker">Livraison terminée</span>
                <h2 class="sp-modal-title" id="historyTitle"></h2>
            </div>

            <button type="button" class="sp-act is-ghost" onclick="closeHistoryModal()">Fermer</button>
        </div>

        <div class="sp-modal-body" id="historyContent"></div>
    </div>
</div>

<script>
    function formatDateFr(value) {
        if (!value) return '—';
        const d = new Date(value);
        return isNaN(d) ? '—' : d.toLocaleString('fr-FR');
    }

    function openHistoryModal(livraison) {
        document.getElementById('historyTitle').textContent = 'Livraison #' + livraison.id;

        const steps = [
            ['Mission créée', livraison.created_at],
            ['Colis récupéré', livraison.picked_up_at],
            ['Livraison démarrée', livraison.started_at],
            ['Colis livré', livraison.delivered_at],
        ].filter(s => s[1]);

        const esc = (v) => String(v == null ? '' : v)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

        document.getElementById('historyContent').innerHTML = `
            <div class="sp-modal-grid">
                <div class="sp-modal-cell">
                    <span class="sp-modal-label">Statut</span>
                    <strong>Terminée</strong>
                </div>
                <div class="sp-modal-cell">
                    <span class="sp-modal-label">Montant perçu</span>
                    <strong class="sp-modal-price">${esc(livraison.total_price)} €</strong>
                </div>
            </div>

            <div class="sp-modal-cell">
                <span class="sp-modal-label">Adresse de départ</span>
                <strong>${esc(livraison.pickup_address || '—')}</strong>
            </div>

            <div class="sp-modal-cell">
                <span class="sp-modal-label">Adresse de livraison</span>
                <strong>${esc(livraison.delivery_address || '—')}</strong>
            </div>

            <div class="sp-modal-cell">
                <span class="sp-modal-label">Déroulé</span>
                <ol class="sp-timeline">
                    ${steps.map(([label, date]) => `
                        <li>
                            <span class="sp-timeline-label">${esc(label)}</span>
                            <span class="sp-timeline-date">${esc(formatDateFr(date))}</span>
                        </li>
                    `).join('')}
                </ol>
            </div>
        `;

        document.getElementById('historyModal').hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeHistoryModal() {
        document.getElementById('historyModal').hidden = true;
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeHistoryModal();
    });
</script>
@endsection
