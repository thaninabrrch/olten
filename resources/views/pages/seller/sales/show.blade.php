@extends('layouts.connected')
@section('title', 'Détails de la vente - Olten')

@section('content')

<div class="breadcrumb">
    <a href="#">Accueil</a>
    <span>></span>
    <a href="{{ route('seller.sales') }}">Mes ventes</a>
    <span>></span>
    <span>Détails</span>
</div>

<h1 class="page-title">Détails de la vente</h1>

<div class="annonce-card d-flex gap-4 p-3">

    <!-- IMAGE PRODUIT -->
    <div class="annonce-image">
        <img src="{{ $sale->product->images->first() 
            ? asset('storage/' . $sale->product->images->first()->image) 
            : asset('assets/images/no-image.jpg') }}"
             alt="{{ $sale->product->name }}">
    </div>

    <!-- INFOS -->
    <div class="annonce-details w-100">

        <h2>{{ $sale->product->name }}</h2>

        <div class="annonce-tags mb-2">
            <span class="tag tag-orange">
                {{ $sale->product->category->nom ?? 'Catégorie non définie' }}
            </span>
        </div>

        <hr>

        <!-- INFOS ACHETEUR -->
        <h4><i class="fas fa-user"></i> Acheteur</h4>
        <p>
            {{ $sale->buyer->fullname ?? 'Utilisateur inconnu' }} <br>
            {{ $sale->buyer->email ?? '' }}
        </p>

        <!-- INFOS VENTE -->
        <h4 class="mt-3"><i class="fas fa-cubes"></i> Détails de la commande</h4>

        <div class="annonce-stats">
            <span class="stat-item">
                <i class="fas fa-cubes"></i> Quantité : {{ $sale->quantity }}
            </span>

            <span class="stat-item">
                <i class="fas fa-money-bill-wave"></i>
                Prix unitaire : {{ number_format($sale->product->price, 2) }} €
            </span>

            <span class="stat-item">
                <i class="fas fa-euro-sign"></i>
                Total : {{ number_format($sale->total_price, 2) }} €
            </span>

            <span class="stat-item">
                <i class="fas fa-calendar-alt"></i>
                Date : {{ $sale->created_at->format('d/m/Y H:i') }}
            </span>

            <span class="stat-item">
                <i class="fas fa-check-circle"></i>
                Statut :
                <span class="badge 
                    {{ $sale->status == 'paid' ? 'bg-success' : 'bg-warning' }}">
                    {{ ucfirst($sale->status) }}
                </span>
            </span>
        </div>

        <hr>

        <div class="mt-3 d-flex gap-2">
            <a href="{{ route('seller.sales') }}" class="btn btn-secondary">
                <i class="fas fa-long-arrow-alt-left"></i> Retour
            </a>

            @if($sale->status !== 'paid')
                <form method="POST" action="{{ route('seller.sales.paid', $sale) }}">
                    @csrf
                    <button class="btn btn-success">
                        Marquer comme payé
                    </button>
                </form>
            @endif

            @if($sale->status !== 'delivered')
                <form method="POST" action="{{ route('seller.sales.delivered', $sale) }}">
                    @csrf
                    <button class="btn btn-success">
                        <i class="fas fa-location-arrow"></i> Marquer comme livré
                    </button>
                </form>
            @endif

            <a href="{{ route('seller.sales.invoice', $sale) }}" class="btn btn-dark">
                <i class="fas fa-file-invoice-dollar"></i> Télécharger facture
            </a>
        </div>

    </div>
</div>

@endsection