@extends('layouts.connected')
@section('title', $trip->depart_ville . ' → ' . $trip->destination_ville . ' - Ma réservation - Olten')

@php
    /*
     | Détail d'une réservation de covoiturage, en espace connecté.
     |
     | Même principe que la fiche publique du trajet (carte, sens aller /
     | retour, chronologie, infos voyage) mais :
     |   - aucun bouton « Réserver » : le trajet est déjà réservé et payé ;
     |   - seuls les sens réservés sont affichés ;
     |   - le montant payé, frais de service compris, remplace le
     |     récapitulatif à cocher ;
     |   - le contact est celui de l'autre partie : le conducteur pour le
     |     passager, le passager pour le conducteur.
     |
     | Le design est autonome (classes bkd-*) : il ne dépend d'aucune feuille
     | de la fiche publique, seulement du fil d'ariane et de l'en-tête sp-*.
     */
    $driver  = $trip->conducteur;
    $vehicle = $driver?->vehicle;

    $avatarUrl = function ($user) {
        $photo = $user->profile_photo ?? null;
        if (! $photo) return null;

        return \Illuminate\Support\Str::startsWith($photo, ['http://', 'https://', '/'])
            ? $photo
            : asset('storage/' . ltrim($photo, '/'));
    };

    // L'autre partie : le conducteur pour le passager, le passager pour le conducteur
    $other      = $isDriver ? $booking->passenger : $driver;
    $otherPhone = $isDriver ? $booking->phone : ($driver->phone ?? null);
    $otherRole  = $isDriver ? 'Passager' : 'Conducteur';
    $otherPhoto = $other ? $avatarUrl($other) : null;
    $otherName  = $other ? trim(($other->firstname ?? '') . ' ' . ($other->lastname ?? '')) : '—';
    $initials   = $other
        ? mb_strtoupper(mb_substr($other->firstname ?? '?', 0, 1) . mb_substr($other->lastname ?? '', 0, 1))
        : '?';

    $states = [
        'upcoming'  => ['Confirmée', 'fa-solid fa-circle-check'],
        'past'      => ['Terminée',  'fa-solid fa-flag-checkered'],
        'cancelled' => ['Annulée · remboursée', 'fa-solid fa-ban'],
        // Libellés de la validation manuelle, lus sur le statut
        'pending'   => ["En attente de l'accord du conducteur", 'fa-solid fa-hourglass-half'],
        'refused'   => ['Refusée · remboursée', 'fa-solid fa-circle-xmark'],
        'expired'   => ['Sans réponse · remboursée', 'fa-solid fa-clock-rotate-left'],
    ];

    $modes = [
        'womenOnly'    => ['fa-venus', 'Femmes uniquement', 'Ce trajet est réservé aux passagères.'],
        'maxBackSeats' => ['fa-user-group', 'Maximum 2 à l\'arrière', 'Plus de place pour voyager confortablement.'],
        'mixed'        => ['fa-users', 'Trajet mixte', 'Ouvert à tous les passagers.'],
    ];
    $mode = $modes[$trip->passenger_mode] ?? null;

    $rate      = rtrim(rtrim(number_format((float) $booking->commission_rate, 2, ',', ''), '0'), ',');
    // Taux de la réservation, figé au paiement : le passager lit ses tarifs
    // frais de service compris à ce taux.
    $feeRate   = (float) $booking->commission_rate;
    // Seul le passager annule sa réservation : le conducteur s'est engagé envers lui.
    $canCancel = $state === 'upcoming' && ! $isDriver;
    $sens      = count($legs) > 1 ? 'Aller & retour' : (($legs['retour'] ?? null) ? 'Retour' : 'Aller');
    $cancelMsg = $booking->isPending()
        ? 'Annuler votre demande ? Vous serez intégralement remboursé.'
        : 'Annuler votre réservation ? Vous serez intégralement remboursé.';
    // Pastille : l'état, précisé par le statut (en attente d'accord, refusée, sans réponse)
    $badge     = in_array($booking->status, ['pending', 'refused', 'expired'], true) ? $booking->status : $state;
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('trips.myBookings') }}">Mes trajets réservés</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Réservation #{{ $booking->id }}</span>
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
            <h1 class="sp-title">{{ $trip->depart_ville }} → {{ $trip->destination_ville }}</h1>
            <p class="sp-subtitle">Réservation #{{ $booking->id }} · {{ $sens }}</p>
        </div>

        <span class="bkd-status bkd-status--{{ $badge }}">
            <i class="{{ $states[$badge][1] }}"></i> {{ $states[$badge][0] }}
        </span>
    </header>

    <div class="bkd">

        {{-- ==================== Ligne 1 : carte + paiement ==================== --}}
        <div class="bkd-row bkd-row--main">

            <div class="bkd-card bkd-mapcard">
                <div id="bkdMap" class="bkd-map"
                     data-legs="{{ json_encode(collect($legs)->map(fn ($leg) => ['key' => $leg['label'], 'path' => $leg['path']])->values()) }}"></div>

                <div class="bkd-mapcard-who">
                    <span class="bkd-mini-avatar">
                        <span>{{ $initials }}</span>
                        @if ($otherPhoto)
                            <img src="{{ $otherPhoto }}" alt="{{ $otherName }}" onerror="this.remove()">
                        @endif
                    </span>
                    <span class="bkd-mapcard-who-info">
                        <strong>{{ $otherName }}</strong>
                        @if ($other?->verifie)
                            <small class="is-ok"><i class="fa-solid fa-circle-check"></i> Profil vérifié</small>
                        @else
                            <small>{{ $otherRole }}</small>
                        @endif
                    </span>
                </div>
            </div>

            <aside class="bkd-side">

                <div class="bkd-card bkd-recap">
                    <span class="bkd-label">Ma réservation</span>

                    {{-- Sens réservés : figés, on ne les coche plus --}}
                    <div class="bkd-rlegs">
                        @foreach ($legs as $key => $leg)
                            <div class="bkd-rleg">
                                <span class="bkd-rleg-icon {{ $key === 'retour' ? 'is-return' : '' }}">
                                    <i class="fa-solid {{ $key === 'retour' ? 'fa-arrow-left-long' : 'fa-arrow-right-long' }}"></i>
                                </span>
                                <span class="bkd-rleg-info">
                                    <strong>{{ $key === 'retour' ? 'Retour' : 'Aller' }}</strong>
                                    <small>
                                        {{ $leg['from'] }} → {{ $leg['to'] }}
                                        @if ($leg['date'])
                                            · {{ $leg['date']->translatedFormat('d M') }}
                                        @endif
                                        @if ($leg['time'])
                                            {{ $leg['time'] }}
                                        @endif
                                    </small>
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Paiement --}}
                    <div class="bkd-lines">
                        @if ($isDriver)
                            <div class="bkd-total">
                                <span>Votre gain</span>
                                <strong>{{ number_format((float) $booking->driver_amount, 2, ',', ' ') }} €</strong>
                            </div>
                        @else
                            {{-- Montant payé, frais de service compris : pas de ligne de frais à part --}}
                            <div class="bkd-total">
                                <span>{{ $state === 'cancelled' ? 'Total remboursé' : 'Total payé' }}</span>
                                <strong>{{ number_format((float) $booking->total_price, 2, ',', ' ') }} €</strong>
                            </div>
                        @endif
                    </div>

                    {{-- Pas de « Réserver » ici : la seule action possible est l'annulation --}}
                    @if ($canCancel)
                        <form action="{{ route('bookings.cancel', $booking) }}" method="POST"
                              data-booking-cancel data-message="{{ $cancelMsg }}">
                            @csrf
                            <button type="submit" class="bkd-cancel">
                                <i class="fa-solid fa-ban"></i> {{ $booking->isPending() ? 'Annuler ma demande' : 'Annuler la réservation' }}
                            </button>
                        </form>
                    @elseif ($isDriver && $booking->isPending())
                        {{-- Validation manuelle : le conducteur accepte ou refuse la demande --}}
                        <form action="{{ route('bookings.approve', $booking) }}" method="POST">
                            @csrf
                            <button type="submit" class="bkd-accept">
                                <i class="fa-solid fa-check"></i> Approuver la réservation
                            </button>
                        </form>
                        <form action="{{ route('bookings.refuse', $booking) }}" method="POST"
                              data-booking-cancel data-title="Refuser cette demande ?"
                              data-message="Refuser cette demande ? Le passager sera intégralement remboursé."
                              data-confirm-label="Oui, refuser" data-keep-label="Revenir">
                            @csrf
                            <button type="submit" class="bkd-cancel">
                                <i class="fa-solid fa-xmark"></i> Refuser la demande
                            </button>
                        </form>
                    @elseif ($isDriver && $state === 'upcoming')
                        <p class="bkd-note">
                            <i class="fa-solid fa-lock"></i>
                            Vous vous êtes engagé auprès de ce passager : seul lui peut annuler sa réservation.
                        </p>
                    @endif
                </div>

                <div class="bkd-card bkd-vehicle">
                    <span class="bkd-vehicle-icon"><i class="fa-solid fa-car-side"></i></span>
                    <span class="bkd-vehicle-info">
                        <strong>
                            {{ $vehicle ? \Illuminate\Support\Str::title(trim($vehicle->marque . ' ' . $vehicle->modele)) : 'Véhicule non renseigné' }}
                        </strong>
                        <small>
                            @php $seatsLeft = $trip->seats_left; @endphp
                            {{ $vehicle?->couleur ? ucfirst($vehicle->couleur) . ' · ' : '' }}
                            @if ($seatsLeft < 1)
                                Complet
                            @else
                                {{ $seatsLeft }} place{{ $seatsLeft > 1 ? 's' : '' }} restante{{ $seatsLeft > 1 ? 's' : '' }}
                            @endif
                        </small>
                    </span>
                </div>
            </aside>
        </div>

        {{-- ==================== Ligne 2 : sens réservés + infos voyage ==================== --}}
        <div class="bkd-row bkd-row--legs">

            <div class="bkd-card bkd-legs">
                @if (count($legs) > 1)
                    <div class="bkd-tabs" role="tablist">
                        @foreach ($legs as $key => $leg)
                            <button type="button" role="tab" class="bkd-tab {{ $loop->first ? 'is-active' : '' }}"
                                    data-cvd-tab="{{ $key }}">
                                <i class="fa-solid {{ $key === 'retour' ? 'fa-arrow-left-long' : 'fa-arrow-right-long' }}"></i>
                                {{ $leg['label'] }}
                            </button>
                        @endforeach
                    </div>
                @endif

                @foreach ($legs as $key => $leg)
                    <div class="bkd-panel {{ $loop->first ? 'is-active' : '' }}" data-cvd-panel="{{ $key }}">

                        <div class="bkd-panel-main">
                            <h2 class="bkd-panel-title">
                                {{ $leg['from'] }} <i class="fa-solid fa-arrow-right"></i> {{ $leg['to'] }}
                            </h2>

                            <div class="bkd-chips">
                                <span class="bkd-chip">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ $leg['date']?->translatedFormat('D d M Y') ?? 'Date à confirmer' }}
                                </span>
                                @if ($leg['duration'] > 0)
                                    <span class="bkd-chip">
                                        <i class="fa-regular fa-clock"></i>
                                        {{ intdiv($leg['duration'], 3600) }}h{{ str_pad((string) intdiv($leg['duration'] % 3600, 60), 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                @endif
                                @if ($leg['distance'] > 0)
                                    <span class="bkd-chip">
                                        <i class="fa-solid fa-road"></i>
                                        {{ number_format($leg['distance'] / 1000, 1, ',', ' ') }} km
                                    </span>
                                @endif
                            </div>

                            <div class="bkd-timeline">
                                <div class="bkd-stop">
                                    <span class="bkd-time">{{ $leg['time'] ?: '--:--' }}</span>
                                    <span class="bkd-dot is-start"></span>
                                    <span class="bkd-place">
                                        <strong>{{ $leg['from'] }}</strong>
                                        <small>{{ $leg['address']['from'] }}</small>
                                    </span>
                                </div>

                                <div class="bkd-rail"><span></span></div>

                                <div class="bkd-stop">
                                    <span class="bkd-time">
                                        {{ $leg['arrival'] ?: '--:--' }}
                                        @if ($leg['next_day'])
                                            <em>+1j</em>
                                        @endif
                                    </span>
                                    <span class="bkd-dot is-end"></span>
                                    <span class="bkd-place">
                                        <strong>{{ $leg['to'] }}</strong>
                                        <small>{{ $leg['address']['to'] }}</small>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="bkd-pricing">
                            <span class="bkd-label">
                                {{ count($leg['segments']) > 1 ? 'Escales et tarifs' : 'Tarif' }}
                            </span>

                            @php
                                // Le passager lit ses tarifs frais de service compris (les
                                // escales se partagent le prix du sens) ; le conducteur
                                // garde son propre prix.
                                $legPrice  = $isDriver
                                    ? $leg['total']
                                    : \App\Models\Covoiturage::passengerPrice($leg['total'], $feeRate);
                                $segPrices = $isDriver
                                    ? array_map(fn ($segment) => (float) ($segment['price'] ?? 0), $leg['segments'])
                                    : \App\Models\Covoiturage::passengerSegmentPrices($leg['segments'], $leg['total'], $feeRate);
                            @endphp

                            @forelse ($leg['segments'] as $segment)
                                @php
                                    $segFrom = \App\Models\Covoiturage::villeCourte($segment['from'] ?? '');
                                    $segTo   = \App\Models\Covoiturage::villeCourte($segment['to'] ?? '');
                                @endphp
                                <div class="bkd-line">
                                    <span>
                                        @if ($segFrom && $segTo)
                                            {{ $segFrom }} → {{ $segTo }}
                                        @else
                                            Étape {{ $loop->iteration }}
                                        @endif
                                    </span>
                                    <span>{{ number_format($segPrices[$loop->index], 2, ',', ' ') }} €</span>
                                </div>
                            @empty
                                <div class="bkd-line">
                                    <span>Trajet complet</span>
                                    <span>{{ number_format($legPrice, 2, ',', ' ') }} €</span>
                                </div>
                            @endforelse

                            <div class="bkd-total bkd-total--sm">
                                <span>Total {{ $key === 'retour' ? 'retour' : 'aller' }}</span>
                                <strong>{{ number_format($legPrice, 2, ',', ' ') }} €</strong>
                            </div>

                            <p class="bkd-note">
                                <i class="fa-solid fa-circle-info"></i>
                                @if ($isDriver)
                                    Prix par place fixé par le conducteur. Les frais de service
                                    ({{ $rate }} %) sont ajoutés et réglés en ligne.
                                @else
                                    Prix par place, réglé en ligne.
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <aside class="bkd-card bkd-infos">
                <span class="bkd-label">Infos voyage</span>

                <div class="bkd-info">
                    <span class="bkd-info-icon is-blue"><i class="fa-solid fa-user-group"></i></span>
                    <span class="bkd-info-text">
                        @php
                            // « 2 places réservées », ou « 2 places à l'aller · 1 au retour »
                            $splitSeats = $booking->seatsOn('aller') && $booking->seatsOn('retour')
                                && $booking->seatsOn('aller') !== $booking->seatsOn('retour');
                        @endphp
                        <strong>{{ $booking->seatsLabel() }}{{ $splitSeats ? '' : ' réservée' . ($booking->seats > 1 ? 's' : '') }}</strong>
                        <small>
                            @if ($state === 'cancelled')
                                {{ $booking->seats > 1 ? 'Places libérées' : 'Place libérée' }}
                            @else
                                {{ $isDriver ? 'Au nom de ' . $otherName : 'À votre nom' }}
                            @endif
                        </small>
                    </span>
                </div>

                @if ($mode)
                    <div class="bkd-info">
                        <span class="bkd-info-icon is-pink"><i class="fa-solid {{ $mode[0] }}"></i></span>
                        <span class="bkd-info-text">
                            <strong>{{ $mode[1] }}</strong>
                            <small>{{ $mode[2] }}</small>
                        </span>
                    </div>
                @endif

                <div class="bkd-info">
                    <span class="bkd-info-icon is-green">
                        <i class="fa-solid {{ count($legs) > 1 ? 'fa-arrow-right-arrow-left' : 'fa-arrow-right-long' }}"></i>
                    </span>
                    <span class="bkd-info-text">
                        <strong>{{ $sens }}</strong>
                        <small>{{ count($legs) > 1 ? 'Les deux sens sont réservés' : 'Un seul sens réservé' }}</small>
                    </span>
                </div>

                <div class="bkd-info">
                    <span class="bkd-info-icon is-orange">
                        <i class="fa-solid {{ $trip->booking_mode === 'instant' ? 'fa-bolt' : 'fa-hourglass-half' }}"></i>
                    </span>
                    <span class="bkd-info-text">
                        <strong>{{ $trip->booking_mode === 'instant' ? 'Réservation immédiate' : 'Accord du conducteur' }}</strong>
                        <small>
                            {{ $trip->booking_mode === 'instant'
                                ? 'Votre place est confirmée sans attente.'
                                : 'Le conducteur valide chaque demande.' }}
                        </small>
                    </span>
                </div>
            </aside>
        </div>

        {{-- ==================== Ligne 3 : contact + message ==================== --}}
        <div class="bkd-row bkd-row--contact">

            <div class="bkd-card bkd-contact">
                <span class="bkd-label">{{ $isDriver ? 'Votre passager' : 'Votre conducteur' }}</span>

                <div class="bkd-profile">
                    <span class="bkd-avatar">
                        <span>{{ $initials }}</span>
                        @if ($otherPhoto)
                            <img src="{{ $otherPhoto }}" alt="Photo de {{ $otherName }}" loading="lazy" onerror="this.remove()">
                        @endif
                        @if ($other?->verifie)
                            <i class="bkd-avatar-check fa-solid fa-check" title="Profil vérifié"></i>
                        @endif
                    </span>

                    <span class="bkd-profile-info">
                        <strong>{{ $otherName }}</strong>
                        <span class="bkd-role"><i class="fa-solid {{ $isDriver ? 'fa-user' : 'fa-car-side' }}"></i> {{ $otherRole }}</span>
                        <small>
                            @if ($other?->verifie)
                                <span class="bkd-ok"><i class="fa-solid fa-shield-halved"></i> Profil vérifié</span>
                            @endif
                            @if ($other?->created_at)
                                <span><i class="fa-regular fa-calendar"></i> Membre depuis {{ $other->created_at->translatedFormat('F Y') }}</span>
                            @endif
                        </small>
                    </span>
                </div>

                @if (filled($other?->about_me))
                    <p class="bkd-about">{{ \Illuminate\Support\Str::limit($other->about_me, 220) }}</p>
                @endif

                {{-- Coordonnées échangées une fois la réservation confirmée --}}
                @if ($state !== 'cancelled' && ! $booking->isPending() && $otherPhone)
                    <div class="bkd-phone">
                        <span class="bkd-phone-ico"><i class="fa-solid fa-phone"></i></span>
                        <span class="bkd-phone-text">
                            <small>Téléphone {{ $isDriver ? 'du passager' : 'du conducteur' }}</small>
                            <strong>{{ $otherPhone }}</strong>
                        </span>
                        @if ($state === 'upcoming')
                            <a href="tel:{{ $otherPhone }}" class="bkd-call">
                                <i class="fa-solid fa-phone-volume"></i> Appeler
                            </a>
                        @endif
                    </div>
                @endif

                @unless ($isDriver)
                    <p class="bkd-note">
                        <i class="fa-solid fa-circle-info"></i>
                        Le conducteur vous joint au {{ $booking->phone }}.
                    </p>
                @endunless
            </div>

            <div class="bkd-card bkd-msg">
                <div class="bkd-msg-head">
                    <span class="bkd-msg-icon"><i class="fa-regular fa-message"></i></span>
                    <div>
                        <h3>Message du conducteur</h3>
                        <p>Instructions et préférences de voyage</p>
                    </div>
                </div>

                <div class="bkd-msg-box">
                    @if ($trip->message_conducteur)
                        « {{ $trip->message_conducteur }} »
                    @else
                        Prévenez le conducteur au plus tôt en cas d'empêchement : une place libérée
                        à temps peut profiter à un autre passager.
                    @endif
                </div>

                <div class="bkd-msg-foot">
                    <span class="bkd-msg-dot"></span>
                    <span>Besoin d'aide ? <a href="{{ route('contact') }}">Contacter le support</a></span>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Styles du detail (bkd-), partages avec le suivi d'une reservation
     d'annonce (bookings/show) : assets/css/booking-detail.css --}}
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/booking-detail.css') }}?v={{ @filemtime(public_path('assets/css/booking-detail.css')) ?: 1 }}">
@endpush

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // ---- Annulation : même dialogue de confirmation que les Archives ----
        document.querySelectorAll('[data-booking-cancel]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (form.dataset.confirmed === '1') return;
                e.preventDefault();

                const message = form.dataset.message || 'Annuler cette réservation ?';
                const valider = function () {
                    form.dataset.confirmed = '1';
                    form.submit();
                };

                if (typeof Swal === 'undefined') {
                    if (confirm(message)) valider();
                    return;
                }

                Swal.fire({
                    title: form.dataset.title || 'Annuler cette réservation ?',
                    text: message.replace(/^[^?]*\?\s*/, ''),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: form.dataset.confirmLabel || 'Oui, annuler',
                    cancelButtonText: form.dataset.keepLabel || 'Garder la réservation',
                    confirmButtonColor: '#c0392b',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                }).then(function (result) {
                    if (result.isConfirmed) valider();
                });
            });
        });

        // ---- Onglets aller / retour ----
        const tabs = document.querySelectorAll('[data-cvd-tab]');
        const panels = document.querySelectorAll('[data-cvd-panel]');
        let showLeg = function () {};

        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () {
                tabs.forEach(t => t.classList.remove('is-active'));
                panels.forEach(p => p.classList.remove('is-active'));
                tab.classList.add('is-active');
                document.querySelector('[data-cvd-panel="' + tab.dataset.cvdTab + '"]')?.classList.add('is-active');
                showLeg(index);
            });
        });

        // ---- Carte de l'itinéraire ----
        const holder = document.getElementById('bkdMap');
        if (!holder) return;

        function initMap() {
            let legs = [];
            try {
                legs = JSON.parse(holder.dataset.legs || '[]');
            } catch (e) {
                legs = [];
            }

            const map = L.map(holder, { scrollWheelZoom: false });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            const layers = legs.map(function (leg) {
                const group = L.layerGroup();

                if (!leg.path || leg.path.length < 2) return group;

                L.polyline(leg.path, { color: '#ff3c00', weight: 5, opacity: .9 }).addTo(group);

                const start = leg.path[0];
                const end = leg.path[leg.path.length - 1];

                L.circleMarker(start, { radius: 8, color: '#ff3c00', fillColor: '#fff', fillOpacity: 1, weight: 4 }).addTo(group);
                L.circleMarker(end, { radius: 8, color: '#1f2328', fillColor: '#1f2328', fillOpacity: 1, weight: 4 }).addTo(group);

                return group;
            });

            showLeg = function (index) {
                layers.forEach(layer => map.removeLayer(layer));

                const layer = layers[index];
                if (!layer) return;

                layer.addTo(map);

                const path = legs[index]?.path || [];
                if (path.length > 1) {
                    map.fitBounds(L.latLngBounds(path), { padding: [40, 40] });
                }
            };

            const first = legs.findIndex(leg => (leg.path || []).length > 1);

            if (first === -1) {
                // Aucun tracé exploitable : on centre sur la France plutôt que sur l'océan.
                map.setView([46.6, 2.4], 5);
            } else {
                showLeg(first);
            }

            setTimeout(() => map.invalidateSize(), 200);
        }

        // Le layout connecté ne charge pas forcément Leaflet : on le charge ici au besoin.
        if (typeof L !== 'undefined') {
            initMap();
        } else {
            const css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(css);

            const js = document.createElement('script');
            js.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            js.onload = initMap;
            document.head.appendChild(js);
        }
    });
</script>
@endsection