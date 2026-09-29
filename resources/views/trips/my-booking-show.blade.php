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
     |   - le paiement réel (prix, frais de service, total) remplace le
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
                            <div class="bkd-line">
                                <span>Prix du trajet</span>
                                <span>{{ number_format((float) $booking->driver_amount, 2, ',', ' ') }} €</span>
                            </div>
                            <div class="bkd-line">
                                <span>Frais de service ({{ $rate }} %)</span>
                                <span>{{ number_format((float) $booking->commission, 2, ',', ' ') }} €</span>
                            </div>
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
                                    <span>{{ number_format((float) ($segment['price'] ?? 0), 0, ',', ' ') }} €</span>
                                </div>
                            @empty
                                <div class="bkd-line">
                                    <span>Trajet complet</span>
                                    <span>{{ number_format($leg['total'], 0, ',', ' ') }} €</span>
                                </div>
                            @endforelse

                            <div class="bkd-total bkd-total--sm">
                                <span>Total {{ $key === 'retour' ? 'retour' : 'aller' }}</span>
                                <strong>{{ number_format($leg['total'], 2, ',', ' ') }} €</strong>
                            </div>

                            <p class="bkd-note">
                                <i class="fa-solid fa-circle-info"></i>
                                Prix par place fixé par le conducteur. Les frais de service
                                ({{ $rate }} %) sont ajoutés et réglés en ligne.
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

