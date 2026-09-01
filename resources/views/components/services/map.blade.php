{{--
    Carte des annonces geolocalisees du service.

    Elle vit dans l'onglet « Carte » de la page service : son panneau est
    masque au chargement, donc la carte est construite au premier affichage
    (Leaflet ne sait pas mesurer un conteneur en `display: none`). Le panneau
    previent par un evenement `cs:shown`.

    Un clic sur un repere ouvre la popin d'offre plutot qu'une bulle Leaflet :
    la bulle etait coupee par les bords de la carte et illisible sur mobile.

    Leaflet est deja charge par le layout : le script est pousse dans la pile
    `scripts`, donc joue apres la librairie.
--}}
@props([
    'points' => [],
])

@php
    $points = collect($points)->values();
    $mapId  = 'cs-map-' . uniqid();
@endphp

<section class="cs-map-card">
    <div class="cs-map-header">
        <p class="cs-map-title">
            @if($points->isNotEmpty())
                Cliquez sur un repère pour ouvrir l'offre.
            @else
                Aucune offre n'est localisée pour le moment.
            @endif
        </p>

        <span class="cs-map-badge">
            <i class="fa-solid fa-location-dot"></i>
            {{ $points->count() }} position{{ $points->count() > 1 ? 's' : '' }} sur la carte
        </span>
    </div>

    @if($points->isNotEmpty())
        <div class="cs-map-frame">
            <div id="{{ $mapId }}" class="cs-map-canvas" data-points="{{ json_encode($points, JSON_UNESCAPED_UNICODE) }}"></div>
        </div>

        {{-- Popin d'une offre : remplie au clic depuis les donnees du repere --}}
        <div class="cs-modal" id="{{ $mapId }}-modal" hidden>
            <div class="cs-modal-backdrop" data-cs-modal-close></div>

            <div class="cs-modal-card" role="dialog" aria-modal="true"
                 aria-labelledby="{{ $mapId }}-modal-title">

                <button type="button" class="cs-modal-close" data-cs-modal-close aria-label="Fermer">
                    <i class="fa-solid fa-xmark"></i>
                </button>

                <div class="cs-modal-media">
                    <img data-cs-modal-image src="" alt="">
                </div>

                <div class="cs-modal-body">
                    <span class="cs-modal-cat" data-cs-modal-cat></span>

                    <h3 class="cs-modal-title" id="{{ $mapId }}-modal-title" data-cs-modal-title></h3>

                    <p class="cs-modal-address" data-cs-modal-address>
                        <i class="fa-solid fa-location-dot"></i>
                        <span data-cs-modal-address-text></span>
                    </p>

                    <div class="cs-modal-footer">
                        <span class="cs-modal-price" data-cs-modal-price></span>
                        <a class="cs-modal-btn" data-cs-modal-link href="#">Voir l'offre</a>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                (function () {
                    const conteneur = document.getElementById(@json($mapId));
                    const popin     = document.getElementById(@json($mapId . '-modal'));

                    if (! conteneur || typeof L === 'undefined') {
                        return;
                    }

                    const points = JSON.parse(conteneur.dataset.points || '[]');

                    // ---- Popin d'offre ----
                    let dernierDeclencheur = null;

                    function ouvrirPopin(point) {
                        if (! popin) {
                            window.location.href = point.url;
                            return;
                        }

                        const image = popin.querySelector('[data-cs-modal-image]');
                        image.src = point.image || '';
                        image.alt = point.title || '';

                        popin.querySelector('[data-cs-modal-cat]').textContent   = point.category || '';
                        popin.querySelector('[data-cs-modal-title]').textContent = point.title || '';
                        popin.querySelector('[data-cs-modal-price]').textContent = point.price || '';
                        popin.querySelector('[data-cs-modal-link]').href         = point.url || '#';

                        // L'adresse est facultative : la ligne disparait plutot
                        // que d'afficher une puce de localisation toute seule.
                        const adresse = popin.querySelector('[data-cs-modal-address]');
                        popin.querySelector('[data-cs-modal-address-text]').textContent = point.address || '';
                        adresse.hidden = ! point.address;

                        popin.hidden = false;
                        document.body.classList.add('cs-modal-open');
                        popin.querySelector('.cs-modal-close').focus();
                    }

                    function fermerPopin() {
                        if (! popin || popin.hidden) {
                            return;
                        }

                        popin.hidden = true;
                        document.body.classList.remove('cs-modal-open');
                        dernierDeclencheur?.focus?.();
                    }

                    popin?.querySelectorAll('[data-cs-modal-close]').forEach(function (bouton) {
                        bouton.addEventListener('click', fermerPopin);
                    });

                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape') {
                            fermerPopin();
                        }
                    });

                    // ---- Carte ----
                    let carte = null;

                    function construire() {
                        if (carte) {
                            carte.invalidateSize();
                            return;
                        }

                        carte = L.map(conteneur, { scrollWheelZoom: false });

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap',
                        }).addTo(carte);

                        const reperes = points.map(function (point) {
                            const repere = L.marker([point.lat, point.lng]).addTo(carte);

                            repere.on('click', function () {
                                dernierDeclencheur = document.activeElement;
                                ouvrirPopin(point);
                            });

                            return repere;
                        });

                        // `fitBounds` avant `invalidateSize` cadrait sur une
                        // taille perimee : la carte laissait une bande grise a
                        // droite au premier affichage de l'onglet. On mesure,
                        // on cadre, puis on recommence une fois la mise en page
                        // du panneau terminee.
                        function ajuster() {
                            carte.invalidateSize();

                            if (reperes.length) {
                                carte.fitBounds(L.featureGroup(reperes).getBounds().pad(0.2));
                            }
                        }

                        ajuster();
                        setTimeout(ajuster, 200);
                    }

                    // Le panneau « Carte » signale son affichage ; si la carte
                    // est deja visible (pas d'onglets), on construit tout de suite.
                    document.addEventListener('cs:shown', function (event) {
                        if (event.target.contains(conteneur)) {
                            construire();
                        }
                    });

                    if (conteneur.offsetParent !== null) {
                        construire();
                    }
                })();
            </script>
        @endpush
    @else
        <div class="cs-map-placeholder">
            <i class="fa-solid fa-map-location-dot"></i>
            <p class="cs-map-placeholder-title">Aucune annonce localisée</p>
            <p class="cs-map-placeholder-text">
                Les annonces apparaîtront sur la carte dès qu'une position leur sera associée.
            </p>
        </div>
    @endif
</section>
