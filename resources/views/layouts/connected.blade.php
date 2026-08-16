<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Olten')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/style_connecter/style_connected.css') }}">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.ckeditor.com/ckeditor5/41.3.0/classic/ckeditor.js"></script>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon/olten_location.ico') }}">
</head>

<body>
    <div class="connected-layout">

        {{-- SIDEBAR --}}
        @include('components.sidebar_connected')
        <div class="overlay" id="sidebarOverlay"></div>
        <div class="main-content">

            {{-- HEADER --}}
            @include('components.header_connected')

            {{-- CONTENU SPÉCIFIQUE --}}
            <main class="dashboard-content">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </main>

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('assets/js/adress.js') }}"></script>
    <script src="{{ asset('assets/js/script_connected.js') }}"></script>
    <script src="{{ asset('assets/js/ckeditor.js') }}"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        lucide.createIcons();
        document.addEventListener('DOMContentLoaded', function() {

            // ════════════════════════════════════════
            // 1. SIDEBAR — slide-in mobile
            // ════════════════════════════════════════
            const sidebar = document.querySelector('.connected-sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const btnToggle = document.querySelector('.btn-toggle-sidebar'); // bouton ☰ dans le header

            function openSidebar() {
                sidebar.classList.add('active');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }

            if (btnToggle) {
                btnToggle.addEventListener('click', () =>
                    sidebar.classList.contains('active') ? closeSidebar() : openSidebar()
                );
            }

            overlay?.addEventListener('click', closeSidebar);

            // Fermer sidebar quand on clique un lien (mobile)
            document.querySelectorAll('.connected-sidebar a').forEach(link =>
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 1024) closeSidebar();
                })
            );

            // ════════════════════════════════════════
            // 2. DROPDOWN — menu utilisateur dans le header
            // ════════════════════════════════════════
            const userMenus = document.querySelectorAll('.user-menu'); // avatar + chevron

            userMenus.forEach(menu => {
                menu.addEventListener('click', function(e) {
                    // Ne pas interférer avec les liens/boutons à l'intérieur
                    if (e.target.closest('a') || e.target.closest('button[type="submit"]')) return;
                    menu.classList.toggle('open');
                });
            });

            // Fermer dropdown en cliquant ailleurs
            document.addEventListener('click', function(e) {
                userMenus.forEach(menu => {
                    if (!menu.contains(e.target)) menu.classList.remove('open');
                });
            });

            // ════════════════════════════════════════
            // 3. ÉCHAP — ferme les deux
            // ════════════════════════════════════════
            document.addEventListener('keydown', function(e) {
                if (e.key !== 'Escape') return;
                closeSidebar();
                userMenus.forEach(m => m.classList.remove('open'));
            });

        });
    </script>

</body>

</html>
