@extends('layouts.connected')
@section('title', 'Modifier un produit - Olten')

@section('content')

<div class="breadcrumb">
    <a href="#">Accueil</a>
    <span>></span>
    <span>Modifier un produit</span>
</div>

<h1>Modifier un produit</h1>

<form action="{{ route('seller.produits.update', $product) }}" method="POST" enctype="multipart/form-data">
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
            <div class="form-section-icon">
                <i class="fa-solid fa-box"></i>
            </div>
            <h2 class="form-section-title">Informations du produit</h2>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">
                    Nom du produit <span class="required">*</span>
                </label>
                <input type="text" name="name" class="form-input"
                       value="{{ old('name', $product->name) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">
                    Catégorie <span class="required">*</span>
                </label>
                <select name="category_id" class="form-select" required>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            @selected($category->id == old('category_id', $product->category_id))>
                            {{ $category->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" id="description">{{ old('description', $product->description) }}</textarea>
        </div>

        <!-- IMAGES -->
        <div class="form-group">
            <label class="form-label">Photo de l'annonce</label>
            <input type="file" name="images[]" class="form-input" accept="image/*" multiple>

            @if($product->images->count())
                <br/>
                <div class="current-images" style="display:flex; gap:10px; flex-wrap:wrap;">
                    @foreach($product->images as $img)
                        <div class="image-wrapper" data-id="{{ $img->id }}" style="position:relative;">
                            <img src="{{ asset('storage/' . $img->image) }}" alt="Image produit" width="100">
                            <button type="button" class="delete-image" style="position:absolute; top:0; right:0; background:red;color:white;border:none;">×</button>
                        </div>
                    @endforeach
                </div>
            @endif
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
                       placeholder="Adresse vendeur" value="{{ old('address', $product->address) }}">
                <ul id="adresseSuggestions" class="suggestions"></ul>
            </div>

            <div class="coordinate-fields d-none">
                <div class="form-group">
                    <label class="form-label">Longitude</label>
                    <input type="text" name="longitude" id="longitude" class="form-input"
                           placeholder="Longitude" value="{{ old('longitude', $product->longitude) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Latitude</label>
                    <input type="text" name="latitude" id="latitude" class="form-input"
                           placeholder="Latitude" value="{{ old('latitude', $product->latitude) }}">
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION PRIX & STOCK -->
    <div class="form-container">
        <div class="form-section-header">
            <div class="form-section-icon">
                <i class="fa-solid fa-tag"></i>
            </div>
            <h2 class="form-section-title">Prix & Stock</h2>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">
                    Prix <span class="required">*</span>
                </label>
                <div class="input-group">
                    <input type="number" name="price" step="0.01" class="form-input"
                           value="{{ old('price', $product->price) }}" required>
                    <span class="input-suffix">DA</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    Stock <span class="required">*</span>
                </label>
                <input type="number" name="stock" class="form-input"
                       value="{{ old('stock', $product->stock) }}" required>
            </div>
        </div>
    </div>

    <!-- SECTION STATUT -->
    <div class="form-container">
        <div class="form-section-header">
            <div class="form-section-icon">
                <i class="fa-solid fa-toggle-on"></i>
            </div>
            <h2 class="form-section-title">Statut</h2>
        </div>

        <div class="form-group">
            <label class="form-label">Produit actif ?</label>
            <div class="toggle-switch">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                <label class="toggle-label" for="is_active"></label>
                <input type="hidden" name="is_active" value="0">
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
                <input type="hidden" name="delivery_available" value="0">
                <input type="checkbox" name="delivery_available" id="livraisonActive" value="1" {{ old('delivery_available', $product->delivery_available) ? 'checked' : '' }}>
                <label for="livraisonActive" class="toggle-label"></label>
            </div>
        </div>
    </div>
    <!-- SUBMIT -->
    <div class="form-actions">
        <button type="submit" class="btn-submit">
            <i class="fa-solid fa-pen"></i> Modifier le produit
        </button>
    </div>

</form>
<script src="{{ asset('assets/js/deleteProductImgs.js') }}"></script>
@endsection