@extends('layouts.connected')
@section('title', 'Carte VTC | ' . config('app.name'))

@php
    /*
     | Documents reglementaires du chauffeur VTC. Les pieces sont lues sur
     | la relation documents() de l'utilisateur : une ligne UserDocument par
     | type (identity_card, vtc_card), avec son statut et son identifiant.
     |
     | L'envoi passe par route('documents.upload') : un champ radio
     | « document_type » et un champ fichier « file », comme attendu par
     | CarteVtcController::store().
     */
    $user      = auth()->user();
    $vehicle   = $user->vehicle;
    $documents = $user->documents;

    $types = [
        ['name' => 'identity_card', 'label' => "Pièce d'identité", 'desc' => 'CNI ou passeport en cours de validité (recto/verso).', 'icon' => 'fa-id-card'],
        ['name' => 'vtc_card',      'label' => 'Carte professionnelle', 'desc' => 'Carte VTC délivrée par la préfecture.', 'icon' => 'fa-address-card'],
    ];

    $statuts = [
        'approved' => ['Validé',       'is-paid',      'fa-circle-check'],
        'rejected' => ['Refusé',       'is-cancelled', 'fa-circle-xmark'],
        'pending'  => ['Vérification', 'is-pending',   'fa-hourglass-half'],
    ];

    // Le dossier n'est complet que si les deux pieces sont validees
    $valides = collect($types)->filter(function ($t) use ($documents) {
        return optional($documents->firstWhere('name', $t['name']))->status === 'approved';
    })->count();

    $complet = $valides === count($types);
    $carteVtc = $documents->firstWhere('name', 'vtc_card');
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Carte VTC</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Carte VTC</h1>
            <p class="sp-subtitle">Vos documents réglementaires et le véhicule associé à votre compte.</p>
        </div>

        <button type="button" class="sp-btn-primary" onclick="openUpload()">
            Envoyer un document
        </button>
    </header>

    {{-- Etat du dossier --}}
    <div class="sp-balance">
        <div>
            <span class="sp-balance-label">État du dossier</span>
            <span class="sp-balance-value">{{ $valides }} / {{ count($types) }}</span>
            <span class="sp-balance-note">
                @if($complet)
                    Votre dossier est complet : vous pouvez recevoir des courses.
                @else
                    Transmettez les pièces manquantes pour activer votre activité de chauffeur.
                @endif
            </span>
        </div>

        @if($carteVtc && $carteVtc->identifier)
            <div class="sp-balance-side">
                <span>Identifiant de carte</span>
                <strong>{{ $carteVtc->identifier }}</strong>
            </div>
        @endif
    </div>

    {{-- Documents --}}
    <p class="sp-section-title">Mes documents</p>

    <div class="sp-grid is-flat">
        @foreach($types as $type)
            @php
                $doc = $documents->firstWhere('name', $type['name']);
                [$stLabel, $stClass, $stIcon] = $statuts[$doc->status ?? ''] ?? ['À transmettre', 'is-neutral', 'fa-circle-info'];
            @endphp

            <article class="sp-card sp-doc">
                <div class="sp-doc-head">
                    <span class="sp-doc-icon"><i class="fa-solid {{ $type['icon'] }}"></i></span>

                    <span class="sp-status {{ $stClass }}">
                        <i class="fa-solid {{ $stIcon }}"></i> {{ $stLabel }}
                    </span>
                </div>

                <h3 class="sp-doc-title">{{ $type['label'] }}</h3>
                <p class="sp-doc-desc">{{ $type['desc'] }}</p>

                @if($doc && $doc->identifier)
                    <div class="sp-doc-ref">
                        <span>Identifiant</span>
                        <strong>{{ $doc->identifier }}</strong>
                    </div>
                @endif

                @if($doc && $doc->status === 'rejected' && $doc->rejection_reason)
                    <div class="sp-alert is-compact">
                        <strong>Motif du refus</strong>
                        {{ $doc->rejection_reason }}
                    </div>
                @endif

                <div class="sp-actions">
                    @if($doc)
                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" rel="noopener"
                           class="sp-act is-ghost">Consulter</a>
                    @endif

                    <button type="button" class="sp-act is-edit" onclick="openUpload('{{ $type['name'] }}')">
                        {{ $doc ? 'Remplacer' : 'Transmettre' }}
                    </button>
                </div>
            </article>
        @endforeach
    </div>

    {{-- Vehicule --}}
    <p class="sp-section-title">Mon véhicule</p>

    <section class="sp-panel">
        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">
                    @if($vehicle)
                        {{ $vehicle->marque }} {{ $vehicle->modele }}
                    @else
                        Aucun véhicule enregistré
                    @endif
                </h2>
                <span class="sp-count">
                    {{ $vehicle ? 'Le véhicule utilisé pour vos courses' : 'Renseignez-le pour commencer à recevoir des courses' }}
                </span>
            </div>

            <div class="sp-toolbar-actions">
                <a href="{{ route('vehicle.edit') }}" class="sp-act is-edit">
                    {{ $vehicle ? 'Modifier' : 'Renseigner' }}
                </a>
            </div>
        </div>

        @if($vehicle)
            @if($vehicle->photo)
                <div class="sp-vehicle-photo">
                    <img src="{{ asset('storage/' . $vehicle->photo) }}"
                         alt="{{ trim($vehicle->marque . ' ' . $vehicle->modele) ?: 'Photo du véhicule' }}"
                         loading="lazy">
                </div>
            @endif

            <div class="sp-specs">
                <div>
                    <span>Immatriculation</span>
                    <strong>{{ $vehicle->immatriculation ?: '—' }}</strong>
                </div>
                <div>
                    <span>Année</span>
                    <strong>{{ $vehicle->annee ?: '—' }}</strong>
                </div>
                <div>
                    <span>Places</span>
                    <strong>{{ $vehicle->places ?: '—' }}</strong>
                </div>
                <div>
                    <span>Motorisation</span>
                    <strong>{{ $vehicle->type ?: '—' }}</strong>
                </div>
                <div>
                    <span>Couleur</span>
                    <strong>{{ $vehicle->couleur ?: '—' }}</strong>
                </div>
            </div>
        @else
            <div class="sp-empty">
                <x-empty-state
                    title="Aucun véhicule associé"
                    text="Renseignez votre véhicule pour pouvoir accepter des courses."
                    :action-url="route('vehicle.edit')"
                    action-label="Renseigner mon véhicule" />
            </div>
        @endif
    </section>

    {{-- Aide --}}
    <section class="sp-panel sp-help-card">
        <div>
            <h2>Un problème avec vos documents ?</h2>
            <p>Notre équipe vérifie chaque pièce sous 48 h ouvrées. En cas de refus, le motif est indiqué sur la carte concernée.</p>
        </div>

        <a href="{{ route('contact') }}" class="sp-act is-edit">Contacter le support</a>
    </section>
