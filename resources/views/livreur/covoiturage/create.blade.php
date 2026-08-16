@extends('layouts.connected')

@section('title', 'Carte VTC | ' . config('app.name'))

<!-- Leaflet & Tailwind (Note: En production, mettez-les dans votre layout ou via Vite) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .app-content-area {
        height: calc(100vh - 64px);
        display: flex;
        overflow: hidden;
    }

    .step-view {
        display: none;
        width: 100%;
        height: 100%;
    }

    .step-view.active {
        display: flex;
    }

    .map-container-traj {
        flex: 1;
        height: 100%;
        position: relative;
        z-index: 1;
    }

    .sidebar-panel {
        width: 400px;
        background: white;
        height: 100%;
        overflow-y: auto;
        border-right: 1px solid #e2e8f0;
        z-index: 10;
        display: flex;
        flex-direction: column;
    }

    .sidebar-panel::-webkit-scrollbar {
        width: 5px;
    }

    .sidebar-panel::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .sidebar-panel::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    /* Panel droit décoratif (pages sans carte) */
    .right-showcase {
        flex: 1;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .right-showcase::before {
        content: '';
        position: absolute;
        top: -120px;
        right: -120px;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255, 60, 0, 0.03) 0%, transparent 70%);
        border-radius: 50%;
    }

    .route-option {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        border: 2px solid #f1f5f9;
    }

    .route-option:hover {
        border-color: #cbd5e1;
    }

    .route-option.selected {
        border-color: #ff3c00;
        background-color: #fff7f5;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 60, 0, 0.1);
    }

    .autocomplete-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border-radius: 0 0 12px 12px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        z-index: 50;
        max-height: 250px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
    }

    .autocomplete-item {
        padding: 12px 16px;
        cursor: pointer;
        font-size: 0.9rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .autocomplete-item:hover {
        background: #f8fafc;
        color: #ff3c00;
    }

    .marker-pulse {
        width: 12px;
        height: 12px;
        background: #ff3c00;
        border-radius: 50%;
        box-shadow: 0 0 0 rgba(255, 60, 0, 0.4);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 60, 0, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(255, 60, 0, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(255, 60, 0, 0);
        }
    }

    .card-option {
        border: 2px solid #f1f5f9;
        border-radius: 1.25rem;
        padding: 1.5rem;
        background: #fff;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }

    .card-option:hover {
        border-color: #cbd5e1;
    }

    .card-option.selected {
        border-color: #ff3c00;
        background-color: #fff7f5;
    }

    .input-price {
        border: none;
        background: transparent;
        font-size: 1.25rem;
        font-weight: 900;
        width: 50px;
        text-align: right;
        outline: none;
        color: #ff3c00;
    }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 22px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e2e8f0;
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: #ff3c00;
    }

    input:checked+.slider:before {
        transform: translateX(22px);
    }

    .popin-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 100;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .popin-overlay.active {
        display: flex;
    }

    .pricing-segment {
        position: relative;
        padding-left: 2rem;
    }

    .pricing-segment::before {
        content: '';
        position: absolute;
        left: 0.75rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
        border-radius: 1px;
    }

    .radio-custom:checked+.radio-card {
        border-color: #ff3c00;
        background-color: #fffaf9;
    }

    .radio-custom:checked+.radio-card .check-icon {
        opacity: 1;
        transform: scale(1);
    }

    .radio-custom:checked+.radio-card .label-text {
        color: #0f172a;
    }

    .manual-stop-item {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .manual-stop-item .delete-btn {
        opacity: 0;
        transition: opacity 0.2s;
    }

    .manual-stop-item:hover .delete-btn {
        opacity: 1;
    }

    .popin-autocomplete-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border-radius: 0 0 12px 12px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        z-index: 200;
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
    }

    .popin-autocomplete-results .autocomplete-item {
        padding: 12px 16px;
        cursor: pointer;
        font-size: 0.9rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .popin-autocomplete-results .autocomplete-item:hover {
        background: #f8fafc;
        color: #ff3c00;
    }

    .drag-handle {
        cursor: grab;
    }

    .drag-handle:active {
        cursor: grabbing;
    }

    .sortable-ghost {
        opacity: 0.4;
        background: #fff7f5;
    }

    /* Floating animation for testimonial cards */
    @keyframes floatCard {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-8px);
        }
    }

    .animate-float {
        animation: floatCard 4s ease-in-out infinite;
    }

    .animate-float-delayed {
        animation: floatCard 4s ease-in-out 1.5s infinite;
    }
</style>

