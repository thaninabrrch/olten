<aside id="sidebar"
    class="sidebar overflow-auto fixed inset-y-0 left-0 z-30 w-64 shadow-lg transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out p-4 flex flex-col border-r">

    <!-- Logo -->
    <div class="flex items-center justify-center h-16 mb-6 border-b" style="border-color: #E5E7EB;">
        <img src="{{ asset('assets/images/logo/olten_location.png') }}" width="150px" alt="Logo Olten">
    </div>

    <!-- Navigation -->
    <nav class="flex-1 space-y-2">
        <!-- Tableau de bord -->
        <a href="{{ route('admin.dashboard') }}"
            class="sidebar-link flex items-center p-3 rounded-xl text-sm font-medium">
            <i class="bi bi-grid-fill mr-3 w-5 h-5"></i>
            Tableau de bord
        </a>

        <!-- DROPDOWN GESTION -->
        <div class="space-y-2">
            <button id="gestion-dropdown-toggle"
                class="sidebar-link w-full flex items-center p-3 rounded-xl text-sm font-medium justify-between">
                <div class="flex items-center">
                    <i class="bi bi-tools mr-3 w-5 h-5"></i>
                    Gestion
                </div>
                <svg id="gestion-arrow" class="w-4 h-4 transform transition-transform duration-200 text-gray-400"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div id="gestion-dropdown-content" class="pl-6 space-y-2 hidden">
                <a href="{{ route('admin.categories.index') }}"
                    class="sidebar-link flex items-center p-2 rounded-lg text-xs font-normal hover:text-primary-accent hover:bg-gray-100">
                    Catégories
                </a>
                <a href="{{ route('admin.services.index') }}"
                    class="sidebar-link flex items-center p-2 rounded-lg text-xs font-normal hover:text-primary-accent hover:bg-gray-100">
                    Services
                </a>
            </div>
        </div>

        <!-- Utilisateurs -->
        <a href="{{ route('admin.users.index') }}"
            class="sidebar-link flex items-center p-3 rounded-xl text-sm font-medium">
            <i class="bi bi-person-fill mr-3 w-5 h-5"></i>
            Utilisateurs
        </a>

        <a href="{{ route('admin.contact_messages.index') }}"
            class="sidebar-link flex items-center p-3 rounded-xl text-sm font-medium hover:text-primary-accent hover:bg-gray-100">
            <i class="bi bi-envelope-fill mr-3 w-5 h-5"></i>
            Messages Contact
        </a>

        <a href="{{ route('admin.vtc_cards.index') }}"
            class="sidebar-link flex items-center p-3 rounded-xl text-sm font-medium hover:text-primary-accent hover:bg-gray-100">
            <i class="bi bi-card-checklist mr-3 w-5 h-5"></i>
            Cartes VTC
        </a>
        <a href="{{ route('admin.admin.ads.index') }}"
            class="sidebar-link flex items-center p-3 rounded-xl text-sm font-medium">
            <i class="bi bi-megaphone-fill mr-3 w-5 h-5"></i>
            Annonces
        </a>
        <a href="{{ route('admin.rides.index') }}"
            class="sidebar-link flex items-center p-3 rounded-xl text-sm font-medium hover:text-primary-accent hover:bg-gray-100">
            <i class="bi bi-car-front-fill mr-3 w-5 h-5"></i>
            Trajets
        </a>
        {{-- Paramètres --}}
        <a href="{{ route('admin.settings.index') }}"
            class="sidebar-link flex items-center p-3 rounded-xl text-sm font-medium hover:text-primary-accent hover:bg-gray-100">
            <i class="bi bi-gear-fill mr-3 w-5 h-5"></i>
            Paramètres
        </a>
    </nav>

    <!-- Déconnexion -->
    <div class="mt-auto pt-4 border-t" style="border-color: #E5E7EB;">
        <form action="{{ route('admin.admin.logout') }}" method="POST">
            @csrf
            <button type="submit"
                class="w-full text-left sidebar-link flex items-center p-3 rounded-xl text-sm font-medium text-red-600 hover:bg-red-100">
                <i class="bi bi-box-arrow-right mr-3 w-5 h-5"></i>
                Déconnexion
            </button>
        </form>
    </div>
</aside>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const currentUrl = window.location.href;

        // Dropdown toggle
        const gestionToggle = document.getElementById('gestion-dropdown-toggle');
        const gestionDropdown = document.getElementById('gestion-dropdown-content');
        const gestionArrow = document.getElementById('gestion-arrow');

        // Tous les liens
        const allLinks = document.querySelectorAll('#sidebar a');

        let dropdownActive = false;

        allLinks.forEach(link => {
            if (link.href === currentUrl) {
                const parentDropdown = link.closest('#gestion-dropdown-content');
                if (parentDropdown) {
                    // Lien actif dans le dropdown → gras + souligné
                    link.classList.add('font-bold');
                    link.style.textDecoration = 'underline';

                    // Ouvrir le dropdown parent et mettre bouton rouge
                    parentDropdown.classList.remove('hidden');
                    gestionToggle.style.backgroundColor = getComputedStyle(document.documentElement)
                        .getPropertyValue('--color-primary').trim();
                    gestionToggle.style.color = 'white';
                    gestionArrow.classList.add('rotate-180');
                    dropdownActive = true;
                } else {
                    // Lien direct → juste sidebar-link-active style (pas de soulignement)
                    link.classList.add('sidebar-link-active');
                }
            }
        });

        // Toggle dropdown manuellement
        gestionToggle.addEventListener('click', () => {
            gestionDropdown.classList.toggle('hidden');
            gestionArrow.classList.toggle('rotate-180');

            // Si ouvert manuellement, mettre bouton rouge
            if (!gestionDropdown.classList.contains('hidden')) {
                gestionToggle.style.backgroundColor = getComputedStyle(document.documentElement)
                    .getPropertyValue('--color-primary').trim();
                gestionToggle.style.color = 'white';
            } else if (!dropdownActive) {
                // Sinon revenir au style normal
                gestionToggle.style.backgroundColor = '';
                gestionToggle.style.color = '';
            }
        });
    });
</script>
