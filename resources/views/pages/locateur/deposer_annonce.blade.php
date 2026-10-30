@extends('layouts.connected')
@section('title', 'Déposer une annonce - Olten')

{{-- Service covoiturage : la page accueille l'assistant de trajet, partagé
     avec /covoiturage/create (livreur/covoiturage/partials). Ses styles et
     scripts ne sont chargés que dans ce mode. --}}
@if($tripMode)
    @push('styles')
        @include('livreur.covoiturage.partials.wizard-styles')
        <style>
            /* Dans la page, l'assistant tient dans un cadre plutôt qu'en plein écran */
            .sp-trip .app-content-area {
                height: min(780px, calc(100vh - 140px));
                min-height: 620px;
                border: 1px solid #E4E7EC;
                border-radius: 16px;
            }
        </style>
    @endpush
@endif

@section('content')
@php
    // Les trajets se retrouvent dans « Mes trajets », les annonces dans « Mes annonces »
    [$listUrl, $listLabel] = $tripMode
        ? [route('covoiturage.index'), 'Mes trajets']
        : [route('ads.index'), 'Mes annonces'];
@endphp

<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ $listUrl }}">{{ $listLabel }}</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Déposer une annonce</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Déposer une annonce</h1>
            @if($tripMode)
                <p class="sp-subtitle">Indiquez votre itinéraire, votre date de départ, vos places et vos prix : votre trajet sera proposé aux passagers.</p>
            @else
                <p class="sp-subtitle" id="page-subtitle" data-mode-text
                   data-vente="Décrivez votre bien et fixez son prix : votre annonce part en validation."
                   data-prestation="Décrivez votre besoin, quand et où le réaliser, et votre budget : les prestataires vous feront leurs propositions.">Décrivez votre bien, indiquez vos disponibilités et votre tarif : votre annonce part en validation.</p>
            @endif
        </div>

        <a href="{{ $listUrl }}" class="sp-btn-primary">
            {{ $listLabel }}
        </a>
    </header>

    @if($tripMode)
        {{-- Le choix du service reste en tête : une autre catégorie ramène
             au formulaire d'annonce --}}
        <section class="sp-form-section">
            <div class="sp-field">
                <label class="sp-label" for="category_id">Catégorie</label>
                @include('pages.locateur.partials.category-select')
            </div>
        </section>

        <div class="sp-trip">
            @include('livreur.covoiturage.partials.wizard')
        </div>

        {{-- Dans le contenu et non dans la pile « scripts » : voir l'en-tête du partiel --}}
        @include('livreur.covoiturage.partials.wizard-scripts')
    @else

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
                    <p data-mode-text data-prestation="Le titre et la catégorie permettent aux prestataires concernés de trouver votre demande.">Le titre et la catégorie déterminent où votre annonce sera visible.</p>
                </div>
            </div>

            <div class="sp-form-grid">
                <div class="sp-field">
                    <label class="sp-label" for="title"><span data-mode-text data-prestation="Votre besoin">Titre de l'annonce</span> <span class="sp-req">*</span></label>
                    <input type="text" name="title" id="title" class="sp-input"
                           placeholder="Ex. Mini-pelle 1,5 T avec godet" data-mode-placeholder
                           data-prestation="Ex. Monter une cuisine en kit de 3 mètres"
                           value="{{ old('title') }}" required>
                </div>

                <div class="sp-field">
                    <label class="sp-label" for="category_id">Catégorie <span class="sp-req">*</span></label>
                    @include('pages.locateur.partials.category-select')
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
                <span class="sp-help" data-mode-hide="location vente" hidden>Précisez le travail attendu : surface, quantités, matériel fourni, accès, contraintes… Plus votre demande est claire, plus les propositions seront justes.</span>
                <x-ai-description target="annonce" />
            </div>

            <div class="sp-field">
                <label class="sp-label" for="images">Photos</label>
                <input type="file" name="images[]" id="images" class="sp-input sp-file" accept="image/*" multiple>
                <span class="sp-help">Plusieurs photos possibles. La première servira de vignette.</span>
            </div>
        </section>

        {{-- 2. Disponibilites — masquee pour une categorie de vente ; pour une
             prestation, fenetre dans laquelle le travail doit etre realise --}}
        <section class="sp-form-section" id="section-disponibilites" data-mode-hide="vente prestation">
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
                           min="{{ now()->format('Y-m-d') }}" value="{{ old('available_from') }}" required>
                </div>

                <div class="sp-field">
                    <label class="sp-label" for="available_until"><span data-mode-text data-prestation="Au plus tard le">Disponible jusqu'au</span> <span class="sp-req">*</span></label>
                    <input type="date" name="available_until" id="available_until" class="sp-input"
                           min="{{ now()->format('Y-m-d') }}" value="{{ old('available_until') }}" required>
                </div>
            </div>
        </section>

        {{-- 2. Prix et stock --}}
        <section class="sp-form-section" data-mode-hide="location prestation">
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
                    <p data-mode-text data-prestation="Le lieu où la prestation sera réalisée. Sélectionnez une suggestion pour placer le repère.">Le point de retrait du bien. Sélectionnez une suggestion pour placer le repère.</p>
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
        <section class="sp-form-section" data-mode-hide="vente">
            <div class="sp-form-head">
                <span class="sp-step">4</span>
                <div>
                    <h2 data-mode-text data-prestation="Votre budget">Tarif et livraison</h2>
                    <p id="tarif-intro" data-mode-text
                       data-vente="Le prix affiché aux acheteurs, et la possibilité de faire livrer le bien."
                       data-prestation="Le montant que vous envisagez. Chaque prestataire vous répondra avec son propre prix.">Le prix affiché aux locataires, et la possibilité de faire livrer le bien.</p>
                </div>
            </div>

            {{-- Les deux encarts partagent la meme grille : meme largeur,
                 meme hauteur, meme traitement visuel. --}}
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
                               placeholder="0,00" step="0.01" min="0" value="{{ old('price_per_day') }}" required>
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
                        <input type="checkbox" name="delivery_active" id="livraisonActive" @checked(old('delivery_active'))>
                        <label for="livraisonActive" class="toggle-label"></label>
                    </div>
                </div>
            </div>
        </section>

        {{-- 4. Mise en ligne --}}
        <section class="sp-form-section" data-mode-hide="location prestation">
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
            <a href="{{ route('ads.index') }}" class="sp-act is-ghost">Annuler</a>
            <button type="submit" class="sp-btn-primary" data-mode-text data-prestation="Publier ma demande">Publier l'annonce</button>
        </div>

        <input type="hidden" name="distance_km" id="distanceKm">
        <input type="hidden" name="delivery_cost" id="deliveryCost">
    </form>
    @endif
</div>

{{-- Bascule location / vente / prestation, et passage au covoiturage :
     assets/js/ad-form.js. La meme regle est rejouee cote serveur dans
     AdController. --}}
<script src="{{ asset('assets/js/ad-form.js') }}?v={{ @filemtime(public_path('assets/js/ad-form.js')) ?: 1 }}"></script>

@endsection