@section('content')
    <div class="app-content-area bg-white">

        <!-- ========== POPIN AJOUT ESCALE ALLER ========== -->
        <div id="popin-add-stop" class="popin-overlay">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 relative">
                <button onclick="closeStopPopin('aller')"
                    class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
                <div class="mb-6">
                    <div
                        class="w-14 h-14 bg-orange-100 rounded-2xl flex items-center justify-center text-[#ff3c00] text-2xl mb-4">
                        <i class="fa-solid fa-map-pin"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900">Ajouter une escale</h3>
                    <p class="text-slate-500 text-sm mt-1 font-medium">Recherchez une ville ou une adresse précise.</p>
                </div>
                <div class="relative">
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input id="popin-stop-input" type="text" placeholder="Ex: Lyon, Marseille, Aix-en-Provence..."
                            class="w-full pl-11 pr-4 py-4 rounded-xl border border-slate-200 focus:border-[#ff3c00] focus:ring-2 focus:ring-orange-100 transition-all outline-none font-medium"
                            autocomplete="off">
                    </div>
                    <div id="popin-stop-results" class="popin-autocomplete-results hidden"></div>
                </div>
                <div id="popin-stop-selected" class="mt-4 hidden">
                    <div class="flex items-center gap-3 p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                        <i class="fa-solid fa-circle-check text-emerald-500"></i>
                        <span id="popin-stop-selected-name" class="font-bold text-slate-800 flex-1"></span>
                        <button onclick="clearPopinSelection('aller')" class="text-slate-400 hover:text-red-500">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
                <button id="popin-stop-confirm" onclick="confirmManualStop('aller')" disabled
                    class="w-full mt-6 py-4 bg-slate-900 text-white rounded-xl font-bold text-lg disabled:opacity-30 disabled:cursor-not-allowed hover:bg-[#ff3c00] transition-all">
                    Ajouter cette escale
                </button>
            </div>
        </div>

        <!-- ========== POPIN AJOUT ESCALE RETOUR ========== -->
        <div id="popin-add-return-stop" class="popin-overlay">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 relative">
                <button onclick="closeStopPopin('retour')"
                    class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
                <div class="mb-6">
                    <div
                        class="w-14 h-14 bg-orange-100 rounded-2xl flex items-center justify-center text-[#ff3c00] text-2xl mb-4">
                        <i class="fa-solid fa-map-pin"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900">Escale retour</h3>
                    <p class="text-slate-500 text-sm mt-1 font-medium">Ajoutez un point de passage pour le trajet retour.
                    </p>
                </div>
                <div class="relative">
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input id="popin-return-stop-input" type="text"
                            placeholder="Ex: Lyon, Marseille, Aix-en-Provence..."
                            class="w-full pl-11 pr-4 py-4 rounded-xl border border-slate-200 focus:border-[#ff3c00] focus:ring-2 focus:ring-orange-100 transition-all outline-none font-medium"
                            autocomplete="off">
                    </div>
                    <div id="popin-return-stop-results" class="popin-autocomplete-results hidden"></div>
                </div>
                <div id="popin-return-stop-selected" class="mt-4 hidden">
                    <div class="flex items-center gap-3 p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                        <i class="fa-solid fa-circle-check text-emerald-500"></i>
                        <span id="popin-return-stop-selected-name" class="font-bold text-slate-800 flex-1"></span>
                        <button onclick="clearPopinSelection('retour')" class="text-slate-400 hover:text-red-500">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
                <button id="popin-return-stop-confirm" onclick="confirmManualStop('retour')" disabled
                    class="w-full mt-6 py-4 bg-slate-900 text-white rounded-xl font-bold text-lg disabled:opacity-30 disabled:cursor-not-allowed hover:bg-[#ff3c00] transition-all">
                    Ajouter cette escale
                </button>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- ÉTAPE 1: ITINÉRAIRE                                    -->
        <!-- ===================================================== -->
        <section id="view-itinerary" class="step-view active">
            <div class="sidebar-panel p-6">
                <div class="mb-8">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-2">Déstnation</span>
                    <h1 class="text-2xl font-black text-slate-800">Planifiez votre <span
                            class="text-[#ff3c00]">trajet</span></h1>
                    <p class="text-sm text-slate-500 mt-2 font-medium">Indiquez votre point de départ et votre destination.
                    </p>

                </div>

                <div class="space-y-6 flex-1">
                    <div class="relative">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2 tracking-widest">Lieu de
                            départ</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-emerald-500"><i
                                    class="fa-solid fa-location-dot"></i></span>
                            <input id="input-start" type="text" placeholder="Entrez une ville ou adresse..."
                                class="w-full pl-11 pr-4 py-4 rounded-xl border border-slate-200 focus:border-[#ff3c00] focus:ring-2 focus:ring-orange-100 transition-all outline-none font-medium">
                        </div>
                        <div id="start-results" class="autocomplete-results hidden"></div>
                    </div>

                    <div class="relative">
                        <label
                            class="block text-xs font-bold text-slate-500 uppercase mb-2 tracking-widest">Destination</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#ff3c00]"><i
                                    class="fa-solid fa-flag-checkered"></i></span>
                            <input id="input-end" type="text" placeholder="Où allez-vous ?"
                                class="w-full pl-11 pr-4 py-4 rounded-xl border border-slate-200 focus:border-[#ff3c00] focus:ring-2 focus:ring-orange-100 transition-all outline-none font-medium">
                        </div>
                        <div id="end-results" class="autocomplete-results hidden"></div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100">
                    <button id="btn-next" disabled
                        class="w-full py-4 bg-slate-900 text-white rounded-xl font-bold text-lg shadow-xl shadow-slate-200 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-black transition-all">
                        Calculer l'itinéraire
                    </button>
                </div>
            </div>
            <div class="map-container-traj">
                <div id="map" class="h-full w-full"></div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- ÉTAPE 2: CHOIX DE LA ROUTE                             -->
        <!-- ===================================================== -->
        <section id="view-route" class="step-view">
            <div class="sidebar-panel p-6">
                <button onclick="changeView('view-itinerary')"
                    class="mb-6 text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-chevron-left text-xs"></i> Modifier les adresses
                </button>
                <div class="mb-6">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-2">Route</span>
                    <h2 class="text-2xl font-black text-slate-800">Choisissez votre <span class="text-[#ff3c00]">route</span></h2>
                </div>
                <div id="routes-list" class="space-y-4 flex-1 overflow-y-auto pr-2"></div>
                <div class="pt-6 border-t border-slate-100">
                    <button id="btn-validate-route"
                        class="w-full py-4 bg-slate-900 text-white rounded-xl font-bold text-lg hover:bg-[#ff3c00] transition-all">
                        Confirmer la route
                    </button>
                </div>
            </div>
            <div class="map-container-traj">
                <div id="map-route" class="h-full w-full"></div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- ÉTAPE 3: ÉTAPES INTERMÉDIAIRES (ALLER)                 -->
        <!-- ===================================================== -->
        <section id="view-steps" class="step-view">
            <div class="sidebar-panel p-6">
                <button onclick="changeView('view-route')"
                    class="mb-6 text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-chevron-left text-xs"></i> Retour aux routes
                </button>
                <div class="mb-6">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-2">Éscales</span>
                    <h2 class="text-2xl font-black text-slate-800">Points de <span class="text-[#ff3c00]">passage</span>
                    </h2>
                    <p class="text-sm text-slate-500 mt-2 font-medium">Sélectionnez les villes détectées ou ajoutez vos
                        propres escales.</p>
                </div>

                <div class="mb-4">
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-3">
                        <i class="fa-solid fa-wand-magic-sparkles mr-1 text-[#ff3c00]"></i> Détectées sur la route
                    </p>
                    <div id="intermediate-cities" class="space-y-3"></div>
                </div>

                <div class="flex items-center gap-3 my-5">
                    <div class="flex-1 h-px bg-slate-200"></div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Escales manuelles</span>
                    <div class="flex-1 h-px bg-slate-200"></div>
                </div>

                <div id="manual-stops-list" class="space-y-3 mb-4"></div>

                <button onclick="openStopPopin('aller')"
                    class="w-full py-3 border-2 border-dashed border-slate-200 rounded-xl text-slate-500 font-bold hover:border-[#ff3c00] hover:text-[#ff3c00] transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus"></i> Ajouter une escale
                </button>

                <div class="pt-6 border-t border-slate-100 mt-6">
                    <button id="btn-confirm-steps"
                        class="w-full py-4 bg-slate-900 text-white rounded-xl font-bold text-lg hover:bg-[#ff3c00] transition-all">
                        Générer le récapitulatif
                    </button>
                </div>
            </div>
            <div class="map-container-traj">
                <div id="map-steps" class="h-full w-full"></div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- ÉTAPE 4: RÉCAPITULATIF                                 -->
        <!-- ===================================================== -->
        <section id="view-summary" class="step-view">
            <div class="sidebar-panel p-6">
                <div class="mb-8">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-3">Récapitulatif</span>
                    <h2 class="text-2xl font-black text-slate-800">Finalisez votre <span
                            class="text-[#ff3c00]">mission</span></h2>
                    <p class="text-sm text-slate-500 mt-2 font-medium">Vérifiez les détails de votre itinéraire avant de
                        publier.</p>
                </div>

                <div class="flex-1 space-y-4">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <p class="text-[10px] font-black uppercase text-slate-400 mb-3 tracking-widest">Détails du trajet
                        </p>
                        <ul id="selected-steps"
                            class="space-y-4 relative before:content-[''] before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-[2px] before:bg-slate-200">
                        </ul>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 space-y-3">
                    <button onclick="changeView('view-steps')"
                        class="w-full py-3 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition-all">
                        <i class="fa-solid fa-pen mr-2 text-xs"></i> Modifier
                    </button>
                    <button id="btn-final" onclick="changeView('view-datetime')"
                        class="w-full py-4 bg-[#ff3c00] text-white rounded-xl font-bold text-lg">
                        Continuer
                    </button>
                </div>
            </div>
            <div class="map-container-traj">
                <div id="map-summary" class="h-full w-full"></div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- DATE & HEURE DE DÉPART                                 -->
        <!-- ===================================================== -->
        <section id="view-datetime" class="step-view">
            <div class="sidebar-panel p-8">
                <button onclick="changeView('view-summary')"
                    class="mb-6 text-slate-400 font-bold flex items-center gap-2 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Itinéraire
                </button>

                <div class="mb-2">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-3">Horaires</span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 mb-2">Quand <span class="text-[#ff3c00]">partez-vous</span>
                    ?</h2>
                <p class="text-slate-500 mb-10 font-medium">Indiquez simplement la date et l'heure de votre départ.</p>

                <div class="space-y-8 flex-1">
                    <div class="space-y-3">
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest">Date de
                            départ</label>
                        <div
                            class="flex items-center gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-100 focus-within:border-[#ff3c00] transition-colors">
                            <i class="fa-solid fa-calendar text-[#ff3c00] text-xl"></i>
                            <input type="date" id="input-date" class="bg-transparent text-xl font-bold text-slate-900 w-full outline-none" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="space-y-3">
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest">Heure de
                            rendez-vous</label>
                        <div
                            class="flex items-center gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-100 focus-within:border-[#ff3c00] transition-colors">
                            <i class="fa-solid fa-clock text-[#ff3c00] text-xl"></i>
                            <input type="time" id="input-time"
                                class="bg-transparent text-xl font-bold text-slate-900 w-full outline-none"
                                value="08:30">
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-100">
                    <button onclick="changeView('view-datetimeed')"
                        class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black hover:bg-black transition-all shadow-xl text-lg">Suivant</button>
                </div>
            </div>

            <div class="right-showcase hidden md:flex">
                <div class="max-w-md relative z-10">
                    <div class="text-[#ff3c00] text-6xl mb-8">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <h3 class="text-4xl font-black text-slate-900 mb-6 uppercase tracking-tighter">
                        Rapide et <span class="text-[#ff3c00]">efficace</span>.
                    </h3>
                    <div class="w-16 h-1 bg-[#ff3c00] mx-auto mb-8"></div>
                    <p class="text-slate-500 text-lg font-medium leading-relaxed">
                        Pas de fioritures, allez droit à l'essentiel pour publier votre
                        <span class="text-slate-900 font-black">annonce en moins de 2 minutes</span>.
                    </p>
                    <div class="mt-12 text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">
                        Simple • Direct • Instantané
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- PASSAGERS ET OPTIONS                                   -->
        <!-- ===================================================== -->
        <section id="view-datetimeed" class="step-view">
            <div class="sidebar-panel p-8">
                <button onclick="changeView('view-summary')"
                    class="mb-6 text-slate-400 font-bold flex items-center gap-2 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Résumé
                </button>

                <div class="mb-2">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-3">Passagers</span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 mb-2">Qui <span class="text-[#ff3c00]">voyage</span> ?</h2>
                <p class="text-slate-500 mb-8 font-medium">Configurez le confort et la sécurité de votre trajet.</p>

                <div class="space-y-8 flex-1">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Places
                            disponibles</label>
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="font-bold text-slate-700">Nombre de passagers</span>
                            <div class="flex items-center gap-6">
                                <button onclick="updateQty('passengers', -1)"
                                    class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center hover:bg-slate-100 transition-all shadow-sm">
                                    <i class="fa-solid fa-minus text-sm"></i>
                                </button>
                                <span id="qty-passengers" class="text-2xl font-black text-slate-900">3</span>
                                <button onclick="updateQty('passengers', 1)"
                                    class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center hover:bg-slate-100 transition-all shadow-sm">
                                    <i class="fa-solid fa-plus text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Préférences
                            de trajet</label>
                        <div class="space-y-3">
                            <label class="relative cursor-pointer block">
                                <input type="radio" name="passengerMode" value="mixed" checked
                                    class="radio-custom hidden">
                                <div
                                    class="radio-card flex items-center justify-between p-5 rounded-xl border border-slate-100 bg-white transition-all duration-200">
                                    <span class="label-text font-bold text-slate-500 transition-colors">Mixte (Tout le
                                        monde)</span>
                                    <i
                                        class="fa-solid fa-circle-check check-icon text-[#ff3c00] opacity-0 scale-75 transition-all duration-200"></i>
                                </div>
                            </label>
                            <label class="relative cursor-pointer block">
                                <input type="radio" name="passengerMode" value="womenOnly"
                                    class="radio-custom hidden">
                                <div
                                    class="radio-card flex items-center justify-between p-5 rounded-xl border border-slate-100 bg-white transition-all duration-200">
                                    <span class="label-text font-bold text-slate-500 transition-colors">Entre femmes
                                        uniquement</span>
                                    <i
                                        class="fa-solid fa-circle-check check-icon text-[#ff3c00] opacity-0 scale-75 transition-all duration-200"></i>
                                </div>
                            </label>
                            <label class="relative cursor-pointer block">
                                <input type="radio" name="passengerMode" value="maxBackSeats"
                                    class="radio-custom hidden">
                                <div
                                    class="radio-card flex items-center justify-between p-5 rounded-xl border border-slate-100 bg-white transition-all duration-200">
                                    <span class="label-text font-bold text-slate-500 transition-colors">Maximum 2 places à
                                        l'arrière</span>
                                    <i
                                        class="fa-solid fa-circle-check check-icon text-[#ff3c00] opacity-0 scale-75 transition-all duration-200"></i>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-100">
                    <button onclick="changeView('view-booking')"
                        class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black hover:bg-black transition-all shadow-xl">Continuer</button>
                </div>
            </div>

            <div class="right-showcase hidden md:flex">
                <div class="max-w-md relative z-10">
                    <div class="text-[#ff3c00] text-6xl mb-8">
                        <i class="fa-solid fa-car-side"></i>
                    </div>
                    <h3 class="text-4xl font-black text-slate-900 mb-6 uppercase tracking-tighter">
                        Trajet <span class="text-[#ff3c00]">presque</span> prêt.
                    </h3>
                    <div class="w-16 h-1 bg-[#ff3c00] mx-auto mb-8"></div>
                    <p class="text-slate-500 text-lg font-medium leading-relaxed">
                        Définissez vos propres règles de voyage pour assurer un
                        <span class="text-slate-900 font-black">trajet serein et en toute sécurité</span>.
                    </p>
                    <div class="mt-12 text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">
                        Confiance • Confort • Contrôle
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- RÉSERVATION                                            -->
        <!-- ===================================================== -->
        <section id="view-booking" class="step-view">
            <div class="sidebar-panel p-8">
                <button onclick="changeView('view-datetimeed')"
                    class="mb-6 text-slate-400 font-bold flex items-center gap-2 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Passagers
                </button>

                <div class="mb-2">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-3">Réservation</span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 mb-6">Mode de <span
                        class="text-[#ff3c00]">réservation</span></h2>

                <div class="space-y-4 flex-1">
                    <div onclick="selectBooking(this)" data-mode="instant" class="card-option selected">
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center text-[#ff3c00] text-xl">
                                <i class="fa-solid fa-bolt"></i>
                            </div>
                            <div
                                class="bg-orange-500 text-white text-[10px] font-black px-2 py-1 rounded uppercase tracking-tighter">
                                Recommandé</div>
                        </div>
                        <h3 class="font-black text-lg mb-1">Réservation instantanée</h3>
                        <p class="text-slate-500 text-sm leading-snug">Attirez jusqu'à 2x plus de passagers. Les
                            réservations sont validées automatiquement.</p>
                    </div>

                    <div onclick="selectBooking(this)" data-mode="manual" class="card-option">
                        <div class="flex items-center mb-4">
                            <div
                                class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-500 text-xl">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                        </div>
                        <h3 class="font-black text-lg mb-1">Validation manuelle</h3>
                        <p class="text-slate-500 text-sm leading-snug">Vous consultez chaque demande avant qu'elle n'expire
                            pour accepter ou refuser.</p>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-100">
                    <button onclick="goToPricing()"
                        class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black hover:bg-black transition-all">
                        Valider mon choix
                    </button>
                </div>
            </div>

            <div class="right-showcase hidden md:flex relative overflow-hidden">
                <div class="absolute top-0 right-0 w-1/2 h-full bg-slate-50/30 -skew-x-12 translate-x-20"></div>
                <div class="max-w-xl w-full max-h-[90vh] relative z-10 flex flex-col justify-center">
                    <header class="mb-8">
                        <div
                            class="inline-block bg-[#ff3c00] text-white text-[9px] font-black px-3 py-1 uppercase tracking-[0.3em] mb-4">
                            Expertise</div>
                        <h2
                            class="text-4xl lg:text-5xl font-black text-slate-900 uppercase tracking-tighter leading-[0.95]">
                            Pourquoi l'<span class="text-[#ff3c00]">instantané</span> ?
                        </h2>
                    </header>
                    <div class="grid grid-cols-1 gap-8">
                        <div class="group border-l-4 border-[#ff3c00] pl-6 py-1">
                            <span class="text-[#ff3c00] font-black text-[10px] uppercase tracking-[0.3em] mb-1 block">01.
                                Visibilité</span>
                            <h4 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-2">Maximisez votre
                                impact</h4>
                            <p class="text-slate-500 text-sm lg:text-base font-medium leading-relaxed max-w-md">
                                Un badge distinctif attire l'attention. Les annonces instantanées sont <span
                                    class="text-slate-900 font-bold underline decoration-[#ff3c00] decoration-2">priorisées</span>.
                            </p>
                        </div>
                        <div
                            class="group border-l-4 border-slate-100 hover:border-[#ff3c00] pl-6 py-1 transition-colors duration-500">
                            <span
                                class="text-slate-300 font-black text-[10px] uppercase tracking-[0.3em] mb-1 block group-hover:text-[#ff3c00]">02.
                                Simplicité</span>
                            <h4 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-2">Automatisation
                                totale</h4>
                            <p class="text-slate-500 text-sm lg:text-base font-medium leading-relaxed max-w-md">
                                Gagnez du temps. Notre algorithme gère les réservations <span
                                    class="text-slate-900 font-bold">24h/24</span> sans votre intervention.
                            </p>
                        </div>
                    </div>
                    <div class="mt-10 pt-6 border-t border-slate-50 flex items-center gap-4">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 border-2 border-white bg-slate-200 rounded-full"></div>
                            <div class="w-8 h-8 border-2 border-white bg-slate-300 rounded-full"></div>
                            <div
                                class="w-8 h-8 border-2 border-white bg-[#ff3c00] rounded-full flex items-center justify-center text-white text-[9px] font-black">
                                +5k</div>
                        </div>
                        <p
                            class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-tight max-w-[180px]">
                            Rejoint par la communauté des conducteurs certifiés</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- PRIX ALLER                                             -->
        <!-- ===================================================== -->
        <section id="view-pricing" class="step-view">
            <div class="sidebar-panel p-8">
                <button onclick="changeView('view-booking')"
                    class="mb-6 text-slate-400 font-bold flex items-center gap-2 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Réservation
                </button>
                <div class="mb-2">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-3">Tarification</span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 mb-2">Fixez vos <span class="text-[#ff3c00]">prix</span>
                </h2>
                <p class="text-slate-500 mb-8 font-medium">Prix par passager pour chaque étape.</p>

                <div id="pricing-steps-container" class="space-y-6 flex-1 overflow-y-auto"></div>

                <div class="pt-8 border-t border-slate-100">
                    <button onclick="changeView('view-final')"
                        class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black">Continuer</button>
                </div>
            </div>

            <div class="right-showcase hidden md:flex">
                <div class="max-w-md w-full relative z-10">
                    <div class="mb-8 p-10 bg-white rounded-[2.5rem] shadow-xl border border-slate-100">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Prix total suggéré</p>
                        <div class="text-6xl font-black text-slate-900 mb-2">45<span
                                class="text-[#ff3c00] ml-1 text-4xl">€</span></div>
                        <div class="flex items-center justify-center gap-2 text-emerald-500 font-black text-sm">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            <span>Optimisé par notre algorithme</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- RETOUR ET PUBLICATION                                  -->
        <!-- ===================================================== -->
        <section id="view-final" class="step-view">
            <div class="sidebar-panel p-8">
                <button onclick="changeView('view-pricing')"
                    class="mb-6 text-slate-400 font-bold flex items-center gap-2 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Tarification
                </button>
                <div class="mb-2">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-3">Dernière
                        étape</span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 mb-2">Trajet <span class="text-[#ff3c00]">retour</span> ?
                </h2>
                <p class="text-slate-500 mb-6 font-medium">Souhaitez-vous proposer un trajet retour ?</p>

                <div class="space-y-4 flex-1">
                    <div onclick="selectReturn(this, true)" class="card-option">
                        <div
                            class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600 text-xl mb-4">
                            <i class="fa-solid fa-rotate-left"></i>
                        </div>
                        <h3 class="font-black text-lg mb-1">Oui, avec plaisir !</h3>
                        <p class="text-slate-500 text-sm leading-snug">Générez automatiquement le trajet inverse pour
                            maximiser vos revenus.</p>
                    </div>

                    <div onclick="selectReturn(this, false)" class="card-option">
                        <div
                            class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-500 text-xl mb-4">
                            <i class="fa-solid fa-xmark"></i>
                        </div>
                        <h3 class="font-black text-lg mb-1">Non, merci</h3>
                        <p class="text-slate-500 text-sm leading-snug">Je souhaite uniquement publier le trajet aller pour
                            le moment.</p>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-100">
                    <button id="btn-final-action"
                        class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black hover:bg-black transition-all shadow-xl">
                        Publier le projet
                    </button>
                </div>
            </div>

            <div class="right-showcase hidden md:flex">
                <div class="max-w-md w-full relative z-10">
                    <div
                        class="w-32 h-32 bg-slate-50 rounded-[2.5rem] mx-auto flex items-center justify-center text-5xl mb-10 shadow-sm border border-slate-100/50">
                        <i class="fa-solid fa-paper-plane text-[#ff3c00]"></i>
                    </div>
                    <h2 class="text-5xl font-black text-slate-900 mb-6 tracking-tight leading-none uppercase">
                        Prêt à <span class="text-[#ff3c00]">décoller</span> ?
                    </h2>
                    <p class="text-slate-400 text-lg font-medium leading-relaxed">
                        Votre annonce est complète et prête à être vue par la communauté.
                    </p>
                    <div class="mt-12 flex justify-center gap-2">
                        <div class="w-1 h-1 rounded-full bg-slate-200"></div>
                        <div class="w-12 h-1 rounded-full bg-slate-100"></div>
                        <div class="w-1 h-1 rounded-full bg-slate-200"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- DATE & HEURE DU RETOUR                                 -->
        <!-- ===================================================== -->
        <section id="view-return-datetime" class="step-view">
            <div class="sidebar-panel p-8">
                <button onclick="changeView('view-final')"
                    class="mb-6 text-slate-400 font-bold flex items-center gap-2 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Retour
                </button>

                <div class="mb-2">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-3">Retour</span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 mb-2">Quand <span class="text-[#ff3c00]">rentrez-vous</span>
                    ?</h2>
                <p class="text-slate-500 mb-10 font-medium">Indiquez la date et l'heure de votre trajet retour.</p>

                <div class="space-y-8 flex-1">
                    <div class="space-y-3">
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest">Date de
                            retour</label>
                        <div
                            class="flex items-center gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-100 focus-within:border-[#ff3c00] transition-colors">
                            <i class="fa-solid fa-calendar text-[#ff3c00] text-xl"></i>
                            <input type="date" id="return-date"
                                class="bg-transparent text-xl font-bold text-slate-900 w-full outline-none">
                        </div>
                    </div>
                    <div class="space-y-3">
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest">Heure de
                            retour</label>
                        <div
                            class="flex items-center gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-100 focus-within:border-[#ff3c00] transition-colors">
                            <i class="fa-solid fa-clock text-[#ff3c00] text-xl"></i>
                            <input type="time" id="return-time"
                                class="bg-transparent text-xl font-bold text-slate-900 w-full outline-none">
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-100">
                    <button id="btn-go-return-pricing"
                        class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black hover:bg-black transition-all shadow-xl text-lg">
                        Continuer
                    </button>
                </div>
            </div>

            <div class="right-showcase hidden md:flex">
                <div class="max-w-md relative z-10">
                    <div class="text-[#ff3c00] text-6xl mb-8">
                        <i class="fa-solid fa-rotate-left"></i>
                    </div>
                    <h3 class="text-4xl font-black text-slate-900 mb-6 uppercase tracking-tighter">
                        Le <span class="text-[#ff3c00]">retour</span>, c'est maintenant.
                    </h3>
                    <div class="w-16 h-1 bg-[#ff3c00] mx-auto mb-8"></div>
                    <p class="text-slate-500 text-lg font-medium leading-relaxed">
                        Proposez un retour pour
                        <span class="text-slate-900 font-black">doubler vos chances</span> de trouver des passagers.
                    </p>
                    <div class="mt-12 text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">
                        Aller • Retour • Rentable
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- ROUTE RETOUR                                           -->
        <!-- ===================================================== -->
        <section id="view-return-route" class="step-view">
            <div class="sidebar-panel p-6">
                <button onclick="changeView('view-return-datetime')"
                    class="mb-6 text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-chevron-left text-xs"></i> Date retour
                </button>
                <div class="mb-6">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-2">Retour</span>
                    <h2 class="text-2xl font-black text-slate-800">Choisissez la <span class="text-[#ff3c00]">route
                            retour</span></h2>
                </div>
                <div id="return-routes-list" class="space-y-4 flex-1 overflow-y-auto pr-2"></div>
                <div class="pt-6 border-t border-slate-100">
                    <button id="btn-validate-return-route"
                        class="w-full py-4 bg-slate-900 text-white rounded-xl font-bold text-lg hover:bg-[#ff3c00] transition-all">
                        Confirmer la route retour
                    </button>
                </div>
            </div>
            <div class="map-container-traj">
                <div id="map-return-route" class="h-full w-full"></div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- ESCALES RETOUR                                         -->
        <!-- ===================================================== -->
        <section id="view-return-steps" class="step-view">
            <div class="sidebar-panel p-6">
                <button onclick="changeView('view-return-route')"
                    class="mb-6 text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-chevron-left text-xs"></i> Route retour
                </button>
                <div class="mb-6">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-2">Retour</span>
                    <h2 class="text-2xl font-black text-slate-800">Escales du <span class="text-[#ff3c00]">retour</span>
                    </h2>
                    <p class="text-sm text-slate-500 mt-2 font-medium">Sélectionnez ou ajoutez des escales pour le trajet
                        retour.</p>
                </div>

                <div class="mb-4">
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-3">
                        <i class="fa-solid fa-wand-magic-sparkles mr-1 text-[#ff3c00]"></i> Détectées sur la route retour
                    </p>
                    <div id="return-intermediate-cities" class="space-y-3"></div>
                </div>

                <div class="flex items-center gap-3 my-5">
                    <div class="flex-1 h-px bg-slate-200"></div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Escales manuelles</span>
                    <div class="flex-1 h-px bg-slate-200"></div>
                </div>

                <div id="return-manual-stops-list" class="space-y-3 mb-4"></div>

                <button onclick="openStopPopin('retour')"
                    class="w-full py-3 border-2 border-dashed border-slate-200 rounded-xl text-slate-500 font-bold hover:border-[#ff3c00] hover:text-[#ff3c00] transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus"></i> Ajouter une escale retour
                </button>

                <div class="pt-6 border-t border-slate-100 mt-6">
                    <button id="btn-confirm-return-steps"
                        class="w-full py-4 bg-slate-900 text-white rounded-xl font-bold text-lg hover:bg-[#ff3c00] transition-all">
                        Continuer vers les prix
                    </button>
                </div>
            </div>
            <div class="map-container-traj">
                <div id="map-return-steps" class="h-full w-full"></div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- PRIX RETOUR                                            -->
        <!-- ===================================================== -->
        <section id="view-return-pricing" class="step-view">
            <div class="sidebar-panel p-8">
                <button onclick="changeView('view-return-steps')"
                    class="mb-6 text-slate-400 font-bold flex items-center gap-2 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Escales retour
                </button>
                <div class="mb-2">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-3">Tarification
                        retour</span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 mb-2">
                    Fixez vos <span class="text-[#ff3c00]">prix retour</span>
                </h2>
                <p class="text-slate-500 mb-8 font-medium">
                    Prix par passager pour chaque étape du trajet retour.
                </p>
                <div id="return-pricing-steps-container" class="space-y-6 flex-1 overflow-y-auto"></div>
                <div class="pt-8 border-t border-slate-100">
                    <button id="btn-go-driver-info"
                        class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black hover:bg-black transition-all shadow-xl">
                        Dernière étape
                    </button>
                </div>
            </div>

            <div class="right-showcase hidden md:flex">
                <div class="max-w-md w-full relative z-10">
                    <div class="mb-8 p-10 bg-white rounded-[2.5rem] shadow-xl border border-slate-100">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Prix total retour</p>
                        <div id="return-total-price" class="text-6xl font-black text-slate-900 mb-2">
                            0<span class="text-[#ff3c00] ml-1 text-4xl">€</span>
                        </div>
                        <div class="flex items-center justify-center gap-2 text-emerald-500 font-black text-sm mt-3">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            <span>Ajustez selon vos préférences</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- DRIVER INFO                                            -->
        <!-- ===================================================== -->
        <section id="view-driver-info" class="step-view">
            <div class="sidebar-panel p-8">
                <button onclick="changeView('view-final')"
                    class="group mb-8 text-slate-400 font-bold flex items-center gap-2 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
                    <span>Retour</span>
                </button>

                <div class="mb-2">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-3">Profil</span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 mb-2 leading-tight">
                    Présentez-vous aux <span class="text-[#ff3c00]">passagers</span>
                </h2>
                <p class="text-slate-500 mb-10 font-medium">
                    Les profils avec photo inspirent <span class="text-slate-900 font-bold">plus confiance</span>.
                </p>

                <div class="space-y-8 flex-1">
                    <div class="space-y-4">
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Photo de
                            profil</label>
                        <div class="flex items-center gap-5">
                            <div class="relative">
                                <div id="photo-preview-container"
                                    class="w-20 h-20 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden transition-all duration-300">
                                    <i class="fa-solid fa-user text-2xl text-slate-300"></i>
                                </div>
                                <label for="driver-photo"
                                    class="absolute -bottom-2 -right-2 w-8 h-8 bg-[#ff3c00] text-white rounded-lg flex items-center justify-center cursor-pointer shadow-lg hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-camera text-xs"></i>
                                    <input type="file" id="driver-photo" class="hidden" accept="image/*"
                                        onchange="handleImagePreview(this)">
                                </label>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-slate-500 leading-snug">Ajoutez une photo claire pour être reconnu
                                    facilement.</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Message
                            conducteur</label>
                        <textarea id="driver-message"
                            class="w-full p-5 rounded-2xl bg-slate-50 border border-slate-100 focus:border-[#ff3c00]/30 focus:bg-white focus:ring-4 focus:ring-orange-50 transition-all duration-300 text-slate-700 placeholder:text-slate-300 min-h-[150px] resize-none outline-none font-medium"
                            placeholder="Ex: Conducteur calme, j'aime discuter et je suis flexible sur les points de dépose..."></textarea>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-100">
                    <button id="btn-publish"
                        class="w-full py-4 bg-[#ff3c00] text-white rounded-2xl font-black text-lg shadow-xl shadow-orange-200 hover:shadow-orange-300 hover:-translate-y-1 active:translate-y-0 transition-all duration-300 flex items-center justify-center gap-3">
                        Publier le trajet
                        <i class="fa-solid fa-check"></i>
                    </button>
                </div>
            </div>

            <div class="right-showcase hidden md:flex relative overflow-hidden">
                <div
                    class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] border border-slate-100 rounded-full">
                </div>
                <div
                    class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[350px] h-[350px] border border-slate-100 rounded-full">
                </div>

                <div class="relative w-full max-w-sm z-10">
                    <div>
                        <h3 class="text-4xl font-black text-slate-900 mb-4 leading-tight uppercase tracking-tight italic">
                            La <span class="text-[#ff3c00]">communauté</span> vous attend.
                        </h3>
                        <p class="text-slate-400 text-base font-medium mb-12">
                            95% des passagers réservent plus vite avec un profil complet.
                        </p>
                    </div>

                    <div class="relative h-64 mt-8">
                        <div
                            class="testimonial-card absolute top-0 -left-4 bg-white p-4 rounded-2xl w-56 animate-float z-20 shadow-lg border border-slate-100">
                            <div class="flex items-center gap-3 mb-2">
                                <div
                                    class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-[#ff3c00] font-bold text-[10px]">
                                    TH</div>
                                <div>
                                    <p class="font-bold text-slate-900 text-[9px]">Thomas H.</p>
                                    <div class="flex gap-0.5 text-[7px] text-yellow-400">
                                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                            class="fa-solid fa-star"></i>
                                    </div>
                                </div>
                            </div>
                            <p class="text-slate-500 text-[8px] leading-relaxed">"Super trajet ! Le profil complet m'a
                                vraiment rassuré avant de réserver."</p>
                        </div>

                        <div
                            class="testimonial-card absolute bottom-4 -right-4 bg-white p-5 rounded-2xl w-48 animate-float-delayed shadow-xl border-l-4 border-l-[#ff3c00] z-30">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white text-lg">
                                    <i class="fa-solid fa-shield-check"></i>
                                </div>
                                <div>
                                    <p class="font-black text-slate-900 text-[10px] uppercase">Vérifié</p>
                                    <p class="text-[#ff3c00] font-bold text-[8px]">Profil Gold</p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="absolute top-1/4 right-8 w-10 h-10 rounded-full bg-white shadow-md flex items-center justify-center animate-bounce border border-slate-100">
                            <i class="fa-solid fa-heart text-rose-500 text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <script>
            function handleImagePreview(input) {
                const container = document.getElementById('photo-preview-container');
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        container.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                        container.classList.remove('bg-slate-50', 'border-dashed');
                        container.classList.add('border-solid', 'border-[#ff3c00]/20');
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
        </script>

    </div>

    <script>
        let maps = {
            main: null,
            route: null,
            steps: null,
            summary: null,
            returnRoute: null,
            returnSteps: null,
        };
        let markers = {
            start: null,
            end: null,
            steps: []
        };
        let routes = [];
        let selectedRouteIndex = 0;
        let startCoords = null,
            endCoords = null;
        let intermediateCities = [];

        // ========== VARIABLES ESCALES ==========
        // Escales aller : auto-détectées (via checkbox) + manuelles
        let manualStopsAller = []; // [{name, latlng, marker}]
        let returnManualStops = []; // [{name, latlng, marker}]
        let returnStepMarkers = []; // escales retour auto-détectées cochées

        // Temporaire pour la popin
        let pendingStopData = null; // {name, latlng} en attente de confirmation

        document.addEventListener('DOMContentLoaded', () => {
            initMap('map', 'main');
            setupAutocomplete();
            setupPopinAutocomplete('popin-stop-input', 'popin-stop-results', 'aller');
            setupPopinAutocomplete('popin-return-stop-input', 'popin-return-stop-results', 'retour');

            document.getElementById('btn-next').onclick = () => {
                changeView('view-route');
                initMap('map-route', 'route');
                loadRoutes();
            };

            document.getElementById('btn-validate-route').onclick = () => {
                changeView('view-steps');
                initMap('map-steps', 'steps');
                loadIntermediateCities();
            };

            document.getElementById('btn-confirm-steps').onclick = () => {
                saveSelectedSteps();
                changeView('view-summary');
                initMap('map-summary', 'summary');
                renderSummary();
            };

            // Bouton confirmer escales retour → pricing retour
            document.getElementById('btn-confirm-return-steps').onclick = () => {
                saveReturnSelectedSteps();
                changeView('view-return-pricing');
                generateReturnPricingSteps();
            };
        });

        function initMap(id, key) {
            if (maps[key]) {
                maps[key].invalidateSize();
                return;
            }
            maps[key] = L.map(id, {
                zoomControl: false
            }).setView([46.2276, 2.2137], 6);
            L.control.zoom({
                position: 'bottomright'
            }).addTo(maps[key]);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '©OpenStreetMap'
            }).addTo(maps[key]);

            if (startCoords) L.marker(startCoords).addTo(maps[key]);
            if (endCoords) L.marker(endCoords).addTo(maps[key]);
        }

        function changeView(viewId) {
            document.querySelectorAll('.step-view').forEach(v => v.classList.remove('active'));
            document.getElementById(viewId).classList.add('active');

            const key = viewId.split('-')[1];
            if (maps[key]) {
                setTimeout(() => maps[key].invalidateSize(), 100);
            }
        }

        function setupAutocomplete() {
            const setups = [{
                    input: 'input-start',
                    results: 'start-results',
                    type: 'start'
                },
                {
                    input: 'input-end',
                    results: 'end-results',
                    type: 'end'
                }
            ];

            setups.forEach(s => {
                const input = document.getElementById(s.input);
                const resDiv = document.getElementById(s.results);

                input.addEventListener('input', debounce(async (e) => {
                    const query = e.target.value;
                    if (query.length < 3) {
                        resDiv.classList.add('hidden');
                        return;
                    }

                    const url =
                        `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`;
                    try {
                        const response = await fetch(url);
                        const data = await response.json();

                        resDiv.innerHTML = '';
                        resDiv.classList.remove('hidden');

                        data.forEach(place => {
                            const item = document.createElement('div');
                            item.className = 'autocomplete-item';
                            item.innerText = place.display_name;
                            item.onclick = () => {
                                input.value = place.display_name;
                                const coords = [parseFloat(place.lat), parseFloat(place
                                    .lon)];
                                if (s.type === 'start') startCoords = coords;
                                else endCoords = coords;

                                updateMarkers();
                                resDiv.classList.add('hidden');
                                checkStep1Valid();
                            };
                            resDiv.appendChild(item);
                        });
                    } catch (err) {
                        console.error(err);
                    }
                }, 300));
            });
        }

        // ========== POPIN AUTOCOMPLETE ==========
        function setupPopinAutocomplete(inputId, resultsId, type) {
            const input = document.getElementById(inputId);
            const resDiv = document.getElementById(resultsId);

            input.addEventListener('input', debounce(async (e) => {
                const query = e.target.value;
                if (query.length < 3) {
                    resDiv.classList.add('hidden');
                    return;
                }

                const url =
                    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`;
                try {
                    const response = await fetch(url);
                    const data = await response.json();

                    resDiv.innerHTML = '';
                    resDiv.classList.remove('hidden');

                    data.forEach(place => {
                        const item = document.createElement('div');
                        item.className = 'autocomplete-item';
                        item.innerText = place.display_name;
                        item.onclick = () => {
                            const coords = [parseFloat(place.lat), parseFloat(place.lon)];
                            const cityName = place.display_name.split(',')[0].trim();

                            pendingStopData = {
                                name: cityName,
                                fullName: place.display_name,
                                latlng: coords
                            };

                            // Afficher la sélection
                            if (type === 'aller') {
                                document.getElementById('popin-stop-selected-name')
                                    .textContent = place.display_name;
                                document.getElementById('popin-stop-selected').classList.remove(
                                    'hidden');
                                document.getElementById('popin-stop-confirm').disabled = false;
                            } else {
                                document.getElementById('popin-return-stop-selected-name')
                                    .textContent = place.display_name;
                                document.getElementById('popin-return-stop-selected').classList
                                    .remove('hidden');
                                document.getElementById('popin-return-stop-confirm').disabled =
                                    false;
                            }

                            input.value = '';
                            resDiv.classList.add('hidden');
                        };
                        resDiv.appendChild(item);
                    });
                } catch (err) {
                    console.error(err);
                }
            }, 300));
        }

        function openStopPopin(type) {
            pendingStopData = null;
            if (type === 'aller') {
                document.getElementById('popin-stop-input').value = '';
                document.getElementById('popin-stop-results').classList.add('hidden');
                document.getElementById('popin-stop-selected').classList.add('hidden');
                document.getElementById('popin-stop-confirm').disabled = true;
                document.getElementById('popin-add-stop').classList.add('active');
            } else {
                document.getElementById('popin-return-stop-input').value = '';
                document.getElementById('popin-return-stop-results').classList.add('hidden');
                document.getElementById('popin-return-stop-selected').classList.add('hidden');
                document.getElementById('popin-return-stop-confirm').disabled = true;
                document.getElementById('popin-add-return-stop').classList.add('active');
            }
        }

        function closeStopPopin(type) {
            pendingStopData = null;
            if (type === 'aller') {
                document.getElementById('popin-add-stop').classList.remove('active');
            } else {
                document.getElementById('popin-add-return-stop').classList.remove('active');
            }
        }

        function clearPopinSelection(type) {
            pendingStopData = null;
            if (type === 'aller') {
                document.getElementById('popin-stop-selected').classList.add('hidden');
                document.getElementById('popin-stop-confirm').disabled = true;
            } else {
                document.getElementById('popin-return-stop-selected').classList.add('hidden');
                document.getElementById('popin-return-stop-confirm').disabled = true;
            }
        }

        function confirmManualStop(type) {
            if (!pendingStopData) return;

            if (type === 'aller') {
                // Ajouter le marker sur la carte aller
                const map = maps.steps;
                const marker = L.marker(pendingStopData.latlng).addTo(map)
                    .bindPopup(
                        `<b>${pendingStopData.name}</b><br><span class="text-xs text-slate-500">Ajout manuel</span>`);

                manualStopsAller.push({
                    name: pendingStopData.name,
                    fullName: pendingStopData.fullName,
                    latlng: pendingStopData.latlng,
                    marker: marker
                });

                renderManualStopsList('aller');
                closeStopPopin('aller');
            } else {
                // Ajouter le marker sur la carte retour
                const map = maps.returnSteps;
                const marker = L.marker(pendingStopData.latlng).addTo(map)
                    .bindPopup(
                        `<b>${pendingStopData.name}</b><br><span class="text-xs text-slate-500">Ajout manuel</span>`);

                returnManualStops.push({
                    name: pendingStopData.name,
                    fullName: pendingStopData.fullName,
                    latlng: pendingStopData.latlng,
                    marker: marker
                });

                renderManualStopsList('retour');
                closeStopPopin('retour');
            }

            pendingStopData = null;
        }

        function renderManualStopsList(type) {
            const list = type === 'aller' ? manualStopsAller : returnManualStops;
            const containerId = type === 'aller' ? 'manual-stops-list' : 'return-manual-stops-list';
            const container = document.getElementById(containerId);
            container.innerHTML = '';

            if (list.length === 0) {
                container.innerHTML = '<p class="text-sm text-slate-400 italic text-center py-2">Aucune escale ajoutée</p>';
                return;
            }

            list.forEach((stop, i) => {
                const div = document.createElement('div');
                div.className =
                    "manual-stop-item flex items-center p-4 bg-white rounded-xl border border-slate-100 shadow-sm";

                div.innerHTML = `
                    <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center text-[#ff3c00] text-sm mr-3 flex-shrink-0">
                        <i class="fa-solid fa-map-pin"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-slate-800 text-sm truncate">${stop.name}</p>
                        <p class="text-[10px] text-slate-400 uppercase font-black tracking-wider">Ajout manuel</p>
                    </div>
                    <button onclick="removeManualStop('${type}', ${i})" class="delete-btn ml-2 w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                `;

                container.appendChild(div);
            });
        }

        function removeManualStop(type, index) {
            if (type === 'aller') {
                const stop = manualStopsAller[index];
                if (stop.marker && maps.steps) maps.steps.removeLayer(stop.marker);
                manualStopsAller.splice(index, 1);
            } else {
                const stop = returnManualStops[index];
                if (stop.marker && maps.returnSteps) maps.returnSteps.removeLayer(stop.marker);
                returnManualStops.splice(index, 1);
            }
            renderManualStopsList(type);
        }

        function updateMarkers() {
            const map = maps.main;
            if (startCoords) {
                if (markers.start) map.removeLayer(markers.start);
                markers.start = L.marker(startCoords).addTo(map);
            }
            if (endCoords) {
                if (markers.end) map.removeLayer(markers.end);
                markers.end = L.marker(endCoords).addTo(map);
            }
            if (startCoords && endCoords) {
                const bounds = L.latLngBounds([startCoords, endCoords]);
                map.fitBounds(bounds, {
                    padding: [50, 50]
                });
            }
        }

        function checkStep1Valid() {
            document.getElementById('btn-next').disabled = !(startCoords && endCoords);
        }

        async function loadRoutes() {
            const url =
                `https://router.project-osrm.org/route/v1/driving/${startCoords[1]},${startCoords[0]};${endCoords[1]},${endCoords[0]}?overview=full&geometries=geojson&alternatives=true`;
            const res = await fetch(url);
            const data = await res.json();
            routes = data.routes;
            renderRouteList();
            displayRouteOnMap(0);
        }

        function renderRouteList() {
            const container = document.getElementById('routes-list');
            container.innerHTML = '';
            routes.forEach((route, i) => {
                const div = document.createElement('div');
                div.className =
                    `route-option p-4 rounded-xl bg-white ${i === selectedRouteIndex ? 'selected' : ''}`;
                div.onclick = () => {
                    selectedRouteIndex = i;
                    renderRouteList();
                    displayRouteOnMap(i);
                };

                const dist = (route.distance / 1000).toFixed(1);
                let dur;

                if (route.duration >= 60) {
                    console.log(route.duration)
                    const hours = Math.floor(route.duration / 60);
                    const minutes = Math.round(route.duration % 60);

                    dur = minutes > 0
                        ? `${hours}h ${minutes}min`
                        : `${hours}h`;
                } else {
                    dur = `${Math.round(route.duration)} min`;
                }
                div.innerHTML = `
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-black text-slate-800">Option ${i + 1}</span>
                        ${i === 0 ? '<span class="text-[9px] bg-orange-500 text-white px-2 py-0.5 rounded-full uppercase">Rapide</span>' : ''}
                    </div>
                    <div class="flex gap-3 text-sm font-bold text-slate-500">
                        <span><i class="fa-solid fa-road mr-1"></i> ${dist} km</span>
                        <span><i class="fa-solid fa-clock mr-1"></i> ${dur}</span>
                    </div>
                `;
                container.appendChild(div);
            });
        }

        let currentRouteLayer = null;

        function displayRouteOnMap(index) {
            const map = maps.route;
            if (currentRouteLayer) map.removeLayer(currentRouteLayer);

            currentRouteLayer = L.geoJSON(routes[index].geometry, {
                style: {
                    color: '#ff3c00',
                    weight: 6,
                    opacity: 0.8
                }
            }).addTo(map);

            map.fitBounds(currentRouteLayer.getBounds(), {
                padding: [40, 40]
            });

            const routeData = {
                distance: routes[index].distance,
                duration: routes[index].duration,
                geometry: routes[index].geometry
            };

            saveTripDraftPartial({
                selectedRoute: routeData,
                selectedRouteIndex: index
            });
        }

        async function loadIntermediateCities() {
            const container = document.getElementById('intermediate-cities');
            container.innerHTML =
                '<div class="text-center py-10 text-slate-400"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Recherche des étapes...</div>';

            // Reset escales manuelles aller quand on recharge les auto
            manualStopsAller = [];
            renderManualStopsList('aller');

            const coords = routes[selectedRouteIndex].geometry.coordinates;
            const samples = [
                coords[Math.floor(coords.length * 0.25)],
                coords[Math.floor(coords.length * 0.5)],
                coords[Math.floor(coords.length * 0.75)]
            ];

            const cities = [];
            for (let point of samples) {
                const res = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${point[1]}&lon=${point[0]}`);
                const data = await res.json();
                const cityName = data.address.city || data.address.town || data.address.village;
                if (cityName) cities.push({
                    name: cityName,
                    latlng: [point[1], point[0]]
                });
            }

            container.innerHTML = '';

            if (cities.length === 0) {
                container.innerHTML =
                    '<p class="text-sm text-slate-400 italic text-center py-4">Aucune ville détectée sur ce trajet</p>';
                return;
            }

            cities.forEach((city, i) => {
                const div = document.createElement('div');
                div.className =
                    "flex items-center p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-orange-200 transition-all cursor-pointer";
                div.innerHTML = `
                    <input type="checkbox" id="city-${i}" class="w-5 h-5 accent-[#ff3c00] rounded">
                    <label for="city-${i}" class="ml-4 flex-1 font-bold text-slate-700 cursor-pointer">${city.name}</label>
                    <i class="fa-solid fa-map-pin text-slate-300"></i>
                `;
                div.onclick = (e) => {
                    if (e.target.tagName !== 'INPUT') {
                        const cb = div.querySelector('input');
                        cb.checked = !cb.checked;
                    }
                    toggleStepMarker(city, div.querySelector('input').checked);
                };
                container.appendChild(div);
            });
        }

        let stepMarkers = [];

        function toggleStepMarker(city, show) {
            const map = maps.steps;
            if (show) {
                const m = L.marker(city.latlng).addTo(map).bindPopup(city.name);
                city.marker = m;
                stepMarkers.push(city);
            } else {
                const index = stepMarkers.findIndex(s => s.name === city.name);
                if (index > -1) {
                    map.removeLayer(stepMarkers[index].marker);
                    stepMarkers.splice(index, 1);
                }
            }
        }

        function saveSelectedSteps() {
            // Combine: départ + escales auto cochées + escales manuelles + arrivée
            const allWaypoints = [
                ...stepMarkers.map(s => ({
                    name: s.name,
                    type: 'waypoint',
                    source: 'auto',
                    latlng: s.latlng
                })),
                ...manualStopsAller.map(s => ({
                    name: s.name,
                    type: 'waypoint',
                    source: 'manual',
                    latlng: s.latlng
                }))
            ];

            const finalSteps = [{
                    name: document.getElementById('input-start').value,
                    type: 'start',
                    latlng: startCoords
                },
                ...allWaypoints,
                {
                    name: document.getElementById('input-end').value,
                    type: 'end',
                    latlng: endCoords
                }
            ];
            localStorage.setItem('vtc_final_itinerary', JSON.stringify(finalSteps));
        }

        // ========== ESCALES RETOUR ==========
        function saveReturnSelectedSteps() {
            const itinerary = JSON.parse(localStorage.getItem('vtc_final_itinerary')) || [];
            if (itinerary.length < 2) return;

            // Retour = destination → départ (inversé)
            const returnStart = itinerary[itinerary.length - 1];
            const returnEnd = itinerary[0];

            const allReturnWaypoints = [
                ...returnStepMarkers.map(s => ({
                    name: s.name,
                    type: 'waypoint',
                    source: 'auto',
                    latlng: s.latlng
                })),
                ...returnManualStops.map(s => ({
                    name: s.name,
                    type: 'waypoint',
                    source: 'manual',
                    latlng: s.latlng
                }))
            ];

            const returnItinerary = [{
                    name: returnStart.name,
                    type: 'start',
                    latlng: returnStart.latlng
                },
                ...allReturnWaypoints,
                {
                    name: returnEnd.name,
                    type: 'end',
                    latlng: returnEnd.latlng
                }
            ];

            localStorage.setItem('vtc_return_itinerary', JSON.stringify(returnItinerary));
        }

        async function loadReturnIntermediateCities() {
            const container = document.getElementById('return-intermediate-cities');
            container.innerHTML =
                '<div class="text-center py-10 text-slate-400"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Recherche des étapes retour...</div>';

            // Reset
            returnManualStops = [];
            returnStepMarkers = [];
            renderManualStopsList('retour');

            if (!returnRoutes[selectedReturnRouteIndex]) {
                container.innerHTML =
                    '<p class="text-sm text-slate-400 italic text-center py-4">Aucune route retour sélectionnée</p>';
                return;
            }

            const coords = returnRoutes[selectedReturnRouteIndex].geometry.coordinates;
            const samples = [
                coords[Math.floor(coords.length * 0.25)],
                coords[Math.floor(coords.length * 0.5)],
                coords[Math.floor(coords.length * 0.75)]
            ];

            const cities = [];
            for (let point of samples) {
                const res = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${point[1]}&lon=${point[0]}`);
                const data = await res.json();
                const cityName = data.address.city || data.address.town || data.address.village;
                if (cityName) cities.push({
                    name: cityName,
                    latlng: [point[1], point[0]]
                });
            }

            container.innerHTML = '';

            if (cities.length === 0) {
                container.innerHTML =
                    '<p class="text-sm text-slate-400 italic text-center py-4">Aucune ville détectée sur le trajet retour</p>';
                return;
            }

            cities.forEach((city, i) => {
                const div = document.createElement('div');
                div.className =
                    "flex items-center p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-orange-200 transition-all cursor-pointer";
                div.innerHTML = `
                    <input type="checkbox" id="return-city-${i}" class="w-5 h-5 accent-[#ff3c00] rounded">
                    <label for="return-city-${i}" class="ml-4 flex-1 font-bold text-slate-700 cursor-pointer">${city.name}</label>
                    <i class="fa-solid fa-map-pin text-slate-300"></i>
                `;
                div.onclick = (e) => {
                    if (e.target.tagName !== 'INPUT') {
                        const cb = div.querySelector('input');
                        cb.checked = !cb.checked;
                    }
                    toggleReturnStepMarker(city, div.querySelector('input').checked);
                };
                container.appendChild(div);
            });
        }

        function toggleReturnStepMarker(city, show) {
            const map = maps.returnSteps;
            if (show) {
                const m = L.marker(city.latlng).addTo(map).bindPopup(city.name);
                city.marker = m;
                returnStepMarkers.push(city);
            } else {
                const index = returnStepMarkers.findIndex(s => s.name === city.name);
                if (index > -1) {
                    map.removeLayer(returnStepMarkers[index].marker);
                    returnStepMarkers.splice(index, 1);
                }
            }
        }

        function renderSummary() {
            const container = document.getElementById('selected-steps');
            const data = JSON.parse(localStorage.getItem('vtc_final_itinerary'));
            container.innerHTML = '';

            data.forEach(item => {
                const li = document.createElement('li');
                li.className = "relative pl-8 flex flex-col";

                let iconColor = "bg-slate-300";
                if (item.type === 'start') iconColor = "bg-emerald-500";
                if (item.type === 'end') iconColor = "bg-[#ff3c00]";

                const sourceLabel = item.source === 'manual' ? ' · Manuel' : '';

                li.innerHTML = `
                    <span class="absolute left-0 top-1 w-[24px] h-[24px] rounded-full ${iconColor} border-4 border-white shadow-sm z-10"></span>
                    <span class="text-[10px] font-black uppercase text-slate-400">${item.type}${sourceLabel}</span>
                    <span class="font-bold text-slate-800 leading-tight">${item.name}</span>
                `;
                container.appendChild(li);
            });

            const map = maps.summary;
            L.geoJSON(routes[selectedRouteIndex].geometry, {
                style: {
                    color: '#ff3c00',
                    weight: 4
                }
            }).addTo(map);
            map.fitBounds(L.latLngBounds([startCoords, endCoords]), {
                padding: [50, 50]
            });
        }

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        function updateQty(type, delta) {
            const el = document.getElementById('qty-passengers');
            let value = parseInt(el.innerText, 10) + delta;
            value = Math.max(1, Math.min(8, value));
            el.innerText = value;

            saveTripDraft({
                passengers: {
                    ...getTripDraft().passengers,
                    count: value
                }
            });
        }

        document.querySelectorAll('[data-pref]').forEach(cb => {
            cb.addEventListener('change', () => {
                saveTripDraft({
                    passengers: {
                        ...getTripDraft().passengers,
                        [cb.dataset.pref]: cb.checked
                    }
                });
            });
        });

        function selectBooking(el) {
            document.querySelectorAll('.card-option')
                .forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');

            const mode = el.dataset.mode;

            const draft = JSON.parse(localStorage.getItem('vtc_trip_draft')) || {};
            draft.booking = {
                mode
            };
            localStorage.setItem('vtc_trip_draft', JSON.stringify(draft));
        }

        function savePricingStep(index, price) {
            const draft = getTripDraft();
            draft.pricing = draft.pricing || {
                steps: []
            };

            const itinerary = JSON.parse(localStorage.getItem('vtc_final_itinerary')) || [];

            const from = itinerary[index]?.name || `Étape ${index + 1}`;
            const to = itinerary[index + 1]?.name || `Étape ${index + 2}`;

            draft.pricing.steps[index] = {
                from,
                to,
                price
            };

            saveTripDraft({
                pricing: draft.pricing
            });
        }

        function updateSegmentPrice(input) {
            const index = parseInt(input.dataset.index, 10);
            const value = parseFloat(input.value) || 0;

            const segments = JSON.parse(localStorage.getItem('vtc_pricing_segments')) || [];
            if (!segments[index]) return;

            segments[index].price = value;
            localStorage.setItem('vtc_pricing_segments', JSON.stringify(segments));
            calculateGlobalTotal();
        }

        function calculateGlobalTotal() {
            const segments = JSON.parse(localStorage.getItem('vtc_pricing_segments')) || [];
            const total = segments.reduce((sum, s) => sum + (parseFloat(s.price) || 0), 0);
            const totalEl = document.getElementById('pricing-total');
            if (totalEl) totalEl.innerText = total.toFixed(0);
        }

        function selectReturn(el, value) {
            document.querySelectorAll('#view-final .card-option')
                .forEach(c => c.classList.remove('selected'));

            el.classList.add('selected');
            saveTripDraft({
                returnTrip: value
            });

            const btn = document.getElementById('btn-final-action');

            if (value === true) {
                btn.innerText = "Continuer";
                btn.onclick = () => changeView('view-return-datetime');
            } else {
                btn.innerText = "Dernière étape";
                btn.onclick = () => changeView('view-driver-info');
            }
        }

        function goToReturnRoute() {
            changeView('view-return-route');
            initMap('map-return-route', 'returnRoute');
            loadReturnRoutes();
        }

        function initPricingSegments() {
            const itinerary = JSON.parse(localStorage.getItem('vtc_final_itinerary')) || [];
            if (itinerary.length < 2) return;

            const segments = [];
            for (let i = 0; i < itinerary.length - 1; i++) {
                const from = itinerary[i];
                const to = itinerary[i + 1];
                if (!from.latlng || !to.latlng) continue;

                segments.push({
                    fromCoords: from.latlng,
                    toCoords: to.latlng,
                    price: 0
                });
            }

            localStorage.setItem('vtc_pricing_segments', JSON.stringify(segments));
        }

        async function computeSegmentData() {
            const segments = JSON.parse(localStorage.getItem('vtc_pricing_segments')) || [];

            for (let seg of segments) {
                if (!seg.fromCoords || !seg.toCoords) continue;

                const url =
                    `https://router.project-osrm.org/route/v1/driving/${seg.fromCoords[1]},${seg.fromCoords[0]};${seg.toCoords[1]},${seg.toCoords[0]}?overview=false`;

                try {
                    const res = await fetch(url);
                    const data = await res.json();

                    if (data.routes && data.routes[0]) {
                        seg.distance = data.routes[0].distance / 1000;
                        seg.duration = data.routes[0].duration / 60;
                        seg.price = Math.round(seg.distance * 1.5);
                    }
                } catch (err) {
                    console.error('Erreur lors du calcul du segment', seg, err);
                }
            }

            localStorage.setItem('vtc_pricing_segments', JSON.stringify(segments));
        }

        function renderPricing() {
            const container = document.getElementById('pricing-steps-container');
            if (!container) return;

            const itinerary = JSON.parse(localStorage.getItem('vtc_final_itinerary')) || [];
            container.innerHTML = '';
            if (itinerary.length < 2) return;

            const draft = getTripDraft();
            draft.pricing = draft.pricing || {
                steps: []
            };

            for (let i = 0; i < itinerary.length - 1; i++) {
                if (!draft.pricing.steps[i]) {
                    draft.pricing.steps[i] = {
                        price: 20
                    };
                }

                const from = itinerary[i].name;
                const to = itinerary[i + 1].name;
                const stepPrice = draft.pricing.steps[i].price;

                const block = document.createElement('div');
                block.className = "p-4 bg-slate-50 rounded-2xl border border-slate-100";

                block.innerHTML = `
                    <div class="flex justify-between items-center mb-3">
                        <span class="font-bold text-slate-800">${from} → ${to}</span>
                        <span class="text-[#ff3c00] font-black price-value">${stepPrice}€</span>
                    </div>
                    <input type="range" min="5" max="100" value="${stepPrice}"
                        data-index="${i}"
                        class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#ff3c00]">
                `;

                const range = block.querySelector('input');
                const priceEl = block.querySelector('.price-value');

                range.addEventListener('input', () => {
                    const value = parseFloat(range.value);
                    priceEl.textContent = value + '€';
                    savePricingStep(i, value);
                    updateTotalPrice();
                });

                container.appendChild(block);
            }

            saveTripDraft({
                pricing: draft.pricing
            });
            updateTotalPrice();

            function updateTotalPrice() {
                const draft = getTripDraft();
                const total = draft.pricing.steps.reduce((sum, s) => sum + (s.price || 0), 0);
                const totalEl = document.querySelector('#view-pricing .text-6xl');
                if (totalEl) {
                    totalEl.innerHTML = `${total}<span class="text-[#ff3c00] ml-1 text-4xl">€</span>`;
                }
            }
        }

        document.querySelectorAll('input[name="passengerMode"]').forEach(input => {
            input.addEventListener('change', () => {
                const mode = input.value;
                const draft = getTripDraft();
                draft.passengers = {
                    ...(draft.passengers || {}),
                    mode
                };
                localStorage.setItem('vtc_trip_draft', JSON.stringify(draft));

                document.querySelectorAll('.radio-card').forEach(card => {
                    card.classList.remove('border-orange-500', 'shadow-md');
                    const icon = card.querySelector('.check-icon');
                    if (icon) {
                        icon.style.opacity = 0;
                        icon.style.transform = 'scale(0.75)';
                    }
                });

                const parentLabel = input.closest('label');
                if (parentLabel) {
                    const card = parentLabel.querySelector('.radio-card');
                    if (card) {
                        card.classList.add('border-orange-500', 'shadow-md');
                        const icon = card.querySelector('.check-icon');
                        if (icon) {
                            icon.style.opacity = 1;
                            icon.style.transform = 'scale(1)';
                        }
                    }
                }
            });
        });

        function getTripDraft() {
            return JSON.parse(localStorage.getItem('vtc_trip_draft')) || {};
        }

        document.addEventListener('DOMContentLoaded', () => {
            const draft = getTripDraft();
            const mode = draft.passengers?.mode || 'mixed';

            const input = document.querySelector(`input[name="passengerMode"][value="${mode}"]`);
            if (input) {
                input.checked = true;
                const parentLabel = input.closest('label');
                if (parentLabel) {
                    const card = parentLabel.querySelector('.radio-card');
                    if (card) {
                        card.classList.add('border-orange-500', 'shadow-md');
                        const icon = card.querySelector('.check-icon');
                        if (icon) {
                            icon.style.opacity = 1;
                            icon.style.transform = 'scale(1)';
                        }
                    }
                }
            }
        });

        document.getElementById('btn-go-return-pricing').onclick = () => {
            if (!returnDateInput.value || !returnTimeInput.value) {
                alert("Veuillez sélectionner une date et une heure de retour.");
                return;
            }
            saveReturnDateTime();
            goToReturnRoute();
        };

        async function goToPricing() {
            await computeSegmentData();
            renderPricing();
            changeView('view-pricing');
        }

        function getPassengersPrefs() {
            const selected = document.querySelector('input[name="passengerMode"]:checked');
            const mode = selected ? selected.value : 'mixed';
            return {
                count: 1,
                womenOnly: mode === 'womenOnly',
                maxBackSeats: mode === 'maxBackSeats'
            };
        }

        function savePassengersPrefs() {
            const draft = JSON.parse(localStorage.getItem('vtc_trip_draft')) || {};
            draft.passengers = getPassengersPrefs();
            localStorage.setItem('vtc_trip_draft', JSON.stringify(draft));
        }

        function generateSegmentObjects() {
            const itinerary = JSON.parse(localStorage.getItem('vtc_final_itinerary')) || [];
            const draft = JSON.parse(localStorage.getItem('vtc_trip_draft')) || {};
            const passengers = draft.passengers || {
                count: 1,
                womenOnly: false,
                maxBackSeats: false
            };
            const pricingSteps = (draft.pricing && draft.pricing.steps) || [];

            if (itinerary.length < 2) return [];

            const segments = [];
            for (let i = 0; i < itinerary.length - 1; i++) {
                const from = itinerary[i].name;
                const to = itinerary[i + 1].name;
                const price = pricingSteps[i] ? pricingSteps[i].price : 20;
                segments.push({
                    from,
                    to,
                    price,
                    passengers: {
                        ...passengers
                    }
                });
            }

            localStorage.setItem('vtc_pricing_segments', JSON.stringify(segments));
            return segments;
        }

        const inputDate = document.getElementById('input-date');
        const inputTime = document.getElementById('input-time');

        function saveDateTime() {
            saveTripDraft({
                datetime: {
                    date: inputDate.value,
                    time: inputTime.value
                }
            });
        }
        if (inputDate) inputDate.addEventListener('change', saveDateTime);
        if (inputTime) inputTime.addEventListener('change', saveDateTime);

        // Sauvegarder les valeurs par défaut au chargement
        document.addEventListener('DOMContentLoaded', () => {
            if (inputDate && inputTime && inputDate.value && inputTime.value) {
                const draft = getTripDraft();
                if (!draft.datetime) {
                    saveDateTime();
                }
            }
        });
        const segments = generateSegmentObjects();
        console.log(segments);

        document.addEventListener('DOMContentLoaded', () => {
            const draft = getTripDraft();
            if (draft.passengers?.count) {
                const el = document.getElementById('qty-passengers');
                if (el) el.innerText = draft.passengers.count;
            }
            const savedMode = draft.booking?.mode;
            if (savedMode) {
                document.querySelectorAll('.card-option').forEach(card => {
                    card.classList.toggle('selected', card.dataset.mode === savedMode);
                });
            }
        });

        const STORAGE_KEY = 'vtc_trip_draft';

        function getTripDraft() {
            return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
        }

        function saveTripDraft(partial) {
            const current = getTripDraft() || {};
            const updated = {
                ...current,
                ...partial,
                pricing: {
                    ...(current.pricing || {}),
                    ...(partial.pricing || {}),
                    steps: partial.pricing?.steps ?
                        partial.pricing.steps : (current.pricing?.steps || [])
                }
            };
            localStorage.setItem(STORAGE_KEY, JSON.stringify(updated));
            return updated;
        }

        function saveTripDraftPartial(partial) {
            try {
                const current = getTripDraft();
                const updated = {
                    ...current,
                    ...partial
                };
                localStorage.setItem(STORAGE_KEY, JSON.stringify(updated));
                return updated;
            } catch (e) {
                console.error('Erreur lors de la sauvegarde du draft', e);
                return partial;
            }
        }

        let returnRoutes = [];
        let selectedReturnRouteIndex = 0;
        let returnRouteLayer = null;

        async function loadReturnRoutes() {
            const itinerary = JSON.parse(localStorage.getItem('vtc_final_itinerary')) || [];
            if (itinerary.length < 2) return;

            const start = itinerary[itinerary.length - 1].latlng;
            const end = itinerary[0].latlng;

            const url =
                `https://router.project-osrm.org/route/v1/driving/${start[1]},${start[0]};${end[1]},${end[0]}?overview=full&geometries=geojson&alternatives=true`;

            const res = await fetch(url);
            const data = await res.json();

            returnRoutes = data.routes;
            renderReturnRouteList();
            displayReturnRouteOnMap(0);
        }

        function renderReturnRouteList() {
            const container = document.getElementById('return-routes-list');
            container.innerHTML = '';

            returnRoutes.forEach((route, i) => {
                const div = document.createElement('div');
                div.className =
                    `route-option p-4 rounded-xl bg-white ${i === selectedReturnRouteIndex ? 'selected' : ''}`;

                div.onclick = () => {
                    selectedReturnRouteIndex = i;
                    renderReturnRouteList();
                    displayReturnRouteOnMap(i);
                };

                const dist = (route.distance / 1000).toFixed(1);
                const dur = Math.round(route.duration / 60);

                div.innerHTML = `
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-black text-slate-800">Option ${i + 1}</span>
                    </div>
                    <div class="flex gap-3 text-sm font-bold text-slate-500">
                        <span><i class="fa-solid fa-road mr-1"></i> ${dist} km</span>
                        <span><i class="fa-solid fa-clock mr-1"></i> ${dur} min</span>
                    </div>
                `;
                container.appendChild(div);
            });
        }

        function displayReturnRouteOnMap(index) {
            const map = maps.returnRoute;
            if (returnRouteLayer) map.removeLayer(returnRouteLayer);

            returnRouteLayer = L.geoJSON(returnRoutes[index].geometry, {
                style: {
                    color: '#ff3c00',
                    weight: 6,
                    opacity: 0.8
                }
            }).addTo(map);

            map.fitBounds(returnRouteLayer.getBounds(), {
                padding: [40, 40]
            });
            saveReturnRoute(index);
        }

        function saveReturnRoute(index) {
            const route = returnRoutes[index];
            const retourTrajet = {
                selectedRouteIndex: index,
                distance: route.distance,
                duration: route.duration,
                geometry: route.geometry
            };
            localStorage.setItem('retour_trajet', JSON.stringify(retourTrajet));
            saveTripDraftPartial({
                retour_trajet: retourTrajet
            });
        }

        // ===== MODIFIÉ : btn-validate-return-route → va vers escales retour =====
        document.getElementById('btn-validate-return-route').onclick = () => {
            const route = returnRoutes[selectedReturnRouteIndex];

            const retourTrajet = {
                selectedRouteIndex: selectedReturnRouteIndex,
                distance: route.distance,
                duration: route.duration,
                geometry: route.geometry
            };

            localStorage.setItem('retour_trajet', JSON.stringify(retourTrajet));

            if (!returnRoutes[selectedReturnRouteIndex]) {
                alert("Aucune route retour sélectionnée.");
                return;
            }

            // → Aller vers la page escales retour au lieu du pricing directement
            changeView('view-return-steps');
            initMap('map-return-steps', 'returnSteps');

            // Afficher la route sur la carte des escales retour
            setTimeout(() => {
                if (maps.returnSteps) {
                    L.geoJSON(returnRoutes[selectedReturnRouteIndex].geometry, {
                        style: {
                            color: '#ff3c00',
                            weight: 4,
                            opacity: 0.6
                        }
                    }).addTo(maps.returnSteps);
                    maps.returnSteps.fitBounds(
                        L.geoJSON(returnRoutes[selectedReturnRouteIndex].geometry).getBounds(), {
                            padding: [40, 40]
                        }
                    );
                }
            }, 200);

            loadReturnIntermediateCities();
        };

        function generateReturnPricingSteps() {
            // Utiliser l'itinéraire retour complet (avec escales)
            const returnItinerary = JSON.parse(localStorage.getItem('vtc_return_itinerary')) || [];

            // Fallback si pas d'itinéraire retour stocké
            let itineraryToUse = returnItinerary;
            if (itineraryToUse.length < 2) {
                const itinerary = JSON.parse(localStorage.getItem('vtc_final_itinerary')) || [];
                itineraryToUse = [...itinerary].reverse();
            }

            const container = document.getElementById('return-pricing-steps-container');
            container.innerHTML = '';

            let returnPricing = [];

            for (let i = 0; i < itineraryToUse.length - 1; i++) {
                const from = itineraryToUse[i].name;
                const to = itineraryToUse[i + 1].name;

                returnPricing.push({
                    from,
                    to,
                    price: 0
                });

                const div = document.createElement('div');
                div.className = "p-4 bg-slate-50 rounded-2xl border border-slate-100";

                div.innerHTML = `
                    <div class="flex justify-between items-center mb-3">
                        <span class="font-bold text-slate-800">${from} → ${to}</span>
                        <span id="price-label-${i}" class="text-[#ff3c00] font-black">0€</span>
                    </div>
                    <input type="range"
                        min="0"
                        max="100"
                        value="0"
                        class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#ff3c00]"
                        oninput="updateReturnPrice(${i}, this.value)">
                `;

                container.appendChild(div);
            }

            localStorage.setItem('return_pricing', JSON.stringify(returnPricing));
        }

        function updateReturnPrice(index, value) {
            let pricing = JSON.parse(localStorage.getItem('return_pricing')) || [];
            pricing[index].price = parseInt(value);
            localStorage.setItem('return_pricing', JSON.stringify(pricing));
            document.getElementById(`price-label-${index}`).innerText = value + "€";
            calculateReturnTotal();
        }

        function calculateReturnTotal() {
            let pricing = JSON.parse(localStorage.getItem('return_pricing')) || [];
            const total = pricing.reduce((sum, step) => sum + step.price, 0);
            document.getElementById('return-total-price').innerHTML =
                `${total}<span class="text-[#ff3c00] ml-1 text-4xl">€</span>`;
            localStorage.setItem('return_total_price', total);
        }

        document.getElementById('btn-go-driver-info').onclick = () => {
            const retourTrajet = JSON.parse(localStorage.getItem('retour_trajet'));
            const returnPricing = JSON.parse(localStorage.getItem('return_pricing'));
            const total = localStorage.getItem('return_total_price');

            const returnData = {
                trajet: retourTrajet,
                pricing: returnPricing,
                total: parseInt(total)
            };

            localStorage.setItem('return_trip_data', JSON.stringify(returnData));
            changeView('view-driver-info');
        };

        const returnDateInput = document.getElementById('return-date');
        const returnTimeInput = document.getElementById('return-time');

        function saveReturnDateTime() {
            const returnDateTime = {
                date: returnDateInput.value,
                time: returnTimeInput.value
            };
            localStorage.setItem('return_datetime', JSON.stringify(returnDateTime));
            saveTripDraftPartial({
                return_datetime: returnDateTime
            });
        }

        if (returnDateInput) returnDateInput.addEventListener('change', saveReturnDateTime);
        if (returnTimeInput) returnTimeInput.addEventListener('change', saveReturnDateTime);

        document.getElementById('btn-publish').addEventListener('click', async () => {
            try {
                let draft = getTripDraft();
                const finalItinerary = JSON.parse(localStorage.getItem('vtc_final_itinerary')) || [];
                const returnItinerary = JSON.parse(localStorage.getItem('vtc_return_itinerary')) || [];

                if (finalItinerary.length === 0) {
                    alert('Itinéraire vide. Veuillez sélectionner un trajet.');
                    return;
                }

                draft = saveTripDraftPartial({
                    itineraire: finalItinerary
                });

                const fileInput = document.getElementById('driver-photo');
                const message = document.getElementById('driver-message').value;

                const prixTotal = draft.pricing?.steps?.reduce((sum, s) => sum + (s.price || 0), 0) || 0;
                const returnTripData = JSON.parse(localStorage.getItem('return_trip_data')) || null;
                const returnDateTime = JSON.parse(localStorage.getItem('return_datetime')) || null;

                const formData = new FormData();
                formData.append('passenger_mode', draft.passengers?.mode || 'mixed');
                formData.append('depart', finalItinerary[0]?.name || '');
                formData.append('destination', finalItinerary[finalItinerary.length - 1]?.name || '');
                formData.append('date_depart', draft.datetime?.date || '');
                formData.append('heure_depart', draft.datetime?.time || '');
                formData.append('nb_places', draft.passengers?.count || 1);
                formData.append('prix_place', prixTotal);
                formData.append('retour', draft.returnTrip ? 1 : 0);
                formData.append('itineraire', JSON.stringify(finalItinerary));
                formData.append('segments', JSON.stringify(draft.pricing?.steps || []));
                formData.append('message_conducteur', message);
                formData.append('selected_route', JSON.stringify(draft.selectedRoute || {}));
                formData.append('selected_route_index', draft.selectedRouteIndex ?? 0);
                formData.append('return_trip_data', JSON.stringify(returnTripData));
                formData.append('return_datetime', JSON.stringify(returnDateTime));
                formData.append('return_itinerary', JSON.stringify(returnItinerary));
                formData.append('booking_mode', draft.booking?.mode || 'instant');

                if (fileInput.files[0]) {
                    formData.append('photo_conducteur', fileInput.files[0]);
                }

                const res = await fetch('/covoiturage/publish', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });

                if (!res.ok) {
                    const text = await res.text();
                    console.error('Erreur serveur:', text);
                    throw new Error('Réponse serveur invalide');
                }

                const data = await res.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: 'Trajet publié avec succès !',
                        confirmButtonColor: '#ff3c00',
                        timer: 2000,
                        timerProgressBar: true
                    });
                    localStorage.removeItem('retour_trajet');
                    localStorage.removeItem('return_datetime');
                    localStorage.removeItem('return_pricing');
                    localStorage.removeItem('return_total_price');
                    localStorage.removeItem('return_trip_data');
                    localStorage.removeItem('vtc_final_itinerary');
                    localStorage.removeItem('vtc_return_itinerary');
                    localStorage.removeItem('vtc_pricing_segments');
                    localStorage.removeItem('vtc_trip_draft');
                    window.location.href = `/trajet/${data.covoiturage_id}`;
                } else {
                    console.error(data);
                    alert(data.message || 'Erreur lors de la publication.');
                }

            } catch (err) {
                console.error(err);
                alert('Erreur réseau ou données invalides.');
            }
        });
    </script>
@endsection
