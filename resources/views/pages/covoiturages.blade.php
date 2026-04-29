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

        input[type="date"]::-webkit-calendar-picker-indicator {
            background: transparent;
            bottom: 0;
            color: transparent;
            cursor: pointer;
            height: auto;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            width: auto;
            appearance: none;
            -webkit-appearance: none;
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

        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(48%) sepia(89%) saturate(1483%) hue-rotate(345deg) brightness(101%) contrast(101%);
            cursor: pointer;
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

<body class="bg-white">

    <x-header />


    <main>
        {{-- ===================== HERO SECTION ===================== --}}
        <section
            class="relative min-h-[600px] lg:h-[650px] flex flex-col justify-center px-4 sm:px-8 lg:px-12 pt-32 pb-48 lg:pb-32"
            style="background: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)),
                    url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&q=80&w=1920') center/cover no-repeat;">

            <div class="max-w-6xl mx-auto w-full">
                <!-- Badge -->
                <div class="flex items-center gap-3 mb-6 animate-fade-in">
                    <div class="h-[2px] w-8 sm:w-12 bg-orange-olten"></div>
                    <span class="text-white text-[10px] sm:text-xs uppercase tracking-[0.3em] font-extrabold">
                        Location de véhicules d'exception
                    </span>
                </div>

                <!-- Titre -->
                <h1 class="text-white text-4xl sm:text-6xl lg:text-7xl font-extrabold leading-[1.1] mb-6">
                    Louez votre<br>
                    <span class="text-orange-olten">liberté.</span>
                </h1>

                <!-- Desc -->
                <p class="text-gray-200 text-sm sm:text-base max-w-sm sm:max-w-md leading-relaxed opacity-90">
                    Découvrez une nouvelle façon de louer. Des véhicules premium, une sécurité totale et une expérience
                    sans compromis.
                </p>
            </div>

            {{-- ---- FORMULAIRE DE RECHERCHE RESPONSIVE ---- --}}
            <div
                class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 w-[calc(100%-2rem)] sm:w-[calc(100%-4rem)] max-w-6xl bg-white rounded-[24px] sm:rounded-[32px] shadow-2xl p-5 sm:p-8 z-20">
                <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-5 sm:mb-6">Rechercher un véhicule</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    <!-- Lieu de départ -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest ml-1">Lieu de
                            départ</label>
                        <div class="relative group">
                            <i
                                class="fa-solid fa-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-orange-olten opacity-50"></i>
                            <input type="text" placeholder="D'où partez-vous ?"
                                class="w-full bg-gray-50 border border-gray-100 rounded-xl sm:rounded-2xl py-3.5 sm:py-4 pl-11 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-orange-100 focus:border-orange-olten transition-all">
                        </div>
                    </div>

                    <!-- Lieu de fin -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest ml-1">Lieu de
                            fin</label>
                        <div class="relative group">
                            <i
                                class="fa-solid fa-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-orange-olten opacity-50"></i>
                            <input type="text" placeholder="Où allez-vous ?"
                                class="w-full bg-gray-50 border border-gray-100 rounded-xl sm:rounded-2xl py-3.5 sm:py-4 pl-11 pr-4 text-sm focus:outline-none">
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="sm:col-span-2 grid grid-cols-2 gap-3 sm:gap-4">
                        <div class="space-y-1.5">
                            <label
                                class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest ml-1">Départ</label>
                            <div class="relative">
                                <i
                                    class="fa-solid fa-calendar absolute left-3.5 sm:left-4 top-1/2 -translate-y-1/2 text-orange-olten opacity-50 scale-90 sm:scale-100"></i>
                                <input type="date"
                                    class="w-full bg-gray-50 border border-gray-100 rounded-xl sm:rounded-2xl py-3.5 sm:py-4 pl-10 sm:pl-11 pr-2 text-xs sm:text-sm focus:outline-none">
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label
                                class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest ml-1">Retour</label>
                            <div class="relative">
                                <i
                                    class="fa-solid fa-calendar absolute left-3.5 sm:left-4 top-1/2 -translate-y-1/2 text-orange-olten opacity-50 scale-90 sm:scale-100"></i>
                                <input type="date"
                                    class="w-full bg-gray-50 border border-gray-100 rounded-xl sm:rounded-2xl py-3.5 sm:py-4 pl-10 sm:pl-11 pr-2 text-xs sm:text-sm focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Bouton -->
                    <button
                        class="bg-orange-olten hover:bg-orange-600 text-white font-bold h-[52px] sm:h-[58px] rounded-xl sm:rounded-2xl shadow-lg shadow-orange-200 transition-all active:scale-95 flex items-center justify-center mt-2 lg:mt-0">
                        <span class="px-6">Rechercher</span>
                    </button>
                </div>
            </div>
        </section>

        {{-- Spacer Responsive --}}
        <div class="h-48 sm:h-40 lg:h-32"></div>

        {{-- ===================== TYPES DE VÉHICULES ===================== --}}
        <section class="max-w-6xl mx-auto px-6 py-12 sm:py-20">
            <div class="flex flex-col mb-10 sm:mb-12">
                <h2 class="text-2xl sm:text-4xl font-extrabold text-gray-900 mb-2">
                    Parcourez par type
                </h2>
                <div class="h-1 w-16 bg-orange-olten rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-8">
                <script>
                    const types = [{
                            icon: 'fa-car',
                            label: 'Voiture',
                            text: 'Une large gamme de citadines pour vos déplacements urbains.'
                        },
                        {
                            icon: 'fa-truck-pickup',
                            label: 'SUV',
                            text: 'Pour vos aventures tout terrain et un confort supérieur.'
                        },
                        {
                            icon: 'fa-van-shuttle',
                            label: 'Monospace',
                            text: 'L\'espace idéal pour toute la famille et vos bagages.'
                        },
                        {
                            icon: 'fa-truck',
                            label: 'Utilitaire',
                            text: 'Idéal pour vos déménagements et transports encombrants.'
                        },
                        {
                            icon: 'fa-motorcycle',
                            label: 'Moto',
                            text: 'Pour les passionnés de liberté et d\'agilité urbaine.'
                        },
                        {
                            icon: 'fa-caravan',
                            label: 'Camping-car',
                            text: 'L\'aventure sans limites avec tout le confort de la maison.'
                        }
                    ];

                    document.write(types.map(t => `
                        <div class="group p-6 sm:p-8 border border-gray-100 rounded-[24px] sm:rounded-[32px] bg-white card-shadow hover:border-orange-olten transition-all duration-300 cursor-pointer">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-gray-50 flex items-center justify-center text-gray-900 text-xl sm:text-2xl group-hover:bg-orange-olten group-hover:text-white transition-all duration-300 mb-6">
                                <i class="fa-solid ${t.icon}"></i>
                            </div>
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-2">${t.label}</h3>
                            <p class="text-xs sm:text-sm text-gray-400 leading-relaxed mb-6">${t.text}</p>
                            <div class="flex items-center gap-2 text-orange-olten font-bold text-[10px] sm:text-xs uppercase tracking-wider">
                                Voir les annonces <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </div>
                        </div>
                    `).join(''));
                </script>
            </div>
        </section>

        {{-- ===================== ANNONCES RÉCENTES ===================== --}}
        <section class="max-w-6xl mx-auto px-6 pb-20 sm:pb-32">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 sm:mb-12 gap-4">
                <div class="flex flex-col">
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-gray-900 mb-2">Annonces récentes</h2>
                    <div class="h-1 w-16 bg-orange-olten rounded-full"></div>
                </div>
                <a href="#"
                    class="text-orange-olten font-bold border-b-2 border-orange-olten pb-1 hover:text-orange-700 hover:border-orange-700 transition-all text-xs sm:text-sm w-fit">
                    Voir toutes les annonces
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                <script>
                    const annonces = [1, 2, 3];
                    document.write(annonces.map(a => `
                        <div class="bg-white rounded-[24px] sm:rounded-[32px] overflow-hidden card-shadow border border-gray-50 group">
                            <!-- Image -->
                            <div class="relative h-56 sm:h-64 overflow-hidden">
                                <img src="https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&q=80&w=800" 
                                     alt="BMW" 
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                <div class="absolute top-4 right-4 w-9 h-9 sm:w-10 sm:h-10 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white cursor-pointer hover:bg-white hover:text-red-500 transition-all">
                                    <i class="fa-regular fa-heart"></i>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="p-5 sm:p-6">
                                <div class="flex justify-between items-start mb-4 gap-2">
                                    <div class="min-w-0">
                                        <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 truncate">Peugeot 508</h3>
                                        <div class="flex items-center gap-1 mt-1">
                                            <i class="fa-solid fa-location-dot text-orange-olten text-[10px]"></i>
                                            <span class="text-[11px] font-semibold text-gray-400 truncate">Paris, France</span>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <span class="text-xl sm:text-2xl font-black text-gray-900">45€</span>
                                        <span class="text-gray-400 text-[10px] font-bold block uppercase">/ Jour</span>
                                    </div>
                                </div>

                                <!-- Features -->
                                <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2 scrollbar-hide">
                                    <div class="bg-gray-50 px-3 py-1.5 rounded-lg flex items-center gap-2 flex-shrink-0">
                                        <i class="fa-solid fa-user text-gray-400 text-[10px]"></i>
                                        <span class="text-[10px] font-bold text-gray-600">5 places</span>
                                    </div>
                                    <div class="bg-gray-50 px-3 py-1.5 rounded-lg flex items-center gap-2 flex-shrink-0">
                                        <i class="fa-solid fa-gears text-gray-400 text-[10px]"></i>
                                        <span class="text-[10px] font-bold text-gray-600">Auto</span>
                                    </div>
                                    <div class="bg-gray-50 px-3 py-1.5 rounded-lg flex items-center gap-2 flex-shrink-0">
                                        <i class="fa-solid fa-gauge text-gray-400 text-[10px]"></i>
                                        <span class="text-[10px] font-bold text-gray-600">250km/j</span>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="flex items-center justify-between pt-2">
                                    <div class="flex items-center gap-2.5">
                                        <img src="https://i.pravatar.cc/150?u=${a}" alt="User" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full border-2 border-white shadow-sm">
                                        <span class="text-xs sm:text-sm font-bold text-gray-900">Sylvain M.</span>
                                    </div>
                                    <button class="w-9 h-9 sm:w-10 sm:h-10 bg-[#0F172A] text-white rounded-lg sm:rounded-xl flex items-center justify-center hover:bg-orange-olten transition-all active:scale-90">
                                        <i class="fa-solid fa-chevron-right text-[10px] sm:text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join(''));
                </script>
            </div>
        </section>
    </main>
    <x-footer />

</body>

</html>
