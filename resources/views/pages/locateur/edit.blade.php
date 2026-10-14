@extends('layouts.connected')
@section('title', 'Modifier une annonce - Olten')

@php
    $today = now()->format('Y-m-d');

    $availableFrom  = optional($ad->available_from)->format('Y-m-d');
    $availableUntil = optional($ad->available_until)->format('Y-m-d');

    // Une annonce deja commencee garde sa date de debut comme minimum
    $minFrom = ($availableFrom && $availableFrom < $today) ? $availableFrom : $today;

    // Une annonce expiree est modifiee depuis les archives : on y ramene
    // l'utilisateur, et on lui dit comment la remettre en ligne.
    $expired = $ad->isExpired();
    $retour  = $expired ? route('archives', ['type' => 'annonce']) : route('ads.index');
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('ads.index') }}">Mes annonces</a>
        <i class="fa-solid fa-chevron-right"></i>
        @if ($expired)
            <a href="{{ $retour }}">Archives</a>
            <i class="fa-solid fa-chevron-right"></i>
        @endif
        <span class="is-current">Modifier</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Modifier l'annonce</h1>
            <p class="sp-subtitle">{{ $ad->title }}</p>
        </div>

        <a href="{{ route('ads.show', $ad) }}" class="sp-btn-primary">
            Voir l'annonce
        </a>
    </header>

    {{-- Les identifiants des champs sont ceux attendus par assets/js/adress.js
         (carte, autocompletion), assets/js/ckeditor.js (editeurs de texte) et
         assets/js/deleteAdsImgs.js (suppression d'une photo). --}}
    <form action="{{ route('ads.update', $ad) }}" method="POST" enctype="multipart/form-data" id="annonceForm">
        @csrf
        @method('PUT')

        @if ($expired)
            <div class="sp-note">
                <i class="fa-solid fa-box-archive"></i>
                Cette annonce est archivée depuis la fin de sa disponibilité, le
                {{ $ad->expires_at->format('d/m/Y') }}. Choisissez de nouvelles dates dans
                « Disponibilités » pour la remettre en ligne.
            </div>
        @endif

        @if ($errors->any())
            <div class="sp-alert">
                <strong>L'annonce n'a pas pu être enregistrée.</strong>
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
                    <p data-mode-text data-prestation="Le titre et la catégorie permettent aux prestataires concernés de trouver votre demande.">Le titre et la catégorie déterminent où votre annonce est visible.</p>
                </div>
            </div>

            <div class="sp-form-grid">
                <div class="sp-field">
                    <label class="sp-label" for="title"><span data-mode-text data-prestation="Votre besoin">Titre de l'annonce</span> <span class="sp-req">*</span></label>
                    <input type="text" name="title" id="title" class="sp-input"
                           value="{{ old('title', $ad->title) }}" required>
                </div>

                <div class="sp-field">
                    <label class="sp-label" for="category_id">Catégorie <span class="sp-req">*</span></label>
                    <select name="category_id" id="category_id" class="sp-input" required
                            data-autocomplete="Rechercher une catégorie…">
                        <option value="">Choisir une catégorie</option>
                        @forelse($categories->groupBy(fn ($c) => $c->service->display_name ?? 'Autres') as $serviceName => $group)
                            <optgroup label="{{ $serviceName }}">
                                @foreach($group as $category)
                                    <option value="{{ $category->id }}"
                                            data-service="{{ $category->service->slug ?? '' }}"
                                            data-service-name="{{ $serviceName }}"
                                            data-icon="{{ $category->icon_class }}"
                                            @selected(old('category_id', $ad->category_id) == $category->id)>
                                        {{ $category->nom }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @empty
                            <option value="" disabled>Aucune catégorie disponible</option>
                        @endforelse
                    </select>
                    <x-select-autocomplete />
                </div>
            </div>

            <div class="sp-field">
                <label class="sp-label" for="summary">Aperçu</label>
                <textarea name="summary" id="summary" placeholder="Une phrase qui résume votre offre">{{ old('summary', $ad->summary) }}</textarea>
                <span class="sp-help">C'est ce texte court qui apparaît dans les listes de résultats.</span>
            </div>

            <div class="sp-field">
                <label class="sp-label" for="description">Description</label>
                <textarea name="description" id="description">{{ old('description', $ad->description) }}</textarea>
                <span class="sp-help" data-mode-hide="location vente" hidden>Précisez le travail attendu : surface, quantités, matériel fourni, accès, contraintes… Plus votre demande est claire, plus les propositions seront justes.</span>
                <x-ai-description target="annonce" />
            </div>

            <div class="sp-field">
                <label class="sp-label" for="images">Ajouter des photos</label>
                <input type="file" name="images[]" id="images" class="sp-input sp-file" accept="image/*" multiple>
                <span class="sp-help">Les nouvelles photos s'ajoutent à celles déjà en ligne.</span>
            </div>

            @if($ad->images->count())
                <div class="sp-field">
                    <span class="sp-label">Photos actuelles</span>

                    <div class="sp-thumbs current-images">
                        @foreach($ad->images as $img)
                            <div class="sp-thumb image-wrapper" data-id="{{ $img->id }}">
                                <img src="{{ asset('storage/' . $img->path) }}" alt="Photo de l'annonce" loading="lazy">
                                <button type="button" class="delete-image" aria-label="Supprimer cette photo">&times;</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        {{-- 2. Disponibilites — masquee pour une categorie de vente ; pour une
             prestation, fenetre dans laquelle le travail doit etre realise --}}
        <section class="sp-form-section" id="section-disponibilites" data-mode-hide="vente">
            <div class="sp-form-head">
                <span class="sp-step">2</span>
                <div>
                    <h2 data-mode-text data-prestation="Période de réalisation">Disponibilités</h2>
                    <p data-mode-text data-prestation="Quand la prestation doit-elle être réalisée ? Votre demande reste en ligne jusqu'à la date limite.">La période pendant laquelle votre bien peut être réservé.</p>
                </div>
            </div>

            <div class="sp-form-grid">
                <div class="sp-field">
                    <label class="sp-label" for="available_from"><span data-mode-text data-prestation="À réaliser à partir du">Disponible à partir du</span> <span class="sp-req">*</span></label>
                    <input type="date" name="available_from" id="available_from" class="sp-input"
                           value="{{ old('available_from', $availableFrom) }}"
                           min="{{ $minFrom }}" required>
                </div>

                <div class="sp-field">
                    <label class="sp-label" for="available_until"><span data-mode-text data-prestation="Au plus tard le">Disponible jusqu'au</span> <span class="sp-req">*</span></label>
                    <input type="date" name="available_until" id="available_until" class="sp-input"
                           value="{{ old('available_until', $availableUntil) }}"
                           min="{{ old('available_from', $availableFrom ?? $today) }}" required>
                </div>
            </div>
        </section>

        {{-- 3. Adresse --}}
        <section class="sp-form-section">
            <div class="sp-form-head">
                <span class="sp-step">3</span>
                <div>
                    <h2>Adresse</h2>
                    <p data-mode-text data-prestation="Le lieu où la prestation sera réalisée. Sélectionnez une suggestion pour placer le repère.">Le point de retrait du bien. Sélectionnez une suggestion pour placer le repère.</p>
                </div>
            </div>

            <div class="sp-map">
                <div id="map" style="height:100%"></div>
            </div>

            <div class="sp-field">
                <label class="sp-label" for="adresseVendeur">Adresse</label>
                <input type="text" name="address" id="adresseVendeur" class="sp-input"
                       value="{{ old('address', $ad->address) }}"
                       placeholder="Commencez à saisir une adresse..." autocomplete="off">
                <ul id="adresseSuggestions" class="suggestions"></ul>
            </div>

            <div class="coordinate-fields d-none">
                <div class="sp-field">
                    <label class="sp-label" for="longitude">Longitude</label>
                    <input type="text" name="longitude" id="longitude" class="sp-input"
                           value="{{ old('longitude', $ad->longitude) }}">
                </div>

                <div class="sp-field">
                    <label class="sp-label" for="latitude">Latitude</label>
                    <input type="text" name="latitude" id="latitude" class="sp-input"
                           value="{{ old('latitude', $ad->latitude) }}">
                </div>
            </div>
        </section>

        {{-- 4. Tarif et livraison --}}
        <section class="sp-form-section">
            <div class="sp-form-head">
                <span class="sp-step">4</span>
                <div>
                    <h2 data-mode-text data-prestation="Votre budget">Tarif et livraison</h2>
                    <p id="tarif-intro" data-mode-text
                       data-vente="Le prix affiché aux acheteurs, et la possibilité de faire livrer le bien."
                       data-prestation="Le montant que vous envisagez. Chaque prestataire vous répondra avec son propre prix.">Le prix affiché aux locataires, et la possibilité de faire livrer le bien.</p>
                </div>
            </div>

            <div class="sp-form-grid">
                <div class="sp-box">
                    <div class="sp-box-head">
                        <label class="sp-label" for="price_per_day">
                            <span id="price-label" data-mode-text data-vente="Prix" data-prestation="Budget indicatif">Prix par jour</span> <span class="sp-req">*</span>
                        </label>
                        <span class="sp-help" id="price-help" data-mode-text
                              data-vente="Le prix de vente affiché aux acheteurs."
                              data-prestation="Il aide les prestataires à calibrer leur proposition.">Le tarif journalier affiché aux locataires.</span>
                    </div>

                    <div class="sp-input-group">
                        <input type="number" name="price_per_day" id="price_per_day" class="sp-input"
                               step="0.01" min="0" value="{{ old('price_per_day', $ad->price_per_day) }}" required>
                        <span class="sp-input-suffix" id="price-suffix" data-mode-text data-vente="€" data-prestation="€">€ / jour</span>
                    </div>
                </div>

                {{-- Une prestation ne se livre pas --}}
                <div class="sp-box is-row" data-mode-hide="prestation">
                    <div class="sp-box-head">
                        <label class="sp-label" for="livraisonActive">Proposez-vous une livraison ?</label>
                        <span class="sp-help">Les livreurs de la plateforme pourront se proposer pour l'acheminement.</span>
                    </div>

                    <div class="toggle-switch">
                        <input type="checkbox" name="delivery_active" id="livraisonActive"
                               @checked(old('delivery_active', $ad->delivery_active))>
                        <label for="livraisonActive" class="toggle-label"></label>
                    </div>
                </div>
            </div>
        </section>

        {{-- Barre d'action --}}
        <div class="sp-form-actions">
            <a href="{{ $retour }}" class="sp-act is-ghost">Annuler</a>
            <button type="submit" class="sp-btn-primary">Enregistrer les modifications</button>
        </div>

        <input type="hidden" name="distance_km" id="distanceKm" value="{{ old('distance_km', $ad->distance_km) }}">
        <input type="hidden" name="delivery_cost" id="deliveryCost" value="{{ old('delivery_cost', $ad->delivery_cost) }}">
    </form>
</div>

<script src="{{ asset('assets/js/deleteAdsImgs.js') }}"></script>

{{-- Bascule location / vente / prestation : assets/js/ad-form.js. La meme
     regle est rejouee cote serveur dans AdController. --}}
<script src="{{ asset('assets/js/ad-form.js') }}?v={{ @filemtime(public_path('assets/js/ad-form.js')) ?: 1 }}"></script>
@endsection
