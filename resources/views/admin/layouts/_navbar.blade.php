{{-- Barre superieure --}}
<header class="admin-topbar">
    <div class="admin-topbar__left">
        {{-- Mobile / tablette : ouvre le tiroir --}}
        <button type="button" id="sidebar-toggle" class="admin-icon-btn admin-topbar__burger"
            aria-label="Ouvrir le menu" aria-controls="sidebar" aria-expanded="false">
            <i class="bi bi-list"></i>
        </button>

        {{-- Desktop : replie / deplie la sidebar --}}
        <button type="button" id="sidebar-collapse" class="admin-icon-btn admin-topbar__collapse"
            aria-label="Réduire le menu" aria-controls="sidebar" aria-expanded="true">
            <i class="bi bi-layout-sidebar"></i>
        </button>

        <h1 class="admin-topbar__title">@yield('page_title', 'Administration')</h1>
    </div>

    <div class="admin-topbar__right">
        <div class="admin-topbar__user">
            <span class="admin-topbar__user-mail">
                {{ auth()->user()?->email ?? 'Admin' }}
            </span>
            <img class="admin-avatar admin-avatar--ring"
                src="{{ auth()->user()?->profile_photo
                    ? asset('storage/' . auth()->user()->profile_photo)
                    : 'https://placehold.co/40x40/FFFFFF/000000?text=AD' }}"
                alt="Avatar">
        </div>
    </div>
</header>
