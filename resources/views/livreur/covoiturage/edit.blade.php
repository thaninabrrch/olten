@extends('layouts.connected')
@section('title', 'Modifier le trajet | ' . config('app.name'))

@php
    /*
     | Sommaire d'edition d'un trajet : chaque bloc mene a un ecran dedie.
     |
     | Trois valeurs etaient ecrites en dur dans cette page et affichaient
     | donc la meme chose pour tous les trajets : la reference « #TR-13 »,
     | le statut « En attente » et le prix « 92€ » de la carte Prix. Elles
     | sont desormais lues sur le trajet.
     */
    $statuts = [
        'actif'   => ['Actif',      'is-paid'],
        'validé'  => ['Validé',     'is-confirmed'],
        'pending' => ['En attente', 'is-pending'],
        'complet' => ['Complet',    'is-shipped'],
        'inactif' => ['Inactif',    'is-neutral'],
        'annulé'  => ['Annulé',     'is-cancelled'],
    ];

    [$stLabel, $stClass] = $statuts[$trajet->statut] ?? [ucfirst((string) $trajet->statut ?: 'Inconnu'), 'is-neutral'];

    // Prix affiche sur la carte Prix : le total du trajet, sinon le prix par place
    $prix = $trajet->prix_total_affiche ?: $trajet->prix_place;

    $blocs = [
        [
            'titre' => 'Itinéraire',
            'texte' => 'Points de départ et d\'arrivée, escales et horaires.',
            'icone' => 'fa-route',
            'ton'   => 'is-brand',
            'url'   => route('covoiturage.edititen.edit', $trajet->covoiturage_id),
            'valeur' => $trajet->depart && $trajet->destination
                ? $trajet->depart . ' → ' . $trajet->destination
                : null,
        ],
        [
            'titre' => 'Mode de réservation',
            'texte' => 'Réservation instantanée ou validation manuelle.',
            'icone' => 'fa-circle-check',
            'ton'   => 'is-green',
            'url'   => route('covoiturage.editMode', $trajet->covoiturage_id),
            'valeur' => $trajet->booking_mode ? ucfirst((string) $trajet->booking_mode) : null,
        ],
        [
            'titre' => 'Prix et paiement',
            'texte' => 'Tarif par place et prix de chaque segment.',
            'icone' => 'fa-euro-sign',
            'ton'   => 'is-blue',
            'url'   => route('covoiturage.prix.edit', $trajet->covoiturage_id),
            'valeur' => $prix ? number_format((float) $prix, 2, ',', ' ') . ' €' : null,
        ],
        [
            'titre' => 'Places et confort',
            'texte' => 'Nombre de passagers, bagages et prestations à bord.',
            'icone' => 'fa-users',
            'ton'   => 'is-red',
            'url'   => route('covoiturage.options.edit', $trajet->covoiturage_id),
            'valeur' => $trajet->nb_places ? $trajet->nb_places . ' place' . ($trajet->nb_places > 1 ? 's' : '') : null,
        ],
    ];

    $mois = [1 => 'janv', 'févr', 'mars', 'avr', 'mai', 'juin', 'juil', 'août', 'sept', 'oct', 'nov', 'déc'];
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('covoiturage.index') }}">Mes trajets</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Modifier</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Modifier le trajet</h1>
            <p class="sp-subtitle">
                {{ $trajet->depart }} → {{ $trajet->destination }}
                @if($trajet->date_depart)
                    · {{ $trajet->date_depart->format('d') }}
                    {{ $mois[(int) $trajet->date_depart->format('n')] }}
                    {{ $trajet->date_depart->format('Y') }}
                @endif
            </p>
        </div>

        <div class="sp-role-badges">
            <span class="sp-status {{ $stClass }}">{{ $stLabel }}</span>
            <span class="sp-ref">#{{ $trajet->covoiturage_id }}</span>
        </div>
    </header>

    {{-- Sections a editer --}}
    <p class="sp-section-title">Que souhaitez-vous modifier ?</p>

    <div class="sp-grid is-flat">
        @foreach($blocs as $bloc)
            <a href="{{ $bloc['url'] }}" class="sp-card sp-tile">
                <span class="sp-stat-icon {{ $bloc['ton'] }}">
                    <i class="fa-solid {{ $bloc['icone'] }}"></i>
                </span>

                <div class="sp-tile-body">
                    <div class="sp-tile-head">
                        <h3>{{ $bloc['titre'] }}</h3>

                        @if($bloc['valeur'])
                            <span class="sp-tile-value">{{ $bloc['valeur'] }}</span>
                        @endif
                    </div>

                    <p>{{ $bloc['texte'] }}</p>
                </div>

                <i class="fa-solid fa-chevron-right sp-tile-arrow"></i>
            </a>
        @endforeach
    </div>

    <div class="sp-panel-row">

    {{-- Trajet retour --}}
    <section class="sp-panel sp-help-card">
        <div>
            <h2>{{ $trajet->retour ? 'Trajet retour' : 'Aucun trajet retour' }}</h2>
            <p>
                {{ $trajet->retour
                    ? 'Un retour est associé à ce trajet : vous pouvez en modifier l\'itinéraire, les horaires et les tarifs.'
                    : 'Proposez le trajet inverse pour doubler vos chances de remplir votre véhicule.' }}
            </p>
        </div>

        <div class="sp-row-actions">
            <a href="{{ $trajet->retour
                        ? route('covoiturage.edit-retour', $trajet->covoiturage_id)
                        : route('covoiturage.add-retour', $trajet->covoiturage_id) }}"
               class="sp-act is-edit">
                {{ $trajet->retour ? 'Modifier le retour' : 'Ajouter un retour' }}
            </a>

            @if($trajet->retour)
                <form id="form-supprimer-retour"
                      action="{{ route('covoiturage.destroy-retour', $trajet->covoiturage_id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="sp-act is-cancel" onclick="supprimerRetour()">
                        Supprimer le retour
                    </button>
                </form>
            @endif
        </div>
    </section>

    {{-- Actions sur le trajet --}}
    <section class="sp-panel">
        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">Actions</h2>
                <span class="sp-count">Dupliquer ce trajet ou le retirer définitivement</span>
            </div>

            <div class="sp-toolbar-actions">
                <button type="button" class="sp-act is-ghost" onclick="dupliquerTrajet(event)">Dupliquer</button>

                <form id="form-supprimer-trajet"
                      action="{{ route('covoiturage.destroy', $trajet->covoiturage_id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="sp-act is-cancel" onclick="confirmerSuppression()">Supprimer</button>
                </form>
            </div>
        </div>
    </section>

    </div>