</div>

{{-- Envoi d'un document --}}
<div class="sp-modal" id="uploadModal" hidden>
    <div class="sp-modal-backdrop" onclick="closeUpload()"></div>

    <div class="sp-modal-box" role="dialog" aria-modal="true" aria-labelledby="uploadTitle">
        <div class="sp-modal-head">
            <div>
                <span class="sp-modal-kicker">Document réglementaire</span>
                <h2 class="sp-modal-title" id="uploadTitle">Envoyer un document</h2>
            </div>

            <button type="button" class="sp-act is-ghost" onclick="closeUpload()">Fermer</button>
        </div>

        <form action="{{ route('documents.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf

            <div class="sp-modal-body">
                <div class="sp-field">
                    <span class="sp-label">Type de document <span class="sp-req">*</span></span>

                    <div class="sp-choices">
                        @foreach($types as $type)
                            <label class="sp-choice">
                                <input type="radio" name="document_type" value="{{ $type['name'] }}" required>
                                <span>
                                    <i class="fa-solid {{ $type['icon'] }}"></i>
                                    {{ $type['label'] }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="sp-field">
                    <label class="sp-label" for="file">Fichier <span class="sp-req">*</span></label>
                    <input type="file" name="file" id="file" class="sp-input sp-file"
                           accept=".pdf,.jpg,.jpeg,.png" required>
                    <span class="sp-help">PDF, JPEG ou PNG, 5 Mo maximum.</span>
                </div>
            </div>

            <div class="sp-modal-foot">
                <button type="button" class="sp-act is-ghost" onclick="closeUpload()">Annuler</button>
                <button type="submit" class="sp-act is-success">Envoyer le document</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Ouvre la fenetre, en pre-selectionnant le type quand on part d'une carte
    function openUpload(type) {
        const modal = document.getElementById('uploadModal');

        if (type) {
            const radio = modal.querySelector('input[name="document_type"][value="' + type + '"]');
            if (radio) radio.checked = true;
        }

        modal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeUpload() {
        document.getElementById('uploadModal').hidden = true;
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeUpload();
    });
</script>
@endsection
