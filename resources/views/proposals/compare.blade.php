@extends('layouts.connected')
@section('title', 'Propositions reçues - Olten')

@php
    /*
     | Comparaison des propositions recues sur une demande de prestation
     | (ServiceProposalController::compare). Le client y voit chaque
     | prestataire, son prix situe entre le moins cher et le plus cher, sa
     | disponibilite et son message, puis retient l'un d'eux.
     |
     | Une fois le choix fait, la proposition retenue passe en tete et les
     | autres sont marquees « non retenues » : la page devient l'historique.
     */
    $euros  = fn ($amount) => number_format((float) $amount, 2, ',', ' ') . ' €';
    $budget = (float) $ad->price_per_day;
    $cover  = $ad->images->first() ? asset('storage/' . $ad->images->first()->path) : null;
    $spread = ($stats['max'] ?? 0) - ($stats['min'] ?? 0);

    $sorts = [
        'price'        => ['Prix croissant', 'fa-arrow-up-1-9'],
        'recent'       => ['Plus récentes', 'fa-clock'],
        'availability' => ['Disponibles au plus tôt', 'fa-calendar-day'],
    ];

    // Etat de la demande, dans l'ordre ou il compte pour le client
    [$stateLabel, $stateIcon] = match (true) {
        (bool) $accepted     => ['Prestataire choisi', 'fa-handshake'],
        $ad->isExpired()     => ['Expirée', 'fa-calendar-xmark'],
        ! $ad->is_approved   => ['En validation', 'fa-hourglass-half'],
        default              => ['En ligne', 'fa-circle-check'],
    };
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/proposals.css') }}?v={{ @filemtime(public_path('assets/css/proposals.css')) ?: 1 }}">
@endpush

