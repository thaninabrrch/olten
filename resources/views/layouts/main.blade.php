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
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ @filemtime(public_path('assets/css/style.css')) ?: 1 }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon/olten_location.ico') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    {{-- Etat vide unique de la plateforme (<x-empty-state />) --}}
    <link rel="stylesheet" href="{{ asset('assets/css/empty-state.css') }}?v={{ @filemtime(public_path('assets/css/empty-state.css')) ?: 1 }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pagination.css') }}?v={{ @filemtime(public_path('assets/css/pagination.css')) ?: 1 }}">
    {{-- Recherche : le menu d'autocompletion est pose sous la barre du
         header, donc present sur toutes les pages publiques. La feuille
         porte aussi la page de resultats (/recherche). --}}
    <link rel="stylesheet" href="{{ asset('assets/css/search.css') }}?v={{ @filemtime(public_path('assets/css/search.css')) ?: 1 }}">

    {{-- Feuilles de style poussees par les vues (@push('styles')) --}}
    @stack('styles')
</head>

<body>

    <x-header :categories="$categories ?? collect()" />

    <main>
        @yield('content')
    </main>

    <x-footer />
    <script src="{{ asset('assets/js/script.js') }}"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    {{-- Les boutons du header (sidebar, recherche mobile, menu utilisateur)
         sont cables une seule fois, dans assets/js/script.js. Les recabler
         ici donnait deux bascules par appui sur la loupe : le panneau de
         recherche s'ouvrait et se refermait aussitot. --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>

    {{-- Autocompletion de la barre de recherche. Les deux adresses sont
         passees au script plutot que codees en dur : les noms de route
         restent la seule source de verite des URLs. --}}
    <script>
        const SEARCH_URL = "{{ route('search') }}";
        const SEARCH_SUGGEST_URL = "{{ route('search.suggest') }}";
    </script>
    <script src="{{ asset('assets/js/search-suggest.js') }}?v={{ @filemtime(public_path('assets/js/search-suggest.js')) ?: 1 }}"></script>

    {{-- Scripts pousses par les vues : joues apres Leaflet et flatpickr --}}
    @stack('scripts')
</body>

</html>
