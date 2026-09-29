@extends('layouts.connected')
@section('title', 'Mes trajets réservés - Olten')

@php
    /*
     | Trajets que le membre a réservés et payés. Même structure que la page
     | Archives (sp-page, sp-panel, sp-tabs, sp-grid, sp-card) : seul le
     | contenu des cartes change.
     |
     |   - À venir : au moins un sens reste à faire
     |   - Passés  : tous les sens réservés sont derrière soi
     |   - Annulés : réservation annulée et remboursée
     */
    $tabs = [
        'a-venir'  => 'À venir',
        'passes'   => 'Passés',
        'annulees' => 'Annulés',
    ];

    $titles = [
        'a-venir'  => 'Trajets à venir',
        'passes'   => 'Trajets passés',
        'annulees' => 'Trajets annulés',
    ];

    $states = [
        'upcoming'  => ['Confirmé', 'fa-solid fa-circle-check'],
        'past'      => ['Terminé',  'fa-solid fa-flag-checkered'],
        'cancelled' => ['Annulé',   'fa-solid fa-ban'],
        // Libellés de la validation manuelle, lus sur le statut
        'pending'   => ["En attente d'accord", 'fa-solid fa-hourglass-half'],
        'refused'   => ['Refusé',       'fa-solid fa-circle-xmark'],
        'expired'   => ['Sans réponse', 'fa-solid fa-clock-rotate-left'],
    ];

    $avatarUrl = function ($user) {
        $photo = $user->profile_photo ?? null;
        if (! $photo) return null;

        return \Illuminate\Support\Str::startsWith($photo, ['http://', 'https://', '/'])
            ? $photo
            : asset('storage/' . ltrim($photo, '/'));
    };
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Mes trajets réservés</span>
    </nav>

    @if (session('success'))
        <div class="sp-note"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="sp-note"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
    @endif

    {{-- En-tête --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Mes trajets réservés</h1>
            <p class="sp-subtitle">Le détail de chaque trajet payé : dates, conducteur, véhicule et paiement.</p>
        </div>
    </header>

    <div class="sp-note">
        <i class="fa-solid fa-circle-info"></i>
        Un trajet passe dans <strong>Passés</strong> le lendemain de son dernier sens réservé.
        Vous pouvez <strong>annuler</strong> un trajet à venir : le paiement est alors intégralement remboursé
        et la réservation rejoint l'onglet <strong>Annulés</strong>.
    </div>

    {{-- Panneau --}}
    <section class="sp-panel">

        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">{{ $titles[$tab] }}</h2>
                <span class="sp-count">
                    {{ $list->count() }} trajet{{ $list->count() > 1 ? 's' : '' }}
                </span>
            </div>

            <div class="sp-toolbar-actions">
                <span class="sp-tag">
                    <i class="fa-solid fa-wallet"></i>
                    Total payé : {{ number_format($spent, 2, ',', ' ') }} €
                </span>
            </div>
        </div>

        {{-- Onglets --}}
        <div class="sp-tabs">
            @foreach ($tabs as $value => $label)
                <a href="{{ route('trips.myBookings', ['onglet' => $value]) }}"
                   class="sp-tab {{ $tab === $value ? 'is-active' : '' }}">
                    {{ $label }}
                    <span class="sp-tab-count">{{ $counts[$value] }}</span>
                </a>
            @endforeach
        </div>

        @if ($list->count())
            <div class="sp-grid">
                @foreach ($list as $item)
                    @php
                        $booking = $item['booking'];
                        $driver  = $item['driver'];
                        $vehicle = $item['vehicle'];
                        $state   = $item['state'];
                        // Pastille : l'état de l'onglet, précisé par le statut
                        // (en attente d'accord, refusé, sans réponse)
                        $badge   = in_array($booking->status, ['pending', 'refused', 'expired'], true) ? $booking->status : $state;
                        $photo   = $driver ? $avatarUrl($driver) : null;
                        $initial = $driver
                            ? mb_strtoupper(mb_substr($driver->firstname ?? '?', 0, 1) . mb_substr($driver->lastname ?? '', 0, 1))
                            : '?';
                        $rate    = rtrim(rtrim(number_format((float) $booking->commission_rate, 2, ',', ''), '0'), ',');
                        $sens    = $item['legs']->count() > 1 ? 'Aller & retour' : ($item['legs']->first()['label'] ?? 'Aller');
                    @endphp

                    <article class="sp-card {{ $state === 'upcoming' ? '' : 'is-out' }}">

                        {{-- Visuel de l'itinéraire --}}
                        @if ($item['url'])
                            <a href="{{ $item['url'] }}" class="sp-media" title="Voir la réservation">
                        @else
                            <div class="sp-media">
                        @endif
                            <img src="{{ $item['image'] }}" alt="{{ $item['from'] }} - {{ $item['to'] }}" loading="lazy">

                            <div class="sp-media-badges">
                                <span class="sp-badge is-type">
                                    <i class="fa-solid fa-car-side"></i> Covoiturage
                                </span>
                                <span class="sp-badge mtb-state mtb-state--{{ $badge }}">
                                    <i class="{{ $states[$badge][1] }}"></i> {{ $states[$badge][0] }}
                                </span>
                            </div>
                        @if ($item['url'])
                            </a>
                        @else
                            </div>
                        @endif

                        <div class="sp-body">
                            <div class="sp-list-main">
                                <span class="sp-chip">
                                    <i class="fa-solid fa-route"></i>
                                    {{ $sens }} · {{ $booking->seatsLabel() }} · Réservation #{{ $booking->id }}
                                </span>

                                @if ($item['url'])
                                    <a href="{{ $item['url'] }}" class="sp-name">{{ $item['from'] }} → {{ $item['to'] }}</a>
                                @else
                                    <span class="sp-name">{{ $item['from'] }} → {{ $item['to'] }}</span>
                                @endif

                                <div class="sp-price">
                                    {{ number_format((float) $booking->total_price, 2, ',', ' ') }} €
                                    <small>{{ $state === 'cancelled' ? 'remboursé' : 'payé' }}</small>
                                </div>
                            </div>

                            {{-- Dates de chaque sens --}}
                            <div class="sp-meta">
                                @foreach ($item['legs'] as $leg)
                                    @if ($leg['date'])
                                        <span class="sp-tag">
                                            <i class="fa-regular fa-calendar"></i>
                                            {{ $leg['label'] }} · {{ $leg['date']->translatedFormat('d M Y') }}@if ($leg['time']) · {{ $leg['time'] }}@endif
                                        </span>
                                    @endif
                                @endforeach

                                @if ($driver)
                                    <span class="sp-tag">
                                        <i class="fa-regular fa-user"></i>
                                        {{ trim(($driver->firstname ?? '') . ' ' . ($driver->lastname ?? '')) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Détail : conducteur, véhicule, paiement --}}
                            <details class="mtb-more">
                                <summary>
                                    <i class="fa-solid fa-receipt"></i> Voir le détail
                                    <i class="fa-solid fa-chevron-down mtb-more-caret"></i>
                                </summary>

                                <div class="mtb-driver">
                                    <span class="mtb-avatar">
                                        <span>{{ $initial }}</span>
                                        @if ($photo)
                                            <img src="{{ $photo }}" alt="Photo de {{ $driver->firstname }}" loading="lazy" onerror="this.remove()">
                                        @endif
                                    </span>

                                    <span class="mtb-driver-info">
                                        <strong>
                                            {{ $driver ? trim(($driver->firstname ?? '') . ' ' . ($driver->lastname ?? '')) : '—' }}
                                            @if ($driver?->verifie)
                                                <i class="fa-solid fa-circle-check mtb-verified" title="Profil vérifié"></i>
                                            @endif
                                        </strong>
                                        @if ($vehicle)
                                            <small>
                                                <i class="fa-solid fa-car"></i>
                                                {{ \Illuminate\Support\Str::title(trim($vehicle->marque . ' ' . $vehicle->modele)) }}@if ($vehicle->couleur) · {{ ucfirst($vehicle->couleur) }}@endif
                                            </small>
                                        @endif
                                        @if ($state === 'upcoming' && $driver?->phone)
                                            <a href="tel:{{ $driver->phone }}" class="mtb-phone">
                                                <i class="fa-solid fa-phone"></i> {{ $driver->phone }}
                                            </a>
                                        @endif
                                    </span>
                                </div>

                                <div class="mtb-pay">
                                    <div class="mtb-pay-line">
                                        <span>Prix du trajet</span>
                                        <span>{{ number_format((float) $booking->driver_amount, 2, ',', ' ') }} €</span>
                                    </div>
                                    <div class="mtb-pay-line">
                                        <span>Frais de service ({{ $rate }} %)</span>
                                        <span>{{ number_format((float) $booking->commission, 2, ',', ' ') }} €</span>
                                    </div>
                                    <div class="mtb-pay-line mtb-pay-line--total">
                                        <span>{{ $state === 'cancelled' ? 'Total remboursé' : 'Total payé' }}</span>
                                        <span>{{ number_format((float) $booking->total_price, 2, ',', ' ') }} €</span>
                                    </div>
                                </div>
                            </details>
                        </div>

                        <div class="sp-actions">
                            @if ($item['url'])
                                <a href="{{ $item['url'] }}" class="sp-act is-edit">Voir la réservation</a>
                            @endif

                            @if ($state === 'upcoming')
                                <form action="{{ route('bookings.cancel', $booking) }}" method="POST"
                                      data-booking-cancel data-name="{{ $item['from'] }} → {{ $item['to'] }}">
                                    @csrf
                                    <button type="submit" class="sp-act is-delete"
                                            aria-label="Annuler la réservation {{ $item['from'] }} → {{ $item['to'] }}">
                                        Annuler
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="sp-empty">
                @if ($tab === 'a-venir')
                    <x-empty-state
                        title="Aucun trajet à venir"
                        text="Vos prochains trajets réservés et payés apparaîtront ici."
                        :action-url="route('services.show', 'covoiturage')"
                        action-label="Trouver un trajet" />
                @elseif ($tab === 'passes')
                    <x-empty-state
                        title="Aucun trajet passé"
                        text="Les trajets que vous avez effectués seront conservés ici." />
                @else
                    <x-empty-state
                        title="Aucune annulation"
                        text="Les réservations annulées et remboursées apparaîtront ici." />
                @endif
            </div>
        @endif
    </section>
</div>

<style>
    /* Compléments propres à cette page : le reste vient de la feuille sp-* */
    .sp-badge.mtb-state--upcoming  { background: #2f9e5f; color: #fff; }
    .sp-badge.mtb-state--past      { background: #6b7280; color: #fff; }
    .sp-badge.mtb-state--cancelled { background: #b42318; color: #fff; }
    .sp-badge.mtb-state--pending   { background: #f79009; color: #fff; }
    .sp-badge.mtb-state--refused,
    .sp-badge.mtb-state--expired   { background: #b42318; color: #fff; }

    .mtb-more { margin-top: 12px; border-top: 1px dashed var(--color-divider, #e9ecef); padding-top: 10px; }

    .mtb-more > summary {
        display: flex; align-items: center; gap: 8px;
        list-style: none; cursor: pointer;
        font-size: 12.5px; font-weight: 700; color: var(--color-primary, #ff3c00);
    }
    .mtb-more > summary::-webkit-details-marker { display: none; }
    .mtb-more-caret { margin-left: auto; font-size: 10px; transition: transform .2s ease; }
    .mtb-more[open] .mtb-more-caret { transform: rotate(180deg); }

    .mtb-driver { display: flex; align-items: center; gap: 12px; margin-top: 12px; }

    .mtb-avatar { position: relative; flex: 0 0 46px; width: 46px; height: 46px; border-radius: 50%; }
    .mtb-avatar span, .mtb-avatar img { position: absolute; inset: 0; width: 100%; height: 100%; border-radius: 50%; }
    .mtb-avatar span {
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #ff3c00, #ff8a4c);
        color: #fff; font-size: 15px; font-weight: 800;
    }
    .mtb-avatar img { object-fit: cover; }

    .mtb-driver-info { min-width: 0; display: flex; flex-direction: column; gap: 2px; }
    .mtb-driver-info strong { font-size: 13.5px; font-weight: 700; }
    .mtb-driver-info small { font-size: 12px; color: #6b7280; }
    .mtb-driver-info small i, .mtb-phone i { color: var(--color-primary, #ff3c00); margin-right: 5px; }
    .mtb-verified { color: #2f9e5f; font-size: 12px; margin-left: 4px; }
    .mtb-phone { font-size: 12.5px; font-weight: 600; color: inherit; text-decoration: none; }

    .mtb-pay { margin-top: 12px; padding: 12px 14px; border-radius: 12px; background: var(--color-grey-light, #f6f7f9); }
    .mtb-pay-line { display: flex; justify-content: space-between; gap: 12px; padding: 4px 0; font-size: 12.5px; color: #555; }
    .mtb-pay-line span:last-child { white-space: nowrap; }
    .mtb-pay-line--total {
        margin-top: 4px; padding-top: 9px;
        border-top: 1px solid var(--color-divider, #e9ecef);
        color: var(--color-black, #111); font-weight: 800; font-size: 13.5px;
    }
</style>

<script>
    // Annulation : même dialogue de confirmation que la page Archives
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-booking-cancel]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (form.dataset.confirmed === '1') return;
                e.preventDefault();

                const name = form.dataset.name || 'ce trajet';
                const valider = function () {
                    form.dataset.confirmed = '1';
                    form.submit();
                };

                if (typeof Swal === 'undefined') {
                    if (confirm('Annuler « ' + name + ' » ? Vous serez intégralement remboursé.')) valider();
                    return;
                }

                Swal.fire({
                    title: 'Annuler cette réservation ?',
                    html: '« <strong>' + name.replace(/</g, '&lt;') + '</strong> » : vous serez intégralement remboursé.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, annuler',
                    cancelButtonText: 'Garder ma réservation',
                    confirmButtonColor: '#c0392b',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                }).then(function (result) {
                    if (result.isConfirmed) valider();
                });
            });
        });
    });
</script>
@endsection