@section('content')
<div class="sp-page pp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('proposals.index', ['onglet' => 'recues']) }}">Propositions reçues</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">{{ \Illuminate\Support\Str::limit($ad->title, 50) }}</span>
    </nav>

    {{-- En-tete : la demande --}}
    <header class="sp-head">
        <div>
            <span class="pp-eyebrow">
                <i class="fa-solid fa-tag"></i> {{ $ad->category->nom ?? 'Prestation' }}
                <span class="pp-state"><i class="fa-solid {{ $stateIcon }}"></i> {{ $stateLabel }}</span>
            </span>
            <h1 class="sp-title">{{ $ad->title }}</h1>
            <p class="sp-subtitle pp-request-meta">
                <span><i class="fa-solid fa-wallet"></i> Budget {{ $euros($budget) }}</span>
                @if ($ad->available_from && $ad->available_until)
                    <span>
                        <i class="fa-regular fa-calendar"></i>
                        Du {{ $ad->available_from->translatedFormat('d M') }} au {{ $ad->available_until->translatedFormat('d M Y') }}
                    </span>
                @endif
                @if ($ad->address)
                    <span><i class="fa-solid fa-location-dot"></i> {{ \Illuminate\Support\Str::limit($ad->address, 40) }}</span>
                @endif
            </p>
        </div>

        <div class="sp-head-actions">
            <a href="{{ route('ads.show', $ad) }}" class="sp-act">Voir la demande</a>
            @unless ($accepted)
                <a href="{{ route('ads.edit', $ad) }}" class="sp-act is-edit">Modifier</a>
            @endunless
        </div>
    </header>

    @if ($stats['count'])
        {{-- Les chiffres de la comparaison --}}
        <div class="sp-stats">
            <div class="sp-stat">
                <span class="sp-stat-icon is-brand"><i class="fa-solid fa-hand-holding-dollar"></i></span>
                <span>
                    <span class="sp-stat-value">{{ $stats['count'] }}</span>
                    <span class="sp-stat-label">Proposition{{ $stats['count'] > 1 ? 's' : '' }} reçue{{ $stats['count'] > 1 ? 's' : '' }}</span>
                </span>
            </div>

            <div class="sp-stat">
                <span class="sp-stat-icon is-green"><i class="fa-solid fa-arrow-down-wide-short"></i></span>
                <span>
                    <span class="sp-stat-value">{{ $euros($stats['min']) }}</span>
                    <span class="sp-stat-label">Prix le plus bas</span>
                </span>
            </div>

            <div class="sp-stat">
                <span class="sp-stat-icon is-blue"><i class="fa-solid fa-scale-balanced"></i></span>
                <span>
                    <span class="sp-stat-value">{{ $euros($stats['avg']) }}</span>
                    <span class="sp-stat-label">Prix moyen</span>
                </span>
            </div>

            <div class="sp-stat">
                <span class="sp-stat-icon {{ $stats['min'] <= $budget ? 'is-green' : 'is-red' }}"><i class="fa-solid fa-wallet"></i></span>
                <span>
                    <span class="sp-stat-value">{{ $euros($budget) }}</span>
                    <span class="sp-stat-label">
                        Votre budget
                        <small>
                            @php $within = $proposals->filter(fn ($p) => (float) $p->price <= $budget)->count(); @endphp
                            {{ $within }} proposition{{ $within > 1 ? 's' : '' }} dans le budget
                        </small>
                    </span>
                </span>
            </div>
        </div>
    @endif

    {{-- Choix fait : la suite se passe avec le prestataire --}}
    @if ($accepted)
        <div class="pp-chosen">
            <span class="pp-chosen-icon"><i class="fa-solid fa-handshake"></i></span>
            <div class="pp-chosen-text">
                <strong>Vous avez choisi {{ $accepted->provider?->public_name }} pour {{ $euros($accepted->price) }}</strong>
                <p>Écrivez-lui pour convenir ensemble de la date, du lieu et des détails de la prestation. Les autres prestataires ont été prévenus.</p>
            </div>
            <button type="button" class="sp-btn-primary" data-cm-open="cm-proposal-{{ $accepted->id }}">
                <i class="fa-regular fa-comments"></i> Écrire au prestataire
            </button>
        </div>
    @endif

    <section class="sp-panel">
        <div class="sp-tabs">
            @foreach ($sorts as $value => [$label, $icon])
                <a href="{{ route('ads.proposals', ['ad' => $ad, 'tri' => $value]) }}"
                   class="sp-tab {{ $sort === $value ? 'is-active' : '' }}">
                    <i class="fa-solid {{ $icon }}"></i> {{ $label }}
                </a>
            @endforeach

            @if ($stats['count'] && ! $accepted)
                <span class="sp-tabs-note">Choisissez un prestataire : les autres seront prévenus que leur proposition n'est pas retenue.</span>
            @endif
        </div>

        @if ($proposals->isNotEmpty())
            <ul class="pp-list">
                @foreach ($proposals as $proposal)
                    @php
                        $provider = $proposal->provider;
                        $price    = (float) $proposal->price;
                        [$label, $class, $icon] = $proposal->statusMeta();
                        // Position du prix sur l'echelle des propositions (0 = le moins cher)
                        $position = $spread > 0 ? round(($price - $stats['min']) / $spread * 100) : 0;
                        $isBest   = $stats['count'] > 1 && $price == $stats['min'];
                        $gap      = $price - $budget;
                        $count    = (int) ($hired[$proposal->user_id] ?? 0);
                    @endphp

                    <li class="pp-card {{ $proposal->isAccepted() ? 'is-chosen' : '' }} {{ $proposal->status === \App\Models\ServiceProposal::DECLINED ? 'is-out' : '' }}"
                        id="proposition-{{ $proposal->id }}">

                        {{-- Le prestataire --}}
                        <div class="pp-provider">
                            @if ($provider?->avatar_url)
                                <img src="{{ $provider->avatar_url }}" alt="" class="pp-avatar">
                            @else
                                <span class="pp-avatar is-initials">{{ $provider?->initials ?? '?' }}</span>
                            @endif

                            <span class="pp-provider-info">
                                <strong>{{ $provider?->public_name ?? 'Membre supprimé' }}</strong>
                                @if ($provider?->created_at)
                                    <small>Membre depuis {{ $provider->created_at->translatedFormat('F Y') }}</small>
                                @endif
                                <span class="pp-provider-tags">
                                    @if ($provider?->is_approved)
                                        <span class="pp-tag is-ok"><i class="fa-solid fa-circle-check"></i> Profil vérifié</span>
                                    @endif
                                    <span class="pp-tag">
                                        <i class="fa-solid fa-medal"></i>
                                        {{ $count ? 'Retenu ' . $count . ' fois' : 'Nouveau prestataire' }}
                                    </span>
                                </span>
                            </span>
                        </div>

                        {{-- Sa proposition --}}
                        <div class="pp-offer">
                            <div class="pp-offer-meta">
                                <span class="pp-badge {{ $class }}"><i class="fa-solid {{ $icon }}"></i> {{ $label }}</span>
                                <span>
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ $proposal->available_on ? 'Disponible dès le ' . $proposal->available_on->translatedFormat('l d M') : 'Disponibilité à convenir' }}
                                </span>
                                <span><i class="fa-regular fa-clock"></i> Reçue {{ $proposal->created_at->diffForHumans() }}</span>
                            </div>

                            <p class="pp-offer-message">{!! nl2br(e($proposal->message)) !!}</p>
                        </div>

                        {{-- Son prix, situe parmi les autres --}}
                        <div class="pp-price">
                            <strong>{{ $euros($price) }}</strong>

                            <span class="pp-price-flags">
                                @if ($isBest)
                                    <span class="pp-tag is-best"><i class="fa-solid fa-trophy"></i> Meilleur prix</span>
                                @endif
                                @if ($gap <= 0)
                                    <span class="pp-tag is-ok">Dans votre budget</span>
                                @else
                                    <span class="pp-tag is-warn">+{{ $euros($gap) }} / budget</span>
                                @endif
                            </span>

                            @if ($stats['count'] > 1)
                                <span class="pp-scale" title="Entre {{ $euros($stats['min']) }} et {{ $euros($stats['max']) }}">
                                    <span class="pp-scale-dot" style="left: {{ $position }}%"></span>
                                </span>
                                <span class="pp-scale-legend"><span>{{ $euros($stats['min']) }}</span><span>{{ $euros($stats['max']) }}</span></span>
                            @endif

                            <div class="pp-actions">
                                @if ($proposal->isPending() && ! $accepted)
                                    <form method="POST" action="{{ route('proposals.select', $proposal) }}" data-pp-select
                                          data-name="{{ $provider?->public_name }}" data-price="{{ $euros($price) }}">
                                        @csrf
                                        <button type="submit" class="sp-act is-edit">
                                            <i class="fa-solid fa-check"></i> Choisir
                                        </button>
                                    </form>
                                @endif

                                @if ($provider)
                                    <button type="button" class="sp-act" data-cm-open="cm-proposal-{{ $proposal->id }}">
                                        <i class="fa-regular fa-comment"></i> Message
                                    </button>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <x-empty-state
                title="Aucune proposition pour l'instant"
                :text="$ad->is_approved
                    ? 'Les prestataires qui consultent votre demande peuvent vous faire une proposition : vous serez prévenu dans la cloche.'
                    : 'Votre demande est en cours de validation. Dès sa mise en ligne, les prestataires pourront vous faire leurs propositions.'"
                :action-url="route('ads.show', $ad)"
                action-label="Voir ma demande" />
        @endif
    </section>