</div>

<script>
    // Duplication : l'appel et la route sont ceux d'origine
    function dupliquerTrajet(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Dupliquer ce trajet ?',
            text: 'Un trajet identique sera créé. Vous pourrez le modifier avant de le publier.',
            icon: 'question',
            iconColor: '#ff3c00',
            showCancelButton: true,
            confirmButtonText: 'Oui, dupliquer',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#ff3c00',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
        }).then(result => {
            if (!result.isConfirmed) return;

            fetch('{{ route('covoiturage.dupliquer', $trajet->covoiturage_id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) throw new Error('Duplication refusée');

                Swal.fire({
                    icon: 'success',
                    title: 'Trajet dupliqué',
                    text: 'La copie a bien été créée.',
                    showCancelButton: true,
                    confirmButtonText: 'Voir le trajet',
                    cancelButtonText: 'Rester ici',
                    confirmButtonColor: '#1a8245',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                }).then(r => {
                    if (r.isConfirmed) window.location.href = '/trajet/' + data.covoiturage_id;
                });
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'La duplication n\'a pas abouti. Réessayez.',
                    confirmButtonColor: '#ff3c00',
                });
            });
        });
    }

    function supprimerRetour() {
        Swal.fire({
            title: 'Supprimer le trajet retour ?',
            text: "Seul le retour sera retiré : l'aller et ses tarifs restent inchangés.",
            icon: 'warning',
            iconColor: '#c0392b',
            showCancelButton: true,
            confirmButtonText: 'Oui, supprimer le retour',
            cancelButtonText: 'Conserver',
            confirmButtonColor: '#c0392b',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
        }).then(result => {
            if (result.isConfirmed) document.getElementById('form-supprimer-retour').submit();
        });
    }

    function confirmerSuppression() {
        Swal.fire({
            title: 'Supprimer ce trajet ?',
            text: 'Cette action est définitive : le trajet et ses données seront perdus.',
            icon: 'warning',
            iconColor: '#c0392b',
            showCancelButton: true,
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Conserver',
            confirmButtonColor: '#c0392b',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
        }).then(result => {
            if (result.isConfirmed) document.getElementById('form-supprimer-trajet').submit();
        });
    }
</script>
@endsection
