@extends('layouts.main')

@section('title', $trip->depart_ville . ' → ' . $trip->destination_ville . ' - Détails du trajet - Olten.fr')

@php
    $driver  = $trip->conducteur;
    $vehicle = $driver?->vehicle;
    $avatar  = $trip->photo_conducteur
        ?: ($driver?->profile_photo ? asset('storage/' . $driver->profile_photo) : null);

    $modes = [
        'womenOnly'    => ['fa-venus', 'Femmes uniquement', 'Ce trajet est réservé aux passagères.'],
        'maxBackSeats' => ['fa-user-group', 'Maximum 2 à l\'arrière', 'Plus de place pour voyager confortablement.'],
        'mixed'        => ['fa-users', 'Trajet mixte', 'Ouvert à tous les passagers.'],
    ];
    $mode = $modes[$trip->passenger_mode] ?? null;
@endphp

@section('content')

<div class="cv-page cvd">

    <div class="cvd-wrap">

        {{-- Fil de titre --}}
        <div class="cvd-head">
            <a href="{{ route('covoiturage.trips', ['from' => $trip->depart_ville, 'to' => $trip->destination_ville]) }}"
               class="cvd-back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <span class="cvd-head-label">Détails du trajet</span>
            <span class="cvd-head-route">
                {{ $trip->depart_ville }} <i class="fa-solid fa-arrow-right"></i> {{ $trip->destination_ville }}
            </span>
        </div>

        {{-- Ligne 1 : carte + récapitulatif --}}
        <div class="cvd-row cvd-row--map">

            <div class="cvd-map-card">
                <div id="cvdMap" class="cvd-map"
                     data-legs="{{ json_encode(collect($legs)->map(fn ($leg) => ['key' => $leg['label'], 'path' => $leg['path']])->values()) }}"></div>

                <div class="cvd-map-driver">
                    @if ($avatar)
                        <img src="{{ $avatar }}" alt="{{ $driver->name ?? 'Conducteur' }}">
                    @else
                        <span class="cvd-avatar-initial">{{ strtoupper(mb_substr($driver->name ?? '?', 0, 1)) }}</span>
                    @endif
                    <span class="cvd-map-driver-info">
                        <strong>{{ $driver->name ?? 'Conducteur' }}</strong>
                        @if ($driver?->is_approved)
                            <small class="is-verified"><i class="fa-solid fa-circle-check"></i> Vérifié</small>
                        @else
                            <small>Conducteur</small>
                        @endif
                    </span>
                </div>
            </div>

            <aside class="cvd-side">

                <div class="cvd-recap" data-cvd-recap>
                    <span class="cvd-recap-label">Récapitulatif</span>

                    {{-- Chaque sens se coche : le passager choisit l'aller, le retour ou les deux. --}}
                    @foreach ($legs as $key => $leg)
                        <label class="cvd-recap-leg">
                            <input type="checkbox" class="cvd-recap-check" checked
                                   data-cvd-leg="{{ $key }}"
                                   data-cvd-price="{{ $leg['total'] }}">
                            <span class="cvd-recap-box"><i class="fa-solid fa-check"></i></span>

                            <span class="cvd-recap-icon {{ $key === 'retour' ? 'is-return' : '' }}">
                                <i class="fa-solid {{ $key === 'retour' ? 'fa-arrow-left-long' : 'fa-arrow-right-long' }}"></i>
                            </span>

                            <span class="cvd-recap-leg-info">
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

                            <span class="cvd-recap-leg-price">
                                {{ number_format($leg['total'], 0, ',', ' ') }}€
                            </span>
                        </label>
                    @endforeach

                    <div class="cvd-recap-total">
                        <span>Total</span>
                        <strong data-cvd-total>{{ number_format($total, 0, ',', ' ') }}€</strong>
                    </div>

                    {{-- La destination reste la meme pour tous : un visiteur non
                         connecte voit la popin de connexion (data-auth-required,
                         gere par assets/js/auth.js) et arrive ici une fois
                         connecte, sens selectionnes compris. --}}
                    @php $bookUrl = $driver ? route('messages.show', $driver) : route('contact'); @endphp

                    <a href="{{ $bookUrl }}" class="cvd-recap-btn" data-cvd-book
                       data-cvd-href="{{ $bookUrl }}" data-auth-required>
                        Réserver maintenant
                    </a>

                    <p class="cvd-recap-hint" data-cvd-hint hidden>
                        Sélectionnez au moins un sens pour réserver.
                    </p>
                </div>

                <div class="cvd-vehicle">
                    <span class="cvd-vehicle-icon"><i class="fa-solid fa-car-side"></i></span>
                    <span class="cvd-vehicle-info">
                        <strong>
                            {{ $vehicle ? \Illuminate\Support\Str::title(trim($vehicle->marque . ' ' . $vehicle->modele)) : 'Véhicule non renseigné' }}
                        </strong>
                        <small>
                            @if ($vehicle)
                                {{ $vehicle->couleur ? ucfirst($vehicle->couleur) . ' · ' : '' }}
                                {{ $vehicle->places ? $vehicle->places . ' places' : $trip->nb_places . ' places proposées' }}
                            @else
                                {{ $trip->nb_places }} place{{ $trip->nb_places > 1 ? 's' : '' }} proposée{{ $trip->nb_places > 1 ? 's' : '' }}
                            @endif
                        </small>
                    </span>
                </div>

            </aside>
        </div>

        {{-- Ligne 2 : segments + infos voyage --}}
        <div class="cvd-row cvd-row--legs">

            <div class="cvd-legs">
                @if (count($legs) > 1)
                    <div class="cvd-tabs">
                        @foreach ($legs as $key => $leg)
                            <button type="button" class="cvd-tab {{ $loop->first ? 'is-active' : '' }}"
                                    data-cvd-tab="{{ $key }}">
                                {{ $leg['label'] }}
                            </button>
                        @endforeach
                    </div>
                @endif

                @foreach ($legs as $key => $leg)
                    <div class="cvd-leg {{ $loop->first ? 'is-active' : '' }}" data-cvd-panel="{{ $key }}">

                        <div class="cvd-leg-grid">
                            <div class="cvd-leg-main">
                                <h2 class="cvd-leg-title">
                                    {{ $leg['from'] }} <i class="fa-solid fa-arrow-right"></i> {{ $leg['to'] }}
                                </h2>

                                <p class="cvd-leg-meta">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ $leg['date']?->translatedFormat('D d M Y') ?? 'Date à confirmer' }}
                                    @if ($leg['duration'] > 0)
                                        · <i class="fa-regular fa-clock"></i>
                                        {{ intdiv($leg['duration'], 3600) }}h{{ str_pad((string) intdiv($leg['duration'] % 3600, 60), 2, '0', STR_PAD_LEFT) }}
                                    @endif
                                    @if ($leg['distance'] > 0)
                                        · <i class="fa-solid fa-road"></i>
                                        {{ number_format($leg['distance'] / 1000, 1, ',', ' ') }} km
                                    @endif
                                </p>

                                <div class="cvd-leg-stop">
                                    <span class="cvd-leg-time">{{ $leg['time'] ?: '--:--' }}</span>
                                    <span class="cvd-leg-dot is-start"></span>
                                    <span class="cvd-leg-place">
                                        <strong>{{ $leg['from'] }}</strong>
                                        <small>{{ $leg['address']['from'] }}</small>
                                    </span>
                                </div>

                                <div class="cvd-leg-rail"><span></span></div>

                                <div class="cvd-leg-stop">
                                    <span class="cvd-leg-time">
                                        {{ $leg['arrival'] ?: '--:--' }}
                                        @if ($leg['next_day'])
                                            <em>+1j</em>
                                        @endif
                                    </span>
                                    <span class="cvd-leg-dot is-end"></span>
                                    <span class="cvd-leg-place">
                                        <strong>{{ $leg['to'] }}</strong>
                                        <small>{{ $leg['address']['to'] }}</small>
                                    </span>
                                </div>
                            </div>

                            <div class="cvd-leg-pricing">
                                <span class="cvd-pricing-label">
                                    {{ count($leg['segments']) > 1 ? 'Escales et tarifs' : 'Tarif' }}
                                </span>

                                @forelse ($leg['segments'] as $segment)
                                    @php
                                        // Les trajets anterieurs au formulaire actuel n'ont pas
                                        // toujours les villes de leurs segments : on retombe alors
                                        // sur un libelle d'etape plutot que sur une fleche vide.
                                        $segFrom = \App\Models\Covoiturage::villeCourte($segment['from'] ?? '');
                                        $segTo   = \App\Models\Covoiturage::villeCourte($segment['to'] ?? '');
                                    @endphp
                                    <div class="cvd-price-row">
                                        <span>
                                            @if ($segFrom && $segTo)
                                                {{ $segFrom }} → {{ $segTo }}
                                            @else
                                                Étape {{ $loop->iteration }}
                                            @endif
                                        </span>
                                        <strong>{{ number_format((float) ($segment['price'] ?? 0), 0, ',', ' ') }}€</strong>
                                    </div>
                                @empty
                                    <div class="cvd-price-row">
                                        <span>Trajet complet</span>
                                        <strong>{{ number_format($leg['total'], 0, ',', ' ') }}€</strong>
                                    </div>
                                @endforelse

                                <div class="cvd-price-total">
                                    <span>Total {{ $leg['key'] === 'retour' ? 'retour' : 'aller' }}</span>
                                    <strong>{{ number_format($leg['total'], 2, ',', ' ') }}€</strong>
                                </div>

                                <div class="cvd-price-note">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span>
                                        Prix par place, fixé par le conducteur. Le paiement se règle
                                        directement avec lui, aucun frais n'est ajouté par Olten.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <aside class="cvd-infos">
                <span class="cvd-infos-label">Infos voyage</span>

                <div class="cvd-info">
                    <span class="cvd-info-icon is-blue"><i class="fa-solid fa-user-group"></i></span>
                    <span class="cvd-info-text">
                        <strong>{{ $trip->nb_places }} place{{ $trip->nb_places > 1 ? 's' : '' }}</strong>
                        <small>{{ $trip->statut === 'complet' ? 'Trajet complet' : 'Encore disponibles' }}</small>
                    </span>
                </div>

                @if ($mode)
                    <div class="cvd-info">
                        <span class="cvd-info-icon is-pink"><i class="fa-solid {{ $mode[0] }}"></i></span>
                        <span class="cvd-info-text">
                            <strong>{{ $mode[1] }}</strong>
                            <small>{{ $mode[2] }}</small>
                        </span>
                    </div>
                @endif

                <div class="cvd-info">
                    <span class="cvd-info-icon is-green">
                        <i class="fa-solid {{ $trip->retour ? 'fa-arrow-right-arrow-left' : 'fa-arrow-right-long' }}"></i>
                    </span>
                    <span class="cvd-info-text">
                        <strong>{{ $trip->retour ? 'Aller-retour' : 'Aller simple' }}</strong>
                        <small>
                            {{ $trip->retour && $trip->return_date
                                ? 'Retour le ' . $trip->return_date->translatedFormat('d F Y')
                                : 'Trajet sans retour prévu' }}
                        </small>
                    </span>
                </div>

                <div class="cvd-info">
                    <span class="cvd-info-icon is-orange">
                        <i class="fa-solid {{ $trip->booking_mode === 'instant' ? 'fa-bolt' : 'fa-hourglass-half' }}"></i>
                    </span>
                    <span class="cvd-info-text">
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

        {{-- Ligne 3 : aide + conditions --}}
        <div class="cvd-row cvd-row--help">

            <div class="cvd-help">
                <span class="cvd-help-icon"><i class="fa-regular fa-life-ring"></i></span>
                <h3 class="cvd-help-title">Besoin d'aide ?</h3>
                <p class="cvd-help-text">
                    Une question sur ce trajet, le point de rendez-vous ou le paiement ?
                    Notre équipe vous répond avant comme après votre voyage.
                </p>
                <a href="{{ route('contact') }}" class="cvd-help-btn">Contacter le support</a>
            </div>

            <div class="cvd-policy">
                <span class="cvd-policy-icon"><i class="fa-solid fa-shield-halved"></i></span>
                <div>
                    <h3 class="cvd-policy-title">Conditions et annulation</h3>
                    <p class="cvd-policy-text">
                        @if ($trip->message_conducteur)
                            « {{ $trip->message_conducteur }} »
                        @else
                            Prévenez le conducteur au plus tôt en cas d'empêchement : une place libérée
                            à temps peut profiter à un autre passager. Les conditions d'annulation sont
                            convenues directement avec lui.
                        @endif
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // ---- Selection des sens : le total suit les cases cochees ----
        const checks = document.querySelectorAll('[data-cvd-leg]');
        const totalEl = document.querySelector('[data-cvd-total]');
        const bookBtn = document.querySelector('[data-cvd-book]');
        const hintEl = document.querySelector('[data-cvd-hint]');

        function euros(value) {
            return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(value) + '€';
        }

        function refreshTotal() {
            let total = 0;
            const selected = [];

            checks.forEach(function (check) {
                check.closest('.cvd-recap-leg')?.classList.toggle('is-off', !check.checked);

                if (!check.checked) return;

                total += parseFloat(check.dataset.cvdPrice || '0');
                selected.push(check.dataset.cvdLeg);
            });

            if (totalEl) totalEl.textContent = euros(total);

            if (bookBtn) {
                const base = bookBtn.dataset.cvdHref || '#';
                bookBtn.classList.toggle('is-disabled', selected.length === 0);

                bookBtn.href = selected.length
                    ? base + (base.includes('?') ? '&' : '?') + 'legs=' + selected.join(',')
                    : base;
            }

            if (hintEl) hintEl.hidden = selected.length > 0;
        }

        checks.forEach(function (check) {
            check.addEventListener('change', refreshTotal);
        });

        refreshTotal();

        // ---- Onglets aller / retour ----
        const tabs = document.querySelectorAll('[data-cvd-tab]');
        const panels = document.querySelectorAll('[data-cvd-panel]');

        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () {
                tabs.forEach(t => t.classList.remove('is-active'));
                panels.forEach(p => p.classList.remove('is-active'));
                tab.classList.add('is-active');
                document.querySelector('[data-cvd-panel="' + tab.dataset.cvdTab + '"]')?.classList.add('is-active');
                showLeg(index);
            });
        });

        // ---- Carte de l'itineraire ----
        const holder = document.getElementById('cvdMap');
        if (!holder || typeof L === 'undefined') return;

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

            L.polyline(leg.path, { color: '#ff3c00', weight: 4, opacity: .9 }).addTo(group);

            const start = leg.path[0];
            const end = leg.path[leg.path.length - 1];

            L.circleMarker(start, { radius: 7, color: '#ff3c00', fillColor: '#fff', fillOpacity: 1, weight: 3 }).addTo(group);
            L.circleMarker(end, { radius: 7, color: '#1f2328', fillColor: '#1f2328', fillOpacity: 1, weight: 3 }).addTo(group);

            return group;
        });

        function showLeg(index) {
            layers.forEach(layer => map.removeLayer(layer));

            const layer = layers[index];
            if (!layer) return;

            layer.addTo(map);

            const path = legs[index]?.path || [];
            if (path.length > 1) {
                map.fitBounds(L.latLngBounds(path), { padding: [30, 30] });
            }
        }

        const first = legs.findIndex(leg => (leg.path || []).length > 1);

        if (first === -1) {
            // Aucun trace exploitable : on centre sur la France plutot que sur l'ocean.
            map.setView([46.6, 2.4], 5);
        } else {
            showLeg(first);
        }

        setTimeout(() => map.invalidateSize(), 200);
    });
</script>

@endsection
