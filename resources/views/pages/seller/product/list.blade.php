@extends('layouts.connected')
@section('title', 'Mes produits - Olten')

@php
    /*
     | Aucune donnee supplementaire n'est demandee au controleur : on se sert
     | uniquement du paginateur deja fourni.
     |   - le total est celui de la requete complete ($products->total())
     |   - les autres indicateurs portent sur les produits affiches, ce qui est
     |     precise sous la valeur des qu'il y a plusieurs pages.
     */
    $onPage      = $products->getCollection();
    $search      = trim((string) request('search'));
    $countOnPage = $onPage->count();
    $online      = $onPage->where('is_active', true)->count();
    $outOfStock  = $onPage->filter(fn ($p) => (int) $p->stock <= 0)->count();
    $stockValue  = $onPage->sum(fn ($p) => (float) $p->price * (int) $p->stock);
    $partial     = $products->hasPages();
    $scopeLabel  = $partial ? 'sur cette page' : null;
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Mes produits</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Mes produits</h1>
            <p class="sp-subtitle">Gérez votre catalogue, vos prix et vos stocks en un coup d'œil.</p>
        </div>

        <a href="{{ route('seller.produits.create') }}" class="sp-btn-primary">
            Ajouter un produit
        </a>
    </header>

    {{-- Indicateurs --}}
    <div class="sp-stats">
        <div class="sp-stat">
            <span class="sp-stat-icon is-brand"><i class="fa-solid fa-box-open"></i></span>
            <div>
                <span class="sp-stat-value">{{ $products->total() }}</span>
                <span class="sp-stat-label">Produit{{ $products->total() > 1 ? 's' : '' }} au total</span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-green"><i class="fa-solid fa-circle-check"></i></span>
            <div>
                <span class="sp-stat-value">{{ $online }}</span>
                <span class="sp-stat-label">
                    En ligne
                    @if($scopeLabel)<small>{{ $scopeLabel }}</small>@endif
                </span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-red"><i class="fa-solid fa-triangle-exclamation"></i></span>
            <div>
                <span class="sp-stat-value">{{ $outOfStock }}</span>
                <span class="sp-stat-label">
                    En rupture
                    @if($scopeLabel)<small>{{ $scopeLabel }}</small>@endif
                </span>
            </div>
        </div>

        <div class="sp-stat">
            <span class="sp-stat-icon is-blue"><i class="fa-solid fa-euro-sign"></i></span>
            <div>
                <span class="sp-stat-value">{{ number_format($stockValue, 2, ',', ' ') }} €</span>
                <span class="sp-stat-label">
                    Valeur du stock
                    @if($scopeLabel)<small>{{ $scopeLabel }}</small>@endif
                </span>
            </div>
        </div>
    </div>

    {{-- Panneau catalogue --}}
    <section class="sp-panel">

        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">Catalogue</h2>
                <span class="sp-count">
                    @if($search)
                        {{ $products->total() }} résultat{{ $products->total() > 1 ? 's' : '' }} pour « {{ $search }} »
                    @else
                        {{ $countOnPage }} produit{{ $countOnPage > 1 ? 's' : '' }} affiché{{ $countOnPage > 1 ? 's' : '' }} sur {{ $products->total() }}
                    @endif
                </span>
            </div>

            <div class="sp-toolbar-actions">
                <form method="GET" action="{{ route('seller.produits.index') }}" class="sp-search" role="search">
                    <div class="sp-search-field">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" class="sp-search-input"
                               placeholder="Rechercher un produit..."
                               value="{{ request('search') }}"
                               aria-label="Rechercher un produit">

                        @if($search)
                            <a href="{{ route('seller.produits.index') }}" class="sp-search-clear" title="Effacer la recherche" aria-label="Effacer la recherche">&times;</a>
                        @endif
                    </div>

                    <button type="submit" class="sp-search-submit">Rechercher</button>
                </form>

                <div class="sp-view-toggle" role="group" aria-label="Affichage">
                    <button type="button" class="sp-view-btn is-active" data-sp-view="grid">Grille</button>
                    <button type="button" class="sp-view-btn" data-sp-view="list">Liste</button>
                </div>
            </div>
        </div>

        @if($products->count())
            <div class="sp-grid" data-sp-grid>
                @foreach ($products as $product)
                    @php
                        $stock  = (int) $product->stock;
                        $images = $product->images;
                        $cover  = $images->first()
                            ? asset('storage/' . $images->first()->image)
                            : asset('assets/images/no-image.jpg');
                    @endphp

                    <article class="sp-card {{ $stock <= 0 ? 'is-out' : '' }}">

                        {{-- Visuel --}}
                        <a href="{{ route('products.show', $product) }}" class="sp-media" title="Voir la fiche produit">
                            <img src="{{ $cover }}" alt="{{ $product->name }}" loading="lazy">

                            <div class="sp-media-badges">
                                @if($product->is_active)
                                    <span class="sp-badge is-online"><i class="fa-solid fa-circle" style="font-size:6px"></i> En ligne</span>
                                @else
                                    <span class="sp-badge is-draft"><i class="fa-solid fa-eye-slash"></i> Hors ligne</span>
                                @endif

                                @if($stock <= 0)
                                    <span class="sp-badge is-out">Rupture</span>
                                @endif
                            </div>

                            @if($images->count() > 1)
                                <div class="sp-media-foot">
                                    <span class="sp-badge is-photos"><i class="fa-regular fa-images"></i> {{ $images->count() }}</span>
                                </div>
                            @endif
                        </a>

                        {{-- Informations --}}
                        <div class="sp-body">
                            <div class="sp-list-main">
                                <span class="sp-chip">
                                    <i class="fa-solid fa-tag"></i>
                                    {{ $product->category->nom ?? 'Sans catégorie' }}
                                </span>

                                <a href="{{ route('products.show', $product) }}" class="sp-name">{{ $product->name }}</a>

                                <div class="sp-price">
                                    {{ number_format((float) $product->price, 2, ',', ' ') }} €
                                    <small>l'unité</small>
                                </div>
                            </div>

                            <div class="sp-meta">
                                <span class="sp-tag {{ $stock <= 0 ? 'is-danger' : ($stock <= 5 ? 'is-warn' : 'is-ok') }}">
                                    <i class="fa-solid fa-cubes"></i>
                                    {{ $stock <= 0 ? 'Stock épuisé' : 'Stock : ' . $stock }}
                                </span>

                                <span class="sp-tag">
                                    <i class="fa-regular fa-eye"></i>
                                    {{ $product->views ?? 0 }} vue{{ ($product->views ?? 0) > 1 ? 's' : '' }}
                                </span>

                                <span class="sp-tag {{ $product->delivery_available ? 'is-ok' : '' }}">
                                    <i class="fa-solid fa-truck-fast"></i>
                                    {{ $product->delivery_available ? 'Livraison' : 'Sans livraison' }}
                                </span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="sp-actions">
                            <a href="{{ route('seller.produits.edit', $product) }}" class="sp-act is-edit">
                                Modifier
                            </a>

                            <a href="{{ route('products.show', $product) }}" class="sp-act is-view" title="Voir la fiche publique">
                                Voir
                            </a>

                            <form action="{{ route('seller.produits.destroy', $product) }}" method="POST"
                                  class="sp-delete-form" data-sp-delete data-name="{{ $product->name }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="sp-act is-delete" aria-label="Supprimer {{ $product->name }}">Supprimer</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            @if($products->hasPages())
                <div class="sp-pagination">
                    {{ $products->withQueryString()->links() }}
                </div>
            @endif
        @else
            <div class="sp-empty">
                @if($search)
                    <x-empty-state
                        title="Aucun produit ne correspond à votre recherche"
                        text="Essayez un autre mot-clé ou affichez de nouveau tout votre catalogue."
                        :action-url="route('seller.produits.index')"
                        action-label="Voir tous mes produits" />
                @else
                    <x-empty-state
                        title="Aucun produit en ligne"
                        text="Ajoutez votre premier produit pour le mettre en vente sur Olten."
                        :action-url="route('seller.produits.create')"
                        action-label="Ajouter un produit" />
                @endif
            </div>
        @endif
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Bascule grille / liste (preference conservee dans le navigateur)
        const grid = document.querySelector('[data-sp-grid]');
        const buttons = document.querySelectorAll('[data-sp-view]');

        function applyView(view) {
            if (grid) grid.classList.toggle('is-list', view === 'list');
            buttons.forEach(btn => btn.classList.toggle('is-active', btn.dataset.spView === view));
        }

        let saved = 'grid';
        try { saved = localStorage.getItem('olten.seller.products.view') || 'grid'; } catch (e) {}
        applyView(saved);

        buttons.forEach(btn => btn.addEventListener('click', function () {
            const view = btn.dataset.spView;
            applyView(view);
            try { localStorage.setItem('olten.seller.products.view', view); } catch (e) {}
        }));

        // Confirmation de suppression
        document.querySelectorAll('[data-sp-delete]').forEach(form => {
            form.addEventListener('submit', function (e) {
                if (form.dataset.confirmed === '1') return;
                e.preventDefault();

                const name = form.dataset.name || 'ce produit';

                if (typeof Swal === 'undefined') {
                    if (confirm('Supprimer « ' + name + ' » ? Cette action est définitive.')) {
                        form.dataset.confirmed = '1';
                        form.submit();
                    }
                    return;
                }

                Swal.fire({
                    title: 'Supprimer ce produit ?',
                    html: '« <strong>' + name.replace(/</g, '&lt;') + '</strong> » sera définitivement retiré de votre catalogue.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler',
                    confirmButtonColor: '#c0392b',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                }).then(result => {
                    if (result.isConfirmed) {
                        form.dataset.confirmed = '1';
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endsection
