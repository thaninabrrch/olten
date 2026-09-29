@extends('layouts.connected')
@section('title', 'Notifications - Olten')

@php
    /*
     | Toutes les notifications du membre, les plus recentes d'abord. La cloche
     | du header n'en montre que les six dernieres. Ouvrir une notification la
     | marque lue et mene a sa page (NotificationController::open).
     */
    $total = $notifications->total();
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Notifications</span>
    </nav>

    {{-- En-tête --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Notifications</h1>
            <p class="sp-subtitle">Nouveaux trajets de vos alertes, horaires modifiés par un conducteur…</p>
        </div>

        <div class="sp-head-actions">
            <a href="{{ route('trips.alerts.index') }}" class="sp-act">
                <i class="fa-regular fa-bell"></i> Mes alertes trajet
            </a>

            @if ($unread)
                <form method="POST" action="{{ route('notifications.readAll') }}">
                    @csrf
                    <button type="submit" class="sp-btn-primary">Tout marquer comme lu</button>
                </form>
            @endif
        </div>
    </header>

    <section class="sp-panel">
        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">Toutes les notifications</h2>
                <span class="sp-count">
                    {{ $total }} notification{{ $total > 1 ? 's' : '' }}
                    · {{ $unread }} non lue{{ $unread > 1 ? 's' : '' }}
                </span>
            </div>
        </div>

        @if ($notifications->count())
            <ul class="ntf-list">
                @foreach ($notifications as $notification)
                    <li>
                        <a href="{{ route('notifications.open', $notification->id) }}"
                           class="ntf-row {{ $notification->read_at ? '' : 'is-unread' }}">
                            <span class="ntf-icon">
                                <i class="fa-solid {{ $notification->data['icon'] ?? 'fa-bell' }}"></i>
                            </span>

                            <span class="ntf-body">
                                <strong>{{ $notification->data['title'] ?? 'Notification' }}</strong>
                                @if (! empty($notification->data['text']))
                                    <small>{{ $notification->data['text'] }}</small>
                                @endif
                            </span>

                            <time class="ntf-time" datetime="{{ $notification->created_at->toIso8601String() }}"
                                  title="{{ $notification->created_at->translatedFormat('d F Y à H:i') }}">
                                {{ $notification->created_at->diffForHumans() }}
                            </time>

                            <i class="fa-solid fa-chevron-right ntf-arrow" aria-hidden="true"></i>
                        </a>
                    </li>
                @endforeach
            </ul>

            @if ($notifications->hasPages())
                <div class="sp-pagination">
                    {{ $notifications->links() }}
                </div>
            @endif
        @else
            <div class="sp-empty">
                <x-empty-state
                    title="Aucune notification"
                    text="Créez une alerte trajet : vous serez prévenu ici dès qu'un conducteur publie la liaison qui vous intéresse."
                    :action-url="route('trips.alerts.index')"
                    action-label="Créer une alerte trajet" />
            </div>
        @endif
    </section>
</div>

<style>
    /* Compléments propres à cette page : le reste vient de la feuille sp-* */
    .ntf-list { list-style: none; margin: 0; padding: 12px 24px 24px; display: flex; flex-direction: column; gap: 8px; }

    .ntf-row {
        display: flex; align-items: center; gap: 14px;
        padding: 14px 16px;
        border: 1px solid var(--sp-border, #eceef1); border-radius: 14px;
        background: #fff; color: inherit; text-decoration: none;
        transition: background .15s ease, border-color .15s ease;
    }
    .ntf-row:hover { background: #f8f9fb; color: inherit; }
    .ntf-row.is-unread { background: #fff5f1; border-color: #ffe0d2; }
    .ntf-row.is-unread:hover { background: #ffede5; }

    .ntf-icon {
        flex: 0 0 40px; width: 40px; height: 40px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 12px; background: #fff1ec; color: var(--color-primary, #ff3c00);
    }

    .ntf-body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 3px; }
    .ntf-body strong { font-size: 14px; font-weight: 700; color: var(--sp-ink, #16191d); }
    .ntf-row.is-unread .ntf-body strong::before {
        content: ''; display: inline-block; width: 7px; height: 7px; margin-right: 7px;
        border-radius: 50%; background: var(--color-primary, #ff3c00); vertical-align: middle;
    }
    .ntf-body small { font-size: 12.5px; color: #6b7280; line-height: 1.45; }

    .ntf-time { flex-shrink: 0; font-size: 12px; color: #98a2b3; white-space: nowrap; }
    .ntf-arrow { flex-shrink: 0; font-size: 11px; color: #c3c8cf; }

    @media (max-width: 576px) {
        .ntf-list { padding: 12px 16px 16px; }
        .ntf-row { flex-wrap: wrap; }
        .ntf-time { order: 3; flex-basis: 100%; padding-left: 54px; }
        .ntf-arrow { display: none; }
    }
</style>
@endsection
