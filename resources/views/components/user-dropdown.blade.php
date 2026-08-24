@php
    $u          = auth()->user();
    $fullName   = $u->name;
    $shortName  = \Illuminate\Support\Str::of($fullName)->explode(' ')->take(2)
                    ->map(fn ($p, $i) => $i === 0 ? $p : \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($p, 0, 1)) . '.')
                    ->implode(' ');
    $initials   = \Illuminate\Support\Str::of($fullName)->explode(' ')->filter()->take(2)
                    ->map(fn ($p) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($p, 0, 1)))
                    ->implode('');
    $photo      = $u->profile_photo ?? null;

    $isLocateur = $u->hasRole('locateur') || $u->hasRole('vendeur');
    $verified   = $u->hasVerifiedEmail() && (bool) $u->is_approved;
    $pending    = ! $verified;

    $adsCount      = $menuAdsCount      ?? 0;
    $receivedCount = $menuReceivedCount ?? 0;
    $messagesCount = $menuMessagesCount ?? 0;
@endphp

<div class="user-menu">
    <div class="user-avatar {{ $pending ? 'is-pending' : '' }}">
        @if ($photo)
            <img src="{{ asset('storage/' . $photo) }}" alt="{{ $fullName }}">
        @else
            {{ $initials }}
        @endif
    </div>
    <span class="username">{{ $fullName }}</span>
    <i class="fa-solid fa-chevron-down"></i>

    <!-- DROPDOWN MENU -->
    <ul class="user-dropdown">

        {{-- En-tête : identité --}}
        <li class="ud-head">
            <div class="ud-head-avatar">
                @if ($photo)
                    <img src="{{ asset('storage/' . $photo) }}" alt="{{ $fullName }}">
                @else
                    {{ $initials }}
                @endif
            </div>
            <div class="ud-head-info">
                <strong>{{ $shortName }}</strong>
                <span class="ud-mail">{{ $u->email }}</span>
                @if ($verified)
                    <span class="ud-badge is-ok">
                        <i class="fa-solid fa-check"></i> Compte vérifié
                    </span>
                @else
                    <a href="{{ route('account.pending') }}" class="ud-badge is-wait">
                        <i class="fa-regular fa-clock"></i> Compte en attente
                    </a>
                @endif
            </div>
        </li>

        {{-- Section : je propose --}}
        <li class="ud-label">{{ $isLocateur ? 'Je propose' : 'Mon espace' }}</li>

        <li class="{{ Route::is('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}">
                <i class="fa-solid fa-table-columns"></i>
                <span>Tableau de bord</span>
            </a>
        </li>

        @if ($isLocateur)
            <li class="{{ Route::is('ads.*') ? 'active' : '' }}">
                <a href="{{ route('ads.index') }}">
                    <i class="fa-solid fa-list"></i>
                    <span>Mes annonces</span>
                    @if ($adsCount)
                        <em class="ud-count">{{ $adsCount }}</em>
                    @endif
                </a>
            </li>

            <li class="{{ Route::is('bookings.receivedBookings') ? 'active' : '' }}">
                <a href="{{ route('bookings.receivedBookings') }}">
                    <i class="fa-regular fa-envelope-open"></i>
                    <span>Réservations reçues</span>
                    @if ($receivedCount)
                        <em class="ud-count is-alert">{{ $receivedCount }}</em>
                    @endif
                </a>
            </li>
        @endif

        {{-- Section : je réserve --}}
        <li class="ud-label">Je réserve</li>

        <li class="{{ Route::is('bookings.myBookings') ? 'active' : '' }}">
            <a href="{{ route('bookings.myBookings') }}">
                <i class="fa-regular fa-calendar-check"></i>
                <span>Mes réservations</span>
            </a>
        </li>

        <li class="{{ Route::is('favoris') ? 'active' : '' }}">
            <a href="{{ route('favoris') }}">
                <i class="fa-regular fa-heart"></i>
                <span>Favoris</span>
            </a>
        </li>

        {{-- Section : compte --}}
        <li class="ud-sep"></li>

        <li class="{{ Route::is('messages*') ? 'active' : '' }}">
            <a href="{{ route('messages') }}">
                <i class="fa-regular fa-envelope"></i>
                <span>Messages</span>
                @if ($messagesCount)
                    <em class="ud-count is-alert">{{ $messagesCount }}</em>
                @endif
            </a>
        </li>

        <li class="{{ Route::is('profile*') ? 'active' : '' }}">
            <a href="{{ route('profile') }}">
                <i class="fa-regular fa-user"></i>
                <span>Mon profil</span>
            </a>
        </li>

        {{-- Déconnexion --}}
        <li class="ud-sep"></li>

        <li class="ud-logout">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Déconnexion</span>
                </button>
            </form>
        </li>

    </ul>
</div>
