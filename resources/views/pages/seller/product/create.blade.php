@extends('layouts.connected')
@section('title', 'Ajouter un produit - Olten')

@section('content')

<div class="breadcrumb">
    <a href="#">Accueil</a>
    <span>></span>
    <span>Ajouter un produit</span>
</div>

<h1>Ajouter un produit</h1>

<form action="{{ route('seller.produits.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

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
                <input type="text" name="name" class="form-input" placeholder="Nom du produit" required>
            </div>

            <div class="form-group">
                <label class="form-label">
                    Catégorie <span class="required">*</span>
                </label>
                <select name="category_id" class="form-select" required>
                    <option value="">Choisir Catégorie</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->nom }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" id="description" placeholder="Description du produit"></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Images du produit</label>
            <input type="file" name="images[]" class="form-input" accept="image/*" multiple>
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
                <input type="text" name="address" id="adresseVendeur" class="form-input" placeholder="Adresse vendeur">
                <ul id="adresseSuggestions" class="suggestions"></ul>
            </div>

            <div class="coordinate-fields d-none">
                <div class="form-group">
                    <label class="form-label">Longitude</label>
                    <input type="text" name="longitude" id="longitude" class="form-input" placeholder="Longitude">
                </div>
                <div class="form-group">
                    <label class="form-label">Latitude</label>
                    <input type="text" name="latitude" id="latitude" class="form-input" placeholder="Latitude">
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
                    <input type="number" name="price" class="form-input" step="0.01" required>
                    <span class="input-suffix">€</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    Stock <span class="required">*</span>
                </label>
                <input type="number" name="stock" class="form-input" required>
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
                <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                <label class="toggle-label" for="is_active"></label>
                <input type="hidden" name="is_active" value="0"> {{-- apres le label --}}
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
                {{-- Hidden en premier pour envoyer 0 par defaut --}}
                <input type="hidden" name="delivery_available" value="0">
                {{-- Checkbox avec value="1" pour que Laravel accepte la validation boolean --}}
                <input type="checkbox" name="delivery_available" id="livraisonActive" value="1">
                <label for="livraisonActive" class="toggle-label"></label>
            </div>
        </div>
    </div>

    <!-- SUBMIT -->
    <div class="form-actions">
        <button type="submit" class="btn-submit">
            <i class="fa-solid fa-paper-plane"></i> Ajouter le produit
        </button>
    </div>

</form>

@endsection