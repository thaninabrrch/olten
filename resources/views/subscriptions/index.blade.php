@extends('layouts.main')

@section('title', 'Abonnements — Olten.fr')

@push('styles')
    <link rel="stylesheet"
          href="{{ asset('assets/css/subscriptions.css') }}?v={{ @filemtime(public_path('assets/css/subscriptions.css')) ?: 1 }}">
@endpush

@section('content')

<div class="olten-plans">
    <div class="container">

        <header class="olten-plans-head">
            <span class="olten-plans-eyebrow">Abonnements</span>

            <h1 class="olten-plans-title">Choisissez votre abonnement</h1>

            <p class="olten-plans-subtitle">
                Sélectionnez l'offre qui correspond le mieux à vos besoins.
                Sans engagement, résiliable à tout moment.
            </p>

            <div class="olten-plans-notice" role="note">
                <i class="fas fa-circle-info" aria-hidden="true"></i>
                <div>
                    <strong>Cette étape est importante et obligatoire.</strong>
                    <p>
                        Vous devez choisir un abonnement afin d'accéder aux fonctionnalités
                        d'Olten et de commencer à utiliser la plateforme.
                    </p>
                </div>
            </div>
        </header>

        {{-- Le flux Stripe renvoie ici avec un message d'erreur en session
             (paiement non confirme, abonnement introuvable...). layouts.main
             n'affiche pas les messages flash : la page s'en charge. --}}
        @if (session('error'))
            <div class="olten-plans-alert is-error" role="alert">
                <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if (session('success'))
            <div class="olten-plans-alert is-success" role="status">
                <i class="fas fa-circle-check" aria-hidden="true"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="olten-plans-grid">

            @foreach($subscriptions as $subscription)

                @php
                    // Standard = forfait le plus populaire
                    $isFeatured = $subscription->slug === 'standard';

                    // La description en base sert de phrase de presentation : seule
                    // sa premiere ligne est retenue, les suivantes ne sont pas
                    // affichees sur cette page.
                    $accroche = trim(strtok((string) $subscription->description, "\r\n"));

                    $gratuit = (float) $subscription->price <= 0;
                @endphp

                <article class="olten-plan @if($isFeatured) is-featured @endif">

                    @if($isFeatured)
                        <span class="olten-plan-flag">
                            <i class="fas fa-star" aria-hidden="true"></i>
                            Le plus populaire
                        </span>
                    @endif

                    <div class="olten-plan-head">
                        <h2 class="olten-plan-name">{{ $subscription->name }}</h2>
                        <p class="olten-plan-desc">{{ $accroche }}</p>
                    </div>

                    <div class="olten-plan-price">
                        {{-- « 0 € » plutot que « Gratuit » : le nom de l'offre le dit
                             deja juste au-dessus, et un montant garde les quatre
                             cartes lisibles sur la meme ligne. --}}
                        <span class="olten-plan-amount">
                            {{ $gratuit ? '0 €' : number_format($subscription->price, 2, ',', ' ') . ' €' }}
                        </span>

                        <span class="olten-plan-period">/ mois</span>
                    </div>

                    <form class="olten-plan-action"
                          action="{{ route('subscriptions.select', $subscription->slug) }}" method="POST">
                        @csrf

                        <button type="submit"
                                class="olten-plan-btn @unless($isFeatured) is-ghost @endunless">
                            Choisir {{ $subscription->name }}
                        </button>
                    </form>

                </article>

            @endforeach

        </div>

        <div class="olten-plans-footer">
            <div class="olten-plans-trust">
                <span>
                    <i class="fas fa-lock" aria-hidden="true"></i>
                    Paiement sécurisé par Stripe
                </span>
                <span>
                    <i class="fas fa-rotate-left" aria-hidden="true"></i>
                    Sans engagement, résiliable à tout moment
                </span>
                <span>
                    <i class="fas fa-headset" aria-hidden="true"></i>
                    Une question ?
                    <a href="{{ route('contact') }}">Contactez-nous</a>
                </span>
            </div>
        </div>

    </div>
</div>

@endsection
