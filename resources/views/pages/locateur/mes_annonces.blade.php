@extends('layouts.connected')
@section('title', 'Mes annonces - Olten')

@php
    /*
     | Tout vient du paginateur $ads et de $categories fournis par le
     | controleur. Le total est celui de la requete complete ; les autres
     | indicateurs portent sur les annonces affichees (« sur cette page »).
     |
     | Le statut d'une annonce se lit sur trois colonnes : is_approved,
     | rejected_at et expires_at. Les memes regles que le filtre serveur
     | sont reprises ici pour l'affichage.
     */
    $onPage     = $ads->getCollection();
    $search     = trim((string) request('search'));
    $status     = (string) request('status');
    $categoryId = (string) request('category_id');

    $adStatus = function ($ad) {
        if ($ad->rejected_at) return 'rejected';
        if ($ad->expires_at && $ad->expires_at->isPast()) return 'expired';
        return $ad->is_approved ? 'approved' : 'pending';
    };

    $approved   = $onPage->filter(fn ($a) => $adStatus($a) === 'approved')->count();
    $pending    = $onPage->filter(fn ($a) => $adStatus($a) === 'pending')->count();
    $views      = $onPage->sum(fn ($a) => (int) ($a->views ?? 0));
    $scopeLabel = $ads->hasPages() ? 'sur cette page' : null;

    $tabs = [
        ''         => 'Toutes',
        'approved' => 'Approuvées',
        'pending'  => 'En attente',
        'rejected' => 'Refusées',
        'expired'  => 'Expirées',
    ];

    $statusMeta = [
        'approved' => ['Approuvée',  'is-online', 'fa-circle-check'],
        'pending'  => ['En attente', 'is-draft',  'fa-hourglass-half'],
        'rejected' => ['Refusée',    'is-out',    'fa-circle-xmark'],
        'expired'  => ['Expirée',    'is-draft',  'fa-clock'],
    ];

    // Les filtres actifs suivent l'utilisateur d'un onglet a l'autre
    $keep = array_filter(['search' => $search, 'category_id' => $categoryId]);
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Mes annonces</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Mes annonces</h1>
            <p class="sp-subtitle">Gérez vos annonces de location, leur visibilité et leur validité.</p>
        </div>

        <a href="{{ route('ads.create') }}" class="sp-btn-primary">
            Déposer une annonce
        </a>
    </header>

    {{-- Indicateurs --}}
    <div class="sp-stats">
        <div class="sp-stat">
            <span class="sp-stat-icon is-brand"><i class="fa-solid fa-bullhorn"></i></span>
            <div>
                <span class="sp-stat-value">{{ $ads->total() }}</span>
                <span class="sp-stat-label">Annonce{{ $ads->total() > 1 ? 's' : '' }} au total</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-green"><i class="fa-solid fa-circle-check"></i></span>
            <div>
                <span class="sp-stat-value">{{ $approved }}</span>
                <span class="sp-stat-label">
                    Approuvée{{ $approved > 1 ? 's' : '' }}
                    @if($scopeLabel)<small>{{ $scopeLabel }}</small>@endif
                </span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-red"><i class="fa-solid fa-hourglass-half"></i></span>
            <div>
                <span class="sp-stat-value">{{ $pending }}</span>
                <span class="sp-stat-label">
                    En attente de validation
                    @if($scopeLabel)<small>{{ $scopeLabel }}</small>@endif
                </span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-blue"><i class="fa-regular fa-eye"></i></span>
            <div>
                <span class="sp-stat-value">{{ $views }}</span>
                <span class="sp-stat-label">
                    Vue{{ $views > 1 ? 's' : '' }} cumulées
                    @if($scopeLabel)<small>{{ $scopeLabel }}</small>@endif
                </span>
            </div>
        </div>
    </div>

    {{-- Panneau --}}
    <section class="sp-panel">

        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">Mes publications</h2>
                <span class="sp-count">
                    @if($search)
                        {{ $ads->total() }} résultat{{ $ads->total() > 1 ? 's' : '' }} pour « {{ $search }} »
                    @else
                        {{ $onPage->count() }} annonce{{ $onPage->count() > 1 ? 's' : '' }} affichée{{ $onPage->count() > 1 ? 's' : '' }} sur {{ $ads->total() }}
                    @endif
                </span>
            </div>

            <div class="sp-toolbar-actions">
                <form method="GET" action="{{ route('ads.index') }}" class="sp-search" role="search">
                    @if($status)
                        <input type="hidden" name="status" value="{{ $status }}">
                    @endif

                    <div class="sp-search-field">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" class="sp-search-input"
                               placeholder="Rechercher une annonce..."
                               value="{{ request('search') }}"
                               aria-label="Rechercher une annonce">

                        @if($search)
                            <a href="{{ route('ads.index', array_filter(['status' => $status, 'category_id' => $categoryId])) }}"
                               class="sp-search-clear" title="Effacer la recherche" aria-label="Effacer la recherche">&times;</a>
                        @endif
                    </div>

                    <select name="category_id" class="sp-select" aria-label="Filtrer par catégorie">
                        <option value="all">Toutes les catégories</option>
                        @foreach($categories->groupBy(fn ($c) => $c->service->nom ?? 'Autres') as $serviceName => $group)
                            <optgroup label="{{ $serviceName }}">
                                @foreach($group as $category)
                                    <option value="{{ $category->id }}" @selected($categoryId == $category->id)>
                                        {{ $category->nom }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>

                    <button type="submit" class="sp-search-submit">Filtrer</button>
                </form>
            </div>
        </div>

        {{-- Onglets de statut (filtre serveur, memes valeurs que le controleur) --}}
        <div class="sp-tabs">
            @foreach($tabs as $value => $label)
                <a href="{{ route('ads.index', array_filter($keep + ['status' => $value])) }}"
                   class="sp-tab {{ $status === $value ? 'is-active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        @if($ads->count())
            <div class="sp-grid">
                @foreach ($ads as $ad)
                    @php
                        $state = $adStatus($ad);
                        [$stLabel, $stClass, $stIcon] = $statusMeta[$state];
                        $images = $ad->images;
                        $cover  = $images->first()
                            ? asset('storage/' . $images->first()->path)
                            : asset('assets/images/no-image.jpg');
                    @endphp

                    <article class="sp-card {{ in_array($state, ['rejected', 'expired']) ? 'is-out' : '' }}">

                        <a href="{{ route('ads.show', $ad) }}" class="sp-media" title="Voir l'annonce">
                            <img src="{{ $cover }}" alt="{{ $ad->title }}" loading="lazy">

                            <div class="sp-media-badges">
                                <span class="sp-badge {{ $stClass }}">
                                    <i class="fa-solid {{ $stIcon }}"></i> {{ $stLabel }}
                                </span>
                            </div>

                            @if($images->count() > 1)
                                <div class="sp-media-foot">
                                    <span class="sp-badge is-photos"><i class="fa-regular fa-images"></i> {{ $images->count() }}</span>
                                </div>
                            @endif
                        </a>

                        <div class="sp-body">
                            <div class="sp-list-main">
                                <span class="sp-chip">
                                    <i class="fa-solid fa-tag"></i>
                                    {{ $ad->category->nom ?? 'Sans catégorie' }}
                                </span>

                                <a href="{{ route('ads.show', $ad) }}" class="sp-name">{{ $ad->title }}</a>

                                <div class="sp-price">
                                    {{ number_format((float) $ad->price_per_day, 2, ',', ' ') }} €
                                    <small>/ jour</small>
                                </div>
                            </div>

                            <div class="sp-meta">
                                <span class="sp-tag">
                                    <i class="fa-regular fa-eye"></i>
                                    {{ $ad->views ?? 0 }} vue{{ ($ad->views ?? 0) > 1 ? 's' : '' }}
                                </span>

                                <span class="sp-tag {{ $state === 'expired' ? 'is-danger' : '' }}">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ $ad->expires_at ? 'Jusqu\'au ' . $ad->expires_at->format('d/m/Y') : 'Sans échéance' }}
                                </span>

                                <span class="sp-tag {{ $ad->delivery_active ? 'is-ok' : '' }}">
                                    <i class="fa-solid fa-truck-fast"></i>
                                    {{ $ad->delivery_active ? 'Livraison' : 'Sans livraison' }}
                                </span>

                                @if($ad->address)
                                    <span class="sp-tag">
                                        <i class="fa-solid fa-location-dot"></i>
                                        {{ \Illuminate\Support\Str::limit($ad->address, 28) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="sp-actions">
                            <a href="{{ route('ads.edit', $ad) }}" class="sp-act is-edit">Modifier</a>

                            <a href="{{ route('ads.ical', $ad) }}" class="sp-act is-ghost" title="Exporter le calendrier">iCal</a>

                            <button type="button" class="sp-act is-delete"
                                    data-bs-toggle="modal" data-bs-target="#deleteAdModal"
                                    data-title="{{ $ad->title }}"
                                    data-url="{{ route('ads.destroy', $ad) }}">Supprimer</button>
                        </div>
                    </article>
                @endforeach
            </div>

            @if($ads->hasPages())
                <div class="sp-pagination">
                    {{ $ads->withQueryString()->links() }}
                </div>
            @endif
        @else
            <div class="sp-empty">
                @if($search || $status || ($categoryId && $categoryId !== 'all'))
                    <x-empty-state
                        title="Aucune annonce ne correspond à ces filtres"
                        text="Modifiez votre recherche ou affichez de nouveau toutes vos annonces."
                        :action-url="route('ads.index')"
                        action-label="Voir toutes mes annonces" />
                @else
                    <x-empty-state
                        title="Aucune annonce publiée"
                        text="Déposez votre première annonce pour la rendre visible sur la plateforme."
                        :action-url="route('ads.create')"
                        action-label="Déposer une annonce" />
                @endif
            </div>
        @endif
    </section>
</div>

@include('pages.modals.ad_confim_delete')
<script src="{{ asset('assets/js/confirm_delete_ad.js') }}"></script>
@endsection
