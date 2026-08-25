@extends('layouts.connected')

@section('title', 'Détail du trajet | ' . config('app.name'))

{{--
    Fiche d'un trajet de covoiturage, alignee sur le design de l'espace
    connecte : fil d'ariane, en-tete, indicateurs, panneaux et boutons
    viennent du design system « sp- » (style_connected.css).

    Les libelles de statut et le composant d'itineraire (.sp-trip) sont
    exactement ceux de la liste « Mes trajets », dont cette page est le
    detail.

    Leaflet est deja charge par layouts.connected : les doublons de
    <link> / <script> qui trainaient ici ont ete retires, ainsi que
    mapbox-gl qui n'etait jamais utilise.
--}}

@php
    $statuts = [
        'actif'   => ['Actif',      'is-paid'],
        'validé'  => ['Validé',     'is-confirmed'],
        'pending' => ['En attente', 'is-pending'],
        'complet' => ['Complet',    'is-shipped'],
        'inactif' => ['Inactif',    'is-neutral'],
        'annulé'  => ['Annulé',     'is-cancelled'],
    ];

    [$stLabel, $stClass] = $statuts[$trajet->statut] ?? [ucfirst((string) $trajet->statut ?: 'Inconnu'), 'is-neutral'];

    $mois = [1 => 'janv', 'févr', 'mars', 'avr', 'mai', 'juin', 'juil', 'août', 'sept', 'oct', 'nov', 'déc'];

    $dateCourte = function ($date) use ($mois) {
        if (! $date) {
            return null;
        }
        $d = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);

        return $d->format('d') . ' ' . $mois[(int) $d->format('n')] . ' ' . $d->format('Y');
    };

    $heure = fn ($h) => $h ? \Illuminate\Support\Str::of((string) $h)->substr(0, 5) : null;

    $duree = function ($secondes) {
        $h = floor($secondes / 3600);
        $m = floor(($secondes % 3600) / 60);

        return $h > 0 ? $h . ' h ' . $m . ' min' : $m . ' min';
    };

    // Trajet retour : les donnees vivent dans return_trip_data
    $trajetRetour = $trajet->retour ? ($returnTripData['trajet'] ?? null) : null;
    $segmentsRetour = $returnTripData['pricing'] ?? [];
    $totalRetour = (float) ($returnTripData['total'] ?? 0);
    $totalAller = (float) ($trajet->prix_total_affiche ?: $prixTotal);

    $coordsAller = $route['geometry']['coordinates'] ?? [];
    $coordsRetour = $trajetRetour['geometry']['coordinates'] ?? [];

    /*
     | L'ecran « Prix » ne renvoie que le montant de chaque portion : les
     | cles from / to disparaissent des segments des la premiere edition.
     | On retombe alors sur les etapes de l'itineraire, qui les portent.
     */
    $etapesAller = $itineraire ?: [];
    $etapesRetour = $trajet->return_itinerary ?: [];

    $bornes = function (array $segment, array $etapes, int $index) {
        return [
            ($segment['from'] ?? '') ?: ($etapes[$index]['name'] ?? ''),
            ($segment['to'] ?? '') ?: ($etapes[$index + 1]['name'] ?? ''),
        ];
    };

    // passenger_mode est tantot une chaine simple, tantot un objet JSON
    $reglages = json_decode((string) $trajet->passenger_mode, true);
    $modePassager = is_array($reglages)
        ? ($reglages['passenger_mode'] ?? null)
        : $trajet->passenger_mode;

    $modesPassager = [
        'mixed'        => ['Mixte',              'fa-users'],
        'womenOnly'    => ['Entre femmes',       'fa-venus'],
        'maxBackSeats' => ['Confort (max 2 à l\'arrière)', 'fa-chair'],
    ];

    [$mpLabel, $mpIcone] = $modesPassager[$modePassager] ?? ['Non précisé', 'fa-users'];
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('covoiturage.index') }}">Mes trajets</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Détail</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Détail du trajet</h1>
            <p class="sp-subtitle">
                {{ $trajet->depart_ville }} → {{ $trajet->destination_ville }}
                @if($trajet->date_depart)
                    · {{ $dateCourte($trajet->date_depart) }}
                    @if($heure($trajet->heure_depart))
                        à {{ $heure($trajet->heure_depart) }}
                    @endif
                @endif
            </p>
        </div>

        <div class="sp-role-badges">
            <span class="sp-status {{ $stClass }}">{{ $stLabel }}</span>
            <span class="sp-ref">#{{ $trajet->covoiturage_id }}</span>
        </div>
    </header>

    {{-- Indicateurs --}}
    <div class="sp-stats">
        <div class="sp-stat">
            <span class="sp-stat-icon is-brand"><i class="fa-solid fa-euro-sign"></i></span>
            <div>
                <span class="sp-stat-value">{{ number_format($totalAller + $totalRetour, 2, ',', ' ') }} €</span>
                <span class="sp-stat-label">{{ $trajet->retour ? 'Total aller-retour' : 'Prix du trajet' }}</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-blue"><i class="fa-solid fa-users"></i></span>
            <div>
                <span class="sp-stat-value">{{ $trajet->nb_places }}</span>
                <span class="sp-stat-label">Place{{ $trajet->nb_places > 1 ? 's' : '' }} proposée{{ $trajet->nb_places > 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-green"><i class="fa-solid fa-road"></i></span>
            <div>
                <span class="sp-stat-value">
                    {{ isset($route['distance']) ? number_format($route['distance'] / 1000, 1, ',', ' ') . ' km' : '—' }}
                </span>
                <span class="sp-stat-label">Distance aller</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-red"><i class="fa-solid fa-map-pin"></i></span>
            <div>
                <span class="sp-stat-value">{{ count($segments) }}</span>
                <span class="sp-stat-label">Segment{{ count($segments) > 1 ? 's' : '' }} tarifé{{ count($segments) > 1 ? 's' : '' }}</span>
            </div>
        </div>
    </div>

    <div class="sp-trip-view">

        {{-- Colonne principale : aller, puis retour --}}
        <div class="sp-trip-main">

            {{-- Trajet aller --}}
            <section class="sp-panel">
                <div class="sp-toolbar">
                    <div>
                        <h2 class="sp-toolbar-title">Trajet aller</h2>
                        <span class="sp-count">
                            @if($trajet->date_depart)
                                {{ $dateCourte($trajet->date_depart) }}
                                @if($heure($trajet->heure_depart))
                                    à {{ $heure($trajet->heure_depart) }}
                                @endif
                            @else
                                Date non définie
                            @endif
                        </span>
                    </div>

                    <div class="sp-toolbar-actions">
                        <span class="sp-tag">
                            <i class="fa-solid fa-tag"></i>
                            {{ number_format($totalAller, 2, ',', ' ') }} €
                        </span>
                    </div>
                </div>

                <div class="sp-rows">
                    <div class="sp-trip">
                        <div class="sp-trip-step">
                            <span class="sp-trip-dot"></span>
                            <div>
                                <span class="sp-trip-label">Départ</span>
                                <span class="sp-trip-value">{{ $trajet->depart ?: 'Non précisé' }}</span>
                            </div>
                        </div>

                        <div class="sp-trip-step is-end">
                            <span class="sp-trip-dot"></span>
                            <div>
                                <span class="sp-trip-label">Arrivée</span>
                                <span class="sp-trip-value">{{ $trajet->destination ?: 'Non précisée' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="sp-specs is-inset">
                        <div>
                            <span>Distance</span>
                            <strong>
                                {{ isset($route['distance']) ? number_format($route['distance'] / 1000, 1, ',', ' ') . ' km' : '—' }}
                            </strong>
                        </div>
                        <div>
                            <span>Durée estimée</span>
                            <strong>{{ isset($route['duration']) ? $duree($route['duration']) : '—' }}</strong>
                        </div>
                        <div>
                            {{-- Somme des portions : prix_place n'est pas remis a jour par l'ecran « Prix » --}}
                            <span>Prix du trajet</span>
                            <strong>{{ number_format($totalAller, 2, ',', ' ') }} €</strong>
                        </div>
                    </div>

                    @if(count($segments))
                        <p class="sp-section-label">Escales et tarifs</p>

                        <div class="sp-segments">
                            @foreach($segments as $index => $segment)
                                @php [$depuis, $vers] = $bornes($segment, $etapesAller, $index); @endphp

                                <div class="sp-segment">
                                    <div class="sp-segment-way">
                                        <span class="sp-segment-from">{{ \App\Models\Covoiturage::villeCourte($depuis) ?: 'Départ' }}</span>
                                        <span class="sp-segment-arrow">→</span>
                                        <span class="sp-segment-to">{{ \App\Models\Covoiturage::villeCourte($vers) ?: 'Destination' }}</span>
                                    </div>

                                    <span class="sp-segment-price">
                                        {{ number_format((float) ($segment['price'] ?? 0), 2, ',', ' ') }} €
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if(count($coordsAller))
                    <div class="sp-map is-flush">
                        <div id="map-aller" style="height:100%"></div>
                    </div>

                    <div class="sp-actions">
                        <button type="button" class="sp-act is-ghost map-fullscreen-btn" data-map-type="aller">
                            <i class="fa-solid fa-expand"></i> Voir en plein écran
                        </button>
                    </div>
                @endif
            </section>

            {{-- Trajet retour --}}
            @if($trajetRetour)
                <section class="sp-panel">
                    <div class="sp-toolbar">
                        <div>
                            <h2 class="sp-toolbar-title">Trajet retour</h2>
                            <span class="sp-count">
                                {{ $dateCourte($trajet->return_date) ?? 'Date non définie' }}
                                @if($heure($trajet->return_time))
                                    à {{ $heure($trajet->return_time) }}
                                @endif
                            </span>
                        </div>

                        <div class="sp-toolbar-actions">
                            <span class="sp-tag">
                                <i class="fa-solid fa-tag"></i>
                                {{ number_format($totalRetour, 2, ',', ' ') }} €
                            </span>
                        </div>
                    </div>

                    <div class="sp-rows">
                        <div class="sp-trip">
                            <div class="sp-trip-step">
                                <span class="sp-trip-dot"></span>
                                <div>
                                    <span class="sp-trip-label">Départ</span>
                                    <span class="sp-trip-value">{{ $trajet->destination ?: 'Non précisé' }}</span>
                                </div>
                            </div>

                            <div class="sp-trip-step is-end">
                                <span class="sp-trip-dot"></span>
                                <div>
                                    <span class="sp-trip-label">Arrivée</span>
                                    <span class="sp-trip-value">{{ $trajet->depart ?: 'Non précisée' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="sp-specs is-inset">
                            <div>
                                <span>Distance</span>
                                <strong>
                                    {{ isset($trajetRetour['distance']) ? number_format($trajetRetour['distance'] / 1000, 1, ',', ' ') . ' km' : '—' }}
                                </strong>
                            </div>
                            <div>
                                <span>Durée estimée</span>
                                <strong>{{ isset($trajetRetour['duration']) ? $duree($trajetRetour['duration']) : '—' }}</strong>
                            </div>
                            <div>
                                <span>Prix du retour</span>
                                <strong>{{ number_format($totalRetour, 2, ',', ' ') }} €</strong>
                            </div>
                        </div>

                        @if(count($segmentsRetour))
                            <p class="sp-section-label">Escales et tarifs</p>

                            <div class="sp-segments">
                                @foreach($segmentsRetour as $index => $segment)
                                    @php [$depuis, $vers] = $bornes($segment, $etapesRetour, $index); @endphp

                                    <div class="sp-segment">
                                        <div class="sp-segment-way">
                                            <span class="sp-segment-from">{{ \App\Models\Covoiturage::villeCourte($depuis) ?: 'Départ' }}</span>
                                            <span class="sp-segment-arrow">→</span>
                                            <span class="sp-segment-to">{{ \App\Models\Covoiturage::villeCourte($vers) ?: 'Destination' }}</span>
                                        </div>

                                        <span class="sp-segment-price">
                                            {{ number_format((float) ($segment['price'] ?? 0), 2, ',', ' ') }} €
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if(count($coordsRetour))
                        <div class="sp-map is-flush">
                            <div id="map-retour" style="height:100%"></div>
                        </div>

                        <div class="sp-actions">
                            <button type="button" class="sp-act is-ghost map-fullscreen-btn" data-map-type="retour">
                                <i class="fa-solid fa-expand"></i> Voir en plein écran
                            </button>
                        </div>
                    @endif
                </section>
            @endif
        </div>

        {{-- Colonne de droite : recapitulatif et actions --}}
        <aside class="sp-trip-aside">

            @if($trajet->retour && ($trajetRetour || $totalRetour > 0))
                <section class="sp-panel">
                    <div class="sp-toolbar">
                        <div>
                            <h2 class="sp-toolbar-title">Recette attendue</h2>
                            <span class="sp-count">Prix affiché aux passagers</span>
                        </div>
                    </div>

                    <div class="sp-rows">
                        <div class="sp-facts">
                            <div class="sp-doc-ref">
                                <span>Aller</span>
                                <strong>{{ number_format($totalAller, 2, ',', ' ') }} €</strong>
                            </div>

                            <div class="sp-doc-ref">
                                <span>Retour</span>
                                <strong>{{ number_format($totalRetour, 2, ',', ' ') }} €</strong>
                            </div>
                        </div>

                        <div class="sp-total">
                            <span class="sp-total-label">Total aller-retour</span>
                            <span class="sp-total-value">{{ number_format($totalAller + $totalRetour, 2, ',', ' ') }} €</span>
                        </div>
                    </div>
                </section>
            @endif

            <section class="sp-panel">
                <div class="sp-toolbar">
                    <div>
                        <h2 class="sp-toolbar-title">Informations</h2>
                        <span class="sp-count">Véhicule et conditions à bord</span>
                    </div>
                </div>

                <div class="sp-rows">
                    <div class="sp-facts">
                        <div class="sp-doc-ref">
                            <span>Places disponibles</span>
                            <strong>{{ $trajet->nb_places }}</strong>
                        </div>

                        <div class="sp-doc-ref">
                            <span>Type de trajet</span>
                            <strong>{{ $trajet->retour ? 'Aller-retour' : 'Aller simple' }}</strong>
                        </div>

                        <div class="sp-doc-ref">
                            <span>Mode passager</span>
                            <strong><i class="fa-solid {{ $mpIcone }}"></i> {{ $mpLabel }}</strong>
                        </div>

                        <div class="sp-doc-ref">
                            <span>Réservation</span>
                            <strong>{{ $trajet->booking_mode === 'manual' ? 'Validation manuelle' : 'Instantanée' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="sp-actions">
                    <a href="{{ route('covoiturage.edit', $trajet->covoiturage_id) }}" class="sp-act is-edit">
                        Modifier le trajet
                    </a>

                    <form id="form-annuler-trajet"
                          action="{{ route('covoiturage.destroy', $trajet->covoiturage_id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="sp-act is-cancel" onclick="confirmerAnnulation()">
                            Annuler
                        </button>
                    </form>
                </div>
            </section>

            <div class="sp-note">
                Pensez à vérifier l'état de votre véhicule avant chaque départ : c'est ce qui
                pèse le plus dans les avis laissés par les passagers.
            </div>
        </aside>
    </div>
</div>

{{-- Carte en plein ecran --}}
<div class="sp-modal" id="mapFullScreenModal" hidden>
    <div class="sp-modal-backdrop" data-map-close></div>

    <div class="sp-modal-box is-wide" role="dialog" aria-modal="true" aria-labelledby="mapModalTitle">
        <div class="sp-modal-head">
            <div>
                <span class="sp-modal-kicker">Parcours</span>
                <h2 class="sp-modal-title" id="mapModalTitle">Carte du trajet</h2>
            </div>

            <button type="button" class="sp-act is-ghost" id="closeMapModal" data-map-close>Fermer</button>
        </div>

        <div class="sp-modal-body">
            <div class="sp-map is-modal">
                <div id="map-fullscreen" style="height:100%"></div>
            </div>
        </div>
    </div>
</div>

<script>
    // Couleurs de la plateforme : orange pour l'aller, bleu pour le retour
    const TRAJET_COULEURS = { aller: '#ff3c00', retour: '#14539c' };

    let validCoordsAller = [];
    let validCoordsRetour = [];

    @if(count($coordsAller))
        validCoordsAller = @json($coordsAller)
            .filter(c => Array.isArray(c) && c.length === 2)
            .map(c => [c[1], c[0]]); // Leaflet attend [lat, lng]
    @endif

    @if(count($coordsRetour))
        validCoordsRetour = @json($coordsRetour)
            .filter(c => Array.isArray(c) && c.length === 2)
            .map(c => [c[1], c[0]]);
    @endif

    function tracerParcours(cible, coords, type) {
        if (!coords.length) return null;

        const carte = L.map(cible).fitBounds(coords);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '©OpenStreetMap'
        }).addTo(carte);

        L.polyline(coords, {
            color: TRAJET_COULEURS[type],
            weight: 5,
            opacity: .9,
            dashArray: type === 'retour' ? '6, 6' : null
        }).addTo(carte);

        const pastille = (couleur) => L.divIcon({
            className: '',
            html: '<div style="width:16px;height:16px;background:' + couleur +
                  ';border:3px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(16,24,40,.3)"></div>',
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        });

        L.marker(coords[0], { icon: pastille(TRAJET_COULEURS[type]) })
            .bindPopup(type === 'retour' ? 'Départ du retour' : 'Départ')
            .addTo(carte);

        L.marker(coords[coords.length - 1], { icon: pastille('#16191d') })
            .bindPopup(type === 'retour' ? 'Arrivée du retour' : 'Arrivée')
            .addTo(carte);

        return carte;
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('map-aller')) {
            tracerParcours('map-aller', validCoordsAller, 'aller');
        }

        if (document.getElementById('map-retour')) {
            tracerParcours('map-retour', validCoordsRetour, 'retour');
        }
    });

    // ── Carte en plein ecran ──
    const mapModal = document.getElementById('mapFullScreenModal');
    let mapFull = null;

    function ouvrirCartePleinEcran(type) {
        const coords = type === 'retour' ? validCoordsRetour : validCoordsAller;
        if (!coords.length) return;

        document.getElementById('mapModalTitle').textContent =
            type === 'retour' ? 'Parcours du retour' : 'Parcours de l\'aller';

        mapModal.hidden = false;
        document.body.style.overflow = 'hidden';

        // requestAnimationFrame garantit que la fenetre est peinte (conteneur
        // non nul) avant que Leaflet calcule ses dimensions
        requestAnimationFrame(() => {
            if (mapFull) { mapFull.remove(); mapFull = null; }
            mapFull = tracerParcours('map-fullscreen', coords, type);
            if (mapFull) mapFull.invalidateSize();
        });
    }

    function fermerCartePleinEcran() {
        mapModal.hidden = true;
        document.body.style.overflow = '';
        if (mapFull) { mapFull.remove(); mapFull = null; }
    }

    document.querySelectorAll('.map-fullscreen-btn').forEach(btn => {
        btn.addEventListener('click', () => ouvrirCartePleinEcran(btn.dataset.mapType));
    });

    document.querySelectorAll('[data-map-close]').forEach(el => {
        el.addEventListener('click', fermerCartePleinEcran);
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !mapModal.hidden) fermerCartePleinEcran();
    });

    // ── Annulation du trajet ──
    function confirmerAnnulation() {
        Swal.fire({
            title: 'Annuler ce trajet ?',
            text: 'Cette action est définitive : le trajet et ses réservations seront supprimés.',
            icon: 'warning',
            iconColor: '#c0392b',
            showCancelButton: true,
            confirmButtonText: 'Oui, annuler le trajet',
            cancelButtonText: 'Conserver',
            confirmButtonColor: '#c0392b',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
        }).then(result => {
            if (result.isConfirmed) document.getElementById('form-annuler-trajet').submit();
        });
    }
</script>
@endsection
