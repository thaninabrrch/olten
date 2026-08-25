@extends('layouts.connected')
@section('title', 'Modifier un produit - Olten')

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('seller.produits.index') }}">Mes produits</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Modifier</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Modifier le produit</h1>
            <p class="sp-subtitle">{{ $product->name }}</p>
        </div>

        <a href="{{ route('products.show', $product) }}" class="sp-btn-primary">
            Voir la fiche
        </a>
    </header>

    {{-- Les identifiants des champs sont ceux attendus par assets/js/adress.js
         (carte, autocompletion), assets/js/ckeditor.js (editeur de texte) et
         assets/js/deleteProductImgs.js (suppression d'une photo). --}}
    <form action="{{ route('seller.produits.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="sp-alert">
                <strong>Le produit n'a pas pu être enregistré.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 1. Informations --}}
        <section class="sp-form-section">
            <div class="sp-form-head">
                <span class="sp-step">1</span>
                <div>
                    <h2>Informations</h2>
                    <p>Le nom et la catégorie déterminent où votre produit est visible.</p>
                </div>
            </div>

            <div class="sp-form-grid">
                <div class="sp-field">
                    <label class="sp-label" for="name">Nom du produit <span class="sp-req">*</span></label>
                    <input type="text" name="name" id="name" class="sp-input"
                           value="{{ old('name', $product->name) }}" required>
                </div>

                <div class="sp-field">
                    <label class="sp-label" for="category_id">Catégorie <span class="sp-req">*</span></label>
                    <select name="category_id" id="category_id" class="sp-input" required>
                        <option value="">Choisir une catégorie</option>
                        @forelse($categories->groupBy(fn ($c) => $c->service->display_name ?? 'Autres') as $serviceName => $group)
                            <optgroup label="{{ $serviceName }}">
                                @foreach($group as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>
                                        {{ $category->nom }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @empty
                            <option value="" disabled>Aucune catégorie disponible</option>
                        @endforelse
                    </select>
                </div>
            </div>

            <div class="sp-field">
                <label class="sp-label" for="description">Description</label>
                <textarea name="description" id="description">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="sp-field">
                <label class="sp-label" for="images">Ajouter des photos</label>
                <input type="file" name="images[]" id="images" class="sp-input sp-file" accept="image/*" multiple>
                <span class="sp-help">Les nouvelles photos s'ajoutent à celles déjà en ligne.</span>
            </div>

            @if($product->images->count())
                <div class="sp-field">
                    <span class="sp-label">Photos actuelles</span>

                    <div class="sp-thumbs current-images">
                        @foreach($product->images as $img)
                            <div class="sp-thumb image-wrapper" data-id="{{ $img->id }}">
                                <img src="{{ asset('storage/' . $img->image) }}" alt="Photo du produit" loading="lazy">
                                <button type="button" class="delete-image" aria-label="Supprimer cette photo">&times;</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        {{-- 2. Prix et stock --}}
        <section class="sp-form-section">
            <div class="sp-form-head">
                <span class="sp-step">2</span>
                <div>
                    <h2>Prix et stock</h2>
                    <p>Le tarif à l'unité et la quantité dont vous disposez.</p>
                </div>
            </div>

            <div class="sp-form-grid">
                <div class="sp-box">
                    <div class="sp-box-head">
                        <label class="sp-label" for="price">Prix <span class="sp-req">*</span></label>
                        <span class="sp-help">Le montant payé par l'acheteur, à l'unité.</span>
                    </div>

                    <div class="sp-input-group">
                        <input type="number" name="price" id="price" class="sp-input"
                               step="0.01" min="0" value="{{ old('price', $product->price) }}" required>
                        <span class="sp-input-suffix">€</span>
                    </div>
                </div>

                <div class="sp-box">
                    <div class="sp-box-head">
                        <label class="sp-label" for="stock">Stock <span class="sp-req">*</span></label>
                        <span class="sp-help">À zéro, le produit s'affiche en rupture.</span>
                    </div>

                    <input type="number" name="stock" id="stock" class="sp-input"
                           min="0" value="{{ old('stock', $product->stock) }}" required>
                </div>
            </div>
        </section>

        {{-- 3. Adresse --}}
        <section class="sp-form-section">
            <div class="sp-form-head">
                <span class="sp-step">3</span>
                <div>
                    <h2>Adresse</h2>
                    <p>Le point de retrait du produit. Sélectionnez une suggestion pour placer le repère.</p>
                </div>
            </div>

            <div class="sp-map">
                <div id="map" style="height:100%"></div>
            </div>

            <div class="sp-field">
                <label class="sp-label" for="adresseVendeur">Adresse</label>
                <input type="text" name="address" id="adresseVendeur" class="sp-input"
                       value="{{ old('address', $product->address) }}"
                       placeholder="Commencez à saisir une adresse..." autocomplete="off">
                <ul id="adresseSuggestions" class="suggestions"></ul>
            </div>

            <div class="coordinate-fields d-none">
                <div class="sp-field">
                    <label class="sp-label" for="longitude">Longitude</label>
                    <input type="text" name="longitude" id="longitude" class="sp-input"
                           value="{{ old('longitude', $product->longitude) }}">
                </div>

                <div class="sp-field">
                    <label class="sp-label" for="latitude">Latitude</label>
                    <input type="text" name="latitude" id="latitude" class="sp-input"
                           value="{{ old('latitude', $product->latitude) }}">
                </div>
            </div>
        </section>

        {{-- 4. Mise en ligne --}}
        <section class="sp-form-section">
            <div class="sp-form-head">
                <span class="sp-step">4</span>
                <div>
                    <h2>Mise en ligne</h2>
                    <p>La visibilité du produit et la possibilité de le faire livrer.</p>
                </div>
            </div>

            <div class="sp-form-grid">
                <div class="sp-box is-row">
                    <div class="sp-box-head">
                        <label class="sp-label" for="is_active">Produit actif</label>
                        <span class="sp-help">Désactivé, il reste dans votre catalogue sans être visible.</span>
                    </div>

                    {{-- Le champ cache suit la case : sans lui, decocher n'envoie rien --}}
                    <div class="toggle-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               @checked(old('is_active', $product->is_active))>
                        <label class="toggle-label" for="is_active"></label>
                    </div>
                </div>

                <div class="sp-box is-row">
                    <div class="sp-box-head">
                        <label class="sp-label" for="livraisonActive">Proposez-vous une livraison ?</label>
                        <span class="sp-help">Les livreurs de la plateforme pourront se proposer.</span>
                    </div>

                    <div class="toggle-switch">
                        <input type="hidden" name="delivery_available" value="0">
                        <input type="checkbox" name="delivery_available" id="livraisonActive" value="1"
                               @checked(old('delivery_available', $product->delivery_available))>
                        <label for="livraisonActive" class="toggle-label"></label>
                    </div>
                </div>
            </div>
        </section>

        {{-- Barre d'action --}}
        <div class="sp-form-actions">
            <a href="{{ route('seller.produits.index') }}" class="sp-act is-ghost">Annuler</a>
            <button type="submit" class="sp-btn-primary">Enregistrer les modifications</button>
        </div>
    </form>
</div>

<script src="{{ asset('assets/js/deleteProductImgs.js') }}"></script>
@endsection
