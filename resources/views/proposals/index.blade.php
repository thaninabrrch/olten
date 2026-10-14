@extends('layouts.connected')
@section('title', 'Mes propositions - Olten')

@php
    /*
     | « Mes propositions » (ServiceProposalController::index), en deux
     | onglets :
     |   envoyees : les propositions faites en tant que prestataire, avec la
     |              reponse du client ;
     |   recues   : les demandes de prestation publiees, avec le nombre de
     |              propositions et le meilleur prix, vers la comparaison.
     */
    $euros   = fn ($amount) => number_format((float) $amount, 2, ',', ' ') . ' €';
    $waiting = $received->sum('pending_count');
    $cover   = fn ($ad) => $ad?->images->first()
        ? asset('storage/' . $ad->images->first()->path)
        : asset('assets/images/no-image.jpg');
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
        <span class="is-current">Mes propositions</span>
    </nav>

    <header class="sp-head">
        <div>
            <h1 class="sp-title">Prestations de services</h1>
            <p class="sp-subtitle">
                Publiez une demande et comparez les propositions des prestataires, ou répondez aux demandes des autres membres.
            </p>
        </div>

        <div class="sp-head-actions">
            <a href="{{ route('services.show', \App\Models\ServiceProposal::SERVICE_SLUG) }}" class="sp-act">
                <i class="fa-solid fa-magnifying-glass"></i> Trouver des demandes
            </a>
            <a href="{{ route('ads.create') }}" class="sp-btn-primary">
                <i class="fa-solid fa-circle-plus"></i> Publier une demande
            </a>
        </div>
    </header>

    <section class="sp-panel">
        <div class="sp-tabs">
            <a href="{{ route('proposals.index', ['onglet' => 'recues']) }}" class="sp-tab {{ $tab === 'recues' ? 'is-active' : '' }}">
                <i class="fa-solid fa-inbox"></i> Reçues
                @if ($waiting)
                    <span class="sp-tab-count" aria-label="{{ $waiting }} à comparer">{{ $waiting }}</span>
                @endif
            </a>
            <a href="{{ route('proposals.index') }}" class="sp-tab {{ $tab === 'envoyees' ? 'is-active' : '' }}">
                <i class="fa-regular fa-paper-plane"></i> Envoyées
                @if ($sent->isNotEmpty())
                    <span class="sp-tab-count">{{ $sent->count() }}</span>
                @endif
            </a>
        </div>

        @if ($tab === 'recues')
            {{-- Mes demandes et leurs propositions --}}
            @if ($received->isNotEmpty())
                <ul class="pp-rows">
                    @foreach ($received as $ad)
                        @php
                            $chosen = $ad->acceptedProposal;
                            [$state, $stateClass] = match (true) {
                                (bool) $chosen      => ['Prestataire choisi', 'is-ok'],
                                $ad->isExpired()    => ['Expirée', 'is-muted'],
                                ! $ad->is_approved  => ['En validation', 'is-wait'],
                                default             => ['En ligne', 'is-online'],
                            };
                        @endphp

                        <li class="pp-row">
                            <a href="{{ route('ads.show', $ad) }}" class="pp-row-media">
                                <img src="{{ $cover($ad) }}" alt="" loading="lazy">
                            </a>

                            <div class="pp-row-main">
                                <span class="pp-row-chips">
                                    <span class="pp-badge {{ $stateClass }}">{{ $state }}</span>
                                    <span class="pp-tag"><i class="fa-solid fa-tag"></i> {{ $ad->category->nom ?? 'Prestation' }}</span>
                                </span>
                                <a href="{{ route('ads.show', $ad) }}" class="pp-row-title">{{ $ad->title }}</a>
                                <span class="pp-row-meta">
                                    Budget {{ $euros($ad->price_per_day) }}
                                    @if ($ad->expires_at)
                                        · jusqu'au {{ $ad->expires_at->translatedFormat('d M Y') }}
                                    @endif
                                </span>
                            </div>

                            <div class="pp-row-figures">
                                <span>
                                    <strong>{{ $ad->proposals_count }}</strong>
                                    proposition{{ $ad->proposals_count > 1 ? 's' : '' }}
                                </span>
                                @if ($chosen)
                                    <span>Choisi : <strong>{{ $chosen->provider?->public_name }}</strong> · {{ $euros($chosen->price) }}</span>
                                @elseif ($ad->best_price !== null)
                                    <span>Meilleur prix <strong>{{ $euros($ad->best_price) }}</strong></span>
                                @endif
                            </div>

                            <div class="pp-row-actions">
                                @if ($ad->proposals_count)
                                    <a href="{{ route('ads.proposals', $ad) }}" class="sp-act is-edit">
                                        {{ $chosen ? 'Voir' : 'Comparer' }}
                                        @if ($ad->pending_count && ! $chosen)
                                            <span class="pp-count">{{ $ad->pending_count }}</span>
                                        @endif
                                    </a>
                                @else
                                    <a href="{{ route('ads.show', $ad) }}" class="sp-act">Voir la demande</a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <x-empty-state
                    title="Vous n'avez publié aucune demande"
                    text="Déposez une annonce dans une catégorie « Prestations de services » : les prestataires vous feront leurs propositions, vous n'aurez plus qu'à comparer et choisir."
                    :action-url="route('ads.create')"
                    action-label="Publier une demande" />
            @endif
        @else
            {{-- Mes propositions de prestataire --}}
            @if ($sent->isNotEmpty())
                <ul class="pp-rows">
                    @foreach ($sent as $proposal)
                        @php
                            $ad = $proposal->ad;
                            [$label, $class, $icon] = $proposal->statusMeta();
                        @endphp

                        <li class="pp-row {{ $proposal->status === \App\Models\ServiceProposal::WITHDRAWN ? 'is-faded' : '' }}">
                            <a href="{{ $ad ? route('ads.show', $ad) : '#' }}" class="pp-row-media">
                                <img src="{{ $cover($ad) }}" alt="" loading="lazy">
                            </a>

                            <div class="pp-row-main">
                                <span class="pp-row-chips">
                                    <span class="pp-badge {{ $class }}"><i class="fa-solid {{ $icon }}"></i> {{ $label }}</span>
                                    @if ($ad?->category)
                                        <span class="pp-tag"><i class="fa-solid fa-tag"></i> {{ $ad->category->nom }}</span>
                                    @endif
                                </span>
                                <a href="{{ $ad ? route('ads.show', $ad) : '#' }}" class="pp-row-title">{{ $ad->title ?? 'Demande supprimée' }}</a>
                                <span class="pp-row-meta">
                                    {{ $ad?->user?->public_name ?? 'Client' }}
                                    · envoyée le {{ $proposal->created_at->translatedFormat('d M Y') }}
                                    @if ($proposal->available_on)
                                        · disponible dès le {{ $proposal->available_on->translatedFormat('d M') }}
                                    @endif
                                </span>
                            </div>

                            <div class="pp-row-figures">
                                <span>Votre prix <strong>{{ $euros($proposal->price) }}</strong></span>
                                @if ($ad)
                                    <span>Budget du client {{ $euros($ad->price_per_day) }}</span>
                                @endif
                            </div>

                            <div class="pp-row-actions">
                                @if ($proposal->isAccepted() && $ad?->user)
                                    <button type="button" class="sp-act is-edit" data-cm-open="cm-client-{{ $proposal->id }}">
                                        <i class="fa-regular fa-comments"></i> Écrire au client
                                    </button>
                                @elseif ($ad)
                                    <a href="{{ route('ads.show', $ad) }}#proposition" class="sp-act">
                                        {{ $proposal->isPending() ? 'Modifier' : 'Voir la demande' }}
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <x-empty-state
                    title="Aucune proposition envoyée"
                    text="Parcourez les demandes des membres et proposez vos services : le client compare les propositions et choisit son prestataire."
                    :action-url="route('services.show', \App\Models\ServiceProposal::SERVICE_SLUG)"
                    action-label="Trouver des demandes" />
            @endif
        @endif
    </section>
</div>

{{-- Popin « Message » vers le client de chaque proposition retenue, ouverte
     par son bouton (data-cm-open). --}}
@if ($tab === 'envoyees')
    @foreach ($sent->filter(fn ($proposal) => $proposal->isAccepted() && $proposal->ad?->user) as $proposal)
        <x-contact-owner id="cm-client-{{ $proposal->id }}"
                         :owner="$proposal->ad->user"
                         listing-type="proposal"
                         :listing-id="$proposal->id"
                         :title="$proposal->ad->title"
                         :price="$euros($proposal->price)"
                         price-label="votre prix"
                         :image="$proposal->ad->images->first() ? asset('storage/' . $proposal->ad->images->first()->path) : null"
                         kind="prestation" />
    @endforeach
@endif
@endsection
