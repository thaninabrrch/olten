<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Olten Admin Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}?v={{ @filemtime(public_path('assets/css/admin.css')) ?: 1 }}">
    {{-- Applique l'etat replie de la sidebar avant le rendu (evite le saut visuel) --}}
    <script>
        try {
            if (localStorage.getItem('admin-sidebar-collapsed') === '1') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        } catch (e) {}
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon/olten_location.ico') }}">
    {{-- Etat vide unique de la plateforme (<x-empty-state />) --}}
    <link rel="stylesheet" href="{{ asset('assets/css/empty-state.css') }}?v={{ @filemtime(public_path('assets/css/empty-state.css')) ?: 1 }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pagination.css') }}?v={{ @filemtime(public_path('assets/css/pagination.css')) ?: 1 }}">
    @stack('styles')
</head>

<body class="light-mode admin-body">

    <!-- Sidebar -->
    @include('admin.layouts._sidebar')

    <div id="main-content" class="admin-main">
        <!-- Navbar -->
        @include('admin.layouts._navbar')

        <!-- Page Content -->
        <main class="admin-content">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="admin-footer">
            &copy; {{ date('Y') }} Olten Admin. Tous droits réservés.
        </footer>
    </div>


    @if (session('success'))
        <div id="customToast" class="toast-custom">
            {{ session('success') }}
        </div>


        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const toast = document.getElementById('customToast');
                if (toast) {
                    // Affiche le toast
                    toast.classList.add('show');

                    // Masque après 2 secondes
                    setTimeout(() => {
                        toast.classList.remove('show');
                    }, 2000);
                }
            });
        </script>
    @endif



    <script src="{{ asset('assets/js/admin/dash.js') }}?v={{ @filemtime(public_path('assets/js/admin/dash.js')) ?: 1 }}"></script>

</body>

</html>
