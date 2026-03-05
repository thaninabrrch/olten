@extends('layouts.connected')
@section('title', 'Détails du Trajet | ' . config('app.name'))
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
    .leaflet-container {
        border-radius: 12px;
        height: 350px;
        width: 100%;
    }
</style>
<script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
@section('content')
    <div class="min-h-screen bg-[#F8F9FA] pb-12">
        <!-- Header Section -->
        <div class="">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <nav class="flex mb-2" aria-label="Breadcrumb">
                            <ol
                                class="inline-flex items-center space-x-1 md:space-x-3 text-xs font-medium text-gray-400 uppercase tracking-wider">
                                <li>Chauffeur VTC</li>
                                <li><i class="fas fa-chevron-right mx-2 text-[10px]"></i> Mes trajets</li>
                            </ol>
                        </nav>
                        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                            Trajet <span class="text-[#FF4500]">#TR-{{ $trajet->covoiturage_id }}</span>
                        </h1>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="px-4 py-1.5 rounded-full text-sm font-semibold 
                        {{ $trajet->statut === 'active' ? 'bg-green-50 text-green-600 border border-green-100' : 'bg-gray-50 text-gray-600 border border-gray-100' }}">
                            ● {{ ucfirst($trajet->statut) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Colonne Gauche: Détails et Segments -->
                <div class="lg:col-span-2 space-y-8">

                    <!-- CARD: ALLER -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-4 border-b border-gray-50 bg-orange-50/30">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center">
                                <i class="fas fa-arrow-right mr-2 text-[#FF4500]"></i>
                                Trajet Aller
                                @if ($trajet->retour)
                                    <span class="ml-3 text-xs font-normal text-gray-500">
                                        {{ $trajet->date_depart->format('d M Y') }} à {{ $trajet->heure_depart }}
                                    </span>
                                @endif
                            </h3>
                        </div>
                        <div class="p-8">
                            <div class="flex items-start justify-between mb-8">
                                <div class="flex-1">
                                    <div class="relative pl-8 border-l-2 border-dashed border-gray-200 space-y-12">
                                        <!-- Départ -->
                                        <div class="relative">
                                            <div
                                                class="absolute -left-[41px] top-0 w-4 h-4 rounded-full bg-white border-4 border-[#FF4500] z-10">
                                            </div>
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Point
                                                de départ</p>
                                            <p class="text-xl font-semibold text-gray-900">{{ $trajet->depart }}</p>
                                            <p class="text-sm text-gray-500 mt-1">
                                                {{ $trajet->date_depart->format('d M Y') }} à
                                                {{ $trajet->heure_depart ?? $trajet->date_depart->format('H:i') }}
                                            </p>
                                        </div>
                                        <!-- Destination -->
                                        <div class="relative">
                                            <div
                                                class="absolute -left-[41px] top-0 w-4 h-4 rounded-full bg-[#FF4500] border-4 border-orange-100 z-10">
                                            </div>
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">
                                                Destination</p>
                                            <p class="text-xl font-semibold text-gray-900">{{ $trajet->destination }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right hidden sm:block">
                                    <div class="bg-orange-50 p-4 rounded-2xl inline-block">
                                        <p class="text-xs font-bold text-[#FF4500] uppercase mb-1">Prix par place</p>
                                        <p class="text-3xl font-black text-[#FF4500]">
                                            {{ number_format($trajet->prix_place, 2) }}€</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Informations trajet aller (distance, durée) -->
                            @if ($route && isset($route['distance']))
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="bg-gray-50 p-3 rounded-xl">
                                        <p class="text-xs text-gray-500">Distance aller</p>
                                        <p class="text-lg font-bold">{{ number_format($route['distance'] / 1000, 1) }} km
                                        </p>
                                    </div>
                                    <div class="bg-gray-50 p-3 rounded-xl">
                                        <p class="text-xs text-gray-500">Durée aller</p>
                                        <p class="text-lg font-bold">
                                            {{ floor($route['duration'] / 3600) }}h
                                            {{ floor(($route['duration'] % 3600) / 60) }}min
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <!-- Segments d'escales aller -->
                            @if ($segments && count($segments))
                                <div class="pt-6 border-t border-gray-50">
                                    <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center">
                                        <i class="fas fa-map-signs mr-2 text-gray-400"></i> Escales aller
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach ($segments as $segment)
                                            <div class="flex items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                                                <div
                                                    class="w-8 h-8 rounded-lg bg-white flex items-center justify-center shadow-sm mr-3">
                                                    <i class="fas fa-stop text-[10px] text-orange-400"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-xs font-medium text-gray-500 italic">
                                                        {{ $segment['from'] ?? 'Escale' }} →
                                                        {{ $segment['to'] ?? 'Escale' }}
                                                    </p>
                                                    <p class="text-sm font-bold text-gray-800">
                                                        {{ number_format($segment['price'] ?? 0, 2) }}€</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Carte aller -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-4 border-b border-gray-50 flex justify-between items-center">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center">
                                <i class="fas fa-map mr-2 text-[#FF4500]"></i>
                                Parcours aller
                            </h3>
                            <span class="text-xs text-gray-400 cursor-pointer" id="openMapFullscreen" data-map-type="aller">
                                <i class="fas fa-expand-alt mr-1"></i> Plein écran
                            </span>
                        </div>
                        <div id="map-aller"></div>

                    </div>

                    <!-- CARD: RETOUR (si retour existe) -->
                    @if ($trajet->retour && $returnTripData && isset($returnTripData['trajet']))
                        @php $trajetRetour = $returnTripData['trajet']; @endphp

                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-8">
                            <div class="p-4 border-b border-gray-50 bg-blue-50/30">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center">
                                    <i class="fas fa-rotate-right mr-2 text-blue-500"></i>
                                    Trajet Retour
                                    <span class="ml-3 text-xs font-normal text-gray-500">
                                        {{ \Carbon\Carbon::parse($trajet->return_date)->format('d M Y') }} à
                                        {{ $trajet->return_time }}
                                    </span>
                                </h3>
                            </div>
                            <div class="p-8">
                                <div class="flex items-start justify-between mb-8">
                                    <div class="flex-1">
                                        <div class="relative pl-8 border-l-2 border-dashed border-gray-200 space-y-12">
                                            <!-- Départ retour (qui est la destination aller) -->
                                            <div class="relative">
                                                <div
                                                    class="absolute -left-[41px] top-0 w-4 h-4 rounded-full bg-white border-4 border-blue-500 z-10">
                                                </div>
                                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">
                                                    Point
                                                    de départ retour</p>
                                                <p class="text-xl font-semibold text-gray-900">{{ $trajet->destination }}
                                                </p>
                                                <p class="text-sm text-gray-500 mt-1">
                                                    {{ \Carbon\Carbon::parse($trajet->return_date)->format('d M Y') }} à
                                                    {{ $trajet->return_time }}
                                                </p>
                                            </div>
                                            <!-- Destination retour (qui est le départ aller) -->
                                            <div class="relative">
                                                <div
                                                    class="absolute -left-[41px] top-0 w-4 h-4 rounded-full bg-blue-500 border-4 border-blue-100 z-10">
                                                </div>
                                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">
                                                    Destination retour</p>
                                                <p class="text-xl font-semibold text-gray-900">{{ $trajet->depart }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right hidden sm:block">
                                        <div class="bg-blue-50 p-4 rounded-2xl inline-block">
                                            <p class="text-xs font-bold text-blue-600 uppercase mb-1">Prix retour</p>
                                            <p class="text-3xl font-black text-blue-600">
                                                {{ number_format($returnTripData['total'] ?? 0, 2) }}€</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations trajet retour -->
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="bg-gray-50 p-3 rounded-xl">
                                        <p class="text-xs text-gray-500">Distance retour</p>
                                        <p class="text-lg font-bold">
                                            {{ number_format($trajetRetour['distance'] / 1000, 1) }} km
                                        </p>
                                    </div>
                                    <div class="bg-gray-50 p-3 rounded-xl">
                                        <p class="text-xs text-gray-500">Durée retour</p>
                                        <p class="text-lg font-bold">
                                            {{ floor($trajetRetour['duration'] / 3600) }}h
                                            {{ floor(($trajetRetour['duration'] % 3600) / 60) }}min
                                        </p>
                                    </div>
                                </div>

                                <!-- Segments d'escales retour -->
                                @if (isset($returnTripData['pricing']) && count($returnTripData['pricing']))
                                    <div class="pt-6 border-t border-gray-50">
                                        <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center">
                                            <i class="fas fa-map-signs mr-2 text-gray-400"></i> Escales retour
                                        </h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach ($returnTripData['pricing'] as $segment)
                                                <div
                                                    class="flex items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                                                    <div
                                                        class="w-8 h-8 rounded-lg bg-white flex items-center justify-center shadow-sm mr-3">
                                                        <i class="fas fa-stop text-[10px] text-blue-400"></i>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-xs font-medium text-gray-500 italic">
                                                            {{ $segment['from'] ?? 'Escale' }} →
                                                            {{ $segment['to'] ?? 'Escale' }}
                                                        </p>
                                                        <p class="text-sm font-bold text-gray-800">
                                                            {{ number_format($segment['price'] ?? 0, 2) }}€</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Carte retour -->
                        @if (isset($trajetRetour['geometry']['coordinates']) && count($trajetRetour['geometry']['coordinates']))
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                                <div class="p-4 border-b border-gray-50 flex justify-between items-center">
                                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center">
                                        <i class="fas fa-map mr-2 text-blue-500"></i>
                                        Parcours retour
                                    </h3>
                                    <span class="text-xs text-gray-400 cursor-pointer" id="openMapFullscreen"
                                        data-map-type="aller">
                                        <i class="fas fa-expand-alt mr-1"></i> Plein écran
                                    </span>
                                </div>
                                <div id="map-retour"></div>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Colonne Droite: Résumé & Actions -->
                <div class="space-y-6">

                    <!-- Résumé des prix aller-retour -->
                    @if ($trajet->retour && isset($returnTripData['total']))
                        <div class="bg-gradient-to-br from-orange-50 to-blue-50 rounded-2xl p-5 border border-orange-100">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-bold text-gray-700">Aller</span>
                                <span
                                    class="font-bold text-[#FF4500]">{{ number_format($trajet->prix_total_affiche, 2) }}€</span>
                            </div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-bold text-gray-700">Retour</span>
                                <span
                                    class="font-bold text-blue-600">{{ number_format($returnTripData['total'], 2) }}€</span>
                            </div>
                            <div class="border-t border-orange-200 my-2 pt-2 flex items-center justify-between">
                                <span class="text-base font-extrabold text-gray-900">Total AR</span>
                                <span
                                    class="text-xl font-black text-gray-900">{{ number_format($trajet->prix_total_affiche + $returnTripData['total'], 2) }}€</span>
                            </div>
                        </div>
                    @endif

                    <!-- Détails Techniques -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-6">Informations véhicule
                        </h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 text-sm">Places disponibles</span>
                                <span
                                    class="bg-gray-900 text-white text-xs font-bold px-2.5 py-1 rounded-lg">{{ $trajet->nb_places }}
                                    places</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 text-sm">Type de trajet</span>
                                <span class="text-sm font-semibold {{ $trajet->retour ? 'text-blue-600' : '' }}">
                                    {{ $trajet->retour ? 'Aller-Retour' : 'Aller simple' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-start">
                                <span class="text-gray-500 text-sm">Mode passager</span>
                                <div class="text-right text-sm font-semibold">
                                    @if ($trajet->passenger_mode === 'mixed')
                                        <span class="text-blue-600"><i class="fas fa-users mr-1"></i> Mixte</span>
                                    @elseif($trajet->passenger_mode === 'womenOnly')
                                        <span class="text-pink-500"><i class="fas fa-venus mr-1"></i> Femmes
                                            uniquement</span>
                                    @elseif($trajet->passenger_mode === 'maxBackSeats')
                                        <span class="text-gray-700"><i class="fas fa-chair mr-1"></i> Confort (Max 2
                                            AR)</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Rapides -->
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('covoiturage.edit', $trajet->covoiturage_id) }}"
                            class="flex flex-col items-center justify-center p-4 bg-white border border-gray-100 rounded-2xl shadow-sm hover:border-[#FF4500] hover:text-[#FF4500] transition-all group">
                            <i class="fas fa-edit mb-2 text-gray-400 group-hover:text-[#FF4500]"></i>
                            <span class="text-xs font-bold uppercase">Modifier</span>
                        </a>
                        <form action="{{ route('covoiturage.destroy', $trajet->covoiturage_id) }}" method="POST"
                            onsubmit="return confirm('Voulez-vous vraiment annuler ce trajet ?');" class="flex">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                class="flex flex-col items-center justify-center p-4 bg-white border border-gray-100 rounded-2xl shadow-sm hover:border-red-500 hover:text-red-500 transition-all group">
                                <i class="fas fa-trash-alt mb-2 text-gray-400 group-hover:text-red-500"></i>
                                <span class="text-xs font-bold uppercase">Annuler</span>
                            </button>
                        </form>
                    </div>

                    <!-- Tip Card -->
                    <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-6 text-white shadow-lg">
                        <i class="fas fa-lightbulb text-yellow-400 mb-3 text-xl"></i>
                        <p class="text-sm font-medium leading-relaxed">Pensez à vérifier l'état de votre véhicule avant
                            chaque trajet pour garantir la meilleure expérience à vos passagers.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal plein écran -->
    <div id="mapFullScreenModal" class="fixed inset-0 z-[10000] hidden bg-black/90 flex items-center justify-center">
        <div class="relative w-full h-full max-w-full max-h-full p-4">
            <button id="closeMapModal"
                class="absolute top-4 right-4 z-[10001] text-white text-3xl font-bold hover:text-gray-300">
                &times;
            </button>
            <div id="map-fullscreen" class="w-full h-full rounded-lg shadow-lg"></div>
        </div>
    </div>
    <!-- Scripts Mapbox -->
    @if ($route)
        <script>
            @if ($route && isset($route['geometry']['coordinates']))
                const coordsAller = {!! json_encode($route['geometry']['coordinates']) !!};
                const validCoordsAller = coordsAller.filter(c => Array.isArray(c) && c.length === 2)
                    .map(c => [c[1], c[0]]); // Leaflet prend [lat, lng]

                if (validCoordsAller.length) {
                    const mapAller = L.map('map-aller').fitBounds(validCoordsAller);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(mapAller);

                    L.polyline(validCoordsAller, {
                        color: '#FF4500',
                        weight: 5,
                        opacity: 0.8
                    }).addTo(mapAller);

                    L.marker(validCoordsAller[0]).bindPopup('Départ Aller').addTo(mapAller);
                    L.marker(validCoordsAller[validCoordsAller.length - 1]).bindPopup('Arrivée Aller').addTo(mapAller);
                }
            @endif

            @if ($trajet->retour && isset($returnTripData['trajet']['geometry']['coordinates']))
                const coordsRetour = {!! json_encode($returnTripData['trajet']['geometry']['coordinates']) !!};
                const validCoordsRetour = coordsRetour.filter(c => Array.isArray(c) && c.length === 2)
                    .map(c => [c[1], c[0]]); // [lat, lng]

                if (validCoordsRetour.length) {
                    const mapRetour = L.map('map-retour').fitBounds(validCoordsRetour);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(mapRetour);

                    L.polyline(validCoordsRetour, {
                        color: '#3B82F6',
                        weight: 5,
                        opacity: 0.8,
                        dashArray: '5, 5'
                    }).addTo(mapRetour);

                    L.marker(validCoordsRetour[0]).bindPopup('Départ Retour').addTo(mapRetour);
                    L.marker(validCoordsRetour[validCoordsRetour.length - 1]).bindPopup('Arrivée Retour').addTo(mapRetour);
                }
            @endif
        </script>
        <script>
            const openMapBtns = document.querySelectorAll('#openMapFullscreen');
            const mapModal = document.getElementById('mapFullScreenModal');
            const closeMapBtn = document.getElementById('closeMapModal');
            let mapFull = null;

            openMapBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const type = btn.dataset.mapType; // "aller" ou "retour"
                    mapModal.classList.remove('hidden');

                    // Supprime la carte précédente si elle existe
                    if (mapFull) mapFull.remove();

                    // Sélection des coordonnées
                    let coords = [];
                    if (type === 'aller') coords = validCoordsAller;
                    if (type === 'retour') coords = validCoordsRetour;

                    // Crée la carte plein écran
                    mapFull = L.map('map-fullscreen').fitBounds(coords);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(mapFull);

                    // Tracer la ligne
                    L.polyline(coords, {
                        color: type === 'aller' ? '#FF4500' : '#3B82F6',
                        weight: 5,
                        opacity: 0.8,
                        dashArray: type === 'retour' ? '5,5' : null
                    }).addTo(mapFull);

                    // Marqueurs
                    L.marker(coords[0]).bindPopup(type === 'aller' ? 'Départ Aller' : 'Départ Retour').addTo(
                        mapFull);
                    L.marker(coords[coords.length - 1]).bindPopup(type === 'aller' ? 'Arrivée Aller' :
                        'Arrivée Retour').addTo(mapFull);
                });
            });

            // Fermer le modal
            closeMapBtn.addEventListener('click', () => {
                mapModal.classList.add('hidden');
                if (mapFull) mapFull.remove();
            });


            let id = $(this).data('id');

            if (!confirm('Voulez-vous vraiment annuler ce trajet ?')) {
                return;
            }

            $.ajax({
                url: '/covoiturage/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function() {
                    alert('Erreur lors de la suppression');
                }
            });
        </script>
    @endif
@endsection
