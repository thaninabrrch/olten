@extends('layouts.main')

@section('title', 'Réservation #' . $booking->id . ' - Olten.fr')

@php
    $trip     = $booking->trip;
    $legs     = collect($booking->legs);
    $status    = $booking->status;
    $isPaid    = $status === 'paid';
    // Validation manuelle : payée, la place est tenue, le conducteur doit accepter
    $isPending = $status === 'pending';
    $isLive    = $isPaid || $isPending;
    $isDriver = auth()->id() === $trip->conducteur_id;

    $departVille      = \App\Models\Covoiturage::villeCourte($trip->depart);
    $destinationVille = \App\Models\Covoiturage::villeCourte($trip->destination);

    // Coordonnées : la relation du trajet s'appelle « conducteur » (et non « driver »)
    $driverUser = $trip->conducteur;
    $passenger  = $booking->user;
    $fullName   = fn ($u) => $u ? trim(($u->firstname ?? '') . ' ' . ($u->lastname ?? '')) ?: '—' : '—';
    $driverName = $driverUser->firstname ?? 'le conducteur';

    // Personne à afficher dans la fiche : le passager pour le conducteur, le conducteur pour le passager
    $contact      = $isDriver ? $passenger : $driverUser;
    $contactRole  = $isDriver ? 'Passager' : 'Conducteur';
    $contactPhone = $isDriver ? $booking->phone : ($driverUser->phone ?? null);

    $avatarUrl = function ($u) {
        $p = $u->profile_photo ?? null;
        if (! $p) return null;
        return \Illuminate\Support\Str::startsWith($p, ['http://', 'https://', '/'])
            ? $p
            : asset('storage/' . ltrim($p, '/'));
    };
    $initials = fn ($u) => $u
        ? mb_strtoupper(mb_substr($u->firstname ?? '', 0, 1) . mb_substr($u->lastname ?? '', 0, 1)) ?: '?'
        : '?';
    $photo      = $contact ? $avatarUrl($contact) : null;
    $memberSince = $contact?->created_at?->translatedFormat('F Y');

    // Date et heure de chaque sens réservé
    $legInfo = fn (string $leg) => $leg === 'retour'
        ? ['label' => 'Retour', 'from' => $destinationVille, 'to' => $departVille, 'date' => $trip->return_date, 'time' => $trip->return_time]
        : ['label' => 'Aller',  'from' => $departVille, 'to' => $destinationVille, 'date' => $trip->date_depart, 'time' => $trip->heure_depart];

    $confirmMsg = $isPending
        ? 'Annuler votre demande ? Vous serez intégralement remboursé.'
        : 'Annuler votre réservation ? Vous serez intégralement remboursé.';
@endphp

@section('content')