<style>
    /* ==========================================================================
       Détail d'une réservation — design autonome (préfixe .bkd)
       Même charte que le tunnel de réservation : orange Olten, cartes blanches,
       coins arrondis, récapitulatif collé à droite sur grand écran.
       ========================================================================== */
    .bkd {
        --bkd-primary: var(--color-primary, #ff3c00);
        --bkd-primary-dark: var(--color-primary-dark, #e13800);
        --bkd-line: var(--color-divider, #e9ecef);
        --bkd-soft: var(--color-grey-light, #f6f7f9);
        --bkd-muted: #8a9099;
        --bkd-text: #1f2328;
        --bkd-ok: #14794a;
        --bkd-danger: #b42318;

        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-top: 8px;
        color: var(--bkd-text);
    }

    .bkd-row { display: grid; gap: 20px; align-items: start; }
    .bkd-row--main    { grid-template-columns: minmax(0, 1.65fr) minmax(0, 1fr); }
    .bkd-row--legs    { grid-template-columns: minmax(0, 1.65fr) minmax(0, 1fr); }
    .bkd-row--contact { grid-template-columns: minmax(0, 1.65fr) minmax(0, 1fr); }

    .bkd-card {
        background: #fff;
        border: 1px solid var(--bkd-line);
        border-radius: 18px;
        padding: 22px;
        min-width: 0;
    }

    .bkd-label {
        display: block;
        margin-bottom: 14px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: var(--bkd-muted);
    }

    .bkd-note {
        display: flex;
        gap: 9px;
        margin: 14px 0 0;
        font-size: 11.5px;
        line-height: 1.6;
        color: var(--bkd-muted);
    }
    .bkd-note i { color: var(--bkd-primary); margin-top: 2px; }

    /* ---- Badge d'état (en-tête) ---- */
    .bkd-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
    }
    .bkd-status--upcoming  { background: rgba(26, 157, 92, .12); color: var(--bkd-ok); }
    .bkd-status--past      { background: #f1f3f5; color: #6b7280; }
    .bkd-status--cancelled { background: rgba(180, 35, 24, .1); color: var(--bkd-danger); }
    .bkd-status--pending   { background: #fef0c7; color: #b54708; }
    .bkd-status--refused,
    .bkd-status--expired   { background: rgba(180, 35, 24, .1); color: var(--bkd-danger); }

    /* ---- Carte ---- */
    .bkd-mapcard { position: relative; padding: 0; overflow: hidden; }

    .bkd-map { height: 420px; width: 100%; background: #eef1f4; position: relative; z-index: 0; }

    .bkd-mapcard-who {
        position: absolute;
        left: 16px;
        bottom: 16px;
        z-index: 500;
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 9px 16px 9px 9px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 10px 28px rgba(17, 17, 17, .16);
        backdrop-filter: blur(6px);
    }

    .bkd-mini-avatar { position: relative; flex: 0 0 38px; width: 38px; height: 38px; border-radius: 50%; }
    .bkd-mini-avatar span, .bkd-mini-avatar img { position: absolute; inset: 0; width: 100%; height: 100%; border-radius: 50%; }
    .bkd-mini-avatar span {
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #ff3c00, #ff8a4c);
        color: #fff; font-size: 13px; font-weight: 800;
    }
    .bkd-mini-avatar img { object-fit: cover; }

    .bkd-mapcard-who-info { display: flex; flex-direction: column; line-height: 1.25; }
    .bkd-mapcard-who-info strong { font-size: 13.5px; font-weight: 700; }
    .bkd-mapcard-who-info small { font-size: 11.5px; color: var(--bkd-muted); }
    .bkd-mapcard-who-info small.is-ok { color: var(--bkd-ok); font-weight: 600; }

    /* ---- Colonne de droite ---- */
    .bkd-side { display: flex; flex-direction: column; gap: 16px; position: sticky; top: 20px; }

    .bkd-recap { box-shadow: 0 12px 32px rgba(17, 17, 17, .06); }

    .bkd-rlegs { display: flex; flex-direction: column; gap: 10px; }

    .bkd-rleg {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 13px;
        border-radius: 13px;
        background: var(--bkd-soft);
    }

    .bkd-rleg-icon {
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 60, 0, .12);
        color: var(--bkd-primary);
        font-size: 13px;
    }
    .bkd-rleg-icon.is-return { background: rgba(31, 35, 40, .1); color: var(--bkd-text); }

    .bkd-rleg-info { display: flex; flex-direction: column; min-width: 0; }
    .bkd-rleg-info strong { font-size: 13.5px; font-weight: 700; }
    .bkd-rleg-info small { font-size: 12px; color: var(--bkd-muted); }

    .bkd-lines { margin-top: 16px; }

    .bkd-line {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 0;
        font-size: 13px;
        color: #555;
    }
    .bkd-line span:last-child { white-space: nowrap; font-weight: 600; color: var(--bkd-text); }

    .bkd-total {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        margin-top: 6px;
        padding-top: 14px;
        border-top: 1px solid var(--bkd-line);
        font-size: 15px;
        font-weight: 800;
    }
    .bkd-total strong { font-size: 23px; letter-spacing: -.01em; color: var(--bkd-primary); }
    .bkd-total--sm strong { font-size: 18px; }

    .bkd-cancel {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        width: 100%;
        margin-top: 18px;
        padding: 13px 20px;
        border-radius: 13px;
        border: 1px solid #f3c7c2;
        background: #fff;
        color: var(--bkd-danger);
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: background .2s ease;
    }
    .bkd-cancel:hover { background: #fdeceb; }

    .bkd-accept {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        width: 100%;
        margin-top: 18px;
        padding: 13px 20px;
        border: 0;
        border-radius: 13px;
        background: #1a9d5c;
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: background .2s ease;
    }
    .bkd-accept:hover { background: #148a50; }
    .bkd-accept + form .bkd-cancel { margin-top: 10px; }

    .bkd-vehicle { display: flex; align-items: center; gap: 14px; padding: 16px 18px; }
    .bkd-vehicle-icon {
        flex: 0 0 44px; width: 44px; height: 44px; border-radius: 13px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(255, 60, 0, .1); color: var(--bkd-primary); font-size: 18px;
    }
    .bkd-vehicle-info { display: flex; flex-direction: column; min-width: 0; }
    .bkd-vehicle-info strong { font-size: 14px; font-weight: 700; }
    .bkd-vehicle-info small { font-size: 12px; color: var(--bkd-muted); }

    /* ---- Sens : onglets, chronologie, tarifs ---- */
    .bkd-tabs {
        display: inline-flex;
        gap: 4px;
        margin-bottom: 20px;
        padding: 4px;
        border-radius: 13px;
        background: var(--bkd-soft);
    }

    .bkd-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: #6b7280;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: background .2s ease, color .2s ease, box-shadow .2s ease;
    }
    .bkd-tab:hover { color: var(--bkd-text); }
    .bkd-tab.is-active { background: #fff; color: var(--bkd-primary); box-shadow: 0 2px 8px rgba(17, 17, 17, .08); }

    .bkd-panel { display: none; grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr); gap: 24px; }
    .bkd-panel.is-active { display: grid; }

    .bkd-panel-title { margin: 0 0 12px; font-size: 1.15rem; font-weight: 800; letter-spacing: -.01em; }
    .bkd-panel-title i { color: #9aa0a6; font-size: 12px; margin: 0 5px; }

    .bkd-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 22px; }
    .bkd-chip {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 6px 12px; border-radius: 999px;
        background: var(--bkd-soft); color: #555;
        font-size: 12px; font-weight: 600;
    }
    .bkd-chip i { color: var(--bkd-primary); font-size: 11.5px; }

    .bkd-stop, .bkd-rail { display: grid; grid-template-columns: 58px 22px minmax(0, 1fr); column-gap: 10px; }
    .bkd-stop { align-items: start; }

    .bkd-time { text-align: right; font-size: 15px; font-weight: 800; line-height: 1.2; padding-top: 1px; }
    .bkd-time em { display: block; font-style: normal; font-size: 10.5px; font-weight: 700; color: var(--bkd-primary); }

    .bkd-dot { justify-self: center; width: 16px; height: 16px; margin-top: 2px; border-radius: 50%; box-sizing: border-box; }
    .bkd-dot.is-start { border: 4px solid var(--bkd-primary); background: #fff; }
    .bkd-dot.is-end   { border: 4px solid var(--bkd-text); background: var(--bkd-text); }

    .bkd-place { display: flex; flex-direction: column; min-width: 0; }
    .bkd-place strong { font-size: 14.5px; font-weight: 700; }
    .bkd-place small { font-size: 12px; line-height: 1.45; color: var(--bkd-muted); overflow-wrap: anywhere; }

    .bkd-rail span {
        grid-column: 2;
        justify-self: center;
        width: 2px;
        height: 42px;
        background: repeating-linear-gradient(to bottom, #cfd4da 0 5px, transparent 5px 9px);
    }

    .bkd-pricing { padding: 18px; border-radius: 14px; background: var(--bkd-soft); align-self: start; }
    .bkd-pricing .bkd-label { margin-bottom: 8px; }

    /* ---- Infos voyage ---- */
    .bkd-infos { display: flex; flex-direction: column; }
    .bkd-info { display: flex; align-items: center; gap: 13px; padding: 13px 0; }
    .bkd-info + .bkd-info { border-top: 1px solid var(--bkd-line); }

    .bkd-info-icon {
        flex: 0 0 40px; width: 40px; height: 40px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center; font-size: 15px;
    }
    .bkd-info-icon.is-blue   { background: #e8f1fd; color: #1a6fd6; }
    .bkd-info-icon.is-pink   { background: #fdeaf3; color: #d6338a; }
    .bkd-info-icon.is-green  { background: #e6f6ee; color: #1a9d5c; }
    .bkd-info-icon.is-orange { background: #fff0e6; color: var(--bkd-primary); }

    .bkd-info-text { display: flex; flex-direction: column; min-width: 0; }
    .bkd-info-text strong { font-size: 13.5px; font-weight: 700; }
    .bkd-info-text small { font-size: 12px; line-height: 1.45; color: var(--bkd-muted); }

    /* ---- Fiche contact ---- */
    .bkd-profile { display: flex; align-items: center; gap: 18px; }

    .bkd-avatar {
        position: relative;
        flex: 0 0 80px;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        box-shadow: 0 0 0 4px #fff, 0 0 0 5px var(--bkd-line), 0 10px 24px rgba(17, 17, 17, .1);
    }
    .bkd-avatar > span, .bkd-avatar > img { position: absolute; inset: 0; width: 100%; height: 100%; border-radius: 50%; }
    .bkd-avatar > span {
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #ff3c00, #ff8a4c);
        color: #fff; font-size: 26px; font-weight: 800;
    }
    .bkd-avatar > img { object-fit: cover; }
    .bkd-avatar-check {
        position: absolute; right: -2px; bottom: -2px;
        width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;
        border-radius: 50%; background: #2f9e5f; color: #fff; font-size: 10px;
        border: 3px solid #fff; box-sizing: content-box;
    }

    .bkd-profile-info { display: flex; flex-direction: column; align-items: flex-start; gap: 5px; min-width: 0; }
    .bkd-profile-info strong { font-size: 1.2rem; font-weight: 800; letter-spacing: -.01em; }
    .bkd-role {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 11px; border-radius: 999px;
        background: rgba(255, 60, 0, .1); color: var(--bkd-primary);
        font-size: 11.5px; font-weight: 700;
    }
    .bkd-role i { font-size: 10px; }
    .bkd-profile-info small { display: flex; flex-wrap: wrap; gap: 4px 14px; font-size: 12px; color: var(--bkd-muted); }
    .bkd-profile-info small i { margin-right: 5px; font-size: 11px; }
    .bkd-ok { color: var(--bkd-ok); font-weight: 600; }

    .bkd-about {
        margin: 18px 0 0; padding: 14px 16px; border-radius: 12px;
        background: var(--bkd-soft); font-size: 13px; line-height: 1.65; color: #555;
    }

    .bkd-phone {
        display: flex; align-items: center; gap: 12px;
        margin-top: 18px; padding: 12px 14px;
        border: 1px solid var(--bkd-line); border-radius: 14px;
    }
    .bkd-phone-ico {
        flex: 0 0 38px; width: 38px; height: 38px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(255, 60, 0, .1); color: var(--bkd-primary); font-size: 14px;
    }
    .bkd-phone-text { flex: 1; min-width: 0; }
    .bkd-phone-text small { display: block; font-size: 11px; color: var(--bkd-muted); }
    .bkd-phone-text strong { font-size: 14.5px; font-weight: 700; }

    .bkd-call {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 18px; border-radius: 11px;
        background: var(--bkd-primary); color: #fff;
        font-size: 13px; font-weight: 700; text-decoration: none;
        box-shadow: 0 8px 18px rgba(255, 60, 0, .22);
        transition: background .2s ease, transform .15s ease;
    }
    .bkd-call:hover { background: var(--bkd-primary-dark); color: #fff; transform: translateY(-1px); }

    /* ---- Message du conducteur ---- */
    .bkd-msg-head { display: flex; align-items: center; gap: 13px; margin-bottom: 16px; }
    .bkd-msg-icon {
        flex: 0 0 42px; width: 42px; height: 42px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(255, 60, 0, .1); color: var(--bkd-primary); font-size: 17px;
    }
    .bkd-msg-head h3 { margin: 0; font-size: 1rem; font-weight: 800; }
    .bkd-msg-head p { margin: 2px 0 0; font-size: 12px; color: var(--bkd-muted); }

    .bkd-msg-box {
        padding: 16px 18px;
        border-left: 3px solid var(--bkd-primary);
        border-radius: 0 12px 12px 0;
        background: var(--bkd-soft);
        font-size: 13.5px;
        line-height: 1.7;
        color: #444;
    }

    .bkd-msg-foot { display: flex; align-items: center; gap: 9px; margin-top: 14px; font-size: 12px; color: var(--bkd-muted); }
    .bkd-msg-foot a { color: var(--bkd-primary); font-weight: 600; text-decoration: none; }
    .bkd-msg-foot a:hover { text-decoration: underline; }
    .bkd-msg-dot { width: 8px; height: 8px; border-radius: 50%; background: #2f9e5f; box-shadow: 0 0 0 4px rgba(47, 158, 95, .16); }

    /* ---- Responsive ---- */
    @media (max-width: 991.98px) {
        .bkd-row--main, .bkd-row--legs, .bkd-row--contact { grid-template-columns: 1fr; }
        .bkd-side { position: static; }
        .bkd-map { height: 320px; }
        .bkd-panel.is-active { grid-template-columns: 1fr; gap: 18px; }
    }

    @media (max-width: 575.98px) {
        .bkd { gap: 16px; }
        .bkd-card { padding: 17px; }
        .bkd-mapcard { padding: 0; }
        .bkd-map { height: 260px; }
        .bkd-tabs { display: flex; }
        .bkd-tab { flex: 1; justify-content: center; }
        .bkd-stop, .bkd-rail { grid-template-columns: 48px 20px minmax(0, 1fr); }
        .bkd-profile { flex-direction: column; align-items: flex-start; }
        .bkd-phone { flex-wrap: wrap; }
        .bkd-call { width: 100%; justify-content: center; }
        .bkd-mapcard-who { left: 10px; bottom: 10px; }
    }
</style>

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