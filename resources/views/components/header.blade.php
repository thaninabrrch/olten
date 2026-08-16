<header class="header">
    <!-- Logo -->
    <div class="header-left">
        <div class="logo">
            <a href="{{ url('/') }}">
                <img src="{{ asset('assets/images/logo/olten_location.png') }}" alt="Olten Logo">
            </a>
        </div>
    </div>

    <!-- Barre de recherche (PC uniquement) -->
    <div class="search-bar">
        <form method="GET" action="{{ route('home') }}" class="search-bar w-100">

            <div class="search-field">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" placeholder="Que recherchez-vous ?" class="search-input"
                    value="{{ request('search') }}">
            </div>

            <div class="divider"></div>

            <div class="search-field">
                <i class="fa-solid fa-location-dot"></i>
                <input type="text" name="location" placeholder="Emplacement" class="location-input"
                    value="{{ request('location') }}">
            </div>

            <div class="divider"></div>

            <div class="search-field">
                <select name="category" class="category-select">
                    <option value="">Toutes les catégories</option>

                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->nom }}
                        </option>
                    @endforeach

                </select>
            </div>

            <button type="submit" class="search-btn">Rechercher</button>

        </form>
    </div>

    <!-- Profil / Menu / Icônes Mobile -->
    <div class="header-right">
        <button class="icon-btn search-toggle" id="searchToggle">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>

        @if (Auth::check())
            <div class="user-menu">
                @php
                    $name = Auth::user()->name;
                    $initial = strtoupper(substr($name, 0, 1));
                @endphp
                <div class="user-avatar">{{ $initial }}</div>
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
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
                            </a>
                        </form>
                    </li>
                </ul>
            </div>
        @else
            <button class="icon-btn">
                <i class="fa-solid fa-right-to-bracket"></i>
            </button>
        @endif

        <button class="icon-btn" id="menuToggle">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <!-- Bloc de recherche mobile -->
    <div class="mobile-search" id="mobileSearch">
        <div class="mobile-search-content">

            <form method="GET" action="{{ route('home') }}">

                <div class="search-field">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" placeholder="Que recherchez-vous ?" class="search-input"
                        value="{{ request('search') }}">
                </div>

                <div class="search-field">
                    <i class="fa-solid fa-location-dot"></i>
                    <input type="text" name="location" placeholder="Emplacement" class="location-input"
                        value="{{ request('location') }}">
                </div>

                <div class="search-field">
                    <select name="category" class="category-select">
                        <option value="">Toutes les catégories</option>

                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->nom }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <button type="submit" class="search-btn w-100">
                    Rechercher
                </button>

            </form>

        </div>
    </div>

    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="{{ asset('assets/images/logo/olten_location.jpg') }}" alt="Olten Logo">
            </div>
            <button class="close-btn" id="closeSidebar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <ul class="menu-list">
            <li class="d-flex">
            <a class="d-flex gap-2" href="/" >
                <i class="fas fa-home category-icon-small bg-transparent"></i> Accueil
            </a>
            </li>
            @foreach($footerCategories as $category)
            <li class="d-flex">
                <a href="{{ route('categories.show', $category->slug) }}" class="d-flex gap-2">
                    <i class="{{ $category->icon }} category-icon-small bg-transparent"></i> {{ $category->nom }}
                </a>
            </li>
            @endforeach
            <li class="d-flex">
            <a class="d-flex gap-2" href="/contact">
                <i class="fas fa-envelope category-icon-small bg-transparent"></i> Contact
            </a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <h3>Contactez-nous</h3>
            <p>olten-location@outlook.fr</p>
            <div class="social-icons">
                <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#"><i class="fa-brands fa-instagram"></i></a>
                <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
            </div>
        </div>
    </nav>
</header>

<!-- Modal Connexion / Inscription -->
<div id="authModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" id="closeModal">&times;</span>

        <div class="tabs">
            <button class="tab-btn active" data-tab="login">Se connecter</button>
            <button class="tab-btn" data-tab="register">S'inscrire</button>
        </div>

        <!-- Contenu Connexion -->
        <div class="tab-content" id="login">
            <form id="login-form" method="POST" action="{{ route('login') }}">
                @csrf
                <div class="input-group">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="email" placeholder="Nom d'utilisateur / Email" required>
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="Mot de passe" required>
                    <i class="fa-solid fa-eye toggle-password"></i>
                </div>

                <label>
                    <input type="checkbox" name="remember"> Se souvenir de moi
                </label>

                <div id="login-errors"></div>

                <button type="submit" class="submit-btn">Connexion</button>
                <p><a href="{{ route('password.request') }}">Vous avez perdu votre mot de passe ?</a></p>
            </form>
        </div>

        <!-- Contenu Inscription -->
        <div class="tab-content" id="register" style="display:none;">
            <form id="registerForm">
                @csrf

                <div class="input-group">
                    <i class="fa-solid fa-pen"></i>
                    <input type="text" name="first_name" placeholder="Prénom" required>
                </div>

                <div class="input-group">
                    <i class="fa-solid fa-pen"></i>
                    <input type="text" name="last_name" placeholder="Nom de famille" required>
                </div>

                <div class="input-group">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="Adresse e-mail" required>
                </div>

                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="Mot de passe" required>
                    <i class="fa-solid fa-eye toggle-password"></i>
                </div>

                {{-- ✅ Confirmation mot de passe remontée avant le rôle --}}
                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password_confirmation" placeholder="Confirmer le mot de passe"
                        required>
                    <i class="fa-solid fa-eye toggle-password"></i>
                </div>

                {{-- ✅ Rôle en dernier (masqué si géré par l'admin, voir point 2) --}}
                <div class="input-group">
                    <i class="fa-solid fa-user-tag"></i>
                    <select name="role" required>
                        <option value="">Choisir un rôle</option>
                        <option value="locateur">Locateur</option>
                        <option value="vendeur">Vendeur</option>
                        <option value="livreur">Livreur</option>
                        <option value="locateur|livreur">Locataire + Livreur</option>
                        <option value="vendeur|livreur">Vendeur + Livreur</option>
                    </select>
                </div>

                <label>
                    <input type="checkbox" name="terms" required>
                    J'accepte les <a href="#">Conditions de confidentialité</a>
                </label>

                <div id="registerErrors"></div>

                <button type="submit" class="submit-btn">S'inscrire</button>
            </form>
        </div>
    </div>
</div>
<script>
    const REGISTER_URL = "{{ route('register') }}";
    const LOGIN_URL = "{{ route('login') }}";
    const LOGIN_REDIRECT = "{{ route('dashboard') }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";
    window.SHOW_LOGIN_MODAL = @json(session('showLoginModal', false));
    window.PASSWORD_RESET_STATUS = @json(session('status', null));
</script>
<script src="{{ asset('assets/js/auth.js') }}"></script>