<div class="ck">
    <div class="ck-wrap">

        @if (session('success'))
            <div class="ck-flash ck-flash--ok"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="ck-flash ck-flash--error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
        @endif

        {{-- Fil de progression : toutes les étapes sont passées --}}
        @if ($isLive)
            <ol class="ck-steps">
                <li class="is-done">
                    <span class="ck-step-dot"><i class="fa-solid fa-check"></i></span>
                    <span class="ck-step-text">
                        <strong>Trajet choisi</strong>
                        <small>{{ $legs->count() }} sens réservé{{ $legs->count() > 1 ? 's' : '' }}</small>
                    </span>
                </li>
                <li class="is-done">
                    <span class="ck-step-dot"><i class="fa-solid fa-check"></i></span>
                    <span class="ck-step-text">
                        <strong>Coordonnées &amp; paiement</strong>
                        <small>Paiement accepté</small>
                    </span>
                </li>
                <li class="{{ $isPaid ? 'is-done' : 'is-waiting' }}">
                    <span class="ck-step-dot"><i class="fa-solid {{ $isPaid ? 'fa-check' : 'fa-hourglass-half' }}"></i></span>
                    <span class="ck-step-text">
                        <strong>{{ $isPaid ? 'Confirmation' : 'Accord du conducteur' }}</strong>
                        <small>{{ $isPaid ? 'Conducteur prévenu' : 'En attente de sa réponse' }}</small>
                    </span>
                </li>
            </ol>
        @endif

        {{-- En-tête --}}
        <header class="ck-head">
            <div>
                @if ($isPaid)
                    <span class="ck-eyebrow"><i class="fa-solid fa-circle-check"></i> Paiement accepté</span>
                @elseif ($isPending)
                    <span class="ck-eyebrow ck-eyebrow--pending"><i class="fa-solid fa-hourglass-half"></i> Paiement accepté · en attente du conducteur</span>
                @else
                    <span class="ck-eyebrow ck-eyebrow--cancelled">
                        <i class="fa-solid fa-ban"></i>
                        {{ ['refused' => 'Demande refusée', 'expired' => 'Demande sans réponse'][$status] ?? 'Réservation annulée' }}
                    </span>
                @endif

                <h1 class="ck-title {{ $isLive ? '' : 'ck-title--cancelled' }}">
                    @if ($isPending)
                        Demande <em>envoyée</em>
                    @elseif ($status === 'refused')
                        Demande <em>refusée</em>
                    @elseif ($status === 'expired')
                        Demande <em>sans réponse</em>
                    @else
                        Réservation <em>{{ $isPaid ? 'confirmée' : 'annulée' }}</em>
                    @endif
                </h1>

                <p class="ck-lead">
                    @if ($isPending && $isDriver)
                        Ce passager a payé sa place et attend votre accord. Sans réponse de votre part au départ,
                        sa demande est annulée et il est remboursé.
                    @elseif ($isPending)
                        Votre paiement est accepté et votre demande envoyée au conducteur : vous serez prévenu dès
                        qu'il répond. S'il refuse, ou s'il ne répond pas avant le départ, vous êtes intégralement remboursé.
                    @elseif ($status === 'refused')
                        Le conducteur n'a pas accepté cette demande : le paiement est intégralement remboursé.
                    @elseif ($status === 'expired')
                        Le conducteur n'a pas répondu avant le départ : le paiement est intégralement remboursé.
                    @elseif (! $isPaid)
                        Cette réservation a été annulée et le paiement remboursé.
                    @elseif ($isDriver)
                        Un passager a réservé votre trajet. Vous pouvez le joindre au numéro indiqué ci-dessous.
                    @else
                        Votre paiement est accepté et le conducteur est prévenu.
                        Il vous joindra au numéro que vous avez renseigné.
                    @endif
                </p>
            </div>

            <div class="ck-illu">
                <svg viewBox="0 0 300 210" fill="none" xmlns="http://www.w3.org/2000/svg"
                     role="img" aria-label="Illustration : {{ $isPaid ? 'réservation confirmée' : ($isPending ? 'demande en attente' : 'réservation annulée') }}">
                    <circle cx="150" cy="105" r="92" fill="{{ $isLive ? '#ff3c00' : '#b42318' }}" fill-opacity="0.07"/>
                    <circle cx="150" cy="105" r="68" fill="{{ $isLive ? '#ff3c00' : '#b42318' }}" fill-opacity="0.06"/>

                    @if ($isLive)
                        <g class="ck-twinkle" fill="#ffb020">
                            <circle cx="34" cy="46" r="4"/>
                            <circle cx="268" cy="164" r="5"/>
                        </g>
                    @endif

                    {{-- Voiture --}}
                    <g class="{{ $isLive ? 'ck-float' : '' }}" @unless ($isLive) opacity=".55" @endunless>
                        <path d="M62 132 L62 112 Q62 104 70 102 L100 96 L120 74 Q124 70 130 70 L178 70 Q184 70 188 74 L206 96 L232 102 Q240 104 240 112 L240 132 Z" fill="{{ $isLive ? '#ff3c00' : '#8a9099' }}" fill-opacity=".92"/>
                        <path d="M112 96 L128 80 L150 80 L150 96 Z" fill="#ffffff" fill-opacity=".85"/>
                        <path d="M158 96 L158 80 L178 80 L194 96 Z" fill="#ffffff" fill-opacity=".85"/>
                        <rect x="62" y="124" width="178" height="14" rx="7" fill="{{ $isLive ? '#cf3200' : '#6b7280' }}"/>
                        <circle cx="102" cy="138" r="16" fill="#111111"/>
                        <circle cx="102" cy="138" r="7" fill="#e9ecef"/>
                        <circle cx="200" cy="138" r="16" fill="#111111"/>
                        <circle cx="200" cy="138" r="7" fill="#e9ecef"/>
                    </g>

                    {{-- Pastille de statut --}}
                    <g class="{{ $isLive ? 'ck-float-2' : '' }}">
                        <circle cx="228" cy="52" r="38" fill="#ffffff"/>
                        <circle cx="228" cy="52" r="30" fill="{{ $isPaid ? '#1a9d5c' : ($isPending ? '#f79009' : '#b42318') }}"/>
                        @if ($isPaid)
                            <path d="M214 53 l10 10 l19 -20" stroke="#ffffff" stroke-width="6" fill="none"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        @elseif ($isPending)
                            <path d="M228 37 v16 l10 7" stroke="#ffffff" stroke-width="6" fill="none"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        @else
                            <path d="M217 41 l22 22 M239 41 l-22 22" stroke="#ffffff" stroke-width="6" fill="none"
                                  stroke-linecap="round"/>
                        @endif
                    </g>

                    <ellipse cx="150" cy="180" rx="86" ry="10" fill="#111111" fill-opacity="0.05"/>
                </svg>
            </div>
        </header>

        <div class="ck-grid">

            {{-- ---------- Colonne gauche : détails ---------- --}}
            <div class="ck-col">

                {{-- Trajet --}}
                <section class="ck-card">
                    <h2 class="ck-card-title"><span><i class="fa-solid fa-route"></i></span> Votre trajet</h2>

                    <div class="ck-legs">
                        @foreach ($legs as $leg)
                            @php $info = $legInfo($leg); @endphp
                            <div class="ck-leg">
                                <span class="ck-leg-badge">
                                    <i class="fa-solid {{ $leg === 'retour' ? 'fa-arrow-left' : 'fa-arrow-right' }}"></i>
                                    {{ $info['label'] }}
                                </span>
                                <div class="ck-leg-body">
                                    <strong>{{ $info['from'] }} <i class="fa-solid fa-arrow-right"></i> {{ $info['to'] }}</strong>
                                    @if ($info['date'])
                                        <small>
                                            <i class="fa-regular fa-calendar"></i>
                                            {{ $info['date']->translatedFormat('d M Y') }}@if ($info['time']) · {{ $info['time'] }}@endif
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- Fiche contact --}}
                <section class="ck-card">
                    <h2 class="ck-card-title">
                        <span><i class="fa-solid {{ $isDriver ? 'fa-user' : 'fa-id-card' }}"></i></span>
                        {{ $isDriver ? 'Votre passager' : 'Votre conducteur' }}
                    </h2>

                    <div class="ck-profile">
                        <div class="ck-avatar">
                            <span class="ck-avatar-fallback">{{ $initials($contact) }}</span>
                            @if ($photo)
                                <img src="{{ $photo }}" alt="Photo de {{ $contact->firstname }}" loading="lazy"
                                     onerror="this.remove()">
                            @endif
                            @if ($contact?->verifie)
                                <i class="ck-avatar-check fa-solid fa-check" title="Profil vérifié"></i>
                            @endif
                        </div>

                        <div class="ck-profile-info">
                            <strong class="ck-profile-name">{{ $fullName($contact) }}</strong>
                            <span class="ck-profile-role">
                                <i class="fa-solid {{ $isDriver ? 'fa-user' : 'fa-car-side' }}"></i> {{ $contactRole }}
                            </span>
                            <ul class="ck-profile-meta">
                                @if ($contact?->verifie)
                                    <li class="is-ok"><i class="fa-solid fa-shield-halved"></i> Profil vérifié</li>
                                @endif
                                @if ($memberSince)
                                    <li><i class="fa-regular fa-calendar"></i> Membre depuis {{ $memberSince }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    @if (filled($contact?->about_me))
                        <p class="ck-profile-about">{{ \Illuminate\Support\Str::limit($contact->about_me, 220) }}</p>
                    @endif

                    @if ($isPaid && $contactPhone)
                        <div class="ck-contact">
                            <span class="ck-contact-ico"><i class="fa-solid fa-phone"></i></span>
                            <span class="ck-contact-text">
                                <small>Téléphone {{ $isDriver ? 'du passager' : 'du conducteur' }}</small>
                                <strong>{{ $contactPhone }}</strong>
                            </span>
                            <a href="tel:{{ $contactPhone }}" class="ck-contact-btn">
                                <i class="fa-solid fa-phone-volume"></i> Appeler
                            </a>
                        </div>
                    @endif

                    @unless ($isDriver)
                        <p class="ck-profile-note">
                            <i class="fa-solid fa-circle-info"></i>
                            Le conducteur vous joindra au {{ $booking->phone }}.
                        </p>
                    @endunless
                </section>
            </div>

            {{-- ---------- Colonne droite : récapitulatif ---------- --}}
            <aside class="ck-aside">
                <div class="ck-recap">

                    <div class="ck-recap-ad">
                        <span class="ck-recap-ad-ico"><i class="fa-solid fa-car-side"></i></span>
                        <span class="ck-recap-ad-info">
                            <strong>{{ $departVille }} → {{ $destinationVille }}</strong>
                            <small>
                                <i class="fa-solid fa-user"></i>
                                {{ $isDriver ? 'Vous êtes le conducteur' : 'Avec ' . $driverName }}
                            </small>
                        </span>
                    </div>

                    <div class="ck-recap-dates">
                        <span>
                            <small>Réservation</small>
                            <strong>#{{ $booking->id }}</strong>
                        </span>
                        <span class="ck-recap-dates-end">
                            <small>Statut</small>
                            <strong class="{{ $isPaid ? 'ck-ok' : ($isPending ? 'ck-wait' : 'ck-ko') }}">{{ \App\Models\TripBooking::STATUS[$status][0] ?? 'Annulée' }}</strong>
                        </span>
                    </div>

                    @if ($isDriver)
                        <div class="ck-line ck-line--total">
                            <span>Votre gain</span>
                            <span>{{ number_format($booking->driver_amount, 2, ',', ' ') }} €</span>
                        </div>
                    @else
                        <div class="ck-line">
                            <span>Prix du trajet · {{ $booking->seatsLabel() }}</span>
                            <span>{{ number_format($booking->driver_amount, 2, ',', ' ') }} €</span>
                        </div>
                        <div class="ck-line">
                            <span>Frais de service ({{ rtrim(rtrim(number_format($booking->commission_rate, 2, ',', ''), '0'), ',') }} %)</span>
                            <span>{{ number_format($booking->commission, 2, ',', ' ') }} €</span>
                        </div>
                        <div class="ck-line ck-line--total">
                            <span>{{ $isLive ? 'Total payé' : 'Total remboursé' }}</span>
                            <span>{{ number_format($booking->total_price, 2, ',', ' ') }} €</span>
                        </div>
                    @endif

                    {{-- Seul le passager annule sa réservation : le conducteur s'est engagé envers lui --}}
                    @if ($isLive && ! $isDriver)
                        <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                              onsubmit="return confirm('{{ $confirmMsg }}')">
                            @csrf
                            <button type="submit" class="ck-btn-danger">
                                <i class="fa-solid fa-ban"></i> {{ $isPending ? 'Annuler ma demande' : 'Annuler la réservation' }}
                            </button>
                        </form>

                        <p class="ck-recap-note">
                            <i class="fa-solid fa-circle-info"></i>
                            En cas d'annulation, vous êtes intégralement remboursé.
                        </p>
                    @elseif ($isPending)
                        {{-- Validation manuelle : le conducteur accepte ou refuse la demande --}}
                        <form method="POST" action="{{ route('bookings.approve', $booking) }}">
                            @csrf
                            <button type="submit" class="ck-btn-accept">
                                <i class="fa-solid fa-check"></i> Approuver la réservation
                            </button>
                        </form>
                        <form method="POST" action="{{ route('bookings.refuse', $booking) }}"
                              onsubmit="return confirm('Refuser cette demande ? Le passager sera intégralement remboursé.')">
                            @csrf
                            <button type="submit" class="ck-btn-danger">
                                <i class="fa-solid fa-xmark"></i> Refuser la demande
                            </button>
                        </form>
                    @elseif ($isPaid)
                        <p class="ck-recap-note">
                            <i class="fa-solid fa-lock"></i>
                            Vous vous êtes engagé auprès de ce passager : seul lui peut annuler sa réservation.
                        </p>
                    @endif
                </div>

                <div class="ck-mini">
                    <span><small>Places</small><strong>{{ $booking->seatsLabel(short: true) }}</strong></span>
                    <span>
                        <small>{{ $isDriver ? 'Gain' : ($isLive ? 'Total' : 'Remboursé') }}</small>
                        <strong>{{ number_format($isDriver ? $booking->driver_amount : $booking->total_price, 2, ',', ' ') }} €</strong>
                    </span>
                </div>
            </aside>

        </div>
    </div>
</div>

<style>
    /* ==========================================================================
       Tunnel de réservation d'un trajet — page de confirmation.
       Même charte que la page de paiement (préfixe .ck) : mêmes étapes,
       même en-tête illustré, même grille, même récapitulatif collé à droite.
       ========================================================================== */
    .ck {
        background:
            radial-gradient(720px 420px at 92% 4%, rgba(255, 60, 0, .08), transparent 62%),
            linear-gradient(180deg, #fffaf7 0%, var(--color-bg-body, #f8f9fa) 46%);
        padding: 32px 0 90px;
    }

    .ck-wrap {
        max-width: 1140px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* ---- Messages flash ---- */
    .ck-flash {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 18px;
        padding: 12px 15px;
        border-radius: 12px;
        font-size: 12.5px;
        line-height: 1.55;
    }

    .ck-flash--ok    { background: #eaf7f0; border: 1px solid #b7e4c7; color: #14794a; }
    .ck-flash--error { background: #fdeceb; border: 1px solid #f3c7c2; color: #b42318; }

    /* ---- Progression ---- */
    .ck-steps {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        list-style: none;
        margin: 0 0 28px;
        padding: 0;
    }

    .ck-steps li {
        flex: 1 1 220px;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        background: #fff;
        border: 1px solid var(--color-divider, #e9ecef);
        border-radius: 14px;
    }

    .ck-step-dot {
        flex: 0 0 30px;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f3f5;
        color: #9aa0a6;
        font-size: 12.5px;
        font-weight: 700;
    }

    .ck-steps li.is-done { border-color: #b7e4c7; }
    .ck-steps li.is-done .ck-step-dot { background: #2f9e5f; color: #fff; }
    .ck-steps li.is-waiting { border-color: #fedf89; }
    .ck-steps li.is-waiting .ck-step-dot { background: #f79009; color: #fff; }

    .ck-step-text strong { display: block; font-size: 13.5px; font-weight: 700; line-height: 1.3; }
    .ck-step-text small { font-size: 11.5px; color: #8a9099; }

    /* ---- En-tête ---- */
    .ck-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 30px;
        margin-bottom: 26px;
    }

    .ck-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 999px;
        background: rgba(26, 157, 92, .12);
        color: #14794a;
        font-size: .73rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        margin-bottom: 14px;
    }

    .ck-eyebrow--cancelled { background: rgba(180, 35, 24, .1); color: #b42318; }
    .ck-eyebrow--pending { background: #fef0c7; color: #b54708; }

    .ck-title {
        font-size: clamp(1.6rem, 3vw, 2.3rem);
        font-weight: 800;
        letter-spacing: -.02em;
        line-height: 1.16;
        margin: 0 0 12px;
    }

    .ck-title em { font-style: normal; color: var(--color-primary, #ff3c00); }
    .ck-title--cancelled em { color: #b42318; }

    .ck-lead {
        font-size: .98rem;
        line-height: 1.7;
        color: var(--color-grey-dark, #555);
        margin: 0;
        max-width: 560px;
    }

    .ck-illu { flex: 0 0 300px; }
    .ck-illu svg { width: 100%; height: auto; }

    .ck-float   { animation: ck-float 5.2s ease-in-out infinite; transform-box: view-box; transform-origin: center; }
    .ck-float-2 { animation: ck-float 6.4s ease-in-out infinite .6s; transform-box: view-box; transform-origin: center; }
    .ck-twinkle { animation: ck-twinkle 3.2s ease-in-out infinite; }

    @keyframes ck-float {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-9px); }
    }

    @keyframes ck-twinkle {
        0%, 100% { opacity: .25; }
        50%      { opacity: 1; }
    }

    @media (prefers-reduced-motion: reduce) {
        .ck-float, .ck-float-2, .ck-twinkle { animation: none; }
    }

    /* ---- Grille ---- */
    .ck-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) minmax(0, 1fr);
        gap: 20px;
        align-items: start;
    }

    .ck-col { display: flex; flex-direction: column; gap: 20px; min-width: 0; }

    .ck-card {
        background: #fff;
        border: 1px solid var(--color-divider, #e9ecef);
        border-radius: 18px;
        padding: 22px;
    }

    .ck-card-title {
        display: flex;
        align-items: center;
        gap: 11px;
        font-size: 1rem;
        font-weight: 700;
        margin: 0 0 18px;
    }

    .ck-card-title span {
        width: 26px;
        height: 26px;
        flex: 0 0 26px;
        border-radius: 9px;
        background: rgba(255, 60, 0, .1);
        color: var(--color-primary, #ff3c00);
        font-size: 12.5px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* ---- Sens réservés ---- */
    .ck-legs { display: flex; flex-direction: column; gap: 10px; }

    .ck-leg {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        border: 1px solid var(--color-divider, #e9ecef);
        border-radius: 14px;
        background: var(--color-grey-light, #f6f7f9);
    }

    .ck-leg-badge {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255, 60, 0, .1);
        color: var(--color-primary, #ff3c00);
        font-size: 11.5px;
        font-weight: 700;
    }

    .ck-leg-badge i { font-size: 10px; }

    .ck-leg-body { min-width: 0; }

    .ck-leg-body strong {
        display: block;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.35;
    }

    .ck-leg-body strong i { color: #9aa0a6; font-size: 10.5px; margin: 0 3px; }

    .ck-leg-body small {
        display: block;
        margin-top: 3px;
        font-size: 12px;
        color: #8a9099;
    }

    .ck-leg-body small i { color: var(--color-primary, #ff3c00); margin-right: 4px; }

    /* ---- Fiche profil ---- */
    .ck-profile { display: flex; align-items: center; gap: 16px; }

    .ck-avatar {
        position: relative;
        flex: 0 0 76px;
        width: 76px;
        height: 76px;
        border-radius: 50%;
        box-shadow: 0 0 0 4px #fff, 0 0 0 5px var(--color-divider, #e9ecef), 0 8px 20px rgba(17, 17, 17, .08);
    }

    .ck-avatar-fallback,
    .ck-avatar img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border-radius: 50%;
    }

    .ck-avatar-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #ff3c00, #ff8a4c);
        color: #fff;
        font-size: 24px;
        font-weight: 800;
        letter-spacing: .02em;
    }

    .ck-avatar img { object-fit: cover; }

    .ck-avatar-check {
        position: absolute;
        right: -2px;
        bottom: -2px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #2f9e5f;
        color: #fff;
        font-size: 11px;
        border: 3px solid #fff;
        box-sizing: content-box;
        width: 18px;
        height: 18px;
    }

    .ck-profile-info { min-width: 0; }

    .ck-profile-name {
        display: block;
        font-size: 1.15rem;
        font-weight: 800;
        letter-spacing: -.01em;
        line-height: 1.25;
    }

    .ck-profile-role {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 6px;
        padding: 4px 11px;
        border-radius: 999px;
        background: rgba(255, 60, 0, .1);
        color: var(--color-primary, #ff3c00);
        font-size: 11.5px;
        font-weight: 700;
    }

    .ck-profile-role i { font-size: 10px; }

    .ck-profile-meta {
        list-style: none;
        display: flex;
        flex-wrap: wrap;
        gap: 6px 14px;
        margin: 10px 0 0;
        padding: 0;
    }

    .ck-profile-meta li { font-size: 12px; color: #8a9099; }
    .ck-profile-meta li i { margin-right: 5px; font-size: 11px; }
    .ck-profile-meta li.is-ok { color: #14794a; font-weight: 600; }

    .ck-profile-about {
        margin: 16px 0 0;
        padding: 14px 16px;
        border-radius: 12px;
        background: var(--color-grey-light, #f6f7f9);
        font-size: 13px;
        line-height: 1.65;
        color: var(--color-grey-dark, #555);
    }

    .ck-contact {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 16px;
        padding: 12px 14px;
        border: 1px solid var(--color-divider, #e9ecef);
        border-radius: 14px;
    }

    .ck-contact-ico {
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 60, 0, .1);
        color: var(--color-primary, #ff3c00);
        font-size: 14px;
    }

    .ck-contact-text { flex: 1; min-width: 0; }
    .ck-contact-text small { display: block; font-size: 11px; color: #8a9099; }
    .ck-contact-text strong { font-size: 14.5px; font-weight: 700; }

    .ck-contact-btn {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 11px;
        background: var(--color-primary, #ff3c00);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 8px 18px rgba(255, 60, 0, .22);
        transition: background .2s ease, transform .15s ease;
    }

    .ck-contact-btn:hover { background: var(--color-primary-dark, #e13800); color: #fff; transform: translateY(-1px); }

    .ck-profile-note {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin: 14px 0 0;
        font-size: 11.5px;
        line-height: 1.6;
        color: #8a9099;
    }

    .ck-profile-note i { color: var(--color-primary, #ff3c00); margin-top: 2px; }

    /* ---- Récapitulatif ---- */
    .ck-aside { position: sticky; top: 20px; display: flex; flex-direction: column; gap: 14px; }

    .ck-recap {
        background: #fff;
        border: 1px solid var(--color-divider, #e9ecef);
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 12px 32px rgba(17, 17, 17, .06);
    }

    .ck-recap-ad {
        display: flex;
        gap: 12px;
        align-items: center;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--color-divider, #e9ecef);
        margin-bottom: 14px;
    }

    /* Pas de photo pour un trajet : pastille icône à la place de l'image produit */
    .ck-recap-ad-ico {
        width: 66px;
        height: 52px;
        flex: 0 0 66px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 60, 0, .1);
        color: var(--color-primary, #ff3c00);
        font-size: 20px;
    }

    .ck-recap-ad-info { min-width: 0; }

    .ck-recap-ad-info strong {
        display: block;
        font-size: 13.5px;
        font-weight: 700;
        line-height: 1.35;
    }

    .ck-recap-ad-info small {
        display: block;
        font-size: 11px;
        color: #8a9099;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ck-recap-dates {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 14px;
        border-radius: 12px;
        background: var(--color-grey-light, #f6f7f9);
        margin-bottom: 16px;
    }

    .ck-recap-dates small { display: block; font-size: 10.5px; color: #8a9099; text-transform: uppercase; letter-spacing: .05em; }
    .ck-recap-dates strong { font-size: 13px; font-weight: 700; }
    .ck-recap-dates-end { text-align: right; }

    .ck-ok { color: #14794a; }
    .ck-ko { color: #b42318; }
    .ck-wait { color: #b54708; }

    .ck-line {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 9px 0;
        font-size: 13px;
        color: var(--color-grey-dark, #555);
    }

    .ck-line span:first-child { min-width: 0; }
    .ck-line span:last-child  { flex: 0 0 auto; white-space: nowrap; }

    .ck-line--total {
        margin-top: 6px;
        padding-top: 14px;
        border-top: 1px solid var(--color-divider, #e9ecef);
        font-size: 15px;
        font-weight: 800;
        color: var(--color-black, #111);
    }

    .ck-line--total span:last-child { font-size: 21px; }

    .ck-card .ck-line + .ck-line { border-top: 1px solid var(--color-divider, #e9ecef); }

    /* ---- Boutons ---- */
    .ck-btn-ghost {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        width: 100%;
        margin-top: 14px;
        padding: 13px 20px;
        border-radius: 13px;
        border: 1px solid rgba(255, 60, 0, .35);
        background: rgba(255, 60, 0, .06);
        color: var(--color-primary, #ff3c00);
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        transition: background .2s ease;
    }

    .ck-btn-ghost:hover { background: rgba(255, 60, 0, .12); color: var(--color-primary, #ff3c00); }

    .ck-btn-danger {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        width: 100%;
        margin-top: 16px;
        padding: 14px 20px;
        border-radius: 13px;
        border: 1px solid #f3c7c2;
        background: #fff;
        color: #b42318;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: background .2s ease;
    }

    .ck-btn-danger:hover { background: #fdeceb; }

    .ck-btn-accept {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        width: 100%;
        margin-top: 16px;
        padding: 14px 20px;
        border: 0;
        border-radius: 13px;
        background: #1a9d5c;
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: background .2s ease;
    }
    .ck-btn-accept:hover { background: #148a50; }

    .ck-recap-note {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin: 14px 0 0;
        font-size: 11.5px;
        line-height: 1.6;
        color: #8a9099;
    }

    .ck-recap-note i { color: var(--color-primary, #ff3c00); margin-top: 2px; }

    .ck-mini { display: flex; gap: 10px; }

    .ck-mini span {
        flex: 1;
        background: #fff;
        border: 1px solid var(--color-divider, #e9ecef);
        border-radius: 13px;
        padding: 12px 14px;
    }

    .ck-mini small { display: block; font-size: 10.5px; color: #8a9099; text-transform: uppercase; letter-spacing: .05em; }
    .ck-mini strong { font-size: 15px; font-weight: 800; }

    /* ---- Responsive ---- */
    @media (max-width: 991.98px) {
        .ck-grid { grid-template-columns: 1fr; }
        .ck-aside { position: static; }
        .ck-head { flex-direction: column-reverse; align-items: flex-start; gap: 16px; }
        .ck-illu { flex: none; width: 220px; align-self: center; }
    }

    @media (max-width: 575.98px) {
        .ck { padding: 22px 0 64px; }
        .ck-card, .ck-recap { padding: 17px; }
        .ck-steps li { flex: 1 1 100%; }
        .ck-leg { flex-direction: column; align-items: flex-start; gap: 10px; }
        .ck-profile { flex-direction: column; align-items: flex-start; }
        .ck-contact { flex-wrap: wrap; }
        .ck-contact-btn { width: 100%; justify-content: center; }
    }
</style>

@endsection