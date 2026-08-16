@extends('layouts.connected')
@section('title', 'Modifier une annonce - Olten')

@section('content')

<div class="breadcrumb">
    <a href="index.html">Accueil</a>
    <span>></span>
    <span>Modifier une annonce</span>
</div>
@php
    use Carbon\Carbon;
@endphp
<h1>Modifier une annonce</h1>

<form action="{{ route('ads.update', $ad) }}" method="POST" enctype="multipart/form-data" id="annonceForm">
    @csrf
    @method('PUT')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- SECTION INFORMATIONS -->
    <div class="form-container">
        <div class="form-section-header">
            <div class="d-flex justify-content-between flex-wrap w-100">
                <div class="form-section-icon">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <h2 class="form-section-title mt-2">Informations</h2>
                @if($ad->expires_at && Carbon::parse($ad->expires_at)->toDateString() < now()->toDateString())
                    <span class="expired">Expirée</span>
                @endif
            </div>

        </div>
                    
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">
                    Titre de l'annonce <span class="required">*</span>
                </label>
                <input type="text" name="title" class="form-input" 
                        placeholder="Titre de l'annonce" required
                        value="{{ old('title', $ad->title) }}">
            </div>

            <div class="form-group">
                <label class="form-label">
                    Catégorie <span class="required">*</span>
                </label>
                <select name="category_id" class="form-select" required>
                    <option value="">Choisir Catégorie</option>
                    @forelse($categories as $category)
                        <option value="{{ $category->id }}" @selected($category->id == old('category_id', $ad->category_id))>
                            {{ $category->nom }}
                        </option>
                    @empty
                        <option value="1">Aucune catégorie trouvée</option>
                    @endforelse
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Aperçu de l'annonce</label>
            <textarea name="summary" id="summary">{{ old('summary', $ad->summary) }}</textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Description de l'annonce</label>
            <textarea name="description" id="description">{{ old('description', $ad->description) }}</textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Photo de l'annonce</label>
            <input type="file" name="images[]" class="form-input" accept="image/*" multiple>

            @if($ad->images->count())
                <br/>
                <div class="current-images" style="display:flex; gap:10px; flex-wrap:wrap;">
                    @foreach($ad->images as $img)
                        <div class="image-wrapper" data-id="{{ $img->id }}" style="position:relative;">
                            <img src="{{ asset('storage/' . $img->path) }}" alt="Image annonce" width="100">
                            <button type="button" class="delete-image" style="position:absolute; top:0; right:0; background:red;color:white;border:none;">×</button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    @php
        $today = now()->format('Y-m-d');

        $availableFrom = optional($ad->available_from)->format('Y-m-d');
        $availableUntil = optional($ad->available_until)->format('Y-m-d');

        $minFrom = ($availableFrom && $availableFrom < $today) ? $availableFrom : $today;
    @endphp

    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">
                Disponible à partir du <span class="required">*</span>
            </label>

            <input 
                type="date"
                name="available_from"
                class="form-input"
                value="{{ old('available_from', $availableFrom) }}"
                min="{{ $minFrom }}"
                required
            >
        </div>

        <div class="form-group">
            <label class="form-label">
                Disponible jusqu'au <span class="required">*</span>
            </label>

            <input 
                type="date"
                name="available_until"
                class="form-input"
                value="{{ old('available_until', $availableUntil) }}"
                min="{{ old('available_from', $availableFrom ?? $today) }}"
                required
            >
        </div>
    </div>

    <!-- SECTION ADRESSE -->
    <div class="form-container">
        <div class="form-section-header">
            <div class="form-section-icon">
                <i class="fa-solid fa-location-dot"></i>
            </div>
            <h2 class="form-section-title">Adresse du vendeur</h2>
        </div>

        <div class="map-container">
            <div id="map" style="height: 100%;"></div>
        </div>

        <div class="address-fields">
            <div class="form-group">
                <label class="form-label">Adresse</label>
                <input type="text" name="address" id="adresseVendeur" class="form-input"
                       placeholder="Adresse vendeur" value="{{ old('address', $ad->address) }}">
                <ul id="adresseSuggestions" class="suggestions"></ul>
            </div>

            <div class="coordinate-fields d-none">
                <div class="form-group">
                    <label class="form-label">Longitude</label>
                    <input type="text" name="longitude" id="longitude" class="form-input"
                           placeholder="Longitude" value="{{ old('longitude', $ad->longitude) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Latitude</label>
                    <input type="text" name="latitude" id="latitude" class="form-input"
                           placeholder="Latitude" value="{{ old('latitude', $ad->latitude) }}">
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION TARIF -->
    <div class="form-container">
        <div class="form-section-header">
            <div class="form-section-icon">
                <i class="fa-solid fa-tag"></i>
            </div>
            <h2 class="form-section-title">Tarif de l'annonce</h2>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">
                    Prix par jour <span class="required">*</span>
                </label>
                <div class="input-group">
                    <input type="number" name="price_per_day" class="form-input" placeholder="0" step="0.01" required
                           value="{{ old('price_per_day', $ad->price_per_day) }}">
                    <span class="input-suffix">€ / jour</span>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION LIVRAISON -->
    <div class="form-container">
        <div class="form-section-header">
            <div class="form-section-icon">
                <i class="fa-solid fa-truck"></i>
            </div>
            <h2 class="form-section-title">Livraison</h2>
        </div>

        <div class="form-group">
            <label class="form-label">Proposez-vous une livraison ?</label>
            <div class="toggle-switch">
                <input type="checkbox" name="delivery_active" id="livraisonActive"
                       @if(old('delivery_active', $ad->delivery_active)) checked @endif>
                <label for="livraisonActive" class="toggle-label"></label>
            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn-submit">
            <i class="fa-solid fa-paper-plane"></i> Modifier l'annonce
        </button>
    </div>
    <input type="hidden" name="distance_km" id="distanceKm" value="{{ old('distance_km', $ad->distance_km) }}">
    <input type="hidden" name="delivery_cost" id="deliveryCost" value="{{ old('delivery_cost', $ad->delivery_cost) }}">
</form>
<script src="{{ asset('assets/js/deleteAdsImgs.js') }}"></script>
@endsection