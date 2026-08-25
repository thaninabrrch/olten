@extends('layouts.connected')
@section('title', 'Mes commandes - Olten')

@php
    /*
     | Tout vient du paginateur $orders fourni par le controleur.
     | Le total est celui de la requete complete, les autres indicateurs
     | portent sur les commandes affichees (mention « sur cette page »).
     */
    $onPage     = $orders->getCollection();
    $search     = trim((string) request('search'));
    $status     = (string) request('status');
    $inProgress = $onPage->whereIn('order_status', ['pending', 'confirmed', 'shipped'])->count();
    $delivered  = $onPage->where('order_status', 'delivered')->count();
    $spent      = $onPage->filter(fn ($o) => $o->order_status !== 'cancelled')
                         ->sum(fn ($o) => (float) $o->total_price);
    $scopeLabel = $orders->hasPages() ? 'sur cette page' : null;

    $tabs = [
        ''          => 'Toutes',
        'pending'   => 'En attente',
        'confirmed' => 'Confirmées',
        'shipped'   => 'Expédiées',
        'delivered' => 'Livrées',
        'cancelled' => 'Annulées',
    ];

    $statusMeta = [
        'pending'   => ['En attente', 'is-pending',   'fa-hourglass-half'],
        'confirmed' => ['Confirmée',  'is-confirmed', 'fa-circle-check'],
        'shipped'   => ['Expédiée',   'is-shipped',   'fa-truck-fast'],
        'delivered' => ['Livrée',     'is-delivered', 'fa-box-open'],
        'cancelled' => ['Annulée',    'is-cancelled', 'fa-circle-xmark'],
        'en_cours'  => ['En cours',   'is-confirmed', 'fa-spinner'],
    ];
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Mes commandes</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Mes commandes</h1>
            <p class="sp-subtitle">Retrouvez vos achats, leur statut et leur suivi de livraison.</p>
        </div>

        <a href="{{ url('/') }}" class="sp-btn-primary">
            Découvrir des produits
        </a>
    </header>

    {{-- Indicateurs --}}
    <div class="sp-stats">
        <div class="sp-stat">
            <span class="sp-stat-icon is-brand"><i class="fa-solid fa-bag-shopping"></i></span>
            <div>
                <span class="sp-stat-value">{{ $orders->total() }}</span>
                <span class="sp-stat-label">Commande{{ $orders->total() > 1 ? 's' : '' }} au total</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-blue"><i class="fa-solid fa-truck-fast"></i></span>
            <div>
                <span class="sp-stat-value">{{ $inProgress }}</span>
                <span class="sp-stat-label">
                    En cours
                    @if($scopeLabel)<small>{{ $scopeLabel }}</small>@endif
                </span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-green"><i class="fa-solid fa-box-open"></i></span>
            <div>
                <span class="sp-stat-value">{{ $delivered }}</span>
                <span class="sp-stat-label">
                    Livrée{{ $delivered > 1 ? 's' : '' }}
                    @if($scopeLabel)<small>{{ $scopeLabel }}</small>@endif
                </span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-red"><i class="fa-solid fa-euro-sign"></i></span>
            <div>
                <span class="sp-stat-value">{{ number_format($spent, 2, ',', ' ') }} €</span>
                <span class="sp-stat-label">
                    Total dépensé
                    @if($scopeLabel)<small>{{ $scopeLabel }}</small>@endif
                </span>
            </div>
        </div>
    </div>

    {{-- Panneau --}}
    <section class="sp-panel">

        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">Historique des commandes</h2>
                <span class="sp-count">
                    @if($search)
                        {{ $orders->total() }} résultat{{ $orders->total() > 1 ? 's' : '' }} pour « {{ $search }} »
                    @else
                        {{ $onPage->count() }} commande{{ $onPage->count() > 1 ? 's' : '' }} affichée{{ $onPage->count() > 1 ? 's' : '' }} sur {{ $orders->total() }}
                    @endif
                </span>
            </div>

            <div class="sp-toolbar-actions">
                <form method="GET" action="{{ route('orders') }}" class="sp-search" role="search">
                    @if($status)
                        <input type="hidden" name="status" value="{{ $status }}">
                    @endif

                    <div class="sp-search-field">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" class="sp-search-input"
                               placeholder="Rechercher un produit..."
                               value="{{ request('search') }}"
                               aria-label="Rechercher une commande">

                        @if($search)
                            <a href="{{ route('orders', array_filter(['status' => $status])) }}"
                               class="sp-search-clear" title="Effacer la recherche" aria-label="Effacer la recherche">&times;</a>
                        @endif
                    </div>

                    <button type="submit" class="sp-search-submit">Rechercher</button>
                </form>
            </div>
        </div>

        {{-- Onglets de statut --}}
        <div class="sp-tabs">
            @foreach($tabs as $value => $label)
                <a href="{{ route('orders', array_filter(['search' => $search, 'status' => $value])) }}"
                   class="sp-tab {{ $status === $value ? 'is-active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        @if($orders->count())
            <div class="sp-rows">
                @foreach($orders as $order)
                    @php
                        $product = $order->product;
                        $cover   = $product && $product->images->first()
                            ? asset('storage/' . $product->images->first()->image)
                            : asset('assets/images/no-image.jpg');
                        [$stLabel, $stClass, $stIcon] = $statusMeta[$order->order_status]
                            ?? [ucfirst((string) $order->order_status), 'is-neutral', 'fa-circle-info'];
                        $seller = $order->seller;
                    @endphp

                    <article class="sp-row {{ $order->order_status === 'cancelled' ? 'is-cancelled' : '' }}">

                        <div class="sp-row-media">
                            <img src="{{ $cover }}" alt="{{ $product->name ?? 'Produit supprimé' }}" loading="lazy">
                        </div>

                        <div class="sp-row-main">
                            <div class="sp-row-head">
                                @if($product)
                                    <a href="{{ route('products.show', $product) }}" class="sp-row-title">{{ $product->name }}</a>
                                @else
                                    <h3 class="sp-row-title">Produit supprimé</h3>
                                @endif
                                <span class="sp-ref">#{{ $order->id }}</span>
                                <span class="sp-status {{ $stClass }}">
                                    <i class="fa-solid {{ $stIcon }}"></i> {{ $stLabel }}
                                </span>
                                @if($order->status === 'paid')
                                    <span class="sp-status is-paid"><i class="fa-solid fa-credit-card"></i> Payée</span>
                                @endif
                            </div>

                            <span class="sp-chip">
                                <i class="fa-solid fa-tag"></i>
                                {{ $product->category->nom ?? 'Sans catégorie' }}
                            </span>

                            <div class="sp-row-meta">
                                <span class="sp-tag">
                                    <i class="fa-solid fa-store"></i>
                                    Vendeur : {{ $seller->fullname ?? $seller->email ?? 'Inconnu' }}
                                </span>

                                <span class="sp-tag">
                                    <i class="fa-solid fa-cubes"></i>
                                    {{ $order->quantity }} article{{ $order->quantity > 1 ? 's' : '' }}
                                </span>

                                <span class="sp-tag">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ $order->created_at->format('d/m/Y à H:i') }}
                                </span>

                                @if($order->delivery_requested)
                                    <span class="sp-tag is-ok">
                                        <i class="fa-solid fa-truck-fast"></i> Livraison demandée
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="sp-row-side">
                            <div class="sp-amount">
                                {{ number_format((float) $order->total_price, 2, ',', ' ') }} €
                                <small>Total payé</small>
                            </div>

                            <div class="sp-row-actions">
                                @if($order->delivery_requested)
                                    <a href="{{ route('orders.show', $order->id) }}" class="sp-act is-edit">
                                        Suivre ma commande
                                    </a>
                                @elseif($product)
                                    <a href="{{ route('products.show', $product) }}" class="sp-act is-ghost">
                                        Voir le produit
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if($orders->hasPages())
                <div class="sp-pagination">
                    {{ $orders->withQueryString()->links() }}
                </div>
            @endif
        @else
            <div class="sp-empty">
                @if($search || $status)
                    <x-empty-state
                        title="Aucune commande ne correspond à ce filtre"
                        text="Modifiez votre recherche ou affichez de nouveau tout l'historique."
                        :action-url="route('orders')"
                        action-label="Voir toutes mes commandes" />
                @else
                    <x-empty-state
                        title="Aucune commande enregistrée"
                        text="Vos achats sur Olten apparaîtront ici."
                        :action-url="url('/')"
                        action-label="Découvrir des produits" />
                @endif
            </div>
        @endif
    </section>
</div>
@endsection
