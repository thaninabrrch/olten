@extends('layouts.connected')
@section('title', 'Mes Commandes - Olten')

@section('content')
<div class="breadcrumb">
    <a href="#">Accueil</a>
    <span>></span>
    <span>Mes Commandes</span>
</div>

<h1 class="page-title">Mes Commandes</h1>

<div class="annonces-container">
    <div class="section-header">
        <h2 class="section-title">Historique des commandes</h2>

        <div class="search-filters">
            <form method="GET" action="{{ route('seller.clientOrders') }}">
                <input type="text" name="search" class="search-input" placeholder="Rechercher une commande" value="{{ request('search') }}">

                <select name="status" class="search-input">
                    <option value="">Tous</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Expédiée</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Livrée</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                </select>

                <button type="submit" class="btn-search">
                    <i class="fa-solid fa-search"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- LISTE DES COMMANDES -->
    <div class="annonces-list">
        @forelse($orders as $order)
            <div class="annonce-card">

                <div class="annonce-image">
                    <img src="{{ $order->product->images->first() 
                        ? asset('storage/' . $order->product->images->first()->image) 
                        : asset('assets/images/no-image.jpg') }}"
                         alt="{{ $order->product->name }}">
                </div>

                <div class="annonce-details">
                    <div class="d-flex justify-content-between">
                        <h3 class="annonce-title">{{ $order->product->name }}</h3>
                        @if($order->delivery_requested)
                            <div class="annonce-actions">
                                <a href="{{ route('orders.show', $order->id) }}"
                                class="btn-action btn-primary">
                                    <i class="fas fa-truck"></i>
                                    Suivre la commande
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="annonce-tags">
                        <span class="tag tag-orange">
                            {{ $order->product->category->nom ?? 'Catégorie non définie' }}
                        </span>
                    </div>

                    <div class="annonce-stats">
                        <span class="stat-item">
                            <i class="fas fa-user"></i> Acheteur : 
                            {{ $order->buyer->fullname ?? $order->buyer->email }}
                        </span>

                        <span class="stat-item">
                            <i class="fas fa-money-bill"></i> Total : {{ $order->total_price }} €
                        </span>

                        <span class="stat-item">
                            <i class="fas fa-calendar-alt"></i> Date : 
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </span>
                        @php
                            $statusFr = [
                                'pending'   => 'En attente',
                                'confirmed' => 'Confirmée',
                                'shipped'   => 'Expédiée',
                                'delivered' => 'Livrée',
                                'cancelled' => 'Annulée',
                                'en_cours'  => 'En cours',
                            ];
                        @endphp

                        <span class="stat-item">
                            <i class="fas fa-check-circle"></i> Statut : 
                            <strong>{{ $statusFr[$order->order_status] ?? $order->order_status }}</strong>
                        </span>
                    </div>
                </div>

                <div class="annonce-actions">
                    @if($order->order_status == 'pending' && $order->status == 'paid')
                        <form action="{{ route('seller.orders.confirm', $order->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-action btn-success">
                                <i class="fas fa-check"></i> Accepter
                            </button>
                        </form>
                    @endif
                    @if(!in_array($order->order_status, ['delivered', 'cancelled']))
                        <form action="{{ route('seller.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment annuler cette commande ?')">
                            @csrf
                            <button type="submit" class="btn-action btn-cancel">
                                <i class="fas fa-times-circle"></i> Annuler
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p>Aucune commande enregistrée.</p>
        @endforelse
    </div>

    <div class="pagination">
        {{ $orders->links() }}
    </div>
</div>
@endsection