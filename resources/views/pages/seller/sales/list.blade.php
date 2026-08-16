@extends('layouts.connected')
@section('title', 'Mes Ventes - Olten')

@section('content')
<div class="breadcrumb">
    <a href="#">Accueil</a>
    <span>></span>
    <span>Mes Ventes</span>
</div>

<h1 class="page-title">Mes Ventes</h1>

<div class="annonces-container">
    <div class="section-header">
        <h2 class="section-title">Historique des ventes</h2>

        <div class="search-filters">
            <form method="GET" action="{{ route('seller.sales') }}">
                <input type="text" name="search" class="search-input" placeholder="Rechercher une vente" value="{{ request('search') }}">
                <select name="status" class="search-input">
                    <option value="">Tous</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Payé</option>
                </select>
                <button type="submit" class="btn-search">
                    <i class="fa-solid fa-search"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- LISTE DES VENTES -->
    <div class="annonces-list">
        @forelse ($sales as $sale)
            <div class="annonce-card">

                <div class="annonce-image">
                    <img src="{{ $sale->product->images->first() 
                        ? asset('storage/' . $sale->product->images->first()->image) 
                        : asset('assets/images/no-image.jpg') }}"
                         alt="{{ $sale->product->name }}">
                </div>

                <div class="annonce-details">
                    <h3 class="annonce-title">{{ $sale->product->name }}</h3>

                    <div class="annonce-tags">
                        <span class="tag tag-orange">
                            {{ $sale->product->category->nom ?? 'Catégorie non définie' }}
                        </span>
                    </div>

                    <div class="annonce-stats">
                        <span class="stat-item">
                            <i class="fas fa-user"></i> Acheteur : {{ $sale->buyer->fullname ?? $sale->buyer->email }}
                        </span>

                        <span class="stat-item">
                            <i class="fas fa-cubes"></i> Quantité : {{ $sale->quantity }}
                        </span>

                        <span class="stat-item">
                            <i class="fas fa-calendar-alt"></i> Date : {{ $sale->created_at->format('d/m/Y H:i') }}
                        </span>

                        <span class="stat-item">
                            <i class="fas fa-check-circle"></i> Statut : {{ ucfirst($sale->status) }}
                        </span>
                    </div>
                </div>

                <div class="annonce-actions">
                    <a href="{{ route('seller.sales.show', $sale) }}" class="btn-action btn-edit">Détails</a>
                </div>
            </div>
        @empty
            <p>Aucune vente enregistrée.</p>
        @endforelse
    </div>

    <div class="pagination">
        {{ $sales->links() }}
    </div>
</div>
@endsection