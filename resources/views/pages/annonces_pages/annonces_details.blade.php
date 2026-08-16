@extends('layouts.main')

@section('title', 'Détail de l\'annonce - Olten.fr')

@section('content')

<!-- Galerie d'images -->
<div class="image-gallery">
    <div class="gallery-container">
        <div class="gallery-slides" id="gallerySlides">

            @forelse ($ad->images as $image)
                <div class="gallery-slide">
                    <img 
                        src="{{ asset('storage/' . $image->path) }}" 
                        alt="{{ $ad->title }}"
                    >
                </div>
            @empty
                <div class="gallery-slide">
                    <img 
                        src="{{ asset('assets/images/no-image.jpg') }}" 
                        alt="Aucune image"
                    >
                </div>
            @endforelse

        </div>

        <button class="gallery-nav prev" onclick="changeSlide(-1)">‹</button>
        <button class="gallery-nav next" onclick="changeSlide(1)">›</button>

        <div class="gallery-indicators" id="indicators"></div>
    </div>
</div>

<!-- Contenu principal -->
<div class="container-annonce-details">
    <div class="main-content-annonce">

        <!-- SECTION GAUCHE -->
        <div class="left-section-annonce">

            <div class="breadcrumb">
                <div>
                    <a href="#">{{ $ad->category->nom ?? 'Catégorie non définie' }}</a>
                    <span>›</span>
                    <span>{{ $ad->address }}</span>
                </div>
                @if($ad->expires_at && \Carbon\Carbon::parse($ad->expires_at)->toDateString() < now()->toDateString())
                    <span class="expired">Expirée</span>
                @endif
            </div>

            <div class="status-badge">
                <i class="fas fa-check-circle"></i>
                Commence à partir de €{{ $ad->price_per_day }}
            </div>

            <h1>{{ $ad->title }}</h1>

            <div class="tags-container">
                <div class="category-tag">
                    <i class="fas fa-tools"></i>
                    {{ $ad->category->nom ?? 'Catégorie non définie' }}
                </div>
                <div class="location-tag">
                    <i class="fas fa-map-marker-alt"></i>
                    {{ $ad->address }}
                </div>
            </div>

            <div class="tabs-navigation">
                <a href="#apercu" class="tab-link active" onclick="showSection(event, 'apercu')">Aperçu</a>
                <a href="#description" class="tab-link" onclick="showSection(event, 'description')">Description</a>
                <a href="#emplacement" class="tab-link" onclick="showSection(event, 'emplacement')">Emplacement</a>
            </div>

            <section id="apercu" class="content-section active">
                <h2 class="section-title">Aperçu</h2>
                <p class="description">
                    {!! $ad->summary !!}
                </p>
            </section>

            <section id="description" class="content-section">
                <h2 class="section-title">Description</h2>
                <p class="description">
                    {!! $ad->description !!}
                </p>
            </section>

            <section id="emplacement" class="content-section location-section w-100">
                <h2 class="section-title">Emplacement</h2>

                @if($ad->latitude && $ad->longitude)
                    <div id="adMap" class="map-container always-visible" style="width:100%; height:500px;"></div>
                @else
                    <p>Adresse non disponible pour cette annonce.</p>
                @endif
            </section>
        </div>

        <!-- SECTION DROITE -->
        <div class="right-section">
            @if(is_null($ad->user_id))
                <div class="alert-box">
                    <i class="fas fa-exclamation-triangle alert-icon"></i>
                    <span>Non vérifié. Revendiquer cette annonce !</span>
                </div>
            @endif

            @if(!auth()->check() || (auth()->check() && auth()->user()->id != $ad->user_id && $ad->expires_at && \Carbon\Carbon::parse($ad->expires_at)->toDateString() >= now()->toDateString()))
                <div class="reservation-box">
                    <div class="reservation-title">
                        <i class="far fa-calendar-alt"></i>
                        Réservation
                    </div>
                    <form class="w-100" action="{{ route('bookings.store', $ad) }}" method="POST">
                        @csrf
                        <div class="date-selector">

                            <label class="date-label">
                                <i class="fa-solid fa-calendar-days"></i>
                                Dates de réservation
                            </label>

                            <input
                                type="text"
                                id="reservation_dates"
                                class="reservation-datepicker"
                                placeholder="Sélectionner vos dates"
                                readonly
                            >

                            <input type="hidden" name="start_date" id="start_date">
                            <input type="hidden" name="end_date" id="end_date">

                        </div>
                        <button class="reserve-button">Réserver Maintenant</button>
                    </form>

                    <div class="action-buttons">
                        <button class="action-btn" onclick="window.location.href='mailto:{{ $ad->user->email }}'">
                            <i class="far fa-comment"></i> Message
                        </button>

                        <button class="action-btn" onclick="window.location.href='tel:{{ $ad->user->phone }}'">
                            <i class="fas fa-phone"></i> Appeler
                        </button>

                        <button class="action-btn btn-favorite {{ auth()->check() && auth()->user()->hasFavorited($ad) ? 'active' : '' }}" data-ad-id="{{ $ad->id }}" data-favorited="{{ auth()->check() && auth()->user()->hasFavorited($ad) ? 'true' : 'false' }}">
                            <i class="far fa-heart"></i> J'aime
                        </button>
                    </div>
                    <button class="signal-btn" onclick="signalAd({{ $ad->id }})">
                        <i class="fas fa-flag"></i>
                        Signaler cette annonce
                    </button>

                </div>
            @endif
        </div>

    </div>
</div>
<script>
    let mapInitialized = false;
    let map;

    function showSection(event, sectionId) {
        event.preventDefault();

        document.querySelectorAll('.tab-link').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.content-section').forEach(section => section.classList.remove('active'));

        event.target.classList.add('active');
        document.getElementById(sectionId).classList.add('active');

        if (sectionId === 'emplacement') {
            if (!mapInitialized) {
                const lat = {{ $ad->latitude ?? 46.6 }};
                const lng = {{ $ad->longitude ?? 1.8 }};

                map = L.map('adMap').setView([lat, lng], 14);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                L.marker([lat, lng]).addTo(map)
                .bindPopup('{{ $ad->address ?? "Adresse non disponible" }}')
                .openPopup();

                mapInitialized = true;
            } else {
                setTimeout(() => map.invalidateSize(), 100);
            }
        }
    }

    function signalAd(adId) {
        const reason = prompt("Pourquoi voulez-vous signaler cette annonce ?");
        if (!reason) return;

        fetch(`/ads/${adId}/report`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason })
        })
        .then(res => res.json())
        .then(data => alert(data.message || 'Annonce signalée !'))
        .catch(err => console.error(err));
    }

    document.addEventListener('DOMContentLoaded', function () {
        flatpickr("#reservation_dates", {
            mode: "range",
            locale: "fr",
            minDate: "today",
            maxDate: "{{ \Carbon\Carbon::parse($ad->available_until)->format('Y-m-d') }}",

            disable: @json($reservedDates),

            dateFormat: "Y-m-d",

            onChange: function(selectedDates) {

                if (selectedDates.length === 2) {

                    document.getElementById('start_date').value =
                        flatpickr.formatDate(selectedDates[0], "Y-m-d");

                    document.getElementById('end_date').value =
                        flatpickr.formatDate(selectedDates[1], "Y-m-d");
                }
            }
        });
    });

</script>

@endsection
