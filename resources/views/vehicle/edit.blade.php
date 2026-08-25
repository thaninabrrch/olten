@extends('layouts.connected')
@section('title', 'Mon véhicule | ' . config('app.name'))

@php
    /*
     | Formulaire unique : la meme route cree ou met a jour le vehicule
     | (updateOrCreate cote controleur), d'ou l'absence de @method.
     |
     | La motorisation est validee en in:thermique,hybride,electrique :
     | les trois valeurs ci-dessous doivent rester identiques.
     */
    $motorisations = [
        ['id' => 'thermique',   'label' => 'Thermique',  'icon' => 'fa-gas-pump'],
        ['id' => 'hybride',     'label' => 'Hybride',    'icon' => 'fa-leaf'],
        ['id' => 'electrique',  'label' => 'Électrique', 'icon' => 'fa-bolt'],
    ];

    $photo = $vehicle?->photo ? asset('storage/' . $vehicle->photo) : null;
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('livreur.carte.vtc') }}">Carte VTC</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Mon véhicule</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Mon véhicule</h1>
            <p class="sp-subtitle">
                {{ $vehicle
                    ? 'Le véhicule utilisé pour vos courses et vos trajets.'
                    : 'Renseignez votre véhicule pour pouvoir accepter des courses.' }}
            </p>
        </div>

        <a href="{{ route('livreur.carte.vtc') }}" class="sp-btn-primary">
            Ma carte VTC
        </a>
    </header>

    <form action="{{ route('vehicle.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        @if ($errors->any())
            <div class="sp-alert">
                <strong>Le véhicule n'a pas pu être enregistré.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 1. Identification --}}
        <section class="sp-form-section">
            <div class="sp-form-head">
                <span class="sp-step">1</span>
                <div>
                    <h2>Identification</h2>
                    <p>Ces informations sont visibles par vos passagers avant la course.</p>
                </div>
            </div>

            <div class="sp-form-grid">
                <div class="sp-field">
                    <label class="sp-label" for="marque">Marque <span class="sp-req">*</span></label>
                    <input type="text" name="marque" id="marque" class="sp-input"
                           placeholder="Ex. Mercedes-Benz"
                           value="{{ old('marque', $vehicle->marque ?? '') }}" required>
                </div>

                <div class="sp-field">
                    <label class="sp-label" for="modele">Modèle <span class="sp-req">*</span></label>
                    <input type="text" name="modele" id="modele" class="sp-input"
                           placeholder="Ex. Classe E"
                           value="{{ old('modele', $vehicle->modele ?? '') }}" required>
                </div>
            </div>

            <div class="sp-field">
                <label class="sp-label" for="immatriculation">Immatriculation <span class="sp-req">*</span></label>
                <input type="text" name="immatriculation" id="immatriculation" class="sp-input sp-plate"
                       placeholder="AA-123-BB"
                       value="{{ old('immatriculation', $vehicle->immatriculation ?? '') }}" required>
                <span class="sp-help">Telle qu'elle figure sur la carte grise.</span>
            </div>
        </section>

        {{-- 2. Caracteristiques --}}
        <section class="sp-form-section">
            <div class="sp-form-head">
                <span class="sp-step">2</span>
                <div>
                    <h2>Caractéristiques</h2>
                    <p>Le nombre de places conditionne les courses qui vous sont proposées.</p>
                </div>
            </div>

            <div class="sp-form-grid">
                <div class="sp-field">
                    <label class="sp-label" for="annee">Année</label>
                    <input type="number" name="annee" id="annee" class="sp-input"
                           min="1950" max="{{ now()->year + 1 }}" placeholder="{{ now()->year }}"
                           value="{{ old('annee', $vehicle->annee ?? '') }}">
                </div>

                <div class="sp-field">
                    <label class="sp-label" for="couleur">Couleur</label>
                    <input type="text" name="couleur" id="couleur" class="sp-input"
                           placeholder="Ex. Noir"
                           value="{{ old('couleur', $vehicle->couleur ?? '') }}">
                </div>
            </div>

            <div class="sp-field">
                <label class="sp-label" for="places">Nombre de places passagers <span class="sp-req">*</span></label>
                <input type="number" name="places" id="places" class="sp-input"
                       min="1" max="9" placeholder="4"
                       value="{{ old('places', $vehicle->places ?? '') }}" required>
                <span class="sp-help">Sans compter le conducteur, de 1 à 9.</span>
            </div>

            <div class="sp-field">
                <span class="sp-label">Motorisation <span class="sp-req">*</span></span>

                <div class="sp-choices">
                    @foreach($motorisations as $moteur)
                        <label class="sp-choice">
                            <input type="radio" name="type" value="{{ $moteur['id'] }}"
                                   @checked(old('type', $vehicle->type ?? '') === $moteur['id']) required>
                            <span>
                                <i class="fa-solid {{ $moteur['icon'] }}"></i>
                                {{ $moteur['label'] }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- 3. Photo --}}
        <section class="sp-form-section">
            <div class="sp-form-head">
                <span class="sp-step">3</span>
                <div>
                    <h2>Photo</h2>
                    <p>Une photo nette du véhicule rassure les passagers au moment de monter.</p>
                </div>
            </div>

            <div class="sp-photo">
                <div class="sp-photo-preview">
                    <img id="vehiclePreview" src="{{ $photo }}" alt="Photo du véhicule"
                         @if(! $photo) hidden @endif>

                    <span class="sp-photo-empty" id="vehicleEmpty" @if($photo) hidden @endif>
                        Aucune photo
                    </span>
                </div>

                <div class="sp-photo-side">
                    <label class="sp-label" for="photo">Fichier</label>
                    <input type="file" name="photo" id="photo" class="sp-input sp-file" accept="image/*">
                    <span class="sp-help">JPEG ou PNG, 2 Mo maximum. Format paysage de préférence.</span>
                </div>
            </div>
        </section>

        {{-- Barre d'action --}}
        <div class="sp-form-actions">
            <a href="{{ route('livreur.carte.vtc') }}" class="sp-act is-ghost">Annuler</a>
            <button type="submit" class="sp-btn-primary">
                {{ $vehicle ? 'Enregistrer les modifications' : 'Enregistrer le véhicule' }}
            </button>
        </div>
    </form>
</div>

<script>
    // Apercu immediat de la photo choisie
    document.getElementById('photo').addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;

        const reader = new FileReader();

        reader.onload = function (e) {
            const img = document.getElementById('vehiclePreview');
            img.src = e.target.result;
            img.hidden = false;
            document.getElementById('vehicleEmpty').hidden = true;
        };

        reader.readAsDataURL(file);
    });
</script>
@endsection
