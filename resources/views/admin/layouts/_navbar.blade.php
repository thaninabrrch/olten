<!-- 2.1 Barre de Navigation Supérieure (Navbar) -->
<header class="sticky top-0 z-20 shadow-sm p-4 flex justify-between items-center h-16 transition-colors duration-300"
    style="border-bottom: 1px solid #E5E7EB;">

    <!-- Bouton pour Mobile (Toggle Sidebar Gauche) -->
    <button id="sidebar-toggle"
        class="md:hidden text-gray-700 hover:text-primary-accent focus:outline-none p-1 rounded-full hover:bg-gray-100">
        <!-- Icône Menu -->
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
        </svg>
    </button>

    <!-- Barre de Recherche -->
    <div class="hidden sm:block relative flex-grow max-w-lg mx-4">
        <input type="text" placeholder="Rechercher athlète, session, données..."
            class="search-input hidden w-full pl-10 pr-4 py-2 text-sm border-2 rounded-full focus:outline-none focus:ring-2 focus:ring-primary-accent transition duration-150">
        <!-- Icône Loupe -->

        <h1 class="text-xl font-bold text-gray-800 ml-4 sm:ml-0">
            @yield('page_title', 'Titre de la Page')
        </h1>
    </div>

    <!-- Profil Utilisateur & Notifications & Paramètres -->
    <div class="flex items-center space-x-4">





        <!-- Avatar -->
        <div class="flex items-center space-x-2 cursor-pointer">
            <img class="w-10 h-10 rounded-full object-cover border-2 shadow-md"
                src="{{ auth()->user()?->profile_photo
                    ? asset('storage/' . auth()->user()->profile_photo)
                    : 'https://placehold.co/40x40/FFFFFF/000000?text=AD' }}"
                alt="Avatar Utilisateur" style="border-color: var(--color-primary);">



            <span class="text-sm font-semibold hidden lg:block text-gray-800">
                {{ auth()->user() ? auth()->user()->email : 'Admin' }}
            </span>
        </div>

    </div>
</header>
