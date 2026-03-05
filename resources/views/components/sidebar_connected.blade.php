<aside class="connected-sidebar">
    <nav class="sidebar-menu">
        <!-- LOGO -->
        <div class="sidebar-logo">
            <div class="logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('assets/images/logo/olten_location.png') }}" alt="Olten Logo">
                </a>
            </div>

        </div>

        <!-- SECTIONS -->
        <p class="menu-section">PRINCIPAL</p>
        <nav>
            <ul>
                <li class="{{ request()->is('dashboard') ? 'active' : '' }}">
                    <a href="{{ url('/dashboard') }}">
                        <i class="fa-solid fa-table-columns"></i>
                        <span>Tableau de bord</span>
                    </a>
                </li>

                <li>
                    <a href="{{ url('/dashboard') }}">
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Mes réservations</span>
                    </a>
                </li>

                <li class="{{ request()->routeIs('messages*') ? 'active' : '' }}">
                    <a href="{{ route('messages') }}">
                        <i class="fa-solid fa-envelope"></i>
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

            <p class="menu-section">ANNONCES</p>
            <ul>
                <li class="{{ Route::is('ads.create') ? 'active' : '' }}">
                    <a href="{{ route('ads.create') }}">
                        <i class="fa-solid fa-circle-plus"></i>
                        <span>Ajouter une annonce</span>
                    </a>
                </li>

                <li class="{{ Route::is('ads.index') || Route::is('ads.edit') ? 'active' : '' }}">
                    <a href="{{ route('ads.index') }}">
                        <i class="fa-solid fa-list"></i>
                        <span>Mes annonces</span>
                    </a>
                </li>
                <li class="{{ request()->is('statistiques') ? 'active' : '' }}">
                    <a href="{{ route('statistiques') }}">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Statistiques</span>
                    </a>
                </li>
                <li class="{{ request()->is('favoris') ? 'active' : '' }}">
                    <a href="{{ route('favoris') }}">
                        <i class="fa-solid fa-heart"></i>
                        <span>Favoris</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('livreur.ads.index') ? 'active' : '' }}">
                    <a href="{{ route('livreur.ads.index') }}" class="flex items-center gap-3">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Demande de livraison</span>
                    </a>
                </li>

            </ul>
            @auth
                @php
                    $user = auth()->user();
                @endphp

                {{-- Section Chauffeur VTC --}}
                @if ($user->is_vtc_driver)
                    <p class="menu-section">Chauffeur VTC</p>
                    <ul>
                        <li class="{{ request()->routeIs('livreur.carte.vtc') ? 'active' : '' }}">
                            <a href="{{ route('livreur.carte.vtc') }}" class="flex items-center gap-3">
                                <i class="fa-solid fa-id-card"></i>
                                <span>Carte VTC</span>
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('covoiturage.index') ? 'active' : '' }}">
                            <a href="{{ route('covoiturage.index') }}" class="flex items-center gap-3">
                                <i class="fa-solid fa-car-side"></i>
                                <span>Mes trajets</span>
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('covoiturage.create') ? 'active' : '' }}">
                            <a href="{{ route('covoiturage.create') }}" class="flex items-center gap-3">
                                <i class="fa-solid fa-plus-circle"></i>
                                <span>Ajouter un trajet</span>
                            </a>
                        </li>
                    </ul>
                @endif


                {{-- Section Livreur --}}
                @if ($user->hasRole('livreur'))
                    <p class="menu-section">Livreur</p>
                    <ul>
                        <li class="{{ request()->routeIs('delivery.ads') ? 'active' : '' }}">
                            <a href="{{ route('delivery.ads') }}" class="flex items-center gap-3">
                                <i class="fa-solid fa-truck"></i>
                                <span>Annonces à livrer</span>
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('liv_termine') ? 'active' : '' }}">
                            <a href="{{ route('liv_termine') }}" class="flex items-center gap-3">
                                <i class="fa-solid fa-check-circle"></i>
                                <span>Livraisons terminées</span>
                            </a>
                        </li>
                    </ul>
                @endif

            @endauth


            <p class="menu-section">COMPTE</p>
            <ul>
                <li class="{{ request()->is('profile') ? 'active' : '' }}">
                    <a href="{{ route('profile') }}">
                        <i class="fa-solid fa-users"></i>
                        <span>Mon Compte</span>
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Se déconnecter</span>
                        </x-responsive-nav-link>
                    </form>
                </li>
            </ul>
        </nav>
    </nav>
</aside>
