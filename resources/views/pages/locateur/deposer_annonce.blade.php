@extends('layouts.connected')
@section('title', 'Déposer une annonce - Olten')

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('ads.index') }}">Mes annonces</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Déposer une annonce</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Déposer une annonce</h1>
            <p class="sp-subtitle">Décrivez votre bien, indiquez vos disponibilités et votre tarif : votre annonce part en validation.</p>
        </div>

        <a href="{{ route('ads.index') }}" class="sp-btn-primary">
            Mes annonces
        </a>
    </header>

    {{-- Les identifiants des champs sont ceux attendus par assets/js/adress.js
         (carte, autocompletion) et assets/js/ckeditor.js (editeurs de texte). --}}
    <form action="{{ route('ads.store') }}" method="POST" enctype="multipart/form-data" id="annonceForm">
        @csrf

        @if ($errors->any())
            <div class="sp-alert">
                <strong>Votre annonce n'a pas pu être publiée.</strong>
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
                    <p>Le titre et la catégorie déterminent où votre annonce sera visible.</p>
                </div>
            </div>

            <div class="sp-form-grid">
                <div class="sp-field">
                    <label class="sp-label" for="title">Titre de l'annonce <span class="sp-req">*</span></label>
                    <input type="text" name="title" id="title" class="sp-input"
                           placeholder="Ex. Mini-pelle 1,5 T avec godet"
                           value="{{ old('title') }}" required>
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
                <label class="sp-label" for="summary">Aperçu</label>
                <textarea name="summary" id="summary" placeholder="Une phrase qui résume votre offre">{{ old('summary') }}</textarea>
                <span class="sp-help">C'est ce texte court qui apparaît dans les listes de résultats.</span>
            </div>

            <div class="sp-field">
                <label class="sp-label" for="description">Description</label>
                <textarea name="description" id="description" placeholder="État du matériel, conditions de location, équipements fournis...">{{ old('description') }}</textarea>
            </div>

            <div class="sp-field">
                <label class="sp-label" for="images">Photos</label>
                <input type="file" name="images[]" id="images" class="sp-input sp-file" accept="image/*" multiple>
                <span class="sp-help">Plusieurs photos possibles. La première servira de vignette.</span>
            </div>
        </section>

        {{-- 2. Disponibilites --}}
        <section class="sp-form-section">
            <div class="sp-form-head">
                <span class="sp-step">2</span>
                <div>
                    <h2>Disponibilités</h2>
                    <p>La période pendant laquelle votre bien peut être réservé.</p>
                </div>
            </div>

            <div class="sp-form-grid">
                <div class="sp-field">
                    <label class="sp-label" for="available_from">Disponible à partir du <span class="sp-req">*</span></label>
                    <input type="date" name="available_from" id="available_from" class="sp-input"
                           min="{{ now()->format('Y-m-d') }}" value="{{ old('available_from') }}" required>
                </div>

                <div class="sp-field">
                    <label class="sp-label" for="available_until">Disponible jusqu'au <span class="sp-req">*</span></label>
                    <input type="date" name="available_until" id="available_until" class="sp-input"
                           min="{{ now()->format('Y-m-d') }}" value="{{ old('available_until') }}" required>
                </div>
            </div>
        </section>

        {{-- 3. Adresse --}}
        <section class="sp-form-section">
            <div class="sp-form-head">
                <span class="sp-step">3</span>
                <div>
                    <h2>Adresse</h2>
                    <p>Le point de retrait du bien. Sélectionnez une suggestion pour placer le repère.</p>
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
                    <input type="text" name="longitude" id="longitude" class="sp-input" placeholder="Longitude">
                </div>

                <div class="sp-field">
                    <label class="sp-label" for="latitude">Latitude</label>
                    <input type="text" name="latitude" id="latitude" class="sp-input" placeholder="Latitude">
                </div>
            </div>
        </section>

        {{-- 4. Tarif et livraison --}}
        <section class="sp-form-section">
            <div class="sp-form-head">
                <span class="sp-step">4</span>
                <div>
                    <h2>Tarif et livraison</h2>
                    <p>Le prix affiché aux locataires, et la possibilité de faire livrer le bien.</p>
                </div>
            </div>

            {{-- Les deux encarts partagent la meme grille : meme largeur,
                 meme hauteur, meme traitement visuel. --}}
            <div class="sp-form-grid">
                <div class="sp-box">
                    <div class="sp-box-head">
                        <label class="sp-label" for="price_per_day">Prix par jour <span class="sp-req">*</span></label>
                        <span class="sp-help">Le tarif journalier affiché aux locataires.</span>
                    </div>

                    <div class="sp-input-group">
                        <input type="number" name="price_per_day" id="price_per_day" class="sp-input"
                               placeholder="0,00" step="0.01" min="0" value="{{ old('price_per_day') }}" required>
                        <span class="sp-input-suffix">€ / jour</span>
                    </div>
                </div>

                <div class="sp-box is-row">
                    <div class="sp-box-head">
                        <label class="sp-label" for="livraisonActive">Proposez-vous une livraison ?</label>
                        <span class="sp-help">Les livreurs de la plateforme pourront se proposer pour l'acheminement.</span>
                    </div>

                    <div class="toggle-switch">
                        <input type="checkbox" name="delivery_active" id="livraisonActive" @checked(old('delivery_active'))>
                        <label for="livraisonActive" class="toggle-label"></label>
                    </div>
                </div>
            </div>
        </section>

        {{-- Barre d'action --}}
        <div class="sp-form-actions">
            <a href="{{ route('ads.index') }}" class="sp-act is-ghost">Annuler</a>
            <button type="submit" class="sp-btn-primary">Publier l'annonce</button>
        </div>

        <input type="hidden" name="distance_km" id="distanceKm">
        <input type="hidden" name="delivery_cost" id="deliveryCost">
    </form>
</div>
@endsection
