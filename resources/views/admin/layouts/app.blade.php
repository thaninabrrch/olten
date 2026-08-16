<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Olten Admin Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon/olten_location.ico') }}">
    @stack('styles')
</head>

<body class="light-mode min-h-screen">

    <!-- Sidebar -->
    @include('admin.layouts._sidebar')

    <!-- Content Wrapper -->
    @include('admin.layouts.seting')

    <div id="main-content" class="main-content-area flex-1 md:ml-64 transition-all duration-300 ease-in-out">
        <!-- Navbar -->
        @include('admin.layouts._navbar')

        <!-- Page Content -->
        <main class="container-fluid p-6 flex-grow">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="p-4 mt-6 border-t border-gray-200 text-center text-sm text-gray-500">
            &copy; 2025 Olten Admin. Tous droits réservés.
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



    <script src="{{ asset('assets/js/admin/dash.js') }}"></script>

</body>

</html>
