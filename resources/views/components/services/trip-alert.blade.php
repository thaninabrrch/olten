@props(['from', 'to', 'date' => null])

@php
    /*
     | Invitation a creer une alerte trajet sur une liaison :
     | « Prevenez-moi quand un Paris → Lyon est publie ». La notification
     | arrivera dans la cloche du header (TripAlert::notifyFor).
     |
     | Un visiteur non connecte passe par la popin de connexion
     | (data-auth-required, voir assets/js/auth.js) puis revient ici.
     | Un membre qui guette deja la liaison (ce jour-la, ou tous les jours)
     | le lit ici plutot que de se voir proposer un doublon.
     */
    $slugFrom = \App\Models\Covoiturage::citySlug($from);
    $slugTo   = \App\Models\Covoiturage::citySlug($to);

    $active = auth()->check() && auth()->user()->tripAlerts()
        ->where('depart_slug', $slugFrom)
        ->where('destination_slug', $slugTo)
        ->where(fn ($q) => $date ? $q->whereNull('date')->orWhereDate('date', $date) : $q->whereNull('date'))
        ->exists();

    $when = $date ? 'le ' . \Illuminate\Support\Carbon::parse($date)->translatedFormat('l d F') : 'quel que soit le jour';
@endphp

@if ($slugFrom !== '' && $slugTo !== '' && $slugFrom !== $slugTo)
    <div class="cv-alert {{ $active ? 'is-active' : '' }}">
        <span class="cv-alert-icon">
            <i class="{{ $active ? 'fa-solid fa-bell' : 'fa-regular fa-bell' }}"></i>
        </span>

        <div class="cv-alert-body">
            @if ($active)
                <strong>Alerte active : {{ $from }} → {{ $to }}</strong>
                <p>
                    Vous serez prévenu dans la cloche, en haut de page, dès qu'un nouveau trajet est publié.
                    <a href="{{ route('trips.alerts.index') }}">Gérer mes alertes</a>
                </p>
            @else
                <strong>Prévenez-moi des nouveaux trajets {{ $from }} → {{ $to }}</strong>
                <p>Dès qu'un conducteur publie ce trajet ({{ $when }}), une notification arrive dans la cloche, en haut de page.</p>
            @endif

            @if (session('error') && ! $active)
                <p class="cv-alert-error">{{ session('error') }}</p>
            @endif
        </div>

        @unless ($active)
            <form method="POST" action="{{ route('trips.alerts.store') }}" class="cv-alert-form" data-auth-required>
                @csrf
                <input type="hidden" name="depart" value="{{ $from }}">
                <input type="hidden" name="destination" value="{{ $to }}">
                @if ($date)
                    <input type="hidden" name="date" value="{{ $date }}">
                @endif

                <button type="submit" class="cv-alert-btn">
                    <i class="fa-regular fa-bell"></i> Me prévenir
                </button>
            </form>
        @endunless
    </div>
@endif
