@extends('layouts.connected')

@section('title', 'Modifier l\'itinéraire | ' . config('app.name'))

{{--
    Assistant de modification d'itineraire, aligne sur le design de
    l'espace connecte : fil d'ariane, en-tete, panneau a onglets, champs
    et boutons viennent tous du design system « sp- ».

    Les onglets suivent la convention de la plateforme (voir la page
    profil) : bouton « tab-btn-<nom> » avec la classe .is-active, panneau
    « tab-<nom> » en .sp-tab-pane masque par l'attribut hidden.

    Les quelques pieces propres a cet ecran (escales, itineraires
    proposes, carte) sont decrites dans la section « EDITION DE
    L'ITINERAIRE » de style_connected.css : aucune regle n'est ecrite
    en ligne ici.
--}}

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('covoiturage.index') }}">Mes trajets</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('covoiturage.edit', $covoiturage->covoiturage_id) }}">Modifier</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Itinéraire</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Modifier l'itinéraire</h1>
            <p class="sp-subtitle">Départ, arrivée, route empruntée et escales desservies.</p>
        </div>

        <div class="sp-role-badges">
            <span class="sp-ref">#{{ $covoiturage->covoiturage_id }}</span>
        </div>
    </header>

    <div class="sp-route-edit">

        {{-- Colonne de gauche : les trois etapes --}}
        <section class="sp-panel">

            <div class="sp-tabs">
                <button type="button" class="sp-tab is-active" id="tab-btn-locations" onclick="switchTab('locations')">
                    <i class="fa-solid fa-location-dot"></i> Départ &amp; arrivée
                </button>

                <button type="button" class="sp-tab" id="tab-btn-routes" onclick="switchTab('routes')">
                    <i class="fa-solid fa-route"></i> Itinéraire
                </button>

                <button type="button" class="sp-tab" id="tab-btn-stops" onclick="switchTab('stops')">
                    <i class="fa-solid fa-map-pin"></i> Escales
                    <span class="sp-tab-count hidden" id="stops-count">0</span>
                </button>
            </div>

            {{-- Etape 1 : depart et arrivee --}}
            <div class="sp-tab-pane" id="tab-locations">
                <p class="sp-section-label">Adresses du trajet</p>

                <div class="sp-field">
                    <label class="sp-label" for="input-depart">Point de départ</label>

                    <div class="sp-addr">
                        <span class="sp-addr-dot" aria-hidden="true"></span>
                        <input type="text" id="input-depart" class="sp-input" autocomplete="off"
                               value="{{ $covoiturage->depart }}"
                               placeholder="Saisissez une adresse de départ...">
                        <button type="button" class="sp-addr-clear hidden" id="clear-depart"
                                onclick="clearInput('depart')" aria-label="Effacer le départ">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <ul class="suggestions" id="depart-results"></ul>
                </div>

                <div class="sp-addr-link" aria-hidden="true"></div>

                <div class="sp-field">
                    <label class="sp-label" for="input-destination">Destination</label>

                    <div class="sp-addr">
                        <span class="sp-addr-dot is-end" aria-hidden="true"></span>
                        <input type="text" id="input-destination" class="sp-input" autocomplete="off"
                               value="{{ $covoiturage->destination }}"
                               placeholder="Saisissez une adresse d'arrivée...">
                        <button type="button" class="sp-addr-clear hidden" id="clear-destination"
                                onclick="clearInput('destination')" aria-label="Effacer la destination">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <ul class="suggestions" id="destination-results"></ul>
                </div>

                <span class="sp-help">
                    Choisissez une adresse dans les suggestions : le repère est alors placé sur la carte.
                </span>

                <div class="sp-pane-actions">
                    <a href="{{ route('covoiturage.edit', $covoiturage->covoiturage_id) }}" class="sp-act is-ghost">
                        Annuler
                    </a>

                    <button type="button" class="sp-btn-primary" id="btn-validate-locations" onclick="validateLocations()">
                        Choisir l'itinéraire
                    </button>
                </div>
            </div>

            {{-- Etape 2 : choix de la route --}}
            <div class="sp-tab-pane" id="tab-routes" hidden>

                <div class="sp-loading hidden" id="routes-loader">
                    <span class="sp-loader"><span></span><span></span><span></span></span>
                    <p>Calcul des itinéraires possibles...</p>
                </div>

                <div id="routes-empty">
                    <x-empty-state compact
                        title="Validez d'abord le départ et la destination"
                        text="Les itinéraires possibles s'afficheront ici." />
                </div>

                <div class="hidden" id="routes-list-wrapper">
                    <div class="sp-pane-head">
                        <p class="sp-section-label">Itinéraires disponibles</p>
                    </div>

                    <div class="sp-route-options" id="routes-list"></div>

                    <div class="sp-pane-actions">
                        <button type="button" class="sp-act is-ghost" onclick="switchTab('locations')">
                            Retour
                        </button>

                        <button type="button" class="sp-btn-primary" id="btn-validate-route" onclick="validateRoute()">
                            Charger les escales
                        </button>
                    </div>
                </div>
            </div>

            {{-- Etape 3 : escales et tarifs --}}
            <div class="sp-tab-pane" id="tab-stops" hidden>

                <div class="sp-loading hidden" id="stops-loader">
                    <span class="sp-loader"><span></span><span></span><span></span></span>
                    <p>Recherche des escales sur le trajet...</p>
                </div>

                <div id="stops-empty">
                    <x-empty-state compact
                        title="Choisissez d'abord un itinéraire"
                        text="Les escales sont calculées automatiquement à partir de la route retenue." />
                </div>

                <div class="hidden" id="stops-list">
                    <div class="sp-pane-head">
                        <p class="sp-section-label">Escales détectées</p>

                        <button type="button" class="sp-act is-ghost" id="btn-toggle-all" onclick="toggleAllStops()">
                            Tout sélectionner
                        </button>
                    </div>

                    <div class="sp-route">
                        <span class="sp-route-step" id="label-depart">{{ $covoiturage->depart }}</span>
                        <span class="sp-route-arrow">→</span>
                        <span class="sp-route-step" id="label-destination">{{ $covoiturage->destination }}</span>
                    </div>

                    <p class="sp-help">
                        Cochez les escales à desservir, puis fixez le prix de chaque segment.
                    </p>

                    <div class="sp-note hidden" id="stops-none">
                        Aucune escale n'a été détectée sur cet itinéraire : le tarif ci-dessous
                        couvre le trajet direct. Vous pouvez tout de même en ajouter une à la main.
                    </div>

                    <div class="sp-stops" id="stops-container"></div>

                    {{-- Ajout d'une escale a la main --}}
                    <div class="sp-substep hidden" id="add-stop-form">
                        <div class="sp-field">
                            <label class="sp-label" for="input-custom-stop">Nouvelle escale</label>
                            <input type="text" id="input-custom-stop" class="sp-input" autocomplete="off"
                                   placeholder="Rechercher une ville...">
                            <ul class="suggestions" id="custom-stop-results"></ul>
                        </div>

                        <div class="sp-field">
                            <label class="sp-label" for="input-custom-stop-price">Prix du segment</label>
                            <div class="sp-input-group">
                                <input type="number" min="0" step="5" value="0" id="input-custom-stop-price"
                                       class="sp-input sp-price-input">
                                <span class="sp-input-suffix">€</span>
                            </div>
                        </div>

                        <div class="sp-toolbar-actions">
                            <button type="button" class="sp-act is-ghost" onclick="cancelAddCustomStop()">
                                Annuler
                            </button>
                            <button type="button" class="sp-act is-edit" onclick="confirmAddCustomStop()">
                                Ajouter l'escale
                            </button>
                        </div>
                    </div>

                    <button type="button" class="sp-add-stop" id="btn-add-stop" onclick="showAddStopForm()">
                        <i class="fa-solid fa-plus"></i> Ajouter une escale
                    </button>

                    {{-- Dernier segment : de la derniere escale a la destination --}}
                    <div class="sp-stop is-static">
                        <span class="sp-stop-check" aria-hidden="true"><i class="fa-solid fa-flag-checkered"></i></span>

                        <div class="sp-stop-body">
                            <span class="sp-stop-name">Prix du dernier segment</span>
                            <span class="sp-stop-kind" id="last-segment-label">Dernière escale → Destination</span>
                        </div>

                        <div class="sp-input-group">
                            <input type="number" min="0" step="5" value="0" id="price-last-segment"
                                   class="sp-input sp-price-input">
                            <span class="sp-input-suffix">€</span>
                        </div>
                    </div>

                    <div class="sp-total hidden" id="price-summary">
                        <span class="sp-total-label">Prix total du trajet</span>
                        <span class="sp-total-value" id="total-price">0 €</span>
                    </div>

                    <div class="sp-pane-actions hidden" id="stops-save-wrapper">
                        <button type="button" class="sp-act is-ghost" onclick="switchTab('routes')">
                            Retour
                        </button>

                        <button type="button" class="sp-btn-primary" id="btn-save" onclick="saveRoute()">
                            Enregistrer l'itinéraire
                        </button>
                    </div>
                </div>
            </div>
        </section>

        {{-- Colonne de droite : la carte --}}
        <aside class="sp-route-aside">
            <section class="sp-panel">
                <div class="sp-toolbar">
                    <div>
                        <h2 class="sp-toolbar-title">Carte du trajet</h2>
                        <span class="sp-count">Cliquez une route pour la sélectionner</span>
                    </div>
                </div>

                <div class="sp-map">
                    <div id="edit-map" style="height:100%"></div>
                </div>

                <div class="sp-specs hidden" id="route-info">
                    <div>
                        <span>Distance</span>
                        <strong id="route-distance">--</strong>
                    </div>
                    <div>
                        <span>Durée</span>
                        <strong id="route-duration">--</strong>
                    </div>
                </div>
            </section>
        </aside>
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

    // Orange de la plateforme pour la route retenue, deux teintes du
    // design system pour les alternatives.
    const ROUTE_COLORS = ['#ff3c00', '#14539c', '#1a8245'];
    const ROUTE_COLORS_DIM = ['rgba(255,60,0,0.28)', 'rgba(20,83,156,0.28)', 'rgba(26,130,69,0.28)'];

    const existingItineraire = window._existingItineraire || [];
    const existingSegments = window._existingSegments || [];

    // =============================================
    // INIT MAP
    // =============================================
    function initEditMap() {
        map = L.map('edit-map', { zoomControl: false }).setView([46.6, 1.8], 6);
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
                    html: '<div style="width:16px;height:16px;background:#ff3c00;border:3px solid white;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,0.3)"></div>',
                    iconSize: [16, 16],
                    iconAnchor: [8, 8]
                })
            }).addTo(map);
        }
        if (destPlace) {
            endMarker = L.marker(destPlace.latlng, {
                icon: L.divIcon({
                    className: '',
                    html: '<div style="width:16px;height:16px;background:#16191d;border:3px solid white;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,0.3)"></div>',
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
            resDiv = document.createElement('ul');
            resDiv.id = resultsId;
            resDiv.className = 'suggestions hidden';
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
                    const item = document.createElement('li');
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
                        resDiv.innerHTML = '';
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
            Swal.fire({
                icon: 'warning',
                title: 'Champs requis',
                text: 'Sélectionnez un départ et une destination depuis les suggestions.',
                confirmButtonColor: '#ff3c00',
            });
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
                showRoutesEmpty('Aucun itinéraire trouvé');
                return;
            }

            document.getElementById('routes-empty').classList.add('hidden');
            document.getElementById('routes-list-wrapper').classList.remove('hidden');
            renderRouteList();
            displayAllRoutesOnMap();
            highlightRoute(0);

        } catch (err) {
            console.error('OSRM error:', err);
            document.getElementById('routes-loader').classList.add('hidden');
            showRoutesEmpty('Impossible de calculer les itinéraires');
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Impossible de calculer les itinéraires. Réessayez dans un instant.',
                confirmButtonColor: '#ff3c00',
            });
        }
    }

    /*
     * Sans itineraire exploitable, l'etape 2 doit revenir a son etat vide :
     * sinon une liste perimee reste affichee avec un bouton qui mene a une
     * route inexistante.
     */
    function showRoutesEmpty(message) {
        routes = [];
        routeLayers.forEach(l => map.removeLayer(l));
        routeLayers = [];

        document.getElementById('routes-list').innerHTML = '';
        document.getElementById('routes-list-wrapper').classList.add('hidden');
        document.getElementById('route-info').classList.add('hidden');

        const empty = document.getElementById('routes-empty');
        empty.classList.remove('hidden');
        empty.querySelector('.olten-empty-title').textContent = message;
    }

    function renderRouteList() {
        const container = document.getElementById('routes-list');
        container.innerHTML = '';

        routes.forEach((route, i) => {
            const dist = (route.distance / 1000).toFixed(1);
            const dur = Math.round(route.duration / 60);
            const hours = Math.floor(dur / 60);
            const mins = dur % 60;
            const durText = hours > 0 ? `${hours} h ${mins} min` : `${mins} min`;

            const div = document.createElement('div');
            div.className = `sp-route-option ${i === selectedRouteIndex ? 'is-selected' : ''}`;
            div.id = `route-option-${i}`;
            div.onclick = () => selectRoute(i);

            div.innerHTML = `
                <span class="sp-route-option-dot" style="background:${ROUTE_COLORS[i] || ROUTE_COLORS[0]}"></span>

                <div class="sp-route-option-body">
                    <div class="sp-route-option-head">
                        <span class="sp-route-option-title">Option ${i + 1}</span>
                        ${i === 0 ? '<span class="sp-pick-flag">Plus rapide</span>' : ''}
                    </div>

                    <div class="sp-route-option-meta">
                        <span><i class="fa-solid fa-road"></i>${dist} km</span>
                        <span><i class="fa-regular fa-clock"></i>${durText}</span>
                    </div>
                </div>

                <span class="sp-route-check"><i class="fa-solid fa-check"></i></span>
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
        document.getElementById('route-duration').textContent = hours > 0 ? `${hours} h ${mins} min` : `${mins} min`;
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
        if (!departPlace || !destPlace) {
            Swal.fire({
                icon: 'warning',
                title: 'Adresses manquantes',
                text: 'Renseignez à nouveau le départ et la destination.',
                confirmButtonColor: '#ff3c00',
            });
            switchTab('locations');
            return;
        }

        if (!routes[selectedRouteIndex]) {
            Swal.fire({
                icon: 'warning',
                title: 'Aucun itinéraire',
                text: 'Choisissez un itinéraire avant de charger les escales.',
                confirmButtonColor: '#ff3c00',
            });
            return;
        }

        document.getElementById('label-depart').textContent = departPlace.name;
        document.getElementById('label-destination').textContent = destPlace.name;

        switchTab('stops');
        showStopsLoader();

        // Afficher seulement la route sélectionnée
        routeLayers.forEach((layer, i) => {
            if (i !== selectedRouteIndex) map.removeLayer(layer);
        });
        routeLayers[selectedRouteIndex].setStyle({ color: '#ff3c00', weight: 5, opacity: 0.9 });

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

        // Les escales ajoutees a la main portaient un marqueur : on le retire
        // avant de repartir sur la nouvelle liste.
        intermediateCities.forEach(c => {
            if (c && c.marker) map.removeLayer(c.marker);
        });

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

        // Un trajet direct reste enregistrable : on garde l'etape utilisable et
        // on signale simplement l'absence d'escale.
        document.getElementById('stops-none').classList.toggle('hidden', intermediateCities.length > 0);

        empty.classList.add('hidden');
        list.classList.remove('hidden');
        document.getElementById('stops-save-wrapper').classList.remove('hidden');
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
            card.classList.remove('is-selected');
        } else {
            selectedStops.add(index);
            card.classList.add('is-selected');
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
            if (allSelected) { selectedStops.delete(i); card.classList.remove('is-selected'); }
            else { selectedStops.add(i); card.classList.add('is-selected'); }
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
            badge.textContent = selectedStops.size;
        } else {
            badge.classList.add('hidden');
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

    let customStopOutsideBound = false;

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
                    const item = document.createElement('li');
                    item.textContent = place.display_name;
                    item.onclick = () => {
                        newInput.value = place.display_name;
                        customStopPlace = {
                            name: place.display_name,
                            latlng: [parseFloat(place.lat), parseFloat(place.lon)]
                        };
                        resDiv.innerHTML = '';
                        resDiv.classList.add('hidden');
                    };
                    resDiv.appendChild(item);
                });
            } catch (err) { console.error('Nominatim error:', err); }
        }, 300));

        // Le champ est reconstruit a chaque ouverture : l'ecouteur global, lui,
        // n'est pose qu'une fois et vise le champ courant.
        if (!customStopOutsideBound) {
            customStopOutsideBound = true;
            document.addEventListener('click', (e) => {
                const current = document.getElementById('input-custom-stop');
                if (current && !current.contains(e.target) && !resDiv.contains(e.target)) {
                    resDiv.classList.add('hidden');
                }
            });
        }
    }

    function confirmAddCustomStop() {
        if (!customStopPlace) {
            Swal.fire({
                icon: 'warning',
                title: 'Ville requise',
                text: 'Sélectionnez une ville depuis les suggestions.',
                confirmButtonColor: '#ff3c00',
            });
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
            Swal.fire({
                icon: 'info',
                title: 'Escale existante',
                text: 'Cette ville est déjà dans la liste des escales.',
                confirmButtonColor: '#ff3c00',
            });
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
                html: '<div style="width:14px;height:14px;background:#14539c;border:3px solid white;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,0.3)"></div>',
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
        card.className = `sp-stop ${isSelected ? 'is-selected' : ''}`;
        card.onclick = (e) => {
            if (e.target.closest('.sp-price-input') || e.target.closest('.sp-stop-remove')) return;
            toggleStop(index, card);
        };
        card.id = `stop-card-${index}`;

        card.innerHTML = `
            <span class="sp-stop-check"><i class="fa-solid fa-check"></i></span>

            <div class="sp-stop-body">
                <span class="sp-stop-name">${name}</span>
                <span class="sp-stop-kind">${isCustom ? 'Escale ajoutée manuellement' : 'Escale intermédiaire'}</span>
            </div>

            <div class="sp-input-group">
                <input type="number" min="0" step="5" value="${price}" id="price-${index}"
                       class="sp-input sp-price-input" oninput="updateTotalPrice()">
                <span class="sp-input-suffix">€</span>
            </div>

            ${isCustom ? `
            <button type="button" class="sp-stop-remove" onclick="removeCustomStop(${index})" title="Supprimer l'escale">
                <i class="fa-solid fa-trash-can"></i>
            </button>` : ''}
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
                    title: 'Itinéraire mis à jour',
                    text: 'Les modifications ont bien été enregistrées.',
                    confirmButtonText: 'Retour au trajet',
                    confirmButtonColor: '#ff3c00',
                }).then(() => {
                    window.location.href = `/covoiturage/${window._covoiturageId}/edit`;
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: data.message || 'Erreur serveur.',
                    confirmButtonColor: '#ff3c00',
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Erreur de connexion.',
                confirmButtonColor: '#ff3c00',
            });
        });
    }

    // =============================================
    // UTILITAIRES
    // =============================================
    // Onglets : meme mecanique que la page profil (attribut hidden + .is-active)
    const TABS = ['locations', 'routes', 'stops'];

    function switchTab(name) {
        TABS.forEach(function (t) {
            document.getElementById('tab-' + t).hidden = true;
            document.getElementById('tab-btn-' + t).classList.remove('is-active');
        });

        document.getElementById('tab-' + name).hidden = false;
        document.getElementById('tab-btn-' + name).classList.add('is-active');

        // La carte reste montee : on la recalcule apres un changement de hauteur
        if (map) setTimeout(() => map.invalidateSize(), 60);
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

        // L'itineraire calcule ne vaut plus rien sans ses deux extremites
        showRoutesEmpty('Validez à nouveau le départ et la destination');
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

        // Les champs pre-remplis peuvent etre effaces des l'ouverture
        ['depart', 'destination'].forEach(type => {
            if (document.getElementById('input-' + type).value.trim() !== '') {
                document.getElementById('clear-' + type).classList.remove('hidden');
            }
        });
    });
</script>
@endsection
