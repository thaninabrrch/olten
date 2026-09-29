@php
    // Navigation de l'espace admin, regroupee par section.
    // « active » accepte un ou plusieurs motifs de nom de route : le lien reste
    // actif sur les sous-pages (creation, edition, detail...).
    $adminNav = [
        [
            'label' => 'Général',
            'items' => [
                ['label' => 'Tableau de bord', 'icon' => 'bi-grid-1x2-fill', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
            ],
        ],
        [
            'label' => 'Gestion',
            'items' => [
                ['label' => 'Catégories', 'icon' => 'bi-tags-fill', 'route' => 'admin.categories.index', 'active' => ['admin.categories.*', 'admin.subcategories.*']],
                ['label' => 'Services', 'icon' => 'bi-layers-fill', 'route' => 'admin.services.index', 'active' => 'admin.services.*'],
                ['label' => 'Abonnements', 'icon' => 'bi-credit-card-2-front-fill', 'route' => 'admin.subscriptions.index', 'active' => 'admin.subscriptions.*'],
            ],
        ],
        [
            'label' => 'Utilisateurs',
            'items' => [
                ['label' => 'Utilisateurs', 'icon' => 'bi-people-fill', 'route' => 'admin.users.index', 'active' => 'admin.users.*'],
                ['label' => 'Documents requis', 'icon' => 'bi-patch-check-fill', 'route' => 'admin.documents.index', 'active' => 'admin.documents.*'],
            ],
        ],
        [
            'label' => 'Activité',
            'items' => [
                ['label' => 'Annonces', 'icon' => 'bi-megaphone-fill', 'route' => 'admin.admin.ads.index', 'active' => ['admin.admin.ads.*', 'admin.ads.*']],
                ['label' => 'Trajets', 'icon' => 'bi-car-front-fill', 'route' => 'admin.rides.index', 'active' => 'admin.rides.*'],
                ['label' => 'Remboursements', 'icon' => 'bi-arrow-counterclockwise', 'route' => 'admin.refunds.index', 'active' => 'admin.refunds.*'],
                ['label' => 'Messages contact', 'icon' => 'bi-envelope-fill', 'route' => 'admin.contact_messages.index', 'active' => 'admin.contact_messages.*'],
            ],
        ],
    ];

    $adminUser = auth()->user();
@endphp

<aside id="sidebar" class="admin-sidebar" aria-label="Navigation principale">

    {{-- En-tete : logo + fermeture (mobile) --}}
    <div class="admin-sidebar__head">
        <a href="{{ route('admin.dashboard') }}" class="admin-sidebar__brand">
            <img src="{{ asset('assets/images/logo/olten_location.png') }}" alt="Olten"
                class="admin-sidebar__logo">
            <img src="{{ asset('assets/images/favicon/olten_location.ico') }}" alt="Olten"
                class="admin-sidebar__logo-mini">
        </a>
        <button type="button" id="sidebar-close" class="admin-icon-btn admin-sidebar__close"
            aria-label="Fermer le menu">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="admin-sidebar__nav">
        @foreach ($adminNav as $section)
            <div class="admin-nav-section">
                <p class="admin-nav-section__label">{{ $section['label'] }}</p>
                <ul class="admin-nav-section__list">
                    @foreach ($section['items'] as $item)
                        @php $isActive = request()->routeIs(...(array) $item['active']); @endphp
                        <li>
                            <a href="{{ route($item['route']) }}"
                                class="sidebar-link {{ $isActive ? 'is-active' : '' }}"
                                data-label="{{ $item['label'] }}"
                                @if ($isActive) aria-current="page" @endif>
                                <i class="bi {{ $item['icon'] }} sidebar-link__icon"></i>
                                <span class="sidebar-link__text">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    {{-- Pied : utilisateur + deconnexion --}}
    <div class="admin-sidebar__foot">
        <div class="admin-sidebar__user">
            <img class="admin-avatar"
                src="{{ $adminUser?->profile_photo
                    ? asset('storage/' . $adminUser->profile_photo)
                    : 'https://placehold.co/40x40/FFFFFF/000000?text=AD' }}"
                alt="">
            <div class="admin-sidebar__user-info">
                <p class="admin-sidebar__user-name">{{ $adminUser?->name ?: 'Administrateur' }}</p>
                <p class="admin-sidebar__user-mail">{{ $adminUser?->email }}</p>
            </div>
        </div>

        <form action="{{ route('admin.admin.logout') }}" method="POST">
            @csrf
            <button type="submit" class="sidebar-link sidebar-link--danger" data-label="Déconnexion">
                <i class="bi bi-box-arrow-left sidebar-link__icon"></i>
                <span class="sidebar-link__text">Déconnexion</span>
            </button>
        </form>
    </div>
</aside>

<div id="sidebar-backdrop" class="admin-backdrop" aria-hidden="true"></div>
