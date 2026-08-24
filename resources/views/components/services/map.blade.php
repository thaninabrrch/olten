{{--
    Carte des annonces geolocalisees du service.

    Leaflet est deja charge par le layout : le script est pousse dans la pile
    `scripts`, donc joue apres la librairie.
--}}
@props([
    'points' => [],
    'title'  => 'Visualisez instantanément les annonces disponibles autour de vous',
])

@php
    $points = collect($points)->values();
    $mapId  = 'cs-map-' . uniqid();
@endphp

<section class="cs-map-card">
    <div class="cs-map-header">
        <p class="cs-map-title">{{ $title }}</p>
        <span class="cs-map-badge">
            <i class="fa-solid fa-location-dot"></i>
            {{ $points->count() }} position(s) sur la carte
        </span>
    </div>

    @if($points->isNotEmpty())
        <div class="cs-map-frame">
            <div id="{{ $mapId }}" class="cs-map-canvas" data-points="{{ json_encode($points, JSON_UNESCAPED_UNICODE) }}"></div>
        </div>

        @push('scripts')
            <script>
                (function () {
                    const container = document.getElementById(@json($mapId));

                    if (! container || typeof L === 'undefined') {
                        return;
                    }

                    const points = JSON.parse(container.dataset.points || '[]');

                    const map = L.map(container, { scrollWheelZoom: false });

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap',
                    }).addTo(map);

                    const markers = points.map(function (point) {
                        const marker = L.marker([point.lat, point.lng]).addTo(map);

                        marker.bindPopup(
                            '<a class="cs-map-popup" href="' + point.url + '">'
                            + '<img src="' + point.image + '" alt="">'
                            + '<span class="cs-map-popup-cat">' + point.category + '</span>'
                            + '<strong>' + point.title + '</strong>'
                            + '<span class="cs-map-popup-address">' + (point.address || '') + '</span>'
                            + '<span class="cs-map-popup-price">' + point.price + '</span>'
                            + '</a>'
                        );

                        return marker;
                    });

                    map.fitBounds(L.featureGroup(markers).getBounds().pad(0.2));

                    // Le conteneur peut etre mesure avant sa mise en page finale.
                    setTimeout(function () { map.invalidateSize(); }, 200);
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
