@extends('layouts.connected')
@section('title', 'Portefeuille | ' . config('app.name'))

@php
    /*
     | Le controleur fournit les trois totaux deja calcules ; le solde affiche
     | est exactement la somme des lignes montrees en dessous.
     */
    $sources = [];

    // Une source est affichee si l'utilisateur a le role correspondant, ou
    // simplement si elle a rapporte quelque chose : le solde reste ainsi
    // toujours egal a la somme des lignes montrees.
    if ($user->hasRole('locateur') || (float) $adEarnings > 0) {
        $sources[] = ['Gains location', $adEarnings, 'is-blue', 'Réservations payées sur vos annonces'];
    }

    if ($user->hasRole('vendeur') || (float) $productEarnings > 0) {
        $sources[] = ['Gains ventes', $productEarnings, 'is-green', 'Commandes payées sur vos produits'];
    }

    if ($user->hasRole('livreur') || (float) $deliveryEarnings > 0) {
        $sources[] = ['Gains livraisons', $deliveryEarnings, 'is-brand', 'Courses marquées comme livrées'];
    }

    $total = collect($sources)->sum(fn ($s) => (float) $s[1]);
    $ads   = $user->ads ?? collect();
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Portefeuille</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Portefeuille</h1>
            <p class="sp-subtitle">Le détail de ce que votre activité vous a rapporté sur Olten.</p>
        </div>
    </header>

    {{-- Solde --}}
    <div class="sp-balance">
        <div>
            <span class="sp-balance-label">Total encaissé</span>
            <span class="sp-balance-value">{{ number_format($total, 2, ',', ' ') }} €</span>
            <span class="sp-balance-note">
                Somme des paiements confirmés sur l'ensemble de vos activités depuis la création de votre compte.
            </span>
        </div>

        <div class="sp-balance-side">
            <span>Compte</span>
            <strong>{{ $user->fullname ?? $user->name ?? $user->email }}</strong>
        </div>
    </div>

    {{-- Gains par activite --}}
    @if(count($sources))
        <div class="sp-stats is-auto">
            @foreach($sources as [$label, $amount, $tone, $help])
                <div class="sp-stat">
                    <span class="sp-stat-icon {{ $tone }}"><i class="fa-solid fa-euro-sign"></i></span>
                    <div>
                        <span class="sp-stat-value">{{ number_format((float) $amount, 2, ',', ' ') }} €</span>
                        <span class="sp-stat-label">
                            {{ $label }}
                            <small>{{ $help }}</small>
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Detail par annonce --}}
    @if($ads->count())
        <section class="sp-panel" style="margin-bottom:24px">
            <div class="sp-toolbar">
                <div>
                    <h2 class="sp-toolbar-title">Revenus par annonce</h2>
                    <span class="sp-count">{{ $ads->count() }} annonce{{ $ads->count() > 1 ? 's' : '' }} publiée{{ $ads->count() > 1 ? 's' : '' }}</span>
                </div>
            </div>

            <div class="sp-lines">
                @foreach($ads as $ad)
                    @php $earned = (float) $ad->bookings->where('status', 'paid')->sum('total_price'); @endphp

                    <div class="sp-line">
                        <a href="{{ route('ads.show', $ad) }}" class="sp-line-name">{{ $ad->title }}</a>
                        <span class="sp-line-value {{ $earned > 0 ? '' : 'is-muted' }}">
                            {{ number_format($earned, 2, ',', ' ') }} €
                        </span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Detail par produit --}}
    @if($user->products->count())
        <section class="sp-panel">
            <div class="sp-toolbar">
                <div>
                    <h2 class="sp-toolbar-title">Revenus par produit</h2>
                    <span class="sp-count">{{ $user->products->count() }} produit{{ $user->products->count() > 1 ? 's' : '' }} au catalogue</span>
                </div>
            </div>

            <div class="sp-lines">
                @foreach($user->products as $product)
                    @php $earned = (float) $product->sales->where('status', 'paid')->sum('total_price'); @endphp

                    <div class="sp-line">
                        <a href="{{ route('products.show', $product) }}" class="sp-line-name">{{ $product->name }}</a>
                        <span class="sp-line-value {{ $earned > 0 ? '' : 'is-muted' }}">
                            {{ number_format($earned, 2, ',', ' ') }} €
                        </span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Aucune activite --}}
    @if(! $ads->count() && ! $user->products->count())
        <section class="sp-panel">
            <div class="sp-empty">
                <x-empty-state
                    title="Aucun revenu pour le moment"
                    text="Publiez une annonce ou un produit : vos gains s'afficheront ici dès le premier paiement."
                    :action-url="route('home')"
                    action-label="Découvrir Olten" />
            </div>
        </section>
    @endif
</div>
@endsection
