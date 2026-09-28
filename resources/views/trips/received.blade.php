@extends('layouts.connected')
@section('title', 'Réservations reçues - Olten')

@php
    /*
     | Côté conducteur : les réservations reçues sur ses trajets, une carte
     | par trajet avec la liste de ses passagers. Même structure que « Mes
     | trajets réservés » (sp-page, sp-panel, sp-tabs) : seul le contenu des
     | cartes change.
     |
     |   - À venir : au moins un sens du trajet reste à faire
     |   - Passés  : tous les sens du trajet sont derrière soi
     |
     | Une réservation annulée reste affichée, barrée : sa place est libérée
     | et le passager a été remboursé.
     */
    $tabs = [
        'a-venir' => 'À venir',
        'passes'  => 'Passés',
    ];

    $titles = [
        'a-venir' => 'Trajets à venir',
        'passes'  => 'Trajets passés',
    ];

    $avatarUrl = function ($user) {
        $photo = $user->profile_photo ?? null;
        if (! $photo) return null;

        return \Illuminate\Support\Str::startsWith($photo, ['http://', 'https://', '/'])
            ? $photo
            : asset('storage/' . ltrim($photo, '/'));
    };

    $fullName = fn ($user) => $user
        ? (trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')) ?: ($user->name ?? 'Passager'))
        : 'Passager supprimé';

    $euros = fn ($amount) => number_format((float) $amount, 2, ',', ' ') . ' €';
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('covoiturage.index') }}">Mes trajets</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Réservations reçues</span>
    </nav>

    {{-- En-tête --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Réservations reçues</h1>
            <p class="sp-subtitle">Les passagers de vos trajets : places réservées, sens choisis et gains.</p>
        </div>

        <a href="{{ route('covoiturage.index') }}" class="sp-btn-primary">
            Mes trajets
        </a>
    </header>

    {{-- Indicateurs --}}
    <div class="sp-stats">
        <div class="sp-stat">
            <span class="sp-stat-icon is-brand"><i class="fa-solid fa-car-side"></i></span>
            <div>
                <span class="sp-stat-value">{{ $counts['a-venir'] }}</span>
                <span class="sp-stat-label">Trajet{{ $counts['a-venir'] > 1 ? 's' : '' }} réservé{{ $counts['a-venir'] > 1 ? 's' : '' }} <small>à venir</small></span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-blue"><i class="fa-solid fa-user-group"></i></span>
            <div>
                <span class="sp-stat-value">{{ $passengers }}</span>
                <span class="sp-stat-label">Réservation{{ $passengers > 1 ? 's' : '' }} <small>à venir</small></span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-green"><i class="fa-solid fa-chair"></i></span>
            <div>
                <span class="sp-stat-value">{{ $seats }}</span>
                <span class="sp-stat-label">Place{{ $seats > 1 ? 's' : '' }} réservée{{ $seats > 1 ? 's' : '' }} <small>à venir</small></span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-red"><i class="fa-solid fa-euro-sign"></i></span>
            <div>
                <span class="sp-stat-value">{{ $euros($earnings) }}</span>
                <span class="sp-stat-label">Vos gains <small>hors frais de service</small></span>
            </div>
        </div>
    </div>

    {{-- Panneau --}}
    <section class="sp-panel">

        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">{{ $titles[$tab] }}</h2>
                <span class="sp-count">
                    {{ $list->count() }} trajet{{ $list->count() > 1 ? 's' : '' }} avec réservation
                </span>
            </div>
        </div>

        {{-- Onglets --}}
        <div class="sp-tabs">
            @foreach ($tabs as $value => $label)
                <a href="{{ route('trips.received', ['onglet' => $value]) }}"
                   class="sp-tab {{ $tab === $value ? 'is-active' : '' }}">
                    {{ $label }}
                    <span class="sp-tab-count">{{ $counts[$value] }}</span>
                </a>
            @endforeach
        </div>

        @if ($list->count())
            <div class="rtr-list">
                @foreach ($list as $item)
                    @php
                        $trip     = $item['trip'];
                        $upcoming = $item['state'] === 'upcoming';
                    @endphp

                    <article class="rtr-trip" id="trajet-{{ $trip->covoiturage_id }}">

                        {{-- Le trajet --}}
                        <header class="rtr-trip-head">
                            <a href="{{ route('trajet.show', ['covoiturage' => $trip->covoiturage_id]) }}" class="rtr-trip-img">
                                <img src="{{ $item['image'] }}" alt="{{ $item['from'] }} - {{ $item['to'] }}" loading="lazy">
                            </a>

                            <div class="rtr-trip-main">
                                <span class="sp-chip">
                                    <i class="fa-solid {{ $trip->retour ? 'fa-arrow-right-arrow-left' : 'fa-arrow-right-long' }}"></i>
                                    {{ $trip->retour ? 'Aller-retour' : 'Aller simple' }} · Trajet #{{ $trip->covoiturage_id }}
                                </span>

                                <a href="{{ route('trajet.show', ['covoiturage' => $trip->covoiturage_id]) }}" class="rtr-trip-name">
                                    {{ $item['from'] }} → {{ $item['to'] }}
                                </a>

                                <div class="rtr-trip-dates">
                                    @foreach ($item['legs'] as $leg)
                                        @if ($leg['date'])
                                            <span class="sp-tag">
                                                <i class="fa-regular fa-calendar"></i>
                                                {{ $leg['label'] }} · {{ $leg['date']->translatedFormat('D d M Y') }}@if ($leg['time']) · {{ $leg['time'] }}@endif
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            {{-- Remplissage de chaque sens et gains du trajet --}}
                            <div class="rtr-trip-figures">
                                @foreach ($item['legs'] as $leg)
                                    @php
                                        $total   = max(1, (int) $trip->nb_places);
                                        $left    = max(0, (int) $trip->nb_places - $leg['booked']);
                                        $percent = min(100, round($leg['booked'] / $total * 100));
                                    @endphp
                                    <div class="rtr-fill">
                                        <span class="rtr-fill-label">
                                            {{ $leg['label'] }} · {{ $leg['booked'] }} réservée{{ $leg['booked'] > 1 ? 's' : '' }}
                                            <strong>
                                                {{ $left > 0 ? $left . ' restante' . ($left > 1 ? 's' : '') : 'Complet' }}
                                            </strong>
                                        </span>
                                        <span class="rtr-fill-bar" aria-hidden="true">
                                            <span style="width: {{ $percent }}%"></span>
                                        </span>
                                    </div>
                                @endforeach

                                <div class="rtr-earn">
                                    <small>Vos gains</small>
                                    <strong>{{ $euros($item['earnings']) }}</strong>
                                </div>
                            </div>
                        </header>

                        {{-- Les passagers --}}
                        <ul class="rtr-rows">
                            @foreach ($item['bookings'] as $booking)
                                @php
                                    $passenger = $booking->passenger;
                                    $paid      = $booking->status === 'paid';
                                    $photo     = $passenger ? $avatarUrl($passenger) : null;
                                    $name      = $fullName($passenger);
                                    $initial   = mb_strtoupper(mb_substr($name, 0, 1));
                                    $legs      = collect($booking->legs)->map(fn ($l) => $l === 'retour' ? 'Retour' : 'Aller');
                                    $seatCount = (int) $booking->seats;
                                @endphp

                                <li class="rtr-row {{ $paid ? '' : 'is-cancelled' }}">
                                    <span class="rtr-avatar">
                                        <span>{{ $initial }}</span>
                                        @if ($photo)
                                            <img src="{{ $photo }}" alt="Photo de {{ $name }}" loading="lazy" onerror="this.remove()">
                                        @endif
                                    </span>

                                    <span class="rtr-who">
                                        <strong>{{ $name }}</strong>
                                        <small>
                                            Réservation #{{ $booking->id }} · le {{ $booking->created_at?->translatedFormat('d M Y') }}
                                        </small>
                                    </span>

                                    <span class="rtr-cell rtr-cell--seats">
                                        <small>Places</small>
                                        <strong>{{ $seatCount }}</strong>
                                    </span>

                                    <span class="rtr-cell">
                                        <small>Sens</small>
                                        <strong>{{ $legs->count() > 1 ? 'Aller & retour' : $legs->first() }}</strong>
                                    </span>

                                    <span class="rtr-cell rtr-cell--phone">
                                        <small>Téléphone</small>
                                        @if ($paid && $upcoming && $booking->phone)
                                            <a href="tel:{{ $booking->phone }}"><i class="fa-solid fa-phone"></i> {{ $booking->phone }}</a>
                                        @else
                                            <strong>—</strong>
                                        @endif
                                    </span>

                                    <span class="rtr-cell rtr-cell--amount">
                                        <small>{{ $paid ? 'Votre gain' : 'Remboursé' }}</small>
                                        <strong>{{ $euros($paid ? $booking->driver_amount : $booking->total_price) }}</strong>
                                    </span>

                                    <span class="rtr-state {{ $paid ? 'is-paid' : 'is-cancelled' }}">
                                        <i class="fa-solid {{ $paid ? 'fa-circle-check' : 'fa-ban' }}"></i>
                                        {{ $paid ? 'Confirmée' : 'Annulée' }}
                                    </span>

                                    <a href="{{ route('trips.myBookings.show', $booking) }}" class="sp-act rtr-open"
                                       aria-label="Voir la réservation de {{ $name }}">
                                        Détail
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        @else
            <div class="sp-empty">
                @if ($tab === 'a-venir')
                    <x-empty-state
                        title="Aucune réservation à venir"
                        text="Dès qu'un passager réserve l'un de vos trajets, il apparaît ici avec ses places et son numéro."
                        :action-url="route('covoiturage.index')"
                        action-label="Voir mes trajets" />
                @else
                    <x-empty-state
                        title="Aucun trajet passé"
                        text="Les réservations de vos trajets effectués seront conservées ici." />
                @endif
            </div>
        @endif
    </section>
</div>

<style>
    /* Compléments propres à cette page : le reste vient de la feuille sp-* */
    .rtr-list { display: flex; flex-direction: column; gap: 16px; padding: 24px; }

    .rtr-trip {
        background: #fff;
        border: 1px solid var(--sp-border, #eceef1);
        border-radius: var(--sp-radius, 16px);
        overflow: hidden;
        scroll-margin-top: 90px;
    }
    .rtr-trip:target { border-color: var(--color-primary, #ff3c00); box-shadow: 0 0 0 3px rgba(255, 60, 0, .15); }

    .rtr-trip-head { display: flex; align-items: stretch; gap: 18px; padding: 16px; }

    .rtr-trip-img {
        flex: 0 0 150px; height: 112px;
        border-radius: 12px; overflow: hidden; background: #f2f3f5;
    }
    .rtr-trip-img img { width: 100%; height: 100%; object-fit: cover; }

    .rtr-trip-main { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 8px; }
    .rtr-trip-name {
        font-size: 17px; font-weight: 700; line-height: 1.3;
        color: var(--sp-ink, #16191d); text-decoration: none;
    }
    .rtr-trip-name:hover { color: var(--color-primary, #ff3c00); }
    .rtr-trip-dates { display: flex; flex-wrap: wrap; gap: 6px; }

    .rtr-trip-figures {
        flex: 0 0 220px;
        display: flex; flex-direction: column; justify-content: center; gap: 10px;
        padding-left: 18px;
        border-left: 1px solid #f0f1f3;
    }

    .rtr-fill { display: flex; flex-direction: column; gap: 5px; }
    .rtr-fill-label { display: flex; justify-content: space-between; font-size: 12px; color: #6b7280; }
    .rtr-fill-label strong { color: var(--sp-ink, #16191d); font-weight: 700; }
    .rtr-fill-bar { height: 6px; border-radius: 999px; background: #f0f1f3; overflow: hidden; }
    .rtr-fill-bar span { display: block; height: 100%; border-radius: inherit; background: var(--color-primary, #ff3c00); }

    .rtr-earn { display: flex; justify-content: space-between; align-items: baseline; padding-top: 6px; }
    .rtr-earn small { font-size: 12px; color: #6b7280; }
    .rtr-earn strong { font-size: 18px; font-weight: 800; color: #2f9e5f; }

    .rtr-rows { list-style: none; margin: 0; padding: 0; border-top: 1px solid #f0f1f3; }

    .rtr-row {
        display: grid;
        grid-template-columns: 42px minmax(160px, 1.6fr) 70px minmax(110px, 1fr) minmax(140px, 1.2fr) minmax(100px, 1fr) auto auto;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
    }
    .rtr-row + .rtr-row { border-top: 1px dashed #eceef1; }
    .rtr-row.is-cancelled { background: #fcfcfd; }
    .rtr-row.is-cancelled .rtr-who strong,
    .rtr-row.is-cancelled .rtr-cell strong { color: #9aa0a6; text-decoration: line-through; }

    .rtr-avatar { position: relative; width: 42px; height: 42px; border-radius: 50%; }
    .rtr-avatar span, .rtr-avatar img { position: absolute; inset: 0; width: 100%; height: 100%; border-radius: 50%; }
    .rtr-avatar span {
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #ff3c00, #ff8a4c);
        color: #fff; font-size: 15px; font-weight: 800;
    }
    .rtr-avatar img { object-fit: cover; }

    .rtr-who, .rtr-cell { min-width: 0; display: flex; flex-direction: column; gap: 2px; }
    .rtr-who strong { font-size: 13.5px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .rtr-who small, .rtr-cell small { font-size: 11.5px; color: #6b7280; }
    .rtr-cell strong { font-size: 13.5px; font-weight: 700; color: var(--sp-ink, #16191d); }
    .rtr-cell a { font-size: 13px; font-weight: 600; color: inherit; text-decoration: none; white-space: nowrap; }
    .rtr-cell a i { color: var(--color-primary, #ff3c00); margin-right: 4px; }
    .rtr-cell--amount strong { white-space: nowrap; }

    .rtr-state {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 10px; border-radius: 999px;
        font-size: 12px; font-weight: 700; white-space: nowrap;
    }
    .rtr-state.is-paid { background: #e8f6ee; color: #2f9e5f; }
    .rtr-state.is-cancelled { background: #fdeceb; color: #b42318; }

    .sp-page .rtr-open { height: 34px; }

    /* Écran moyen : identité et statut sur une ligne, les chiffres en dessous */
    @media (max-width: 1100px) {
        .rtr-row { grid-template-columns: 42px repeat(4, minmax(0, 1fr)); row-gap: 10px; }
        .rtr-who { grid-column: 2 / 5; }
        .rtr-state { grid-column: 5 / 6; justify-self: end; }
        .rtr-cell--seats { grid-column: 2 / 3; }
        .rtr-open { grid-column: 2 / 6; }
    }

    /* Mobile : deux colonnes de chiffres sous le nom */
    @media (max-width: 768px) {
        .rtr-list { padding: 16px; }
        .rtr-trip-head { flex-direction: column; }
        .rtr-trip-img { flex-basis: auto; height: 150px; }
        .rtr-trip-figures { flex-basis: auto; padding-left: 0; padding-top: 12px; border-left: none; border-top: 1px solid #f0f1f3; }
        .rtr-row { grid-template-columns: 42px repeat(2, minmax(0, 1fr)); }
        .rtr-who { grid-column: 2 / 4; }
        .rtr-state { grid-column: 2 / 4; justify-self: start; }
        .rtr-cell--phone { grid-column: 2 / 3; }
        .rtr-open { grid-column: 1 / 4; }
    }
</style>
@endsection
