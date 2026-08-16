@extends('layouts.connected')
@section('title', 'Mes Produits - Olten')

@section('content')
<div class="breadcrumb">
    <a href="#">Accueil</a>
    <span>></span>
    <span>Mes Produits</span>
</div>

<h1 class="page-title">Mes Produits</h1>

<div class="annonces-container">
    <div class="section-header">
        <h2 class="section-title">Produits publiés</h2>

        <div class="search-filters">
            <form method="GET" action="{{ route('seller.produits.index') }}">
                <input type="text" name="search" class="search-input"
                       placeholder="Rechercher un produit"
                       value="{{ request('search') }}">

                <button type="submit" class="btn-search">
                    <i class="fa-solid fa-search"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- LISTE DES PRODUITS -->
    <div class="annonces-list">
        @forelse ($products as $product)
            <div class="annonce-card">

                <!-- IMAGE -->
                <div class="annonce-image">
                    <img src="{{ $product->images->first() 
                        ? asset('storage/' . $product->images->first()->image) 
                        : asset('assets/images/no-image.jpg') }}"
                         alt="{{ $product->name }}">
                </div>

                <!-- DETAILS -->
                <div class="annonce-details">
                    <h3 class="annonce-title">{{ $product->name }}</h3>

                    <div class="annonce-tags">
                        <span class="tag tag-orange">
                            {{ $product->category->nom ?? 'Catégorie non définie' }}
                        </span>
                    </div>

                    <div class="annonce-stats">
                        <span class="stat-item">
                            <i class="fas fa-money-bill-wave"></i> {{ number_format($product->price, 2) }} €
                        </span>

                        <span class="stat-item">
                            <i class="fas fa-cubes"></i>
                            Stock : {{ $product->stock }}
                        </span>

                        <span class="stat-item">
                            <i class="fa-solid fa-eye"></i>
                            Vues : {{ $product->views ?? 0 }}
                        </span>

                        <span class="stat-item text-white {{ $product->delivery_available ? 'bg-success' : 'bg-danger' }}">
                            <i class="fa-solid fa-truck"></i>
                            Livraison : {{ $product->delivery_available ? "Diponible" : "Non disponible" }}
                        </span>
                    </div>

                    @if($product->stock <= 0)
                        <span class="expired">Rupture de stock</span>
                    @endif
                </div>

                <!-- ACTIONS -->
                <div class="annonce-actions">
                    <a href="{{ route('seller.produits.edit', $product) }}" class="btn-action btn-edit">Modifier</a>

                    <form action="{{ route('seller.produits.destroy', $product) }}" onsubmit="return confirm('Supprimer ce produit ?')" class="btn-action btn-delete" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Supprimer</button>
                    </form>
                </div>
            </div>
        @empty
            <p>Aucun produit disponible.</p>
        @endforelse
    </div>

    <!-- PAGINATION -->
    <div class="pagination">
        {{ $products->links() }}
    </div>

    <!-- BOUTON AJOUT PRODUIT -->
    <div class="create-annonce-section">
        <a href="{{ route('seller.produits.create') }}" class="btn-create-annonce">Ajouter un nouveau produit</a>
    </div>
</div>
@endsection