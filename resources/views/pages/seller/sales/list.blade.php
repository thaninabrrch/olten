@extends('layouts.connected')
@section('title', 'Mes ventes - Olten')

@php
    /*
     | Rien n'est demande en plus au controleur : tout vient du paginateur
     | $sales. Le total est celui de la requete complete, les autres
     | indicateurs portent sur les ventes affichees (mention « sur cette page »).
     */
    $onPage      = $sales->getCollection();
    $search      = trim((string) request('search'));
    $status      = (string) request('status');
    $paid        = $onPage->where('status', 'paid');
    $revenue     = $paid->sum(fn ($s) => (float) $s->total_price);
    $pendingCnt  = $onPage->where('status', 'pending')->count();
    $units       = $onPage->sum(fn ($s) => (int) $s->quantity);
    $scopeLabel  = $sales->hasPages() ? 'sur cette page' : null;

    $tabs = [
        ''        => 'Toutes',
        'pending' => 'En attente',
        'paid'    => 'Payées',
    ];

    $statusMeta = [
        'pending'   => ['En attente', 'is-pending',   'fa-hourglass-half'],
        'paid'      => ['Payée',      'is-paid',      'fa-circle-check'],
        'delivered' => ['Livrée',     'is-delivered', 'fa-box-open'],
        'cancelled' => ['Annulée',    'is-cancelled', 'fa-circle-xmark'],
        'refunded'  => ['Remboursée', 'is-neutral',   'fa-rotate-left'],
    ];
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Mes ventes</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Mes ventes</h1>
            <p class="sp-subtitle">Suivez vos ventes, vos encaissements et vos acheteurs.</p>
        </div>

        <a href="{{ route('seller.produits.index') }}" class="sp-btn-primary">
            Mes produits
        </a>
    </header>

    {{-- Indicateurs --}}
    <div class="sp-stats">
        <div class="sp-stat">
            <span class="sp-stat-icon is-brand"><i class="fa-solid fa-receipt"></i></span>
            <div>
                <span class="sp-stat-value">{{ $sales->total() }}</span>
                <span class="sp-stat-label">Vente{{ $sales->total() > 1 ? 's' : '' }} au total</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-green"><i class="fa-solid fa-euro-sign"></i></span>
            <div>
                <span class="sp-stat-value">{{ number_format($revenue, 2, ',', ' ') }} €</span>
                <span class="sp-stat-label">
                    Encaissé
                    @if($scopeLabel)<small>{{ $scopeLabel }}</small>@endif
                </span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-red"><i class="fa-solid fa-hourglass-half"></i></span>
            <div>
                <span class="sp-stat-value">{{ $pendingCnt }}</span>
                <span class="sp-stat-label">
                    En attente de paiement
                    @if($scopeLabel)<small>{{ $scopeLabel }}</small>@endif
                </span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-blue"><i class="fa-solid fa-cubes"></i></span>
            <div>
                <span class="sp-stat-value">{{ $units }}</span>
                <span class="sp-stat-label">
                    Article{{ $units > 1 ? 's' : '' }} vendu{{ $units > 1 ? 's' : '' }}
                    @if($scopeLabel)<small>{{ $scopeLabel }}</small>@endif
                </span>
            </div>
        </div>
    </div>

    {{-- Panneau --}}
    <section class="sp-panel">

        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">Historique des ventes</h2>
                <span class="sp-count">
                    @if($search)
                        {{ $sales->total() }} résultat{{ $sales->total() > 1 ? 's' : '' }} pour « {{ $search }} »
                    @else
                        {{ $onPage->count() }} vente{{ $onPage->count() > 1 ? 's' : '' }} affichée{{ $onPage->count() > 1 ? 's' : '' }} sur {{ $sales->total() }}
                    @endif
                </span>
            </div>

            <div class="sp-toolbar-actions">
                <form method="GET" action="{{ route('seller.sales') }}" class="sp-search" role="search">
                    @if($status)
                        <input type="hidden" name="status" value="{{ $status }}">
                    @endif

                    <div class="sp-search-field">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" class="sp-search-input"
                               placeholder="Produit, acheteur, statut..."
                               value="{{ request('search') }}"
                               aria-label="Rechercher une vente">

                        @if($search)
                            <a href="{{ route('seller.sales', array_filter(['status' => $status])) }}"
                               class="sp-search-clear" title="Effacer la recherche" aria-label="Effacer la recherche">&times;</a>
                        @endif
                    </div>

                    <button type="submit" class="sp-search-submit">Rechercher</button>
                </form>
            </div>
        </div>

        {{-- Onglets de statut (memes parametres que le formulaire) --}}
        <div class="sp-tabs">
            @foreach($tabs as $value => $label)
                <a href="{{ route('seller.sales', array_filter(['search' => $search, 'status' => $value])) }}"
                   class="sp-tab {{ $status === $value ? 'is-active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        @if($sales->count())
            <div class="sp-rows">
                @foreach ($sales as $sale)
                    @php
                        $product = $sale->product;
                        $cover   = $product && $product->images->first()
                            ? asset('storage/' . $product->images->first()->image)
                            : asset('assets/images/no-image.jpg');
                        [$stLabel, $stClass, $stIcon] = $statusMeta[$sale->status] ?? [ucfirst((string) $sale->status), 'is-neutral', 'fa-circle-info'];
                        $buyer = $sale->buyer;
                    @endphp

                    <article class="sp-row {{ $sale->status === 'cancelled' ? 'is-cancelled' : '' }}">

                        <a href="{{ route('seller.sales.show', $sale) }}" class="sp-row-media">
                            <img src="{{ $cover }}" alt="{{ $product->name ?? 'Produit supprimé' }}" loading="lazy">
                        </a>

                        <div class="sp-row-main">
                            <div class="sp-row-head">
                                <a href="{{ route('seller.sales.show', $sale) }}" class="sp-row-title">
                                    {{ $product->name ?? 'Produit supprimé' }}
                                </a>
                                <span class="sp-ref">#{{ $sale->id }}</span>
                                <span class="sp-status {{ $stClass }}">
                                    <i class="fa-solid {{ $stIcon }}"></i> {{ $stLabel }}
                                </span>
                            </div>

                            <span class="sp-chip">
                                <i class="fa-solid fa-tag"></i>
                                {{ $product->category->nom ?? 'Sans catégorie' }}
                            </span>

                            <div class="sp-row-meta">
                                <span class="sp-tag">
                                    <i class="fa-solid fa-user"></i>
                                    {{ $buyer->fullname ?? $buyer->email ?? 'Acheteur inconnu' }}
                                </span>

                                <span class="sp-tag">
                                    <i class="fa-solid fa-cubes"></i>
                                    {{ $sale->quantity }} article{{ $sale->quantity > 1 ? 's' : '' }}
                                </span>

                                <span class="sp-tag">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ $sale->created_at->format('d/m/Y à H:i') }}
                                </span>

                                @if($sale->delivery_requested)
                                    <span class="sp-tag is-ok">
                                        <i class="fa-solid fa-truck-fast"></i> Livraison demandée
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="sp-row-side">
                            <div class="sp-amount">
                                {{ number_format((float) $sale->total_price, 2, ',', ' ') }} €
                                <small>Montant de la vente</small>
                            </div>

                            <div class="sp-row-actions">
                                <a href="{{ route('seller.sales.show', $sale) }}" class="sp-act is-edit">
                                    Détails
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if($sales->hasPages())
                <div class="sp-pagination">
                    {{ $sales->withQueryString()->links() }}
                </div>
            @endif
        @else
            <div class="sp-empty">
                @if($search || $status)
                    <x-empty-state
                        title="Aucune vente ne correspond à ce filtre"
                        text="Modifiez votre recherche ou affichez de nouveau tout l'historique."
                        :action-url="route('seller.sales')"
                        action-label="Voir toutes mes ventes" />
                @else
                    <x-empty-state
                        title="Aucune vente enregistrée"
                        text="Vos ventes apparaîtront ici dès qu'un client aura commandé un de vos produits."
                        :action-url="route('seller.produits.index')"
                        action-label="Voir mes produits" />
                @endif
            </div>
        @endif
    </section>
</div>
@endsection
