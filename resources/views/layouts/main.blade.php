<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Olten.fr')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <!-- Swiper -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Style -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon/olten_location.ico') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>

    <x-header :categories="$categories ?? collect()" />

    <main>
        @yield('content')
    </main>

    <x-footer />
    <script src="{{ asset('assets/js/script.js') }}"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ════════════════════════════════════════
            // 1. SIDEBAR publique (bouton ☰ menuToggle)
            // ════════════════════════════════════════
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.getElementById('menuToggle');
            const closeSidebar = document.getElementById('closeSidebar');

            menuToggle?.addEventListener('click', () => sidebar?.classList.add('active'));
            closeSidebar?.addEventListener('click', () => sidebar?.classList.remove('active'));

            // ════════════════════════════════════════
            // 2. RECHERCHE mobile (bouton loupe)
            // ════════════════════════════════════════
            const searchToggle = document.getElementById('searchToggle');
            const mobileSearch = document.getElementById('mobileSearch');

            searchToggle?.addEventListener('click', () => mobileSearch?.classList.toggle('active'));

            // ════════════════════════════════════════
            // 3. DROPDOWN user-menu (header public)
            // ════════════════════════════════════════
            const userMenus = document.querySelectorAll('.user-menu');

            userMenus.forEach(function(menu) {
                menu.addEventListener('click', function(e) {
                    if (e.target.closest('a') || e.target.closest('button[type="submit"]')) return;
                    menu.classList.toggle('open');
                });
            });

            document.addEventListener('click', function(e) {
                userMenus.forEach(function(menu) {
                    if (!menu.contains(e.target)) menu.classList.remove('open');
                });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    userMenus.forEach(m => m.classList.remove('open'));
                    sidebar?.classList.remove('active');
                    mobileSearch?.classList.remove('active');
                }
            });

        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
</body>

</html>
