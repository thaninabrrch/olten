{{--
    Scripts de l'assistant de trajet (voir wizard.blade.php).

    À inclure dans le contenu, avant les scripts du layout : les cartes
    s'initialisent au DOMContentLoaded et doivent passer avant
    assets/js/adress.js, qui crée lui aussi une carte sur #map. Leaflet,
    chargé par le layout, est alors disponible.
--}}
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
                showRouteOnStepsMap();
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

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
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

                                updateMarkers(place);
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
                revealOnMap(map, pendingStopData.latlng);

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
                revealOnMap(map, pendingStopData.latlng);

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
                container.innerHTML = '<p class="wz-empty">Aucune escale ajoutée</p>';
                return;
            }

            list.forEach((stop, i) => {
                const div = document.createElement('div');
                div.className = "manual-stop-item";

                div.innerHTML = `
                    <span class="manual-stop-icon" aria-hidden="true"><i class="fa-solid fa-map-pin"></i></span>
                    <div class="manual-stop-body">
                        <p class="manual-stop-name">${stop.name}</p>
                        <p class="manual-stop-meta">Ajoutée par vous</p>
                    </div>
                    <button onclick="removeManualStop('${type}', ${i})" class="delete-btn" aria-label="Retirer cette escale">
                        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
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

        // place : le résultat Nominatim que l'on vient de choisir
        function updateMarkers(place) {
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
                moveMap(map, L.latLngBounds([startCoords, endCoords]), {
                    padding: [50, 50],
                    maxZoom: 14
                });
            } else if (startCoords || endCoords) {
                // Un seul point : la carte quitte la vue France par défaut pour
                // le rejoindre (un départ à New York restait hors champ)
                moveMap(map, placeBounds(place, startCoords || endCoords), {
                    maxZoom: 14
                });
            }
        }

        // Emprise du lieu renvoyée par Nominatim (une ville entière, une rue),
        // sinon un carré de 5 km autour du point
        function placeBounds(place, coords) {
            const box = (place?.boundingbox || []).map(parseFloat);
            if (box.length === 4 && box.every(isFinite)) {
                return L.latLngBounds([box[0], box[2]], [box[1], box[3]]);
            }
            return L.latLng(coords).toBounds(5000);
        }

        // Déplacement animé, sauf si le système demande de limiter les animations
        function moveMap(map, bounds, options) {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                map.fitBounds(bounds, options);
            } else {
                map.flyToBounds(bounds, {
                    duration: 1,
                    ...options
                });
            }
        }

        // Une escale ajoutée hors du cadre : la carte s'élargit pour l'inclure
        function revealOnMap(map, latlng) {
            if (map && !map.getBounds().contains(latlng)) {
                moveMap(map, map.getBounds().extend(latlng), {
                    padding: [40, 40]
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
                div.className = `route-option ${i === selectedRouteIndex ? 'selected' : ''}`;
                div.setAttribute('role', 'radio');
                div.setAttribute('aria-checked', i === selectedRouteIndex);
                div.tabIndex = 0;
                div.onclick = () => {
                    selectedRouteIndex = i;
                    renderRouteList();
                    displayRouteOnMap(i);
                };

                div.innerHTML = routeOptionHtml(route, routes);
                container.appendChild(div);
            });
        }

        // OSRM donne la durée en secondes (la page du trajet la lit ainsi) :
        // l'afficher comme des minutes donnait « 266h 40min » pour 4 h 26.
        function formatDuration(seconds) {
            const total = Math.round(seconds / 60);
            const hours = Math.floor(total / 60);
            const minutes = total % 60;

            return hours ? `${hours} h ${String(minutes).padStart(2, '0')}` : `${minutes} min`;
        }

        // Route proposée : la durée en tête, l'écart avec la plus rapide en dessous
        function routeOptionHtml(route, all) {
            const fastest = Math.min(...all.map(r => r.duration));
            const gap = route.duration - fastest;
            const km = (route.distance / 1000).toLocaleString('fr-FR', {
                maximumFractionDigits: 1
            });

            return `
                <span class="route-option-radio" aria-hidden="true"></span>
                <span class="route-option-body">
                    <span class="route-option-title">
                        ${formatDuration(route.duration)}
                        ${route.duration === fastest ? '<span class="route-option-badge">La plus rapide</span>' : ''}
                    </span>
                    <span class="route-option-meta">
                        <span><i class="fa-solid fa-road" aria-hidden="true"></i>${km} km</span>
                        ${gap >= 60 ? `<span>+ ${formatDuration(gap)}</span>` : ''}
                    </span>
                </span>
            `;
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
                '<p class="wz-loading"><i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i> Recherche des villes sur la route…</p>';

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
                container.innerHTML = '<p class="wz-empty">Aucune ville détectée sur cette route</p>';
                return;
            }

            cities.forEach((city, i) => {
                const div = document.createElement('div');
                div.className = "city-option";
                // Pas de <label for> : son clic recochait la case après le
                // onclick de la ligne, et cliquer le nom ne changeait rien
                div.innerHTML = `
                    <input type="checkbox" id="city-${i}" class="city-option-check" aria-labelledby="city-name-${i}">
                    <span id="city-name-${i}" class="city-option-name">${city.name}</span>
                    <i class="fa-solid fa-location-dot city-option-icon" aria-hidden="true"></i>
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

        // Carte des escales : la route choisie y est tracée et cadrée, comme sur
        // celle du retour (elle restait sinon sur la vue France par défaut)
        let stepsRouteLayer = null;

        function showRouteOnStepsMap() {
            const map = maps.steps;
            const route = routes[selectedRouteIndex];
            if (!map || !route) return;

            if (stepsRouteLayer) map.removeLayer(stepsRouteLayer);
            stepsRouteLayer = L.geoJSON(route.geometry, {
                style: {
                    color: '#ff3c00',
                    weight: 4,
                    opacity: 0.6
                }
            }).addTo(map);

            map.fitBounds(stepsRouteLayer.getBounds(), {
                padding: [40, 40]
            });
        }

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
                '<p class="wz-loading"><i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i> Recherche des villes sur la route du retour…</p>';

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
                container.innerHTML = '<p class="wz-empty">Aucune ville détectée sur la route du retour</p>';
                return;
            }

            cities.forEach((city, i) => {
                const div = document.createElement('div');
                div.className = "city-option";
                // Même structure qu'à l'aller : pas de <label for> (double bascule)
                div.innerHTML = `
                    <input type="checkbox" id="return-city-${i}" class="city-option-check" aria-labelledby="return-city-name-${i}">
                    <span id="return-city-name-${i}" class="city-option-name">${city.name}</span>
                    <i class="fa-solid fa-location-dot city-option-icon" aria-hidden="true"></i>
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

            // Le type stocké reste start / waypoint / end ; il s'affichait tel quel
            const kinds = {
                start: 'Départ',
                waypoint: 'Escale',
                end: 'Arrivée'
            };

            data.forEach(item => {
                const li = document.createElement('li');
                li.className = `summary-stop is-${item.type}`;

                const sourceLabel = item.source === 'manual' ? ' · ajoutée par vous' : '';

                li.innerHTML = `
                    <span class="summary-stop-dot" aria-hidden="true"></span>
                    <span class="summary-stop-kind">${kinds[item.type] || 'Escale'}${sourceLabel}</span>
                    <span class="summary-stop-name">${item.name}</span>
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
            // Les choix du retour (#view-final) sont aussi des .card-option
            document.querySelectorAll('#view-booking .card-option')
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
            btn.disabled = false;

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
                block.className = "price-segment";

                block.innerHTML = `
                    <div class="price-segment-head">
                        <span id="price-route-${i}" class="price-segment-route">${from}<i class="fa-solid fa-arrow-right-long" aria-hidden="true"></i>${to}</span>
                        <span class="price-value">${stepPrice} €</span>
                    </div>
                    <input type="range" min="5" max="100" value="${stepPrice}"
                        data-index="${i}" aria-labelledby="price-route-${i}"
                        class="price-range">
                    <div class="price-segment-scale" aria-hidden="true"><span>5 €</span><span>100 €</span></div>
                `;

                const range = block.querySelector('input');
                const priceEl = block.querySelector('.price-value');

                range.addEventListener('input', () => {
                    const value = parseFloat(range.value);
                    priceEl.textContent = value + ' €';
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
                document.querySelectorAll('#view-booking .card-option').forEach(card => {
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
                div.className = `route-option ${i === selectedReturnRouteIndex ? 'selected' : ''}`;
                div.setAttribute('role', 'radio');
                div.setAttribute('aria-checked', i === selectedReturnRouteIndex);
                div.tabIndex = 0;

                div.onclick = () => {
                    selectedReturnRouteIndex = i;
                    renderReturnRouteList();
                    displayReturnRouteOnMap(i);
                };

                div.innerHTML = routeOptionHtml(route, returnRoutes);
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
                div.className = "price-segment";

                div.innerHTML = `
                    <div class="price-segment-head">
                        <span id="return-price-route-${i}" class="price-segment-route">${from}<i class="fa-solid fa-arrow-right-long" aria-hidden="true"></i>${to}</span>
                        <span id="price-label-${i}" class="price-value">0 €</span>
                    </div>
                    <input type="range"
                        min="0"
                        max="100"
                        value="0"
                        aria-labelledby="return-price-route-${i}"
                        class="price-range"
                        oninput="updateReturnPrice(${i}, this.value)">
                    <div class="price-segment-scale" aria-hidden="true"><span>0 €</span><span>100 €</span></div>
                `;

                container.appendChild(div);
            }

            localStorage.setItem('return_pricing', JSON.stringify(returnPricing));
        }

        function updateReturnPrice(index, value) {
            let pricing = JSON.parse(localStorage.getItem('return_pricing')) || [];
            pricing[index].price = parseInt(value);
            localStorage.setItem('return_pricing', JSON.stringify(pricing));
            document.getElementById(`price-label-${index}`).innerText = value + " €";
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

        // Un second clic pendant l'envoi publiait le trajet une deuxième fois
        let publishing = false;

        document.getElementById('btn-publish').addEventListener('click', async () => {
            if (publishing) return;

            const button = document.getElementById('btn-publish');
            let published = false;
            publishing = true;
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');

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
                // Sans clic sur + / −, le brouillon n'a pas de nombre : on publie
                // celui que le compteur affiche, et non 1
                const shownSeats = parseInt(document.getElementById('qty-passengers')?.innerText, 10);
                formData.append('nb_places', draft.passengers?.count || shownSeats || 1);
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
                    published = true;
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
            } finally {
                // Après un succès, la redirection est en cours : le bouton reste inactif
                if (!published) {
                    publishing = false;
                    button.disabled = false;
                    button.removeAttribute('aria-busy');
                }
            }
        });
    </script>

    <script>
        /*
         * Finitions de l'assistant : rien ici ne modifie le trajet ni les cartes.
         *   - récapitulatif « Votre trajet » à droite des étapes sans carte, lu
         *     dans le brouillon (localStorage) que remplissent les étapes, ou
         *     dans les champs de l'étape en cours ;
         *   - totaux des prix et distance de la route repris dans le panneau
         *     (sur mobile, la colonne de droite est masquée) ;
         *   - routes : attente pendant le calcul, bouton actif une fois listées ;
         *   - clavier : options au clavier, Échap ferme la fenêtre d'escale ;
         *   - saisie : calendrier au clic, retour pas avant l'aller, compteur du
         *     message, partie remplie des curseurs de prix.
         */
        (function () {
            const area = document.querySelector('.app-content-area');
            if (!area) return;

            // Ligne du récapitulatif que l'étape affichée est en train de remplir
            const CURRENT_ROW = {
                'view-datetime': 'date',
                'view-datetimeed': 'places',
                'view-booking': 'reservation',
                'view-final': 'retour',
                'view-return-datetime': 'retour',
            };

            const MODES = {
                mixed: 'Mixte',
                womenOnly: 'Entre femmes',
                maxBackSeats: '2 max. à l’arrière',
            };

            const BOOKING = {
                instant: 'Instantanée',
                manual: 'Validation manuelle',
            };

            const LOADING_ROUTES =
                '<p class="wz-loading"><i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i> Calcul des routes…</p>';

            const recaps = area.querySelectorAll('[data-trip-recap]');

            function read(key) {
                try {
                    return JSON.parse(localStorage.getItem(key));
                } catch (e) {
                    return null;
                }
            }

            function field(id) {
                return document.getElementById(id)?.value || '';
            }

            // Les noms de villes viennent de la saisie : jamais injectés tels quels
            function escape(text) {
                const div = document.createElement('div');
                div.textContent = text ?? '';
                return div.innerHTML;
            }

            function sum(steps) {
                return (steps || []).reduce((total, s) => total + (parseFloat(s?.price) || 0), 0);
            }

            function formatDate(date, time) {
                if (!date) return null;

                const day = new Date(date + 'T' + (time || '00:00'));
                if (isNaN(day)) return null;

                const label = day.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short' });
                return time ? label + ' · ' + time : label;
            }

            // Distance en mètres, durée en secondes (OSRM) : voir formatDuration
            function formatRoute(route) {
                if (!route || !route.distance) return null;

                const km = Math.round(route.distance / 1000) + ' km';
                return route.duration ? km + ' · ' + formatDuration(route.duration) : km;
            }

            function row(key, icon, label, value, current) {
                return `
                    <li class="trip-recap-row${key === current ? ' is-current' : ''}">
                        <i class="fa-solid ${icon}" aria-hidden="true"></i>
                        <span class="trip-recap-label">${label}</span>
                        <span class="trip-recap-value${value ? '' : ' is-empty'}">${value ? escape(value) : 'À définir'}</span>
                    </li>`;
            }

            function render(draft, current) {
                if (!recaps.length) return;

                const itinerary = read('vtc_final_itinerary') || [];

                const start = itinerary[0]?.name || field('input-start');
                const end = itinerary.length > 1 ? itinerary[itinerary.length - 1].name : field('input-end');
                const stops = itinerary.length - 2;

                const seats = draft.passengers?.count
                    || parseInt(document.getElementById('qty-passengers')?.innerText, 10);
                const mode = document.querySelector('input[name="passengerMode"]:checked')?.value
                    || draft.passengers?.mode;
                const price = sum(draft.pricing?.steps);
                const returnAt = read('return_datetime') || {};

                let retour = null;
                if (draft.returnTrip === false) {
                    retour = 'Sans retour';
                } else if (draft.returnTrip) {
                    retour = formatDate(field('return-date') || returnAt.date, field('return-time') || returnAt.time) || 'Oui';
                }

                const route = start || end
                    ? `<div class="trip-recap-route">
                            <span class="trip-recap-dot"></span>
                            <span class="trip-recap-city${start ? '' : ' is-empty'}">${start ? escape(start) : 'Départ à définir'}</span>
                            <span class="trip-recap-line"></span>
                            <span class="trip-recap-via">${stops > 0 ? stops + ' escale' + (stops > 1 ? 's' : '') : ''}</span>
                            <span class="trip-recap-dot is-end"></span>
                            <span class="trip-recap-city${end ? '' : ' is-empty'}">${end ? escape(end) : 'Arrivée à définir'}</span>
                        </div>`
                    : '<p class="trip-recap-city is-empty">Itinéraire à définir</p>';

                const metrics = formatRoute(draft.selectedRoute);

                const html = `
                    <div class="trip-recap-card">
                        <div class="trip-recap-head">
                            <p class="trip-recap-eyebrow">Votre trajet</p>
                            ${route}
                            ${metrics ? `<p class="trip-recap-metrics"><i class="fa-solid fa-road" aria-hidden="true"></i>${metrics}</p>` : ''}
                        </div>
                        <ul class="trip-recap-rows">
                            ${row('date', 'fa-calendar-days', 'Départ', formatDate(field('input-date') || draft.datetime?.date, field('input-time') || draft.datetime?.time), current)}
                            ${row('places', 'fa-user-group', 'Passagers', seats ? seats + ' place' + (seats > 1 ? 's' : '') + (MODES[mode] ? ' · ' + MODES[mode] : '') : null, current)}
                            ${row('reservation', 'fa-bolt', 'Réservation', BOOKING[draft.booking?.mode], current)}
                            ${row('prix', 'fa-euro-sign', 'Prix', price ? price + ' € par passager' : null, current)}
                            ${row('retour', 'fa-rotate-left', 'Retour', retour, current)}
                        </ul>
                    </div>
                    <p class="trip-recap-note">Le récapitulatif se complète à chaque étape.</p>`;

                recaps.forEach(el => { el.innerHTML = html; });
            }

            // Totaux et distance, repris dans le panneau de gauche
            function totals(draft) {
                area.querySelectorAll('[data-trip-total]').forEach(el => {
                    el.textContent = sum(draft.pricing?.steps) + ' €';
                });
                area.querySelectorAll('[data-return-total]').forEach(el => {
                    el.textContent = sum(read('return_pricing')) + ' €';
                });
                area.querySelectorAll('[data-route-metrics]').forEach(el => {
                    el.textContent = formatRoute(draft.selectedRoute) || '';
                });
            }

            // Partie remplie d'un curseur de prix (dégradé réglé par --fill)
            function fill(range) {
                const min = parseFloat(range.min) || 0;
                const max = parseFloat(range.max) || 100;
                range.style.setProperty('--fill', ((range.value - min) / (max - min) * 100) + '%');
            }

            function update() {
                const draft = read('vtc_trip_draft') || {};
                const active = area.querySelector('.step-view.active');

                render(draft, CURRENT_ROW[active?.id]);
                totals(draft);
                area.querySelectorAll('.price-range').forEach(fill);

                // Les scripts basculent la classe « selected » : l'état lu par les lecteurs d'écran suit
                area.querySelectorAll('[role="radio"]').forEach(el => {
                    el.setAttribute('aria-checked', el.classList.contains('selected'));
                });

                // Pas de retour avant l'aller
                const returnDate = document.getElementById('return-date');
                const departure = field('input-date') || draft.datetime?.date;
                if (returnDate && departure) returnDate.min = departure;
            }

            // Routes : pendant le calcul, la liste patiente et « Choisir cette route »
            // reste inactif (sinon il validait la route de la recherche précédente)
            [
                ['btn-next', 'routes-list', 'btn-validate-route'],
                ['btn-go-return-pricing', 'return-routes-list', 'btn-validate-return-route'],
            ].forEach(([trigger, listId, confirmId]) => {
                const list = document.getElementById(listId);
                const confirm = document.getElementById(confirmId);
                if (!list || !confirm) return;

                // En capture, pour passer avant le gestionnaire qui lance le calcul
                area.addEventListener('click', e => {
                    if (!e.target.closest('#' + trigger)) return;
                    list.innerHTML = LOADING_ROUTES;
                    confirm.disabled = true;
                }, true);

                new MutationObserver(() => {
                    confirm.disabled = !list.querySelector('.route-option');
                }).observe(list, { childList: true });
            });

            // Choix en <div> (routes, réservation, retour) : Entrée ou Espace = clic
            area.addEventListener('keydown', e => {
                const option = e.target.closest('[role="radio"]');
                if (!option || (e.key !== 'Enter' && e.key !== ' ')) return;

                e.preventDefault();
                const list = option.parentElement;
                const index = Array.prototype.indexOf.call(list.children, option);
                option.click();

                // La liste des routes se redessine au clic : le focus reste sur l'option
                list.children[index]?.focus();
            });

            // Fenêtres d'escale : champ prêt à la saisie à l'ouverture, Échap pour fermer
            [
                ['popin-add-stop', 'popin-stop-input', 'aller'],
                ['popin-add-return-stop', 'popin-return-stop-input', 'retour'],
            ].forEach(([popinId, inputId, type]) => {
                const popin = document.getElementById(popinId);
                if (!popin) return;

                new MutationObserver(() => {
                    if (popin.classList.contains('active')) document.getElementById(inputId)?.focus();
                }).observe(popin, { attributes: true, attributeFilter: ['class'] });

                document.addEventListener('keydown', e => {
                    if (e.key === 'Escape' && popin.classList.contains('active')) closeStopPopin(type);
                });
            });

            // Date et heure : le sélecteur s'ouvre au clic sur tout le champ
            area.querySelectorAll('input[type="date"], input[type="time"]').forEach(input => {
                input.addEventListener('click', () => {
                    try {
                        input.showPicker();
                    } catch (e) {
                        // Navigateur sans showPicker : saisie classique
                    }
                });
            });

            // Compteur de caractères du message conducteur
            area.querySelectorAll('[data-count-for]').forEach(counter => {
                const input = document.getElementById(counter.dataset.countFor);
                if (!input) return;

                const sync = () => { counter.textContent = input.value.length; };
                input.addEventListener('input', sync);
                sync();
            });

            // Curseurs de prix : remplissage et totaux suivent le geste, sans attendre
            area.addEventListener('input', e => {
                if (!e.target.classList?.contains('price-range')) return;

                fill(e.target);
                totals(read('vtc_trip_draft') || {});
            });

            // Les étapes changent en basculant la classe « active » (changeView)
            const observer = new MutationObserver(update);
            area.querySelectorAll('.step-view').forEach(view => {
                observer.observe(view, { attributes: true, attributeFilter: ['class'] });
            });

            // Les choix s'enregistrent au fil de la saisie et des clics
            let timer;
            const later = () => {
                clearTimeout(timer);
                timer = setTimeout(update, 200);
            };
            ['input', 'change', 'click'].forEach(type => area.addEventListener(type, later));

            // Après les étapes, qui restaurent le brouillon au chargement
            document.addEventListener('DOMContentLoaded', update);
            update();
        })();
    </script>
