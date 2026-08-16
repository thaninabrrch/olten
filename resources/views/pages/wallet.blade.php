@extends('layouts.connected')
@section('title', 'Portefeuille | ' . config('app.name'))

@section('content')

<div class="breadcrumb">
    <a href="#">Accueil</a>
    <span>></span>
    <span>Portefeuille</span>
</div>

<h1 class="page-title">Portefeuille</h1>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">

    <!-- LOCATEUR -->
    @if($user->hasRole('locateur'))
        <div class="bg-blue-50 p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <i class="fa fa-home text-blue-600 text-2xl"></i>
                <div>
                    <p class="text-sm text-gray-500">Gains location</p>
                    <p class="text-xl font-bold">{{ number_format($adEarnings, 2) }} €</p>
                </div>
            </div>
        </div>
    @endif

    <!-- VENDEUR -->
    @if($user->hasRole('vendeur'))
        <div class="bg-green-50 p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <i class="fa fa-shopping-cart text-green-600 text-2xl"></i>
                <div>
                    <p class="text-sm text-gray-500">Gains ventes</p>
                    <p class="text-xl font-bold">{{ number_format($productEarnings, 2) }} €</p>
                </div>
            </div>
        </div>
    @endif

    <!-- LIVREUR -->
    @if($user->hasRole('livreur'))
        <div class="bg-orange-50 p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <i class="fa fa-truck text-orange-600 text-2xl"></i>
                <div>
                    <p class="text-sm text-gray-500">Gains livraisons</p>
                    <p class="text-xl font-bold">
                        {{ number_format($deliveryEarnings, 2) }} €
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>

@if(optional($user->ads)->count() > 0)
<div class="mt-8">
    <h2 class="text-lg font-bold mb-3">
        <i class="fa fa-home"></i> Annonces
    </h2>

    <div class="space-y-4">

        @foreach($user->ads as $ad)

        <div class="bg-white p-4 rounded-xl shadow border flex justify-between">
            <h3 class="font-bold">{{ $ad->title }}</h3>

            <span class="text-blue-600 font-bold">
                {{ number_format($ad->bookings->where('status','paid')->sum('total_price'), 2) }} €
            </span>
        </div>

        @endforeach

    </div>
</div>
@endif


@if($user->products->count() > 0)
<div class="mt-8">
    <h2 class="text-lg font-bold mb-3">
        <i class="fa fa-shopping-bag"></i> Produits
    </h2>

    <div class="space-y-4">

        @foreach($user->products as $product)

        <div class="bg-white p-4 rounded-xl shadow border flex justify-between">
            <h3 class="font-bold">{{ $product->name }}</h3>

            <span class="text-green-600 font-bold">
                {{ number_format($product->sales->where('status','paid')->sum('total_price'), 2) }} €
            </span>
        </div>

        @endforeach

    </div>
</div>
@endif

@endsection