@extends('layouts.connected')

@section('title', 'Modifier l\'itinéraire | ' . config('app.name'))
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
    .tab-btn {
        transition: all 0.3s ease;
    }

    .tab-btn.active {
        background: #0f172a;
        color: white;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3);
    }

    .tab-btn:not(.active) {
        background: white;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    .tab-btn:not(.active):hover {
        border-color: #cbd5e1;
        color: #0f172a;
    }

    .tab-panel {
        display: none;
        animation: fadeUp 0.35s ease;
    }

    .tab-panel.active {
        display: block;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .step-card { transition: all 0.3s ease; }
    .step-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.1); }
    .step-card.selected { border-color: #f97316; background: #fff7ed; }
    .step-card.selected .step-check { background: #f97316; border-color: #f97316; color: white; }

    #edit-map { height: 400px; border-radius: 24px; overflow: hidden; }

    .price-input::-webkit-inner-spin-button { -webkit-appearance: none; }

    .loader-dots span { animation: bounce 1.4s infinite ease-in-out both; }
    .loader-dots span:nth-child(1) { animation-delay: -0.32s; }
    .loader-dots span:nth-child(2) { animation-delay: -0.16s; }

    @keyframes bounce {
        0%, 80%, 100% { transform: scale(0); }
        40% { transform: scale(1); }
    }

    .route-option {
        border: 2px solid #e2e8f0;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .route-option:hover {
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }
    .route-option.selected {
        border-color: #f97316;
        background: #fff7ed;
        box-shadow: 0 4px 15px -3px rgba(249, 115, 22, 0.2);
    }

    .route-color-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        flex-shrink: 0;
    }
</style>

@section('content')
    <div class="min-h-screen">
        <div class="max-w-5xl mx-auto px-4">

            <!-- Breadcrumb -->
            <div class="mb-8">
                <nav class="flex items-center space-x-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">
                    <a href="{{ route('covoiturage.index') }}" class="hover:text-orange-600 transition-colors">Mes trajets</a>
                    <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                    </svg>
                    <a href="{{ route('covoiturage.edit', $covoiturage->covoiturage_id) }}"
                        class="hover:text-orange-600 transition-colors">Édition trajet</a>
                    <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-slate-900">Modifier l'itinéraire</span>
                </nav>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                    Modifier l'itinéraire <span class="text-orange-600">#TR-{{ $covoiturage->covoiturage_id }}</span>
                </h1>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

                <!-- Colonne gauche -->
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden">

                        <!-- Tabs -->
                        <div class="flex p-3 gap-2 bg-slate-50/50 border-b border-slate-100">
                            <button onclick="switchTab('locations')" id="tab-locations"
                                class="tab-btn active flex-1 flex items-center justify-center gap-2 py-3 rounded-2xl text-xs font-black uppercase tracking-wider">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Départ & Arrivée
                            </button>
                            <button onclick="switchTab('routes')" id="tab-routes"
                                class="tab-btn flex-1 flex items-center justify-center gap-2 py-3 rounded-2xl text-xs font-black uppercase tracking-wider">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                                Itinéraire
                            </button>
                            <button onclick="switchTab('stops')" id="tab-stops"
                                class="tab-btn flex-1 flex items-center justify-center gap-2 py-3 rounded-2xl text-xs font-black uppercase tracking-wider">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                Escales
                                <span id="stops-count"
                                    class="hidden ml-1 w-5 h-5 bg-orange-600 text-white rounded-full text-[10px] items-center justify-center">0</span>
                            </button>
                        </div>

                        <!-- Tab 1 : Départ & Arrivée -->
                        <div id="panel-locations" class="tab-panel active p-6 md:p-8">
                            <div class="mb-8">
                                <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-3 block">Point de départ</label>
                                <div class="relative">
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 w-3 h-3 rounded-full bg-orange-600"></div>
                                    <input type="text" id="input-depart" value="{{ $covoiturage->depart }}"
                                        placeholder="Saisissez une adresse de départ..."
                                        class="w-full pl-10 pr-12 py-4 bg-slate-50/80 rounded-2xl border border-slate-200 focus:border-orange-400 focus:ring-4 focus:ring-orange-100 outline-none text-sm font-bold text-slate-800 transition-all"
                                        autocomplete="off">
                                    <button onclick="clearInput('depart')"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 hover:text-red-500 transition-colors hidden"
                                        id="clear-depart">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="flex justify-center my-2">
                                <div class="w-[2px] h-8 bg-slate-200 rounded-full"></div>
                            </div>

                            <div class="mt-8">
                                <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-3 block">Destination</label>
                                <div class="relative">
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 w-3 h-3 rounded-full bg-slate-900"></div>
                                    <input type="text" id="input-destination" value="{{ $covoiturage->destination }}"
                                        placeholder="Saisissez une adresse d'arrivée..."
                                        class="w-full pl-10 pr-12 py-4 bg-slate-50/80 rounded-2xl border border-slate-200 focus:border-slate-400 focus:ring-4 focus:ring-slate-100 outline-none text-sm font-bold text-slate-800 transition-all"
                                        autocomplete="off">
                                    <button onclick="clearInput('destination')"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 hover:text-red-500 transition-colors hidden"
                                        id="clear-destination">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-10">
                                <button onclick="validateLocations()" id="btn-validate-locations"
                                    class="w-full flex items-center justify-center gap-3 py-4 bg-slate-900 hover:bg-orange-600 text-white rounded-2xl transition-all duration-300 shadow-xl shadow-slate-200 hover:shadow-orange-200 hover:-translate-y-0.5">
                                    <span class="text-[11px] font-black uppercase tracking-[0.2em]">Valider & choisir l'itinéraire</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Tab 2 : Choix de route -->
                        <div id="panel-routes" class="tab-panel p-6 md:p-8">

                            <!-- Loader routes -->
                            <div id="routes-loader" class="hidden flex flex-col items-center justify-center py-16">
                                <div class="loader-dots flex gap-2 mb-4">
                                    <span class="w-3 h-3 bg-orange-600 rounded-full inline-block"></span>
                                    <span class="w-3 h-3 bg-orange-600 rounded-full inline-block"></span>
                                    <span class="w-3 h-3 bg-orange-600 rounded-full inline-block"></span>
                                </div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Calcul des itinéraires...</p>
                            </div>

                            <!-- État vide routes -->
                            <div id="routes-empty" class="flex flex-col items-center justify-center py-16">
                                <div class="w-16 h-16 bg-slate-100 rounded-3xl flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-slate-400">Validez d'abord le départ et la destination</p>
                            </div>

                            <!-- Liste des routes -->
                            <div id="routes-list-wrapper" class="hidden">
                                <div class="mb-6">
                                    <p class="text-sm font-black text-slate-900">Itinéraires disponibles</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Sélectionnez l'itinéraire que vous souhaitez emprunter</p>
                                </div>

                                <div id="routes-list" class="space-y-3"></div>

                                <div class="mt-8">
                                    <button onclick="validateRoute()" id="btn-validate-route"
                                        class="w-full flex items-center justify-center gap-3 py-4 bg-slate-900 hover:bg-orange-600 text-white rounded-2xl transition-all duration-300 shadow-xl shadow-slate-200 hover:shadow-orange-200 hover:-translate-y-0.5">
                                        <span class="text-[11px] font-black uppercase tracking-[0.2em]">Valider & charger les escales</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3 : Escales -->
                        <div id="panel-stops" class="tab-panel p-6 md:p-8">

                            <div id="stops-loader" class="hidden flex flex-col items-center justify-center py-16">
                                <div class="loader-dots flex gap-2 mb-4">
                                    <span class="w-3 h-3 bg-orange-600 rounded-full inline-block"></span>
                                    <span class="w-3 h-3 bg-orange-600 rounded-full inline-block"></span>
                                    <span class="w-3 h-3 bg-orange-600 rounded-full inline-block"></span>
                                </div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Chargement des escales...</p>
                            </div>

                            <div id="stops-empty" class="flex flex-col items-center justify-center py-16">
                                <div class="w-16 h-16 bg-slate-100 rounded-3xl flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-slate-400">Choisissez d'abord un itinéraire</p>
                                <p class="text-xs text-slate-300 mt-1">Les escales seront calculées automatiquement</p>
                            </div>

                            <div id="stops-list" class="hidden">
                                <div class="flex items-center justify-between mb-6">
                                    <div>
                                        <p class="text-sm font-black text-slate-900">Escales détectées</p>
                                        <p class="text-xs text-slate-400 mt-0.5">Sélectionnez les escales à conserver et définissez le prix par segment</p>
                                    </div>
                                    <button onclick="toggleAllStops()" id="btn-toggle-all"
                                        class="text-[10px] font-bold uppercase tracking-widest text-orange-600 hover:text-orange-700 transition-colors">
                                        Tout sélectionner
                                    </button>
                                </div>

                                <div class="flex items-center gap-3 mb-3 px-4 py-2 bg-orange-50 rounded-xl">
                                    <div class="w-3 h-3 rounded-full bg-orange-600 flex-shrink-0"></div>
                                    <span class="text-xs font-black text-orange-700 truncate" id="label-depart">{{ $covoiturage->depart }}</span>
                                </div>

                                <div id="stops-container" class="space-y-3 mb-3"></div>

                                <!-- Ajouter une escale manuellement -->
                                <div class="mb-3">
                                    <div id="add-stop-form" class="hidden p-4 bg-blue-50 rounded-2xl border border-blue-200 space-y-3">
                                        <p class="text-xs font-black text-blue-800 uppercase tracking-widest">Nouvelle escale</p>
                                        <div class="relative">
                                            <input type="text" id="input-custom-stop"
                                                placeholder="Rechercher une ville..."
                                                class="w-full px-4 py-3 bg-white rounded-xl border border-blue-200 focus:border-blue-400 focus:ring-4 focus:ring-blue-100 outline-none text-sm font-bold text-slate-800 transition-all"
                                                autocomplete="off">
                                            <div id="custom-stop-results"
                                                class="absolute z-50 w-full bg-white border border-slate-200 rounded-xl mt-1 shadow-lg hidden max-h-48 overflow-y-auto"></div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="number" min="0" step="50" value="0" id="input-custom-stop-price"
                                                placeholder="Prix"
                                                class="price-input flex-1 px-3 py-2 text-sm font-bold text-center bg-white border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-200 focus:border-blue-400 outline-none">
                                            <span class="text-xs font-bold text-slate-400">DA</span>
                                        </div>
                                        <div class="flex gap-2">
                                            <button onclick="confirmAddCustomStop()"
                                                class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                                Ajouter
                                            </button>
                                            <button onclick="cancelAddCustomStop()"
                                                class="py-2.5 px-4 bg-white hover:bg-slate-50 text-slate-500 rounded-xl text-[10px] font-black uppercase tracking-widest border border-slate-200 transition-all">
                                                Annuler
                                            </button>
                                        </div>
                                    </div>
                                    <button onclick="showAddStopForm()" id="btn-add-stop"
                                        class="w-full flex items-center justify-center gap-2 py-3 border-2 border-dashed border-slate-200 hover:border-orange-300 hover:bg-orange-50 rounded-2xl transition-all group">
                                        <svg class="w-4 h-4 text-slate-400 group-hover:text-orange-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 group-hover:text-orange-600 transition-colors">Ajouter une escale</span>
                                    </button>
                                </div>

                                <div class="flex items-center gap-3 px-4 py-2 bg-slate-100 rounded-xl">
                                    <div class="w-3 h-3 rounded-full bg-slate-900 flex-shrink-0"></div>
                                    <span class="text-xs font-black text-slate-700 truncate" id="label-destination">{{ $covoiturage->destination }}</span>
                                </div>

                                <div class="mt-4 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs font-bold text-slate-600">Prix du dernier segment</p>
                                            <p class="text-[10px] text-slate-400 mt-0.5" id="last-segment-label">Dernière escale → Destination</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="number" min="0" step="50" value="0" id="price-last-segment"
                                                class="price-input w-24 px-3 py-2 text-sm font-bold text-center bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none">
                                            <span class="text-xs font-bold text-slate-400">€</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="price-summary" class="hidden mt-6 p-5 bg-gradient-to-r from-orange-50 to-amber-50 rounded-2xl border border-orange-100">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-black uppercase tracking-widest text-orange-800">Prix total du trajet</span>
                                    <span class="text-xl font-black text-orange-600" id="total-price">0 €</span>
                                </div>
                            </div>

                            <div class="mt-8" id="stops-save-wrapper" style="display:none;">
                                <button onclick="saveRoute()" id="btn-save"
                                    class="w-full flex items-center justify-center gap-3 py-4 bg-orange-600 hover:bg-orange-700 text-white rounded-2xl transition-all duration-300 shadow-xl shadow-orange-200 hover:-translate-y-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-[11px] font-black uppercase tracking-[0.2em]">Sauvegarder les modifications</span>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Colonne droite : Carte -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 p-4 sticky top-6">
                        <div id="edit-map" class="w-full"></div>
                        <div id="route-info" class="hidden mt-4 p-4 bg-slate-50 rounded-2xl">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Distance</p>
                                    <p class="text-sm font-black text-slate-900" id="route-distance">--</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Durée</p>
                                    <p class="text-sm font-black text-slate-900" id="route-duration">--</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        window._existingItineraire = @json($covoiturage->itineraire ?? []);
        window._existingSegments = @json($covoiturage->segments ?? []);
        window._covoiturageId = {{ $covoiturage->covoiturage_id }};
    </script>
    <script>
        let map;
        let departPlace = null;
        let destPlace = null;
        let intermediateCities = [];
        let selectedStops = new Set();

        // Routes
        let routes = [];
        let selectedRouteIndex = 0;
        let routeLayers = [];
        let startMarker = null;
        let endMarker = null;

        const ROUTE_COLORS = ['#f97316', '#3b82f6', '#10b981'];
        const ROUTE_COLORS_DIM = ['rgba(249,115,22,0.3)', 'rgba(59,130,246,0.3)', 'rgba(16,185,129,0.3)'];

        const existingItineraire = window._existingItineraire || [];
        const existingSegments = window._existingSegments || [];

        // =============================================
        // INIT MAP
        // =============================================
        function initEditMap() {
            map = L.map('edit-map', { zoomControl: false }).setView([36.75, 3.06], 6);
            L.control.zoom({ position: 'bottomright' }).addTo(map);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '©OpenStreetMap'
            }).addTo(map);

            // Charger le trajet existant
            if (existingItineraire.length >= 2) {
                const start = existingItineraire.find(p => p.type === 'start');
                const end = existingItineraire.find(p => p.type === 'end');
                if (start && end) {
                    departPlace = { name: start.name, latlng: start.latlng };
                    destPlace = { name: end.name, latlng: end.latlng };
                    addMarkers();
                    loadRoutes();
                }
            }
        }

        function addMarkers() {
            if (startMarker) map.removeLayer(startMarker);
            if (endMarker) map.removeLayer(endMarker);

            if (departPlace) {
                startMarker = L.marker(departPlace.latlng, {
                    icon: L.divIcon({
                        className: '',
                        html: '<div style="width:16px;height:16px;background:#f97316;border:3px solid white;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,0.3)"></div>',
                        iconSize: [16, 16],
                        iconAnchor: [8, 8]
                    })
                }).addTo(map);
            }
            if (destPlace) {
                endMarker = L.marker(destPlace.latlng, {
                    icon: L.divIcon({
                        className: '',
                        html: '<div style="width:16px;height:16px;background:#0f172a;border:3px solid white;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,0.3)"></div>',
                        iconSize: [16, 16],
                        iconAnchor: [8, 8]
                    })
                }).addTo(map);
            }

            if (departPlace && destPlace) {
                map.fitBounds(L.latLngBounds([departPlace.latlng, destPlace.latlng]), { padding: [40, 40] });
            }
        }

        // =============================================
        // AUTOCOMPLETE (Nominatim)
        // =============================================
        function setupAutocomplete() {
            setupSingleAutocomplete('input-depart', 'depart-results', 'depart');
            setupSingleAutocomplete('input-destination', 'destination-results', 'destination');
        }

        function setupSingleAutocomplete(inputId, resultsId, type) {
            const input = document.getElementById(inputId);
            let resDiv = document.getElementById(resultsId);
            if (!resDiv) {
                resDiv = document.createElement('div');
                resDiv.id = resultsId;
                resDiv.className = 'absolute z-50 w-full bg-white border border-slate-200 rounded-xl mt-1 shadow-lg hidden max-h-60 overflow-y-auto';
                input.parentElement.style.position = 'relative';
                input.parentElement.appendChild(resDiv);
            }

            input.addEventListener('input', debounce(async (e) => {
                const query = e.target.value;
                if (query.length < 3) { resDiv.classList.add('hidden'); return; }

                try {
                    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`;
                    const response = await fetch(url);
                    const data = await response.json();
                    resDiv.innerHTML = '';
                    resDiv.classList.remove('hidden');

                    data.forEach(place => {
                        const item = document.createElement('div');
                        item.className = 'px-4 py-3 text-sm text-slate-700 hover:bg-orange-50 cursor-pointer border-b border-slate-50 last:border-0';
                        item.textContent = place.display_name;
                        item.onclick = () => {
                            input.value = place.display_name;
                            const coords = [parseFloat(place.lat), parseFloat(place.lon)];
                            if (type === 'depart') {
                                departPlace = { name: place.display_name, latlng: coords };
                                document.getElementById('clear-depart').classList.remove('hidden');
                            } else {
                                destPlace = { name: place.display_name, latlng: coords };
                                document.getElementById('clear-destination').classList.remove('hidden');
                            }
                            resDiv.classList.add('hidden');
                            addMarkers();
                        };
                        resDiv.appendChild(item);
                    });
                } catch (err) { console.error('Nominatim error:', err); }
            }, 300));

            document.addEventListener('click', (e) => {
                if (!input.contains(e.target) && !resDiv.contains(e.target)) resDiv.classList.add('hidden');
            });
        }

        // =============================================
        // ÉTAPE 1 → CHARGER LES ROUTES
        // =============================================
        async function validateLocations() {
            if (!departPlace && existingItineraire.length >= 2) {
                const s = existingItineraire.find(p => p.type === 'start');
                if (s) departPlace = { name: s.name, latlng: s.latlng };
            }
            if (!destPlace && existingItineraire.length >= 2) {
                const e = existingItineraire.find(p => p.type === 'end');
                if (e) destPlace = { name: e.name, latlng: e.latlng };
            }

            if (!departPlace || !destPlace) {
                Swal.fire({ icon: 'warning', title: 'Champs requis', text: 'Sélectionnez un départ et une destination depuis les suggestions.' });
                return;
            }

            switchTab('routes');
            document.getElementById('routes-empty').classList.add('hidden');
            document.getElementById('routes-loader').classList.remove('hidden');

            addMarkers();
            await loadRoutes();
        }

        async function loadRoutes() {
            const s = departPlace.latlng;
            const e = destPlace.latlng;

            try {
                const url = `https://router.project-osrm.org/route/v1/driving/${s[1]},${s[0]};${e[1]},${e[0]}?overview=full&geometries=geojson&alternatives=true`;
                const res = await fetch(url);
                const data = await res.json();

                routes = data.routes || [];
                selectedRouteIndex = 0;

                document.getElementById('routes-loader').classList.add('hidden');

                if (routes.length === 0) {
                    document.getElementById('routes-empty').classList.remove('hidden');
                    document.getElementById('routes-empty').querySelector('p').textContent = 'Aucun itinéraire trouvé';
                    return;
                }

                document.getElementById('routes-list-wrapper').classList.remove('hidden');
                renderRouteList();
                displayAllRoutesOnMap();
                highlightRoute(0);

            } catch (err) {
                console.error('OSRM error:', err);
                document.getElementById('routes-loader').classList.add('hidden');
                Swal.fire({ icon: 'error', title: 'Erreur', text: 'Impossible de calculer les itinéraires.' });
            }
        }

        function renderRouteList() {
            const container = document.getElementById('routes-list');
            container.innerHTML = '';

            routes.forEach((route, i) => {
                const dist = (route.distance / 1000).toFixed(1);
                const dur = Math.round(route.duration / 60);
                const hours = Math.floor(dur / 60);
                const mins = dur % 60;
                const durText = hours > 0 ? `${hours}h ${mins}min` : `${mins} min`;

                const div = document.createElement('div');
                div.className = `route-option p-4 rounded-2xl ${i === selectedRouteIndex ? 'selected' : ''}`;
                div.id = `route-option-${i}`;
                div.onclick = () => selectRoute(i);

                const labels = ['Plus rapide', 'Alternative', 'Alternative'];
                const labelColors = ['bg-orange-500', 'bg-blue-500', 'bg-emerald-500'];

                div.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="route-color-dot" style="background:${ROUTE_COLORS[i] || ROUTE_COLORS[0]}"></div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-black text-slate-800 text-sm">Option ${i + 1}</span>
                                ${i < 3 ? `<span class="text-[9px] ${labelColors[i]} text-white px-2 py-0.5 rounded-full font-bold uppercase">${labels[i]}</span>` : ''}
                            </div>
                            <div class="flex gap-3 text-xs font-bold text-slate-500">
                                <span>🛣️ ${dist} km</span>
                                <span>⏱️ ${durText}</span>
                            </div>
                        </div>
                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all ${i === selectedRouteIndex ? 'border-orange-500 bg-orange-500' : 'border-slate-300'}">
                            ${i === selectedRouteIndex ? '<svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>' : ''}
                        </div>
                    </div>
                `;
                container.appendChild(div);
            });
        }

        function displayAllRoutesOnMap() {
            // Supprimer les anciennes
            routeLayers.forEach(l => map.removeLayer(l));
            routeLayers = [];

            // Dessiner toutes les routes (dim)
            routes.forEach((route, i) => {
                const layer = L.geoJSON(route.geometry, {
                    style: {
                        color: ROUTE_COLORS_DIM[i] || ROUTE_COLORS_DIM[0],
                        weight: 4,
                        opacity: 1
                    }
                }).addTo(map);

                layer.on('click', () => selectRoute(i));
                routeLayers.push(layer);
            });

            if (routeLayers.length > 0) {
                map.fitBounds(routeLayers[0].getBounds(), { padding: [40, 40] });
            }
        }

        function highlightRoute(index) {
            routeLayers.forEach((layer, i) => {
                layer.setStyle({
                    color: i === index ? ROUTE_COLORS[i] || ROUTE_COLORS[0] : ROUTE_COLORS_DIM[i] || ROUTE_COLORS_DIM[0],
                    weight: i === index ? 6 : 3,
                    opacity: 1
                });
                if (i === index) layer.bringToFront();
            });

            // Infos route
            const route = routes[index];
            const dist = (route.distance / 1000).toFixed(1) + ' km';
            const dur = Math.round(route.duration / 60);
            const hours = Math.floor(dur / 60);
            const mins = dur % 60;
            document.getElementById('route-distance').textContent = dist;
            document.getElementById('route-duration').textContent = hours > 0 ? `${hours}h ${mins}min` : `${mins} min`;
            document.getElementById('route-info').classList.remove('hidden');
        }

        function selectRoute(index) {
            selectedRouteIndex = index;
            renderRouteList();
            highlightRoute(index);
        }

        // =============================================
        // ÉTAPE 2 → VALIDER ROUTE ET CHARGER ESCALES
        // =============================================
        async function validateRoute() {
            document.getElementById('label-depart').textContent = departPlace.name;
            document.getElementById('label-destination').textContent = destPlace.name;

            switchTab('stops');
            showStopsLoader();

            // Afficher seulement la route sélectionnée
            routeLayers.forEach((layer, i) => {
                if (i !== selectedRouteIndex) map.removeLayer(layer);
            });
            routeLayers[selectedRouteIndex].setStyle({ color: '#f97316', weight: 5, opacity: 0.8 });

            await extractCitiesFromRoute();
        }

        // =============================================
        // EXTRAIRE VILLES VIA NOMINATIM REVERSE
        // =============================================
        async function extractCitiesFromRoute() {
            const coords = routes[selectedRouteIndex].geometry.coordinates;
            const numSamples = 8;
            const seenCities = new Set();
            const cities = [];

            for (let i = 1; i <= numSamples; i++) {
                const idx = Math.floor((i / (numSamples + 1)) * coords.length);
                const point = coords[idx]; // [lon, lat]

                try {
                    const revRes = await fetch(
                        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${point[1]}&lon=${point[0]}`
                    );
                    const revData = await revRes.json();
                    const cityName = revData.address?.city || revData.address?.town || revData.address?.village;

                    if (cityName && !seenCities.has(cityName.toLowerCase())) {
                        seenCities.add(cityName.toLowerCase());
                        cities.push({ name: cityName, latlng: [point[1], point[0]] });
                    }
                } catch (e) {
                    console.warn('Reverse geocoding failed', e);
                }
            }

            intermediateCities = cities;
            renderStops();
        }

        // =============================================
        // AFFICHER LES ESCALES
        // =============================================
        function renderStops() {
            hideStopsLoader();
            const container = document.getElementById('stops-container');
            const list = document.getElementById('stops-list');
            const empty = document.getElementById('stops-empty');

            if (intermediateCities.length === 0) {
                list.classList.add('hidden');
                empty.classList.remove('hidden');
                empty.querySelector('p:first-child').textContent = 'Aucune escale détectée sur ce trajet';
                return;
            }

            empty.classList.add('hidden');
            list.classList.remove('hidden');
            document.getElementById('stops-save-wrapper').style.display = 'block';
            document.getElementById('price-summary').classList.remove('hidden');
            container.innerHTML = '';
            selectedStops.clear();

            const existingWaypoints = existingItineraire
                .filter(p => p.type === 'waypoint')
                .map(p => p.name.toLowerCase());

            intermediateCities.forEach((city, index) => {
                const isExisting = existingWaypoints.some(
                    w => w.includes(city.name.toLowerCase()) || city.name.toLowerCase().includes(w)
                );

                let existingPrice = 0;
                if (isExisting && existingSegments[index]) {
                    existingPrice = existingSegments[index]?.price || 0;
                }
                if (isExisting) selectedStops.add(index);

                addStopCard(index, city.name, existingPrice, isExisting, false);
            });

            const lastSegPrice = existingSegments.length > 0
                ? (existingSegments[existingSegments.length - 1]?.price || 0) : 0;
            document.getElementById('price-last-segment').value = lastSegPrice;
            document.getElementById('price-last-segment').oninput = updateTotalPrice;

            updateStopsCount();
            updateLastSegmentLabel();
            updateTotalPrice();
        }

        function toggleStop(index, card) {
            if (selectedStops.has(index)) {
                selectedStops.delete(index);
                card.classList.remove('selected');
            } else {
                selectedStops.add(index);
                card.classList.add('selected');
            }
            updateStopsCount();
            updateLastSegmentLabel();
            updateTotalPrice();
        }

        function toggleAllStops() {
            const activeCities = intermediateCities.map((c, i) => c !== null ? i : null).filter(i => i !== null);
            const allSelected = activeCities.every(i => selectedStops.has(i));

            activeCities.forEach(i => {
                const card = document.getElementById(`stop-card-${i}`);
                if (!card) return;
                if (allSelected) { selectedStops.delete(i); card.classList.remove('selected'); }
                else { selectedStops.add(i); card.classList.add('selected'); }
            });
            updateStopsCount();
            updateLastSegmentLabel();
            updateTotalPrice();
            document.getElementById('btn-toggle-all').textContent = allSelected ? 'Tout sélectionner' : 'Tout désélectionner';
        }

        function updateStopsCount() {
            const badge = document.getElementById('stops-count');
            if (selectedStops.size > 0) {
                badge.classList.remove('hidden');
                badge.classList.add('flex');
                badge.textContent = selectedStops.size;
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            }
        }

        function updateLastSegmentLabel() {
            const sorted = [...selectedStops]
                .filter(idx => intermediateCities[idx] !== null)
                .sort((a, b) => a - b);
            const lastCity = sorted.length > 0 ? intermediateCities[sorted[sorted.length - 1]].name : departPlace?.name || 'Départ';
            const destName = destPlace?.name || document.getElementById('input-destination').value;
            document.getElementById('last-segment-label').textContent = `${lastCity} → ${destName}`;
        }

        function updateTotalPrice() {
            let total = 0;
            selectedStops.forEach(idx => {
                if (intermediateCities[idx] === null) return;
                const el = document.getElementById(`price-${idx}`);
                if (el) total += parseFloat(el.value) || 0;
            });
            total += parseFloat(document.getElementById('price-last-segment').value) || 0;
            document.getElementById('total-price').textContent = total.toLocaleString('fr-FR') + ' €';
        }

        // =============================================
        // AJOUT ESCALE MANUELLE
        // =============================================
        let customStopPlace = null;

        function showAddStopForm() {
            document.getElementById('add-stop-form').classList.remove('hidden');
            document.getElementById('btn-add-stop').classList.add('hidden');
            document.getElementById('input-custom-stop').value = '';
            document.getElementById('input-custom-stop-price').value = 0;
            customStopPlace = null;
            setupCustomStopAutocomplete();
            document.getElementById('input-custom-stop').focus();
        }

        function cancelAddCustomStop() {
            document.getElementById('add-stop-form').classList.add('hidden');
            document.getElementById('btn-add-stop').classList.remove('hidden');
            customStopPlace = null;
        }

        function setupCustomStopAutocomplete() {
            const input = document.getElementById('input-custom-stop');
            const resDiv = document.getElementById('custom-stop-results');

            // Retirer l'ancien listener pour éviter les doublons
            const newInput = input.cloneNode(true);
            input.parentNode.replaceChild(newInput, input);

            newInput.addEventListener('input', debounce(async (e) => {
                const query = e.target.value;
                if (query.length < 3) { resDiv.classList.add('hidden'); return; }

                try {
                    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`;
                    const response = await fetch(url);
                    const data = await response.json();
                    resDiv.innerHTML = '';
                    resDiv.classList.remove('hidden');

                    data.forEach(place => {
                        const item = document.createElement('div');
                        item.className = 'px-4 py-3 text-sm text-slate-700 hover:bg-blue-50 cursor-pointer border-b border-slate-50 last:border-0';
                        item.textContent = place.display_name;
                        item.onclick = () => {
                            newInput.value = place.display_name;
                            customStopPlace = {
                                name: place.display_name,
                                latlng: [parseFloat(place.lat), parseFloat(place.lon)]
                            };
                            resDiv.classList.add('hidden');
                        };
                        resDiv.appendChild(item);
                    });
                } catch (err) { console.error('Nominatim error:', err); }
            }, 300));

            document.addEventListener('click', (e) => {
                if (!newInput.contains(e.target) && !resDiv.contains(e.target)) resDiv.classList.add('hidden');
            });
        }

        function confirmAddCustomStop() {
            if (!customStopPlace) {
                Swal.fire({ icon: 'warning', title: 'Ville requise', text: 'Sélectionnez une ville depuis les suggestions.' });
                return;
            }

            // Vérifier doublon (ignorer les null = escales supprimées)
            const alreadyExists = intermediateCities.some(
                c => c !== null && (
                    c.name.toLowerCase() === customStopPlace.name.toLowerCase() ||
                    (Math.abs(c.latlng[0] - customStopPlace.latlng[0]) < 0.01 &&
                     Math.abs(c.latlng[1] - customStopPlace.latlng[1]) < 0.01)
                )
            );

            if (alreadyExists) {
                Swal.fire({ icon: 'info', title: 'Escale existante', text: 'Cette ville est déjà dans la liste des escales.' });
                return;
            }

            const price = parseFloat(document.getElementById('input-custom-stop-price').value) || 0;

            // Ajouter à intermediateCities
            const newIndex = intermediateCities.length;
            intermediateCities.push({
                name: customStopPlace.name,
                latlng: customStopPlace.latlng,
                isCustom: true
            });

            // Ajouter la carte dans le DOM
            addStopCard(newIndex, customStopPlace.name, price, true, true);

            // Sélectionner auto
            selectedStops.add(newIndex);

            // Ajouter un marqueur sur la carte
            const marker = L.marker(customStopPlace.latlng, {
                icon: L.divIcon({
                    className: '',
                    html: '<div style="width:14px;height:14px;background:#3b82f6;border:3px solid white;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,0.3)"></div>',
                    iconSize: [14, 14],
                    iconAnchor: [7, 7]
                })
            }).addTo(map).bindPopup(customStopPlace.name);
            intermediateCities[newIndex].marker = marker;

            // Reset form
            cancelAddCustomStop();
            updateStopsCount();
            updateLastSegmentLabel();
            updateTotalPrice();
        }

        function addStopCard(index, name, price, isSelected, isCustom) {
            const container = document.getElementById('stops-container');

            const card = document.createElement('div');
            card.className = `step-card flex items-center gap-4 p-4 rounded-2xl border border-slate-200 cursor-pointer ${isSelected ? 'selected' : ''}`;
            card.onclick = (e) => {
                if (e.target.closest('.price-input') || e.target.closest('.btn-remove-stop')) return;
                toggleStop(index, card);
            };
            card.id = `stop-card-${index}`;

            card.innerHTML = `
                <div class="step-check w-8 h-8 rounded-xl border-2 border-slate-200 flex items-center justify-center flex-shrink-0 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-800 truncate">${name}</p>
                    <p class="text-[10px] font-medium ${isCustom ? 'text-blue-500' : 'text-slate-400'}">${isCustom ? '✦ Escale ajoutée manuellement' : 'Escale intermédiaire'}</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <input type="number" min="0" step="50" value="${price}" placeholder="Prix"
                        class="price-input w-20 px-3 py-2 text-xs font-bold text-center bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none"
                        oninput="updateTotalPrice()" id="price-${index}">
                    <span class="text-[10px] font-bold text-slate-400">DA</span>
                    ${isCustom ? `
                    <button onclick="removeCustomStop(${index})" class="btn-remove-stop ml-1 w-7 h-7 flex items-center justify-center rounded-lg hover:bg-red-50 text-slate-300 hover:text-red-500 transition-all" title="Supprimer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>` : ''}
                </div>
            `;
            container.appendChild(card);
        }

        function removeCustomStop(index) {
            // Supprimer le marqueur
            if (intermediateCities[index]?.marker) {
                map.removeLayer(intermediateCities[index].marker);
            }

            // Supprimer de la sélection
            selectedStops.delete(index);

            // Supprimer la carte du DOM
            const card = document.getElementById(`stop-card-${index}`);
            if (card) card.remove();

            // Marquer comme supprimé (on ne réindexe pas pour garder la cohérence des IDs)
            intermediateCities[index] = null;

            updateStopsCount();
            updateLastSegmentLabel();
            updateTotalPrice();
        }

        // =============================================
        // SAUVEGARDER
        // =============================================
        function saveRoute() {
            const depart = document.getElementById('input-depart').value;
            const destination = document.getElementById('input-destination').value;

            const itineraire = [];
            itineraire.push({ name: departPlace.name, type: 'start', latlng: departPlace.latlng });

            // Trier les stops sélectionnés et ignorer les null (supprimés)
            const sortedStops = [...selectedStops]
                .filter(idx => intermediateCities[idx] !== null)
                .sort((a, b) => a - b);

            sortedStops.forEach(idx => {
                const city = intermediateCities[idx];
                itineraire.push({
                    name: city.name,
                    type: 'waypoint',
                    latlng: city.latlng,
                    isCustom: city.isCustom || false
                });
            });
            itineraire.push({ name: destPlace.name, type: 'end', latlng: destPlace.latlng });

            const segments = [];
            let prevName = departPlace.name;
            sortedStops.forEach(idx => {
                const city = intermediateCities[idx];
                const price = parseFloat(document.getElementById(`price-${idx}`).value) || 0;
                segments.push({ from: prevName, to: city.name, price });
                prevName = city.name;
            });
            segments.push({
                from: prevName,
                to: destPlace.name,
                price: parseFloat(document.getElementById('price-last-segment').value) || 0
            });

            // Données de la route sélectionnée
            const selectedRoute = routes[selectedRouteIndex] ? {
                distance: routes[selectedRouteIndex].distance,
                duration: routes[selectedRouteIndex].duration,
                geometry: routes[selectedRouteIndex].geometry
            } : null;

            const payload = {
                depart,
                destination,
                itineraire: JSON.stringify(itineraire),
                segments: JSON.stringify(segments),
                selected_route: JSON.stringify(selectedRoute),
                selected_route_index: selectedRouteIndex
            };

            fetch(`/covoiturage/${window._covoiturageId}/update-route`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Itinéraire mis à jour !',
                        text: 'Les modifications ont été sauvegardées avec succès.',
                        confirmButtonText: 'Retour au trajet',
                        confirmButtonColor: '#f97316',
                    }).then(() => {
                        window.location.href = `/covoiturage/${window._covoiturageId}/edit`;
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Erreur', text: data.message || 'Erreur serveur.' });
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Erreur', text: 'Erreur de connexion.' });
            });
        }

        // =============================================
        // UTILITAIRES
        // =============================================
        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            document.getElementById('panel-' + tab).classList.add('active');
        }

        function showStopsLoader() {
            document.getElementById('stops-loader').classList.remove('hidden');
            document.getElementById('stops-empty').classList.add('hidden');
            document.getElementById('stops-list').classList.add('hidden');
        }

        function hideStopsLoader() {
            document.getElementById('stops-loader').classList.add('hidden');
        }

        function clearInput(type) {
            document.getElementById('input-' + type).value = '';
            document.getElementById('clear-' + type).classList.add('hidden');
            if (type === 'depart') departPlace = null;
            else destPlace = null;
        }

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        // --- LANCEMENT ---
        document.addEventListener('DOMContentLoaded', () => {
            initEditMap();
            setupAutocomplete();
        });
    </script>
@endsection