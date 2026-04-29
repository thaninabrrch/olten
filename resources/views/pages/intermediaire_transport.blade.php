<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Olten-location.fr - Redéfinir vos trajets</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fff;
        }

        .orange-accent {
            color: #FF731D;
        }

        .bg-orange-olten {
            background-color: #FF731D;
        }

        /* Ombre douce identique à la maquette */
        .tab-shadow {
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
        }

        /* Effet de chevauchement (Overlap) */
        .overlap-container {
            margin-top: -120px;
            /* Remonte le bloc sur l'image */
            position: relative;
            z-index: 20;
        }

        @media (max-width: 768px) {
            .overlap-container {
                margin-top: -40px;
                /* Moins de chevauchement sur mobile */
            }
        }

        /* Transition fluide pour le changement d'onglet */
        .tab-content {
            transition: all 0.3s ease-in-out;
        }
    </style>
</head>

<body class="bg-white">
    <x-header />

    <main>
        <section class="relative w-full overflow-hidden bg-black" style="height: 70vh; min-height: 500px;">
            <div class="absolute inset-0">
                <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&q=80&w=2000"
                    alt="Hero" class="w-full h-full object-cover opacity-80">
                <div class="absolute inset-0 bg-gradient-to-r from-black/80 to-transparent"></div>
            </div>

            <div class="relative z-10 max-w-6xl mx-auto px-6 h-full flex flex-col justify-center">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-[2px] w-10 bg-orange-olten"></div>
                    <p class="text-white uppercase tracking-widest text-xs font-bold">
                        Mobilité Nouvelle Génération
                    </p>
                </div>
                <h1 class="text-white font-bold leading-[1.1] text-4xl md:text-6xl lg:text-7xl">
                    Redéfinir vos <br>
                    <span class="orange-accent">trajets.</span>
                </h1>
                <p class="text-gray-300 mt-6 text-sm md:text-base max-w-lg leading-relaxed">
                    Une plateforme intelligente pour le covoiturage et la location de véhicules d'exception.
                </p>
            </div>
        </section>

        <section class="max-w-4xl mx-auto px-4 overlap-container">
            <div class="bg-white rounded-[32px] tab-shadow overflow-hidden">

                <div class="flex p-2 gap-2">
                    <button onclick="switchTab('covoiturage')" id="btn-covoiturage"
                        class="flex-1 py-4 flex items-center justify-center gap-3 rounded-2xl transition-all duration-300 font-bold text-white bg-orange-olten">
                        <i class="fa-solid fa-car-side"></i>
                        <span>Covoiturage</span>
                    </button>
                    <button onclick="switchTab('location')" id="btn-location"
                        class="flex-1 py-4 flex items-center justify-center gap-3 rounded-2xl transition-all duration-300 font-bold text-gray-400 hover:bg-gray-50">
                        <i class="fa-solid fa-key"></i>
                        <span>Location</span>
                    </button>
                </div>

                <div id="content-covoiturage" class="p-6 md:p-10 block animate-fade-in">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Covoiturage Intelligent</h3>
                            <p class="text-gray-500 text-sm">Partagez vos frais et voyagez sereinement avec des membres
                                </br> certifiés de la communauté Olten.</p>
                        </div>
                        <div class="flex -space-x-3">
                            <img src="https://i.pravatar.cc/100?u=1"
                                class="w-10 h-10 rounded-full border-2 border-white">
                            <img src="https://i.pravatar.cc/100?u=2"
                                class="w-10 h-10 rounded-full border-2 border-white">
                            <img src="https://i.pravatar.cc/100?u=3"
                                class="w-10 h-10 rounded-full border-2 border-white">
                            <div
                                class="w-10 h-10 rounded-full border-2 border-white bg-gray-100 flex items-center justify-center text-[10px] font-bold text-gray-400">
                                +1k</div>
                        </div>
                    </div>

                    <div
                        class="bg-[#F9FAFB] rounded-2xl border border-gray-100 p-2 flex flex-col md:flex-row items-center gap-2">
                        <div class="flex-1 w-full px-4 py-2 border-b md:border-b-0 md:border-r border-gray-200">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Départ</label>
                            <p class="text-gray-900 font-bold">Paris, France</p>
                        </div>
                        <div class="flex-1 w-full px-4 py-2">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Destination</label>
                            <p class="text-gray-900 font-bold">Lyon, France</p>
                        </div>
                        <a href="{{ route('location.vehicule') }}"
                            class="inline-block w-full md:w-auto bg-orange-olten text-white font-bold py-4 px-8 rounded-xl hover:scale-105 transition-transform text-center">
                            Trouver un trajet
                        </a>
                    </div>
                </div>

                <div id="content-location" class="p-6 md:p-10 hidden animate-fade-in">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Location de Véhicule</h3>
                            <p class="text-gray-500 text-sm">Accédez à notre catalogue exclusif : des citadines </br>
                                électriques aux berlines de luxe pour vos besoins </br> spécifiques..</p>
                        </div>
                        <span
                            class="bg-blue-50 text-blue-500 text-[11px] font-bold px-4 py-2 rounded-full flex items-center gap-2">
                            <i class="fa-solid fa-circle-check"></i> Assurance tout risques incluse
                        </span>
                    </div>

                    <div
                        class="bg-[#F9FAFB] rounded-2xl border border-gray-100 p-2 flex flex-col md:flex-row items-center gap-2">
                        <div class="flex-1 w-full px-4 py-2 border-b md:border-b-0 md:border-r border-gray-200">
                            <label
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Récupération</label>
                            <p class="text-gray-900 font-bold">Aéroport Charles de Gaulle</p>
                        </div>
                        <div class="flex-1 w-full px-4 py-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Période</label>
                            <p class="text-gray-900 font-bold">3 Jours • Ven 12 - Dim 14</p>
                        </div>
                        <a href="{{ route('covoiturages') }}"
                            class="w-full sm:w-auto bg-[#1E293B] hover:bg-black
          text-white font-bold
          py-3 md:py-4 px-5 md:px-8
          rounded-xl transition-all
          text-sm md:text-base whitespace-nowrap">
                            Voir les véhicules
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="max-w-6xl mx-auto px-6 py-24 flex flex-col md:flex-row items-center gap-16">
            <div class="flex-1 order-2 md:order-1">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-12">
                    Pourquoi faire confiance à <br><span class="orange-accent">Olten Location</span> ?
                </h2>

                <div class="space-y-8">
                    <div class="flex gap-6 items-start">
                        <div
                            class="w-12 h-12 shrink-0 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-500">
                            <i class="fa-solid fa-shield-halved text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-1">Sécurité & Vérification</h4>
                            <p class="text-gray-500 text-sm leading-relaxed">Chaque conducteur et chaque véhicule passe
                                par un processus de certification rigoureux..</p>
                        </div>
                    </div>
                    <div class="flex gap-6 items-start">
                        <div
                            class="w-12 h-12 shrink-0 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-500">
                            <i class="fa-solid fa-credit-card text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-1">Paiement Sécurisé</h4>
                            <p class="text-gray-500 text-sm leading-relaxed">Transactions protégées et remboursement
                                garanti en cas d'annulation justifiée.
                                garanti.</p>
                        </div>
                    </div>
                    <div class="flex gap-6 items-start">
                        <div
                            class="w-12 h-12 shrink-0 bg-green-50 rounded-2xl flex items-center justify-center text-green-500">
                            <i class="fa-solid fa-leaf text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-1">Engagement Éco-responsable</h4>
                            <p class="text-gray-500 text-sm leading-relaxed">Nous favorisons les trajets partagés et les
                                véhicules à faible émission.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex-1 order-1 md:order-2">
                <div
                    class="relative rounded-[40px] overflow-hidden shadow-2xl md:rotate-2 hover:rotate-0 transition-transform duration-500">
                    <img src="https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?auto=format&fit=crop&q=80&w=1200"
                        alt="Conduite" class="w-full h-[400px] md:h-[550px] object-cover">
                </div>
            </div>
        </section>
    </main>

    <x-footer />

    <script>
        function switchTab(type) {
            const btnCov = document.getElementById('btn-covoiturage');
            const btnLoc = document.getElementById('btn-location');
            const contentCov = document.getElementById('content-covoiturage');
            const contentLoc = document.getElementById('content-location');

            const activeBtn =
                "flex-1 py-4 flex items-center justify-center gap-3 rounded-2xl transition-all duration-300 font-bold text-white bg-orange-olten shadow-lg shadow-orange-500/20";
            const inactiveBtn =
                "flex-1 py-4 flex items-center justify-center gap-3 rounded-2xl transition-all duration-300 font-bold text-gray-400 hover:bg-gray-50";

            if (type === 'covoiturage') {
                btnCov.className = activeBtn;
                btnLoc.className = inactiveBtn;
                contentCov.classList.remove('hidden');
                contentLoc.classList.add('hidden');
            } else {
                btnLoc.className = activeBtn;
                btnCov.className = inactiveBtn;
                contentLoc.classList.remove('hidden');
                contentCov.classList.add('hidden');
            }
        }
    </script>
</body>

</html>
