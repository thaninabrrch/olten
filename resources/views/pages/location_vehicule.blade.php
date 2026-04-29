<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Olten-location.fr - Location de véhicules</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #ffffff;
            overflow-x: hidden;
        }

        .text-orange-olten {
            color: #FF731D;
        }




        .bg-orange-olten {
            background-color: #FF731D;
        }

        .border-orange-olten {
            border-color: #FF731D;
        }

        .card-shadow {
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.05);
        }

        input[type=number] {
            -moz-appearance: textfield;
        }



        /* APRÈS (cache l'icône) */
        input[type="date"]::-webkit-calendar-picker-indicator {
            display: none;
            -webkit-appearance: none;
        }

        /* Empêcher le défilement horizontal lors des animations */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-900">

    <x-header />

    {{-- ===================== HERO SECTION ===================== --}}
    <section
        class="relative min-h-[650px] lg:h-[650px] flex flex-col justify-center px-6 sm:px-8 lg:px-12 pt-32 pb-64 lg:pb-32"
        style="background: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)),
                    url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&q=80&w=1920') center/cover no-repeat;">

        <div class="max-w-6xl mx-auto w-full">
            <!-- Badge -->
            <div class="flex items-center gap-3 mb-6">
                <div class="h-[2px] w-8 sm:w-12 bg-orange-olten"></div>
                <span class="text-white text-[10px] sm:text-xs uppercase tracking-[0.3em] font-extrabold">
                    Axé sur le trajet personnalisé
                </span>
            </div>

            <!-- Titre -->
            <h1 class="text-white text-4xl sm:text-6xl lg:text-7xl font-extrabold leading-[1.1] mb-6">
                Réservez votre .<br>
                <span class="text-orange-olten">trajet.</span>
            </h1>

            <!-- Desc -->
            <p class="text-gray-200 text-sm sm:text-base max-w-sm sm:max-w-md leading-relaxed opacity-90">
                Profitez d’une expérience de covoiturage unique. Confort premium, sécurité totale et flexibilité à la
                demande.
            </p>
        </div>

        <!-- SEARCH BAR RESPONSIVE -->
        <div
            class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 w-[calc(100%-2.5rem)] sm:w-[calc(100%-4rem)] max-w-6xl bg-white rounded-[24px] sm:rounded-[40px] shadow-2xl p-6 sm:p-10 z-20">
            <h3 class="text-xl sm:text-2xl font-extrabold text-gray-900 mb-6 sm:mb-8">Rechercher un trajet</h3>

            <!-- Grid: 1 col mobile, 2 cols tablet, 12 cols desktop -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 lg:gap-3 items-end">

                <!-- Lieu de départ -->
                <div class="lg:col-span-2 space-y-2">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Lieu de
                        départ</label>
                    <div class="relative group">
                        <i
                            class="fa-solid fa-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-orange-olten opacity-60"></i>
                        <input type="text" placeholder="D'où ?"
                            class="w-full bg-gray-50 border border-gray-100 rounded-xl sm:rounded-2xl py-4 pl-11 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-orange-100 focus:border-orange-olten transition-all">
                    </div>
                </div>

                <!-- Lieu de fin -->
                <div class="lg:col-span-2 space-y-2">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Lieu de
                        fin</label>
                    <div class="relative group">
                        <i
                            class="fa-solid fa-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-orange-olten opacity-60"></i>
                        <input type="text" placeholder="Où ?"
                            class="w-full bg-gray-50 border border-gray-100 rounded-xl sm:rounded-2xl py-4 pl-11 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-orange-100 focus:border-orange-olten transition-all">
                    </div>
                </div>

                <!-- Date de départ -->
                <div class="lg:col-span-2 space-y-2">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Date
                        départ</label>
                    <div class="relative group">
                        <i
                            class="fa-solid fa-calendar absolute left-4 top-1/2 -translate-y-1/2 text-orange-olten opacity-60"></i>
                        <input type="date"
                            class="w-full bg-gray-50 border border-gray-100 rounded-xl sm:rounded-2xl py-4 pl-11 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-orange-100 focus:border-orange-olten transition-all">
                    </div>
                </div>

                <!-- Date de retour -->
                <div class="lg:col-span-2 space-y-2">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Date
                        retour</label>
                    <div class="relative group">
                        <i
                            class="fa-solid fa-calendar absolute left-4 top-1/2 -translate-y-1/2 text-orange-olten opacity-60"></i>
                        <input type="date"
                            class="w-full bg-gray-50 border border-gray-100 rounded-xl sm:rounded-2xl py-4 pl-11 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-orange-100 focus:border-orange-olten transition-all">
                    </div>
                </div>

                <!-- Nombre de personne -->
                <div class="lg:col-span-2 space-y-2">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Passagers</label>
                    <div class="relative group">
                        <input type="number" value="2" min="1" max="10"
                            class="w-full bg-gray-50 border border-gray-100 rounded-xl sm:rounded-2xl py-4 px-5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-100 focus:border-orange-olten transition-all">
                        <div
                            class="absolute right-4 top-1/2 -translate-y-1/2 flex flex-col gap-0.5 text-[10px] text-orange-olten pointer-events-none">
                            <i class="fa-solid fa-chevron-up"></i>
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                </div>

                <!-- Bouton Rechercher -->
                <div class="sm:col-span-2 lg:col-span-2">
                    <button
                        class="w-full bg-orange-olten hover:bg-orange-600 text-white font-bold h-[56px] rounded-xl sm:rounded-2xl shadow-lg shadow-orange-200 transition-all active:scale-95 flex items-center justify-center mt-2 lg:mt-0">
                        Rechercher
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Spacer Responsive --}}
    <div class="h-64 sm:h-52 lg:h-32"></div>

    <main class="mt-20 px-6 sm:px-10 md:px-16 lg:px-24 mb-20">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-10 gap-4">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-800">Destinations disponibles</h2>
                <div class="h-1.5 w-20 bg-orange-500 mt-3 rounded-full"></div>
            </div>
            <a href="#"
                class="text-orange-olten font-bold border-b-2 border-orange-olten pb-1 hover:text-orange-700 hover:border-orange-700 transition-all text-xs sm:text-sm w-fit">
                Voir tous les trajets
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

            <!-- Card Template (Identique à l'image) -->
            <div
                class="bg-white rounded-[32px] overflow-hidden card-custom-shadow border border-gray-50 group transition-transform hover:-translate-y-1">
                <!-- Image avec texte superposé -->
                <div class="relative h-60 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1499856871958-5b9627545d1a?auto=format&fit=crop&q=80&w=600"
                        alt="Paris" class="w-full h-full object-cover">
                    <!-- Overlay dégradé pour lisibilité -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                    <!-- Ville superposée -->
                    <div class="absolute bottom-5 left-6">
                        <h3 class="text-white font-bold text-xl flex items-center gap-2">
                            Paris <span class="text-orange-olten text-sm">→</span> Lyon
                        </h3>
                    </div>
                </div>

                <!-- Contenu sous l'image -->
                <div class="p-6">
                    <!-- Prix et Action -->
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Prix à partir
                                de</p>
                            <p class="text-xl font-extrabold text-slate-900">12,00 <span
                                    class="text-orange-olten">€</span></p>
                        </div>
                        <button
                            class="bg-[#111827] text-white w-12 h-12 rounded-2xl flex items-center justify-center hover:bg-orange-olten transition-colors">
                            <i class="fas fa-chevron-right text-sm"></i>
                        </button>
                    </div>

                    <div class="h-[1px] w-full bg-gray-100 mb-6"></div>

                    <!-- Utilisateurs et Note -->
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="flex -space-x-3">
                                <img src="https://randomuser.me/api/portraits/women/12.jpg"
                                    class="w-9 h-9 rounded-full border-2 border-white object-cover shadow-sm">
                                <img src="https://randomuser.me/api/portraits/men/32.jpg"
                                    class="w-9 h-9 rounded-full border-2 border-white object-cover shadow-sm">
                                <div
                                    class="w-9 h-9 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center text-[10px] font-bold text-gray-500 shadow-sm">
                                    +12
                                </div>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[11px] font-extrabold text-slate-900">14 trajets</span>
                                <span class="text-[9px] text-gray-400 font-medium">Conducteurs certifiés</span>
                            </div>
                        </div>

                        <!-- Badge Note -->
                        <div class="bg-green-100 text-green-600 px-3 py-1.5 rounded-lg flex items-center gap-1">
                            <span class="text-[11px] font-bold">4.8</span>
                            <i class="fa-solid fa-star text-[9px]"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2 (Marseille) -->
            <div
                class="bg-white rounded-[32px] overflow-hidden card-custom-shadow border border-gray-50 group transition-transform hover:-translate-y-1">
                <div class="relative h-60 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1549144511-f099e773c147?auto=format&fit=crop&q=80&w=600"
                        alt="Marseille" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                    <div class="absolute bottom-5 left-6">
                        <h3 class="text-white font-bold text-xl flex items-center gap-2">
                            Paris <span class="text-orange-olten text-sm">→</span> Marseille
                        </h3>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Prix à partir
                                de</p>
                            <p class="text-xl font-extrabold text-slate-900">24,00 <span
                                    class="text-orange-olten">€</span></p>
                        </div>
                        <button
                            class="bg-[#111827] text-white w-12 h-12 rounded-2xl flex items-center justify-center hover:bg-orange-olten transition-colors">
                            <i class="fas fa-chevron-right text-sm"></i>
                        </button>
                    </div>
                    <div class="h-[1px] w-full bg-gray-100 mb-6"></div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="flex -space-x-3">
                                <img src="https://randomuser.me/api/portraits/men/45.jpg"
                                    class="w-9 h-9 rounded-full border-2 border-white object-cover shadow-sm">
                                <img src="https://randomuser.me/api/portraits/women/65.jpg"
                                    class="w-9 h-9 rounded-full border-2 border-white object-cover shadow-sm">
                                <div
                                    class="w-9 h-9 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center text-[10px] font-bold text-gray-500 shadow-sm">
                                    +8</div>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[11px] font-extrabold text-slate-900">8 trajets</span>
                                <span class="text-[9px] text-gray-400 font-medium">Conducteurs certifiés</span>
                            </div>
                        </div>
                        <div class="bg-green-100 text-green-600 px-3 py-1.5 rounded-lg flex items-center gap-1">
                            <span class="text-[11px] font-bold">4.9</span>
                            <i class="fa-solid fa-star text-[9px]"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3 (Lille) -->
            <div
                class="bg-white rounded-[32px] overflow-hidden card-custom-shadow border border-gray-50 group transition-transform hover:-translate-y-1">
                <div class="relative h-60 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1511739001486-6bfe10ce785f?auto=format&fit=crop&q=80&w=600"
                        alt="Lille" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                    <div class="absolute bottom-5 left-6">
                        <h3 class="text-white font-bold text-xl flex items-center gap-2">
                            Paris <span class="text-orange-olten text-sm">→</span> Lille
                        </h3>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Prix à partir
                                de</p>
                            <p class="text-xl font-extrabold text-slate-900">10,50 <span
                                    class="text-orange-olten">€</span></p>
                        </div>
                        <button
                            class="bg-[#111827] text-white w-12 h-12 rounded-2xl flex items-center justify-center hover:bg-orange-olten transition-colors">
                            <i class="fas fa-chevron-right text-sm"></i>
                        </button>
                    </div>
                    <div class="h-[1px] w-full bg-gray-100 mb-6"></div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="flex -space-x-3">
                                <img src="https://randomuser.me/api/portraits/men/22.jpg"
                                    class="w-9 h-9 rounded-full border-2 border-white object-cover shadow-sm">
                                <div
                                    class="w-9 h-9 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center text-[10px] font-bold text-gray-500 shadow-sm">
                                    +5</div>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[11px] font-extrabold text-slate-900">22 trajets</span>
                                <span class="text-[9px] text-gray-400 font-medium">Conducteurs certifiés</span>
                            </div>
                        </div>
                        <div class="bg-green-100 text-green-600 px-3 py-1.5 rounded-lg flex items-center gap-1">
                            <span class="text-[11px] font-bold">4.7</span>
                            <i class="fa-solid fa-star text-[9px]"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Driver Banner: Responsive height and padding -->
        <div
            class="mt-20 sm:mt-28 relative rounded-[32px] sm:rounded-[40px] overflow-hidden bg-slate-900 min-h-[350px] sm:h-96 flex items-center">
            <img src="https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?auto=format&fit=crop&q=80&w=1200"
                alt="Conduite" class="absolute inset-0 w-full h-full object-cover opacity-40">
            <div class="relative z-10 px-8 sm:px-12 md:px-20 py-12">
                <span
                    class="inline-block bg-orange-500 text-white text-[10px] font-bold px-4 py-2 rounded-full w-fit mb-6 tracking-widest uppercase">
                    Offre conducteur
                </span>
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4 leading-tight">
                    Récupérez <span class="text-orange-olten">90 €</span> </br> par trajet.
                </h2>
                <p class="text-slate-300 max-w-xl mb-4 text-sm sm:text-base leading-relaxed">
                    Vous avez une voiture ? Faites-la travailler pour vous (et </br> pas l'inverse). Récupérez jusqu'à
                    90 € en
                    covoiturant sur </br> un trajet de 300 km avec 3 passagers.
                </p>
                <button
                    class="bg-white text-slate-900 font-bold py-4 px-8 sm:px-10 rounded-xl hover:bg-orange-500 hover:text-white transition-all w-fit shadow-xl">
                    Publier un trajet
                </button>
            </div>
        </div>

        <!-- Engagement Section -->
        <div class="mt-28">
            <div class="mb-14 text-center sm:text-left">
                <h2 class="text-3xl font-bold text-slate-800">Notre Engagement</h2>
                <div class="h-1.5 w-20 bg-orange-500 mt-3 rounded-full mx-auto sm:mx-0"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
                <!-- Feature 1 -->
                <div
                    class="bg-white border border-gray-100 p-8 rounded-[32px] shadow-sm hover:shadow-md transition-shadow">
                    <div
                        class="bg-blue-50 text-blue-500 w-14 h-14 rounded-2xl flex items-center justify-center mb-6 text-xl">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4 class="font-bold text-xl mb-3 text-slate-800">L'autonomie absolue</h4>
                    <p class="text-slate-500 text-sm leading-relaxed mb-6">
                        Libérez-vous des contraintes horaires. Voyagez ou publiez vos propres trajets grâce à notre
                        réseau étendu.
                    </p>
                    <a href="#"
                        class="text-[10px] font-bold text-slate-400 hover:text-orange-500 uppercase tracking-widest border-b border-transparent hover:border-orange-200 pb-1 transition-all">En
                        savoir plus</a>
                </div>

                <!-- Feature 2 -->
                <div
                    class="bg-white border border-gray-100 p-8 rounded-[32px] shadow-sm hover:shadow-md transition-shadow">
                    <div
                        class="bg-emerald-50 text-emerald-500 w-14 h-14 rounded-2xl flex items-center justify-center mb-6 text-xl">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="font-bold text-xl mb-3 text-slate-800">Le luxe de l'épargne</h4>
                    <p class="text-slate-500 text-sm leading-relaxed mb-6">
                        Économisez sans sacrifier le confort. Nos trajets premium au meilleur prix vous font redécouvrir
                        la route.
                    </p>
                    <a href="#"
                        class="text-[10px] font-bold text-slate-400 hover:text-orange-500 uppercase tracking-widest border-b border-transparent hover:border-orange-200 pb-1 transition-all">Meilleure
                        offre</a>
                </div>

                <!-- Feature 3 -->
                <div
                    class="bg-white border border-gray-100 p-8 rounded-[32px] shadow-sm hover:shadow-md transition-shadow">
                    <div
                        class="bg-orange-50 text-orange-500 w-14 h-14 rounded-2xl flex items-center justify-center mb-6 text-xl">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h4 class="font-bold text-xl mb-3 text-slate-800">Sérénité certifiée</h4>
                    <p class="text-slate-500 text-sm leading-relaxed mb-6">
                        Profils vérifiés et service client 24/7. Votre sécurité est notre priorité absolue sur chaque
                        trajet.
                    </p>
                    <a href="#"
                        class="text-[10px] font-bold text-slate-400 hover:text-orange-500 uppercase tracking-widest border-b border-transparent hover:border-orange-200 pb-1 transition-all">Réseau
                        certifié</a>
                </div>
            </div>
        </div>
    </main>

    <x-footer />

</body>

</html>
