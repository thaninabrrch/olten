@php
    /*
     | Cloche des notifications, dans le header public et le header connecte.
     |
     | Elle liste les dernieres notifications du membre (alertes trajet,
     | horaires modifies...). Chacune porte icon / title / text / url : la
     | cloche n'a pas a connaitre leur type. Ouvrir une notification la
     | marque lue (NotificationController::open).
     |
     | Ouverture et fermeture : assets/js/notifications.js.
     | Styles : assets/css/notifications.css.
     */
    $user   = auth()->user();
    $unread = $user->unreadNotifications()->count();
    $latest = $user->notifications()->take(6)->get();
@endphp

<div class="notif" data-notif>
    <button type="button" class="notif-toggle" data-notif-toggle
            aria-haspopup="true" aria-expanded="false"
            aria-label="Notifications{{ $unread ? ' : ' . $unread . ' non lue' . ($unread > 1 ? 's' : '') : '' }}">
        <i class="fa-regular fa-bell"></i>
        @if ($unread)
            <span class="notif-count">{{ $unread > 9 ? '9+' : $unread }}</span>
        @endif
    </button>

    <div class="notif-panel" data-notif-panel hidden>
        <div class="notif-head">
            <strong>Notifications</strong>

            @if ($unread)
                <form method="POST" action="{{ route('notifications.readAll') }}">
                    @csrf
                    <button type="submit" class="notif-link">Tout marquer comme lu</button>
                </form>
            @endif
        </div>

        @if ($latest->isNotEmpty())
            <ul class="notif-list">
                @foreach ($latest as $notification)
                    <li>
                        <a href="{{ route('notifications.open', $notification->id) }}"
                           class="notif-item {{ $notification->read_at ? '' : 'is-unread' }}">
                            <span class="notif-icon">
                                <i class="fa-solid {{ $notification->data['icon'] ?? 'fa-bell' }}"></i>
                            </span>
                            <span class="notif-body">
                                <strong>{{ $notification->data['title'] ?? 'Notification' }}</strong>
                                @if (! empty($notification->data['text']))
                                    <small>{{ $notification->data['text'] }}</small>
                                @endif
                                <time datetime="{{ $notification->created_at->toIso8601String() }}">
                                    {{ $notification->created_at->diffForHumans() }}
                                </time>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="notif-empty">
                <i class="fa-regular fa-bell-slash"></i>
                Aucune notification pour le moment. Créez une alerte trajet pour être prévenu
                dès qu'un conducteur publie la liaison qui vous intéresse.
            </p>
        @endif

        <div class="notif-foot">
            <a href="{{ route('notifications.index') }}">Toutes les notifications</a>
            <a href="{{ route('trips.alerts.index') }}"><i class="fa-regular fa-bell"></i> Mes alertes trajet</a>
        </div>
    </div>
</div>
