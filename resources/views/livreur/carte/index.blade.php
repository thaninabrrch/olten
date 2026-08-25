@extends('layouts.connected')
@section('title', 'Documents requis | ' . config('app.name'))

@php
    /*
     | Documents reglementaires du chauffeur VTC. Les pieces sont lues sur
     | la relation documents() de l'utilisateur : une ligne UserDocument par
     | type, avec son statut et son identifiant.
     |
     | La liste des types vient de UserDocument::TYPES : elle etait recopiee
     | ici, dans le controleur, dans le back-office et dans le middleware, ce
     | qui obligeait a penser aux quatre pour ajouter une piece.
     |
     | L'envoi passe par route('documents.upload') : un champ radio
     | « document_type » et un champ fichier « file », comme attendu par
     | CarteVtcController::store().
     */
    use App\Models\UserDocument;

    $user      = auth()->user();
    $vehicle   = $user->vehicle;
    $documents = $user->documents;

    /* Seules les pieces demandees au profil courant : un livreur qui n'est pas
       chauffeur VTC n'a pas a transmettre de carte professionnelle. */
    $types = collect(UserDocument::typesFor($user))
        ->map(fn ($name) => UserDocument::TYPES[$name] + ['name' => $name])
        ->values()
        ->all();

    /* Ce que chaque piece verrouille pour cet utilisateur :
       ['driver_license' => ['accepter des livraisons', 'publier un trajet']] */
    $portes = UserDocument::gatesFor($user);

    $statuts = [
        'approved' => ['Validé',       'is-paid',      'fa-circle-check'],
        'rejected' => ['Refusé',       'is-cancelled', 'fa-circle-xmark'],
        'pending'  => ['Vérification', 'is-pending',   'fa-hourglass-half'],
    ];

    // Le dossier n'est complet que si toutes les pieces sont validees
    $valides = collect($types)->filter(function ($t) use ($documents) {
        return optional($documents->firstWhere('name', $t['name']))->status === 'approved';
    })->count();

    $complet  = $valides === count($types);
    $carteVtc = $documents->firstWhere('name', 'vtc_card');

    /* Pieces qui verrouillent encore une activite : elles sont signalees comme
       telles pour que l'on sache ce qui bloque, sans avoir a se heurter au
       refus pour le decouvrir. */
    $bloquantes = collect(array_keys($portes))
        ->reject(fn ($name) => optional($documents->firstWhere('name', $name))->status === 'approved')
        ->values();

    /* Activites encore fermees, dedoublonnees : « accepter des livraisons »,
       « publier un trajet »… */
    $activitesBloquees = $bloquantes
        ->flatMap(fn ($name) => $portes[$name])
        ->unique()
        ->values();
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Documents requis</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Documents requis</h1>
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
                    Votre dossier est complet : plus rien ne limite votre activité.
                @else
                    Transmettez les pièces manquantes pour activer votre activité.
                @endif

                @if($bloquantes->isNotEmpty())
                    <br>
                    {{ $activitesBloquees->map(fn ($a) => ucfirst($a))->implode(' et ') }}
                    {{ $activitesBloquees->count() > 1 ? 'restent bloqués' : 'reste bloqué' }} tant que
                    {{ $bloquantes->count() > 1 ? 'ces pièces ne sont pas validées' : 'cette pièce n\'est pas validée' }} :
                    {{ $bloquantes->map(fn ($n) => UserDocument::label($n))->implode(', ') }}.
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

                {{-- On lit ici ce que la piece conditionne, au lieu de le
                     decouvrir en se heurtant au refus. Une meme piece peut
                     ouvrir deux activites : le permis vaut pour les livraisons
                     comme pour les trajets. --}}
                @if(! empty($portes[$type['name']]))
                    <p class="sp-doc-req">
                        <i class="fa-solid fa-route"></i>
                        Requis pour {{ collect($portes[$type['name']])->implode(' et ') }}
                    </p>
                @endif

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
                    <span class="sp-label">Fichier <span class="sp-req">*</span></span>

                    {{-- Le cadre entier declenche le selecteur : c'est un
                         <label>, et le champ natif reste dessous, masque mais
                         dans le flux — l'en sortir casserait « required », que
                         le navigateur ne sait pas signaler sur un champ cache. --}}
                    <label class="sp-drop" for="file" id="fileDrop">
                        <input type="file" name="file" id="file"
                               accept=".pdf,.jpg,.jpeg,.png" required>

                        <span class="sp-drop-icon">
                            <i class="fa-solid fa-cloud-arrow-up" id="fileIcon"></i>
                        </span>

                        <span class="sp-drop-text">
                            <strong class="sp-drop-title" id="fileTitle">Choisir un fichier</strong>
                            <span class="sp-drop-note" id="fileNote">ou glissez-le ici — PDF, JPEG ou PNG, 5 Mo maximum</span>
                        </span>

                        <span class="sp-drop-btn" id="fileBtn">Parcourir</span>

                        <button type="button" class="sp-drop-clear" id="fileClear"
                                aria-label="Retirer le fichier">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </label>

                    <p class="sp-drop-error" id="fileError" hidden>
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span></span>
                    </p>
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

        /* Champ vide a chaque ouverture : une piece restee du precedent
           envoi partirait sinon sous le type que l'on vient de choisir. */
        if (window.resetFileField) window.resetFileField();

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

    /* ---------- Champ fichier ----------
       Le cadre dit ce qu'il tient : tant qu'aucune piece n'est choisie il
       annonce les formats acceptes, ensuite il affiche le nom et le poids du
       fichier. Le poids et l'extension sont verifies ici, avant l'envoi : le
       serveur les refuse deja (max:5120, mimes:pdf,jpg,jpeg,png), mais il
       fallait attendre l'aller-retour pour l'apprendre. */
    (function initFileField() {
        const MAX_BYTES = 5 * 1024 * 1024;
        const EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];

        const ICONS = {
            pdf:  'fa-file-pdf',
            jpg:  'fa-file-image',
            jpeg: 'fa-file-image',
            png:  'fa-file-image',
        };

        const drop  = document.getElementById('fileDrop');
        const input = document.getElementById('file');
        const icon  = document.getElementById('fileIcon');
        const title = document.getElementById('fileTitle');
        const note  = document.getElementById('fileNote');
        const btn   = document.getElementById('fileBtn');
        const clear = document.getElementById('fileClear');
        const error = document.getElementById('fileError');
        const modal = document.getElementById('uploadModal');

        if (!drop || !input) return;

        function poids(octets) {
            if (octets < 1024) return octets + ' o';
            if (octets < 1024 * 1024) return Math.round(octets / 1024) + ' Ko';
            return (octets / 1024 / 1024).toFixed(1).replace('.', ',') + ' Mo';
        }

        function refuser(message) {
            input.value = '';
            error.querySelector('span').textContent = message;
            error.hidden = false;
            vider();
        }

        function vider() {
            drop.classList.remove('is-filled');
            icon.className = 'fa-solid fa-cloud-arrow-up';
            title.textContent = 'Choisir un fichier';
            note.textContent = 'ou glissez-le ici — PDF, JPEG ou PNG, 5 Mo maximum';
            btn.textContent = 'Parcourir';
        }

        function afficher() {
            error.hidden = true;

            const fichier = input.files && input.files[0];
            if (!fichier) return vider();

            const ext = fichier.name.split('.').pop().toLowerCase();

            if (EXTENSIONS.indexOf(ext) === -1) {
                return refuser('Format refusé : seuls les PDF, JPEG et PNG sont acceptés.');
            }

            if (fichier.size > MAX_BYTES) {
                return refuser('Fichier trop lourd (' + poids(fichier.size) + ') : 5 Mo maximum.');
            }

            drop.classList.add('is-filled');
            icon.className = 'fa-solid ' + (ICONS[ext] || 'fa-file-lines');
            title.textContent = fichier.name;
            note.textContent = ext.toUpperCase() + ' — ' + poids(fichier.size);
            btn.textContent = 'Changer';
        }

        input.addEventListener('change', afficher);

        // Le bouton vit dans le <label> : sans cela, le retrait rouvrirait
        // aussitot le selecteur de fichier.
        clear.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            input.value = '';
            error.hidden = true;
            vider();
        });

        ['dragenter', 'dragover'].forEach(function (type) {
            drop.addEventListener(type, function (e) {
                e.preventDefault();
                drop.classList.add('is-over');
            });
        });

        ['dragleave', 'dragend', 'drop'].forEach(function (type) {
            drop.addEventListener(type, function () {
                drop.classList.remove('is-over');
            });
        });

        drop.addEventListener('drop', function (e) {
            e.preventDefault();

            const fichier = e.dataTransfer.files && e.dataTransfer.files[0];
            if (!fichier) return;

            // Une seule piece par envoi : on ne garde que le premier fichier
            const transfert = new DataTransfer();
            transfert.items.add(fichier);
            input.files = transfert.files;

            afficher();
        });

        // Un fichier lache a cote de la zone remplacerait la page par le
        // fichier lui-meme, et le formulaire serait perdu.
        ['dragover', 'drop'].forEach(function (type) {
            modal.addEventListener(type, function (e) {
                if (!drop.contains(e.target)) e.preventDefault();
            });
        });

        // La fenetre se rouvre sur un champ propre
        window.resetFileField = function () {
            input.value = '';
            error.hidden = true;
            vider();
        };
    })();
</script>
@endsection
