@extends('layouts.connected')
@section('title', 'Ajouter un produit - Olten')

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('seller.produits.index') }}">Mes produits</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Ajouter un produit</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Ajouter un produit</h1>
            <p class="sp-subtitle">Décrivez votre article, fixez son prix et son stock : il part aussitôt en vente.</p>
        </div>

        <a href="{{ route('seller.produits.index') }}" class="sp-btn-primary">
            Mes produits
        </a>
    </header>

    {{-- Les identifiants des champs sont ceux attendus par assets/js/adress.js
         (carte, autocompletion) et assets/js/ckeditor.js (editeur de texte). --}}
    <form action="{{ route('seller.produits.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        @if ($errors->any())
            <div class="sp-alert">
                <strong>Le produit n'a pas pu être ajouté.</strong>
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
                    <p>Le nom et la catégorie déterminent où votre produit sera visible.</p>
                </div>
            </div>

            <div class="sp-form-grid">
                <div class="sp-field">
                    <label class="sp-label" for="name">Nom du produit <span class="sp-req">*</span></label>
                    <input type="text" name="name" id="name" class="sp-input"
                           placeholder="Ex. Perceuse sans fil 18 V"
                           value="{{ old('name') }}" required>
                </div>

                <div class="sp-field">
                    <label class="sp-label" for="category_id">Catégorie <span class="sp-req">*</span></label>
                    <select name="category_id" id="category_id" class="sp-input" required>
                        <option value="">Choisir une catégorie</option>
                        {{-- Les categories sont des sous-parties d'un service --}}
                        @forelse($categories->groupBy(fn ($c) => $c->service->display_name ?? 'Autres') as $serviceName => $group)
                            <optgroup label="{{ $serviceName }}">
                                @foreach($group as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
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
                <textarea name="description" id="description"
                          placeholder="État, accessoires fournis, garantie...">{{ old('description') }}</textarea>
            </div>

            <div class="sp-field">
                <label class="sp-label" for="images">Photos</label>
                <input type="file" name="images[]" id="images" class="sp-input sp-file" accept="image/*" multiple>
                <span class="sp-help">Plusieurs photos possibles. La première servira de vignette.</span>
            </div>
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
                               step="0.01" min="0" placeholder="0,00" value="{{ old('price') }}" required>
                        <span class="sp-input-suffix">€</span>
                    </div>
                </div>

                <div class="sp-box">
                    <div class="sp-box-head">
                        <label class="sp-label" for="stock">Stock <span class="sp-req">*</span></label>
                        <span class="sp-help">À zéro, le produit s'affiche en rupture.</span>
                    </div>

                    <input type="number" name="stock" id="stock" class="sp-input"
                           min="0" placeholder="0" value="{{ old('stock') }}" required>
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
                       placeholder="Commencez à saisir une adresse..." value="{{ old('address') }}" autocomplete="off">
                <ul id="adresseSuggestions" class="suggestions"></ul>
            </div>

            <div class="coordinate-fields d-none">
                <div class="sp-field">
                    <label class="sp-label" for="longitude">Longitude</label>
                    <input type="text" name="longitude" id="longitude" class="sp-input">
                </div>

                <div class="sp-field">
                    <label class="sp-label" for="latitude">Latitude</label>
                    <input type="text" name="latitude" id="latitude" class="sp-input">
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
                        <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', true))>
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
                               @checked(old('delivery_available'))>
                        <label for="livraisonActive" class="toggle-label"></label>
                    </div>
                </div>
            </div>
        </section>

        {{-- Barre d'action --}}
        <div class="sp-form-actions">
            <a href="{{ route('seller.produits.index') }}" class="sp-act is-ghost">Annuler</a>
            <button type="submit" class="sp-btn-primary">Ajouter le produit</button>
        </div>
    </form>
</div>
@endsection
