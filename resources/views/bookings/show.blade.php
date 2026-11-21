@extends('layouts.connected')
@section('title', ($booking->ad->title ?? 'Réservation') . ' - Suivi de réservation - Olten')

@php
    /*
     | Suivi d'une reservation d'annonce, en espace connecte.
     |
     | Une seule page pour trois cas :
     |   - location simple : dates, retrait sur place ;
     |   - location livree : une mission prise par un livreur de la plateforme
     |     (Delivery) se suit etape par etape, et se note a l'arrivee ;
     |   - offre de livraison (sans dates) : l'annonceur est le livreur, le
     |     client suit son trajet paye au kilometre.
     |
     | Ouverte au client comme a l'annonceur (BookingController::show) : le
     | contact affiche est toujours celui de l'autre partie, et seul
     | l'annonceur peut accepter ou refuser une demande en attente.
     |
     | Charte du detail d'un trajet reserve (classes bkd-, partagees dans
     | assets/css/booking-detail.css) : les deux suivis se ressemblent.
     */
    $ad       = $booking->ad;
    $client   = $booking->user;
    $owner    = $ad?->user;
    $isOwner  = $owner && $owner->id === auth()->id();
    $isOffer  = $booking->isDelivery();
    $mission  = $booking->delivery;
    $status   = $booking->booking_status;
    $accepted = in_array($status, ['confirmed', 'completed'], true);
    $refused  = $status === 'cancelled';
    $refunded = $booking->status === 'refunded';

    $start = $isOffer ? null : \Carbon\Carbon::parse($booking->start_date);
    $end   = $isOffer ? null : \Carbon\Carbon::parse($booking->end_date);
    // Meme decompte que les listes « Mes réservations » et « Réservations reçues »
    $days  = $isOffer ? 0 : max(1, $start->diffInDays($end));

    $ownerRole  = $isOffer ? 'Livreur' : 'Propriétaire';
    $clientRole = $isOffer ? 'Client' : 'Locataire';

    // L'autre partie : l'annonceur pour le client, le client pour l'annonceur
    $other      = $isOwner ? $client : $owner;
    $otherRole  = $isOwner ? $clientRole : $ownerRole;
    $otherName  = $other
        ? (trim(($other->firstname ?? '') . ' ' . ($other->lastname ?? '')) ?: ($other->name ?: $other->email))
        : 'Membre supprimé';
    $otherFirst = $other ? ($other->firstname ?: \Illuminate\Support\Str::before($otherName, ' ')) : '';
    $initials   = $other
        ? mb_strtoupper(mb_substr($other->firstname ?: ($other->name ?: '?'), 0, 1) . mb_substr($other->lastname ?? '', 0, 1))
        : '?';
    $otherPhoto = null;
    if ($other?->profile_photo) {
        $otherPhoto = \Illuminate\Support\Str::startsWith($other->profile_photo, ['http://', 'https://', '/'])
            ? $other->profile_photo
            : asset('storage/' . ltrim($other->profile_photo, '/'));
    }

    $image = $ad?->images->first()
        ? asset('storage/' . $ad->images->first()->path)
        : asset('assets/images/no-image.jpg');

    // Adresse geocodee (« 10, Rue de Rivoli, Paris, ..., France ») : la voie
    // en titre, le reste en dessous. Un numero seul n'est pas un titre.
    $splitAddress = function (?string $address): array {
        $parts = array_values(array_filter(array_map('trim', explode(',', (string) $address))));

        if ($parts === []) {
            return ['Adresse non renseignée', ''];
        }

        $head = ctype_digit($parts[0]) && isset($parts[1])
            ? $parts[0] . ' ' . $parts[1]
            : $parts[0];

        return [$head, implode(', ', array_slice($parts, ctype_digit($parts[0]) && isset($parts[1]) ? 2 : 1))];
    };

    $money = fn ($value) => number_format((float) $value, 2, ',', ' ') . ' €';
    $when  = fn ($date) => $date?->translatedFormat('d M Y à H:i');

    /* ---------------- Pastille d'etat (en-tete) ---------------- */
    $badges = [
        'pending'   => ['pending',   'fa-solid fa-hourglass-half', 'En attente de confirmation'],
        'confirmed' => ['upcoming',  'fa-solid fa-circle-check',   'Confirmée'],
        'completed' => ['past',      'fa-solid fa-flag-checkered', 'Terminée'],
        'cancelled' => ['cancelled', 'fa-solid fa-ban',            $refunded ? 'Refusée · remboursée' : 'Annulée'],
    ];
    [$badgeClass, $badgeIcon, $badgeLabel] = $badges[$status]
        ?? ['past', 'fa-solid fa-circle-info', ucfirst((string) $status)];

    /* ---------------- Etapes ----------------
     | Chaque etape dit si elle est franchie ; la premiere qui ne l'est pas
     | devient l'etape en cours, les suivantes restent a venir. `wait` est le
     | texte affiche tant qu'elle n'est pas franchie. */
    $ownerLower  = mb_strtolower($ownerRole);
    $courierName = null;

    $steps = [[
        'done'  => true,
        'icon'  => 'fa-solid fa-credit-card',
        'title' => 'Réservation payée',
        'text'  => 'Le ' . $when($booking->created_at) . ', par carte bancaire.',
    ]];

    if ($refused) {
        $steps[] = [
            'state' => 'failed',
            'icon'  => 'fa-solid fa-xmark',
            'title' => 'Demande refusée par le ' . $ownerLower,
            'text'  => $refunded ? 'Le paiement a été intégralement remboursé sur votre carte.' : 'La réservation a été annulée.',
        ];
    } else {
        $steps[] = [
            'done'      => $accepted,
            'icon'      => 'fa-solid fa-handshake',
            'title'     => 'Acceptée par le ' . $ownerLower,
            'text'      => $isOwner ? 'Vous avez confirmé cette réservation.' : 'Le ' . $ownerLower . ' a confirmé votre réservation.',
            'waitTitle' => $isOwner ? 'Votre réponse est attendue' : 'En attente de la réponse du ' . $ownerLower,
            'wait'      => $isOwner
                ? 'Acceptez ou refusez la demande : en cas de refus, le client est intégralement remboursé.'
                : 'Vous êtes prévenu par e-mail. En cas de refus, vous êtes intégralement remboursé.',
        ];

        if ($isOffer) {
            $steps[] = [
                'done'      => $status === 'completed',
                'icon'      => 'fa-solid fa-truck-fast',
                'title'     => 'Livraison effectuée',
                'text'      => 'Le colis est arrivé à destination.',
                'waitTitle' => 'Livraison',
                'wait'      => $accepted
                    ? ($isOwner ? "Contactez le client pour convenir de l'enlèvement du colis." : "Le livreur vous contacte pour organiser l'enlèvement du colis.")
                    : "Elle s'organise une fois la réservation acceptée.",
            ];
        } else {
            if ($booking->delivery_requested) {
                $courier = $mission?->deliveryPerson;
                $courierName = $courier ? (trim(($courier->firstname ?? '') . ' ' . ($courier->lastname ?? '')) ?: $courier->name) : null;

                $steps[] = [
                    'done'      => (bool) $mission,
                    'icon'      => 'fa-solid fa-user-check',
                    'title'     => 'Livreur trouvé',
                    'text'      => $courierName ? $courierName . ' achemine le bien.' : 'Un livreur de la plateforme achemine le bien.',
                    'waitTitle' => "Recherche d'un livreur",
                    'wait'      => $accepted
                        ? 'Les livreurs de la plateforme peuvent se proposer pour acheminer le bien.'
                        : 'Elle commence une fois la réservation acceptée.',
                ];
                $steps[] = [
                    'done'      => (bool) $mission?->picked_up_at,
                    'icon'      => 'fa-solid fa-box',
                    'title'     => 'Bien récupéré',
                    'text'      => 'Le ' . $when($mission?->picked_up_at) . '.',
                    'waitTitle' => 'Récupération du bien',
                    'wait'      => 'Le livreur passe le prendre chez le propriétaire.',
                ];
                $steps[] = [
                    'done'      => (bool) $mission?->delivered_at,
                    'icon'      => 'fa-solid fa-house-circle-check',
                    'title'     => 'Livré',
                    'text'      => 'Le ' . $when($mission?->delivered_at) . ', au ' . ($booking->delivery_address ?: 'lieu convenu') . '.',
                    'waitTitle' => 'Livraison',
                    'wait'      => 'À l\'adresse indiquée lors de la réservation.',
                ];
            }

            $steps[] = [
                'done'  => $accepted && $start->lte(today()),
                'icon'  => 'fa-regular fa-calendar',
                'title' => 'Début de la location',
                'text'  => ucfirst($start->translatedFormat('l d F Y')) . '.',
                'wait'  => ucfirst($start->translatedFormat('l d F Y')) . '.',
            ];
            $steps[] = [
                'done'  => $accepted && $end->lt(today()),
                'icon'  => 'fa-solid fa-flag-checkered',
                'title' => 'Fin de la location',
                'text'  => ucfirst($end->translatedFormat('l d F Y')) . '.',
                'wait'  => ucfirst($end->translatedFormat('l d F Y')) . '.',
            ];
        }
    }

    $hasCurrent = false;
    foreach ($steps as $i => $step) {
        if (isset($step['state'])) {
            continue;
        }

        if ($step['done']) {
            $steps[$i]['state'] = 'done';
            continue;
        }

        $steps[$i]['state'] = $hasCurrent ? 'upcoming' : 'current';
        $hasCurrent = true;
    }

    /* ---------------- Avis (location livree, cote client) ---------------- */
    $canRate = $mission
        && $mission->status === 'delivered'
        && $booking->user_id === auth()->id();
    $review  = $canRate ? $mission->reviews->firstWhere('user_id', auth()->id()) : null;
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        @if ($isOwner)
            <a href="{{ route('bookings.receivedBookings') }}">Réservations reçues</a>
        @else
            <a href="{{ route('bookings.myBookings') }}">Mes réservations</a>
        @endif
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Réservation #{{ $booking->id }}</span>
    </nav>

    @if (session('success'))
        <div class="sp-note"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="sp-note"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
    @endif

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">{{ $ad->title ?? 'Annonce supprimée' }}</h1>
            <p class="sp-subtitle">
                Réservation #{{ $booking->id }}
                · {{ $isOffer ? 'Livraison' : ($booking->delivery_requested ? 'Location avec livraison' : 'Location') }}
                · {{ $isOwner ? 'reçue' : 'réservée' }} le {{ $booking->created_at->translatedFormat('d M Y') }}
            </p>
        </div>

        <span class="bkd-status bkd-status--{{ $badgeClass }}">
            <i class="{{ $badgeIcon }}"></i> {{ $badgeLabel }}
        </span>
    </header>

    <div class="bkd">
        <div class="bkd-row bkd-row--main">

            {{-- ==================== Colonne principale ==================== --}}
            <div class="bkd-col">

                {{-- Etapes --}}
                <section class="bkd-card">
                    <span class="bkd-label">Où en est la réservation ?</span>

                    <ol class="bkd-steps">
                        @foreach ($steps as $step)
                            @php
                                $done  = $step['state'] === 'done' || $step['state'] === 'failed';
                                $icon  = $step['state'] === 'done' ? 'fa-solid fa-check' : $step['icon'];
                                $title = $done ? $step['title'] : ($step['waitTitle'] ?? $step['title']);
                                $text  = $done ? $step['text'] : ($step['wait'] ?? '');
                            @endphp
                            <li class="bkd-step is-{{ $step['state'] }}">
                                <span class="bkd-step-dot"><i class="{{ $icon }}"></i></span>
                                <span class="bkd-step-body">
                                    <strong>
                                        {{ $title }}
                                        @if ($step['state'] === 'current')
                                            <span class="bkd-step-now">En cours</span>
                                        @endif
                                    </strong>
                                    @if ($text)
                                        <small>{{ $text }}</small>
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </section>

                {{-- Trajet (offre de livraison) ou periode (location) --}}
                <section class="bkd-card">
                    @if ($isOffer)
                        @php
                            [$fromHead, $fromRest] = $splitAddress($booking->address);
                            [$toHead, $toRest]     = $splitAddress($booking->delivery_address);
                        @endphp

                        <span class="bkd-label">Trajet</span>

                        <div class="bkd-chips">
                            <span class="bkd-chip">
                                <i class="fa-solid fa-road"></i>
                                {{ number_format((float) $booking->delivery_distance_km, 1, ',', ' ') }} km par la route
                            </span>
                            @if ($ad)
                                <span class="bkd-chip">
                                    <i class="fa-solid fa-tag"></i>
                                    {{ number_format($ad->deliveryRate(), 2, ',', ' ') }} € / km
                                </span>
                            @endif
                        </div>

                        <div class="bkd-timeline">
                            <div class="bkd-stop">
                                <span class="bkd-time bkd-time--label">Retrait</span>
                                <span class="bkd-dot is-start"></span>
                                <span class="bkd-place">
                                    <strong>{{ $fromHead }}</strong>
                                    <small>{{ $fromRest }}</small>
                                </span>
                            </div>

                            <div class="bkd-rail"><span></span></div>

                            <div class="bkd-stop">
                                <span class="bkd-time bkd-time--label">Arrivée</span>
                                <span class="bkd-dot is-end"></span>
                                <span class="bkd-place">
                                    <strong>{{ $toHead }}</strong>
                                    <small>{{ $toRest }}</small>
                                </span>
                            </div>
                        </div>
                    @else
                        <span class="bkd-label">Période et lieu</span>

                        <div class="bkd-chips">
                            <span class="bkd-chip">
                                <i class="fa-regular fa-calendar"></i>
                                {{ $days }} jour{{ $days > 1 ? 's' : '' }}
                            </span>
                            @if ($ad)
                                <span class="bkd-chip">
                                    <i class="fa-solid fa-tag"></i>
                                    {{ $money($ad->price_per_day) }} / jour
                                </span>
                            @endif
                            <span class="bkd-chip">
                                <i class="fa-solid {{ $booking->delivery_requested ? 'fa-truck-fast' : 'fa-location-dot' }}"></i>
                                {{ $booking->delivery_requested ? 'Livraison demandée' : 'Retrait sur place' }}
                            </span>
                        </div>

                        <div class="bkd-timeline">
                            <div class="bkd-stop">
                                <span class="bkd-time">{{ $start->format('d') }}<em class="is-month">{{ $start->translatedFormat('M') }}</em></span>
                                <span class="bkd-dot is-start"></span>
                                <span class="bkd-place">
                                    <strong>Début de la location</strong>
                                    <small>
                                        @if ($booking->delivery_requested)
                                            Livré au {{ $booking->delivery_address ?: 'lieu indiqué à la réservation' }}
                                        @else
                                            Retrait : {{ $booking->address ?: ($ad->address ?? 'à convenir avec le propriétaire') }}
                                        @endif
                                    </small>
                                </span>
                            </div>

                            <div class="bkd-rail"><span></span></div>

                            <div class="bkd-stop">
                                <span class="bkd-time">{{ $end->format('d') }}<em class="is-month">{{ $end->translatedFormat('M') }}</em></span>
                                <span class="bkd-dot is-end"></span>
                                <span class="bkd-place">
                                    <strong>Fin de la location</strong>
                                    <small>{{ ucfirst($end->translatedFormat('l d F Y')) }}</small>
                                </span>
                            </div>
                        </div>
                    @endif
                </section>

                {{-- Avis : une fois la livraison effectuee, cote client --}}
                @if ($canRate)
                    <section class="bkd-card" data-bkd-rating
                             data-url="{{ route('delivery.rate', $mission) }}"
                             data-rating="{{ (int) ($review->rating ?? 0) }}">
                        <span class="bkd-label">Votre avis sur la livraison</span>

                        <p class="bkd-rating-lead">
                            Comment s'est passée la livraison{{ $courierName ? ' avec ' . $courierName : '' }} ?
                            Votre note aide les autres membres à choisir leur livreur.
                        </p>

                        <div class="bkd-stars" role="radiogroup" aria-label="Note de la livraison">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" class="bkd-star {{ $i <= ($review->rating ?? 0) ? 'is-on' : '' }}"
                                        data-value="{{ $i }}" role="radio"
                                        aria-checked="{{ (int) ($review->rating ?? 0) === $i ? 'true' : 'false' }}"
                                        aria-label="{{ $i }} étoile{{ $i > 1 ? 's' : '' }} sur 5">
                                    <i class="fa-solid fa-star"></i>
                                </button>
                            @endfor
                        </div>

                        <textarea class="bkd-textarea" data-bkd-comment maxlength="1000"
                                  placeholder="Ponctualité, soin du bien, échanges… (facultatif)">{{ $review->comment ?? '' }}</textarea>

                        <div class="bkd-rating-foot">
                            <span class="bkd-feedback" data-bkd-feedback aria-live="polite">
                                {{ $review ? 'Vous avez déjà donné votre avis : vous pouvez le modifier.' : '' }}
                            </span>
                            <button type="button" class="bkd-btn bkd-btn--primary" data-bkd-send>
                                <i class="fa-solid fa-paper-plane"></i>
                                {{ $review ? 'Mettre à jour mon avis' : 'Envoyer mon avis' }}
                            </button>
                        </div>
                    </section>
                @endif
            </div>

            {{-- ==================== Colonne de droite ==================== --}}
            <aside class="bkd-side">

                {{-- Recapitulatif --}}
                <div class="bkd-card bkd-recap">
                    <span class="bkd-label">{{ $isOwner ? 'La réservation' : 'Ma réservation' }}</span>

                    @if ($ad)
                        <a class="bkd-ad" href="{{ route('ads.show', $ad) }}">
                            <img src="{{ $image }}" alt="{{ $ad->title }}">
                            <span class="bkd-ad-info">
                                <strong>{{ $ad->title }}</strong>
                                <small>{{ $ad->category->nom ?? 'Annonce' }}</small>
                            </span>
                        </a>
                    @endif

                    <div class="bkd-lines">
                        @if ($isOffer)
                            <div class="bkd-line">
                                <span>Distance</span>
                                <span>{{ number_format((float) $booking->delivery_distance_km, 1, ',', ' ') }} km</span>
                            </div>
                        @else
                            <div class="bkd-line">
                                <span>Période</span>
                                <span>{{ $start->translatedFormat('d M') }} → {{ $end->translatedFormat('d M Y') }}</span>
                            </div>
                            @if ($booking->delivery_requested && (float) $booking->delivery_cost > 0)
                                <div class="bkd-line">
                                    <span>dont livraison</span>
                                    <span>{{ $money($booking->delivery_cost) }}</span>
                                </div>
                            @endif
                        @endif

                        <div class="bkd-line">
                            <span>Paiement</span>
                            <span>{{ $refunded ? 'Remboursé' : ($booking->status === 'paid' ? 'Payé par carte' : ucfirst((string) $booking->status)) }}</span>
                        </div>

                        <div class="bkd-total">
                            <span>{{ $refunded ? 'Total remboursé' : ($isOwner ? 'Montant' : 'Total payé') }}</span>
                            <strong>{{ $money($booking->total_price) }}</strong>
                        </div>
                    </div>

                    {{-- Demande en attente : seul l'annonceur y repond --}}
                    @if ($isOwner && $status === 'pending')
                        <form action="{{ route('bookings.accept', $booking) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="bkd-accept">
                                <i class="fa-solid fa-check"></i> Accepter la réservation
                            </button>
                        </form>
                        <form action="{{ route('bookings.reject', $booking) }}" method="POST"
                              data-sp-confirm
                              data-title="Refuser cette réservation ?"
                              data-text="Le client sera prévenu et intégralement remboursé."
                              data-confirm-label="Oui, refuser">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="bkd-cancel">
                                <i class="fa-solid fa-xmark"></i> Refuser la demande
                            </button>
                        </form>
                    @elseif ($ad)
                        <a href="{{ route('ads.show', $ad) }}" class="bkd-btn">
                            <i class="fa-regular fa-eye"></i> Voir l'annonce
                        </a>
                    @endif
                </div>

                {{-- Contact de l'autre partie --}}
                <div class="bkd-card bkd-contact-card">
                    <span class="bkd-label">{{ $isOwner ? 'Votre ' . mb_strtolower($clientRole) : 'Votre ' . $ownerLower }}</span>

                    <div class="bkd-person">
                        <span class="bkd-mini-avatar">
                            <span>{{ $initials }}</span>
                            @if ($otherPhoto)
                                <img src="{{ $otherPhoto }}" alt="{{ $otherName }}" onerror="this.remove()">
                            @endif
                        </span>
                        <span class="bkd-person-info">
                            <strong>{{ $otherName }}</strong>
                            <small>
                                {{ $otherRole }}
                                @if ($other?->created_at)
                                    · membre depuis {{ $other->created_at->translatedFormat('F Y') }}
                                @endif
                            </small>
                        </span>
                    </div>

                    {{-- Coordonnees echangees une fois la reservation acceptee --}}
                    @if ($accepted && $other?->phone)
                        <div class="bkd-phone">
                            <span class="bkd-phone-ico"><i class="fa-solid fa-phone"></i></span>
                            <span class="bkd-phone-text">
                                <small>Téléphone</small>
                                <strong>{{ $other->phone }}</strong>
                            </span>
                            <a href="tel:{{ $other->phone }}" class="bkd-call">
                                <i class="fa-solid fa-phone-volume"></i> Appeler
                            </a>
                        </div>
                    @endif

                    @if ($other)
                        <a href="{{ route('messages', ['avec' => $other->id]) }}" class="bkd-btn">
                            <i class="fa-regular fa-comment"></i> Écrire à {{ $otherFirst }}
                        </a>
                    @endif

                    <div class="bkd-msg-foot">
                        <span class="bkd-msg-dot"></span>
                        <span>Un souci ? <a href="{{ route('contact') }}">Contacter le support</a></span>
                    </div>
                </div>
            </aside>

        </div>
    </div>
</div>

@include('partials.confirm-script')
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/booking-detail.css') }}?v={{ @filemtime(public_path('assets/css/booking-detail.css')) ?: 1 }}">
@endpush

@if ($canRate)
@push('scripts')
<script>
    // Avis sur la livraison : note de 1 a 5 et commentaire, envoyes ensemble
    // (DeliveryAdController::rateDelivery).
    document.addEventListener('DOMContentLoaded', () => {
        const box = document.querySelector('[data-bkd-rating]');
        if (!box) return;

        const stars    = box.querySelectorAll('.bkd-star');
        const comment  = box.querySelector('[data-bkd-comment]');
        const feedback = box.querySelector('[data-bkd-feedback]');
        const send     = box.querySelector('[data-bkd-send]');
        const csrf     = document.querySelector('meta[name="csrf-token"]')?.content;
        let rating     = Number(box.dataset.rating) || 0;

        const paint = (value) => stars.forEach((star) => {
            const v = Number(star.dataset.value);
            star.classList.toggle('is-on', v <= value);
            star.setAttribute('aria-checked', v === rating ? 'true' : 'false');
        });

        const say = (text, kind = '') => {
            feedback.textContent = text;
            feedback.className = 'bkd-feedback' + (kind ? ' is-' + kind : '');
        };

        stars.forEach((star) => {
            star.addEventListener('click', () => {
                rating = Number(star.dataset.value);
                paint(rating);
                say('');
            });
            star.addEventListener('mouseenter', () => paint(Number(star.dataset.value)));
        });

        box.querySelector('.bkd-stars').addEventListener('mouseleave', () => paint(rating));

        send.addEventListener('click', async () => {
            if (!rating) {
                say('Choisissez une note de 1 à 5 étoiles.', 'error');
                return;
            }

            send.disabled = true;

            try {
                const response = await fetch(box.dataset.url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({ rating, comment: comment.value.trim() })
                });

                const data = await response.json().catch(() => ({}));

                if (response.ok && data.success) {
                    say('Merci ! Votre avis est enregistré.', 'ok');
                    send.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Mettre à jour mon avis';
                } else {
                    say(data.message || "Votre avis n'a pas pu être enregistré.", 'error');
                }
            } catch (e) {
                say("Votre avis n'a pas pu être enregistré. Réessayez.", 'error');
            }

            send.disabled = false;
        });
    });
</script>
@endpush
@endif
