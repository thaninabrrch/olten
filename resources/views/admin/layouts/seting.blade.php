<div id="settings-overlay"
    class="fixed inset-0 z-40 bg-gray-900 bg-opacity-30 hidden transition-opacity duration-300 ease-in-out"></div>

<aside id="settings-sidebar"
    class="fixed inset-y-0 right-0 z-50 w-80 shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out p-6 flex flex-col border-l bg-gray-50">

    <!-- Header Sidebar -->
    <div class="flex justify-between items-center pb-4 mb-6 border-b" style="border-color: #E5E7EB;">
        <h2 class="text-xl font-bold">Paramètres</h2>
        <button id="settings-close"
            class="text-gray-500 hover:text-primary-accent focus:outline-none p-1 rounded-full hover:bg-gray-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>


        <!-- Section de Navigation/Actions -->
        <div class="flex flex-col gap-3">

            <!-- Card d'Action Professionnelle (Configurer Type Service) -->
            <a href="{{ route('admin.type_services.index') }}"
                class="group flex items-center p-3 rounded-xl bg-white border border-gray-200
                       shadow-sm hover:shadow-md transition duration-200 ease-in-out
                       hover:border-red-500 hover:bg-red-50/70 cursor-pointer">

                <!-- 1. Icône avec Fond Coloré (Création d'un point focal visuel) -->
                <div class="p-2 bg-red-100 rounded-lg group-hover:bg-red-600 transition duration-200">
                    <!-- Icône: Utilisation d'un cercle croisé pour "Service/Configuration" -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600 group-hover:text-white"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6V4m0 8h.01m-6.938 4h13.856c1.192 0 1.954-1.03 1.954-2.06s-.762-2.06-1.954-2.06H5.062c-1.192 0-1.954 1.03-1.954 2.06s.762 2.06 1.954 2.06zm0 4h13.856c1.192 0 1.954-1.03 1.954-2.06s-.762-2.06-1.954-2.06H5.062c-1.192 0-1.954 1.03-1.954 2.06s.762 2.06 1.954 2.06z" />
                    </svg>
                </div>

                <!-- 2. Titre (Gras et distinct) -->
                <span class="text-gray-800 font-medium text-base ml-4 flex-grow truncate">Configurer Types de
                    Service</span>

                <!-- 3. Flèche d'Indication (Micro-interaction) -->
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-4 h-4 text-gray-400 group-hover:text-red-600 group-hover:translate-x-0.5 transition duration-200"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
</aside>