</div>

{{-- Popin « Message » de chaque prestataire, ouverte par son bouton
     (data-cm-open). Le message part dans la messagerie, a propos de la
     demande (ContactController::ownerMessage, type « proposal »). --}}
@foreach ($proposals as $proposal)
    @if ($proposal->provider)
        <x-contact-owner id="cm-proposal-{{ $proposal->id }}"
                         :owner="$proposal->provider"
                         listing-type="proposal"
                         :listing-id="$proposal->id"
                         :title="$ad->title"
                         :price="$euros($proposal->price)"
                         price-label="proposé"
                         :image="$cover"
                         kind="proposition" />
    @endif
@endforeach
@endsection

@push('scripts')
<script>
    // Choisir un prestataire decline tous les autres : on fait confirmer.
    document.querySelectorAll('[data-pp-select]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var texte = 'Vous retenez la proposition de ' + form.dataset.name + ' (' + form.dataset.price + '). '
                      + 'Les autres prestataires seront prévenus que leur proposition n\'est pas retenue.';

            if (window.Swal) {
                Swal.fire({
                    title: 'Choisir ce prestataire ?',
                    text: texte,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, je le choisis',
                    cancelButtonText: 'Annuler',
                    confirmButtonColor: '#ff3c00',
                    reverseButtons: true
                }).then(function (result) { if (result.isConfirmed) form.submit(); });
            } else if (window.confirm(texte)) {
                form.submit();
            }
        });
    });
</script>
@endpush
