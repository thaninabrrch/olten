@php
    /*
     | Encart « propositions » d'une demande de prestation, dans la colonne
     | droite de la fiche (annonces_details), a la place de la reservation.
     |
     |   le client, auteur de la demande : nombre de propositions recues et
     |       acces a la page de comparaison ;
     |   un prestataire qui a deja repondu : sa proposition et son statut, a
     |       modifier ou retirer tant que le client n'a pas choisi ;
     |   tous les autres, visiteurs compris : le formulaire de proposition
     |       (un visiteur voit la popin de connexion a l'envoi).
     |
     | Variables de la fiche : $ad, $isOwner, $expired, et $prestation
     | (AdController::show). Le layout public n'affiche pas les messages
     | flash : l'encart affiche les siens.
     */
    $count    = $prestation['count'];
    $accepted = $prestation['accepted'];
    $mine     = $prestation['mine'];
    $active   = $mine && $mine->status !== \App\Models\ServiceProposal::WITHDRAWN;
    $open     = ! $expired && $ad->is_approved && ! $accepted;
    $euros    = fn ($amount) => number_format((float) $amount, 2, ',', ' ') . ' €';
@endphp

<div class="pp-box" id="proposition">

    @if (session('success'))
        <div class="pp-flash is-ok" role="status">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="pp-flash is-error" role="alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($isOwner)
        {{-- Le client --}}
        <div class="pp-owner">
            <span class="pp-owner-count">
                <strong>{{ $count }}</strong>
                proposition{{ $count > 1 ? 's' : '' }} reçue{{ $count > 1 ? 's' : '' }}
            </span>

            @if ($accepted)
                <p>Vous avez choisi <strong>{{ $accepted->provider?->public_name }}</strong> pour {{ $euros($accepted->price) }}.</p>
            @elseif ($count)
                <p>Comparez les prix, les disponibilités et les profils, puis choisissez votre prestataire.</p>
            @else
                <p>Les prestataires qui consultent votre demande peuvent vous faire une proposition : vous serez prévenu dans la cloche.</p>
            @endif
        </div>

        @if ($count)
            <a href="{{ route('ads.proposals', $ad) }}" class="dt-btn">
                <i class="fa-solid fa-scale-balanced"></i>
                {{ $accepted ? 'Voir les propositions' : 'Comparer les propositions' }}
            </a>
        @endif

    @elseif ($active)
        {{-- Un prestataire qui a deja repondu --}}
        @php [$label, $class, $icon] = $mine->statusMeta(); @endphp

        <div class="pp-mine">
            <div class="pp-mine-head">
                <span class="pp-mine-title">Votre proposition</span>
                <span class="pp-badge {{ $class }}"><i class="fa-solid {{ $icon }}"></i> {{ $label }}</span>
            </div>

            <strong class="pp-mine-price">{{ $euros($mine->price) }}</strong>

            <span class="pp-mine-meta">
                <i class="fa-regular fa-calendar"></i>
                {{ $mine->available_on ? 'Disponible dès le ' . $mine->available_on->translatedFormat('d M Y') : 'Disponibilité à convenir' }}
                · envoyée le {{ $mine->created_at->translatedFormat('d M') }}
            </span>

            <p class="pp-mine-message">{{ \Illuminate\Support\Str::limit($mine->message, 180) }}</p>
        </div>

        @if ($mine->isAccepted())
            <div class="pp-flash is-ok">
                <i class="fa-solid fa-handshake"></i>
                <span>Le client a retenu votre proposition. Écrivez-lui pour convenir de la date, du lieu et des détails de la prestation.</span>
            </div>

            {{-- Popin « Message » de la fiche (<x-contact-owner>), adressee au client --}}
            <button type="button" class="dt-btn" data-cm-open>
                <i class="fa-regular fa-comments"></i> Écrire au client
            </button>
        @elseif ($mine->status === \App\Models\ServiceProposal::DECLINED)
            <p class="pp-note">
                <i class="fa-solid fa-circle-info"></i>
                Le client a choisi un autre prestataire pour cette demande.
            </p>
        @elseif ($open)
            {{-- Rouvert d'office quand la modification revient en erreur --}}
            <details class="pp-edit" @if ($errors->any()) open @endif>
                <summary class="pp-edit-toggle"><i class="fa-solid fa-pen"></i> Modifier ma proposition</summary>

                <form method="POST" action="{{ route('proposals.update', $mine) }}" class="pp-form">
                    @csrf
                    @method('PUT')
                    @include('proposals.errors')
                    @include('proposals.fields', ['proposal' => $mine])

                    <button type="submit" class="dt-btn">Enregistrer les modifications</button>
                </form>
            </details>

            <form method="POST" action="{{ route('proposals.withdraw', $mine) }}" class="pp-withdraw"
                  onsubmit="return confirm('Retirer votre proposition ? Le client ne la verra plus.')">
                @csrf
                <button type="submit" class="pp-link">Retirer ma proposition</button>
            </form>
        @endif

    @elseif (! $open)
        {{-- Demande fermee. L'expiration a deja sa note en haut de l'encart. --}}
        @if ($accepted)
            <p class="pp-note">
                <i class="fa-solid fa-lock"></i>
                Le client a déjà choisi son prestataire : cette demande n'accepte plus de propositions.
            </p>
        @elseif (! $ad->is_approved && ! $expired)
            <p class="pp-note">
                <i class="fa-regular fa-hourglass-half"></i>
                Cette demande est en cours de validation : vous pourrez y répondre dès sa mise en ligne.
            </p>
        @endif

    @else
        {{-- Formulaire de proposition. `data-auth-required` : un visiteur non
             connecte voit la popin de connexion (assets/js/auth.js), puis
             revient sur cet encart. --}}
        <form method="POST" action="{{ route('proposals.store', $ad) }}" class="pp-form"
              data-auth-required data-auth-redirect="{{ url()->current() }}#proposition">
            @csrf

            <div class="pp-form-head">
                <strong>Proposer mes services</strong>
                <small>Le client compare les propositions reçues et choisit son prestataire. Vous êtes prévenu de sa réponse.</small>
            </div>

            @include('proposals.errors')
            @include('proposals.fields', ['proposal' => null])

            <button type="submit" class="dt-btn">
                <i class="fa-regular fa-paper-plane"></i> Envoyer ma proposition
            </button>
        </form>
    @endif
</div>
