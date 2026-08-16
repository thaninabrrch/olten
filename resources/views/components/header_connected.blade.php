
        <!-- HEADER -->
        <header class="connected-header">
            <div class="header-left">
                <button class="btn-toggle-sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <div class="header-right">
                <div class="user-menu">
                    @php
                        $name = Auth::user()->name;
                        $initial = strtoupper(substr($name, 0, 1));
                    @endphp
                    <div class="user-avatar">{{ $initial }}</div>
                    <span class="username">{{ $name }}</span>
                    <i class="fa-solid fa-chevron-down"></i>
                    <!-- DROPDOWN MENU -->
                    <ul class="user-dropdown">
                        <li>
                            <a href="{{ url('/dashboard') }}">
                                <i class="fa-solid fa-table-columns"></i>
                                Tableau de bord
                            </a>
                        </li>
                        @if(auth()->user()->hasRole('locateur'))
                        <li class="{{ Route::is('bookings.receivedBookings') ? 'active' : '' }}">
                            <a href="{{ url('/mes-reservations-recues') }}">
                                <i class="fa-solid fa-calendar-check"></i>
                                <span>Mes réservations reçues</span>
                            </a>
                        </li>
                        @endif
                        <li class="{{ Route::is('bookings.myBookings') ? 'active' : '' }}">
                            <a href="{{ url('/mes-reservations') }}">
                                <i class="fa-solid fa-calendar-check"></i>
                                <span>Mes réservations</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('ads.index') }}">
                                <i class="fa-solid fa-list"></i>
                                Mes annonces
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('favoris') }}">
                                <i class="fa-solid fa-heart"></i>
                                Favoris
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('messages') }}">
                                <i class="fa-solid fa-envelope"></i>
                                Messages
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('profile') }}">
                                <i class="fa-solid fa-user"></i>
                                Mon profil
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a href="{{route('logout')}}" onclick="event.preventDefault(); this.closest('form').submit();">
                                    <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
                                </a>
                            </form>
                        </li>
                    </ul>
                </div>
                @if(auth()->user()->hasRole('locateur'))
                <a href="{{ auth()->user()->is_approved ? route('ads.create') : '#' }}" class="btn-add-annonce {{ !auth()->user()->is_approved ? 'opacity-50 pointer-events-none cursor-not-allowed' : '' }}">
                    <i class="fa-solid fa-plus"></i>
                    <span>Ajouter une annonce</span>
                </a>
                @elseif(auth()->user()->hasRole('vendeur'))
                <a href="{{ auth()->user()->is_approved ? route('seller.produits.create') : '#' }}" class="btn-add-annonce {{ !auth()->user()->is_approved ? 'opacity-50 pointer-events-none cursor-not-allowed' : '' }}">
                    <i class="fa-solid fa-plus"></i>
                    <span>Ajouter un produit</span>
                </a>
                @endif
            </div>
        </header>