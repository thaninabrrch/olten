@php
    /*
     | Barre laterale de l'espace connecte.
     |
     | Chaque entree porte une icone qui lui est propre : avant, plusieurs
     | liens partageaient l'icone du tableau de bord, ce qui rendait le menu
     | illisible. L'etat actif est calcule comme avant, a une exception pres :
     | « Mes commandes » testait request()->is('/mes-commandes*'), avec un
     | slash initial que Laravel ne fait jamais correspondre — le lien n'etait
     | donc jamais marque actif.
     |
     | Toutes les entrees sont visibles par tout le monde : seules les sections
     | « Chauffeur VTC » et « Livreur » restent conditionnees, car elles n'ont
     | aucun sens sans le role correspondant. Les routes, elles, gardent leurs
     | middlewares : un lien peut donc mener a un refus d'acces.
     */
    $user = auth()->user();
@endphp

<aside class="connected-sidebar">

    {{-- Logo --}}
    <div class="sidebar-logo">
        <a href="{{ url('/') }}" class="logo" aria-label="Accueil Olten">
            <img src="{{ asset('assets/images/logo/olten_location.png') }}" alt="Olten">
        </a>
    </div>

    <nav class="sidebar-menu" aria-label="Navigation principale">

        {{-- ══ Principal ══ --}}
        <p class="menu-section">Principal</p>

        <ul>
            <li class="{{ request()->is('dashboard') ? 'active' : '' }}">
                <a href="{{ url('/dashboard') }}">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Tableau de bord</span>
                </a>
            </li>

            <li class="{{ request()->is('mes-commandes*') ? 'active' : '' }}">
                <a href="{{ route('orders') }}">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span>Mes commandes</span>
                </a>
            </li>

            <li class="{{ Route::is('bookings.receivedBookings') ? 'active' : '' }}">
                <a href="{{ url('/mes-reservations-recues') }}">
                    <i class="fa-solid fa-calendar-plus"></i>
                    <span>Réservations reçues</span>
                </a>
            </li>

            <li class="{{ Route::is('bookings.myBookings') ? 'active' : '' }}">
                <a href="{{ url('/mes-reservations') }}">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Mes réservations</span>
                </a>
            </li>

            <li class="{{ request()->routeIs('messages*') ? 'active' : '' }}">
                <a href="{{ route('messages') }}">
                    <i class="fa-solid fa-comment-dots"></i>
                    <span>Messages</span>
                </a>
            </li>

            <li class="{{ Route::is('walt.index') ? 'active' : '' }}">
                <a href="{{ url('/portefeuille') }}">
                    <i class="fa-solid fa-wallet"></i>
                    <span>Portefeuille</span>
                </a>
            </li>
        </ul>

        {{-- ══ Ma boutique ══ --}}
        <p class="menu-section">Ma boutique</p>

        <ul>
            <li class="{{ request()->is('vendeur/produits*') ? 'active' : '' }}">
                <a href="{{ route('seller.produits.index') }}">
                    <i class="fa-solid fa-box"></i>
                    <span>Mes produits</span>
                </a>
            </li>

            <li class="{{ request()->is('vendeur/ventes*') ? 'active' : '' }}">
                <a href="{{ route('seller.sales') }}">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Mes ventes</span>
                </a>
            </li>

            <li class="{{ request()->is('vendeur/commandes-clients*') ? 'active' : '' }}">
                <a href="{{ route('seller.clientOrders') }}">
                    <i class="fa-solid fa-inbox"></i>
                    <span>Commandes clients</span>
                </a>
            </li>
        </ul>

        {{-- ══ Annonces ══ --}}
        <p class="menu-section">Annonces</p>

        <ul>
            <li class="{{ Route::is('ads.create') ? 'active' : '' }}">
                <a href="{{ $user->is_approved ? route('ads.create') : '#' }}"
                   class="{{ $user->is_approved ? '' : 'is-locked' }}">
                    <i class="fa-solid fa-circle-plus"></i>
                    <span>Déposer une annonce</span>
                </a>
            </li>

            <li class="{{ Route::is('ads.index') || Route::is('ads.edit') ? 'active' : '' }}">
                <a href="{{ route('ads.index') }}">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Mes annonces</span>
                </a>
            </li>

            <li class="{{ request()->is('statistiques') ? 'active' : '' }}">
                <a href="{{ route('statistiques') }}">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Statistiques</span>
                </a>
            </li>
        </ul>

        {{-- ══ Mes envies ══ --}}
        <p class="menu-section">Mes envies</p>

        <ul>
            <li class="{{ request()->is('favoris') ? 'active' : '' }}">
                <a href="{{ route('favoris') }}">
                    <i class="fa-solid fa-heart"></i>
                    <span>Favoris</span>
                </a>
            </li>

            <li class="{{ request()->routeIs('livreur.ads.index') ? 'active' : '' }}">
                <a href="{{ $user->is_approved ? route('livreur.ads.index') : '#' }}"
                   class="{{ $user->is_approved ? '' : 'is-locked' }}">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                    <span>Demandes de livraison</span>
                </a>
            </li>
        </ul>

        {{-- ══ Chauffeur VTC ══ --}}
        @if($user->is_vtc_driver)
            <p class="menu-section">Chauffeur VTC</p>

            <ul>
                <li class="{{ request()->routeIs('livreur.carte.vtc') ? 'active' : '' }}">
                    <a href="{{ route('livreur.carte.vtc') }}">
                        <i class="fa-solid fa-id-card"></i>
                        <span>Carte VTC</span>
                    </a>
                </li>

                <li class="{{ request()->routeIs('covoiturage.index') ? 'active' : '' }}">
                    <a href="{{ route('covoiturage.index') }}">
                        <i class="fa-solid fa-car-side"></i>
                        <span>Mes trajets</span>
                    </a>
                </li>

                <li class="{{ request()->routeIs('covoiturage.create') ? 'active' : '' }}">
                    <a href="{{ route('covoiturage.create') }}">
                        <i class="fa-solid fa-map-location-dot"></i>
                        <span>Ajouter un trajet</span>
                    </a>
                </li>
            </ul>
        @endif

        {{-- ══ Livreur ══ --}}
        @if($user->hasRole('livreur'))
            <p class="menu-section">Livreur</p>

            <ul>
                <li class="{{ request()->routeIs('livreur.missions') || request()->routeIs('livreur.demandes') || request()->routeIs('livreur.livraisons') ? 'active' : '' }}">
                    <a href="{{ route('livreur.missions') }}">
                        <i class="fa-solid fa-route"></i>
                        <span>Missions de livraison</span>
                    </a>
                </li>

                <li class="{{ request()->routeIs('liv_termine') ? 'active' : '' }}">
                    <a href="{{ route('liv_termine') }}">
                        <i class="fa-solid fa-clipboard-check"></i>
                        <span>Livraisons terminées</span>
                    </a>
                </li>
            </ul>
        @endif

        {{-- ══ Compte ══ --}}
        <p class="menu-section">Compte</p>

        <ul>
            <li class="{{ request()->is('profile') ? 'active' : '' }}">
                <a href="{{ route('profile') }}">
                    <i class="fa-solid fa-circle-user"></i>
                    <span>Mon compte</span>
                </a>
            </li>

            <li class="sidebar-logout">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Se déconnecter</span>
                    </button>
                </form>
            </li>
        </ul>
    </nav>
</aside>
