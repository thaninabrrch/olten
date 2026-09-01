@extends('layouts.main')

@section('title', 'Abonnements — Olten.fr')

@push('styles')
    <link rel="stylesheet"
          href="{{ asset('assets/css/subscriptions.css') }}?v={{ @filemtime(public_path('assets/css/subscriptions.css')) ?: 1 }}">
@endpush

@section('content')

@php
    /*
    | Matrice des fonctionnalites.
    |
    | Les trois cartes rendent EXACTEMENT les memes lignes, dans le meme
    | ordre : c'est ce qui permet de comparer les offres horizontalement,
    | d'une carte a l'autre. Une valeur `false` affiche la ligne grisee.
    |
    | Le libelle peut dependre de l'offre (les mises en avant), d'ou la
    | valeur texte plutot qu'un simple booleen.
    */
    $features = [
        [
            'label'  => 'Consultation illimitée des annonces',
            'plans'  => ['standard' => true, 'premium' => true, 'vip' => true],
        ],
        [
            'label'  => 'Dépôt d\'annonces illimité',
            'plans'  => ['standard' => true, 'premium' => true, 'vip' => true],
        ],
        [
            'label'  => 'Répondre aux annonces via la messagerie',
            'plans'  => ['standard' => true, 'premium' => true, 'vip' => true],
        ],
        [
            'label'  => 'Photos illimitées',
            'plans'  => ['standard' => true, 'premium' => true, 'vip' => true],
        ],
        [
            'label'  => 'Mise en avant gratuite',
            'plans'  => [
                'standard' => false,
                'premium'  => '2 mises en avant gratuites / mois',
                'vip'      => '5 mises en avant gratuites / mois',
            ],
        ],
        [
            'label'  => 'Notifications des nouvelles offres',
            'plans'  => ['standard' => false, 'premium' => false, 'vip' => true],
        ],
        [
            'label'  => 'Tous les rôles débloqués',
            'plans'  => ['standard' => false, 'premium' => false, 'vip' => true],
        ],
        [
            'label'  => 'Support prioritaire',
            'plans'  => ['standard' => false, 'premium' => false, 'vip' => true],
        ],
    ];
@endphp

<div class="olten-plans">

    <div class="container">

        <div class="olten-plans-head">

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

        </div>

        {{-- Le flux Stripe renvoie ici avec un message d'erreur en session
             (paiement non confirme, abonnement introuvable...). layouts.main
             n'affiche pas les messages flash : la page s'en charge. --}}
        @if (session('error'))
            <div class="olten-plans-alert" role="alert">
                <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if (session('success'))
            <div class="olten-plans-alert" role="status"
                 style="border-color:#a8d8bd;background:#eef8f2;color:#1f7a4d;">
                <i class="fas fa-circle-check" aria-hidden="true"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="olten-plans-grid">

            @foreach($subscriptions as $subscription)

                @php
                    // Standard = forfait le plus populaire
                    $isFeatured = $subscription->slug === 'standard';

                    // Récupérer les lignes de la description depuis la BDD
                    $descriptionLines = preg_split(
                        "/\r\n|\r|\n/",
                        $subscription->description,
                        -1,
                        PREG_SPLIT_NO_EMPTY
                    );

                    // La première ligne est la phrase de présentation
                    // Les lignes suivantes correspondent aux fonctionnalités
                    $description = $descriptionLines[0] ?? '';
                    $features = array_slice($descriptionLines, 1);
                @endphp

                <div class="olten-plan @if($isFeatured) is-featured @endif">

                    @if($isFeatured)
                        <span class="olten-plan-flag">
                            <i class="fas fa-star" aria-hidden="true"></i>
                            Le plus populaire
                        </span>
                    @endif

                    <h2 class="olten-plan-name">
                        {{ $subscription->name }}
                    </h2>

                    <p class="olten-plan-desc">
                        {{ $description }}
                    </p>

                    <div class="olten-plan-price">
                        <span class="olten-plan-amount">
                            {{ number_format($subscription->price, 2, ',', ' ') }} €
                        </span>

                        <span class="olten-plan-period">
                            / mois
                        </span>
                    </div>

                    <ul class="olten-plan-features">

                        @foreach($features as $feature)

                            <li>
                                <i class="fas fa-check" aria-hidden="true"></i>
                                <span>{{ $feature }}</span>
                            </li>

                        @endforeach

                    </ul>

                    <form action="{{ route('subscriptions.select', $subscription->slug) }}"
                        method="POST">

                        @csrf

                        <button type="submit"
                                class="olten-plan-btn @if(!$isFeatured) is-ghost @endif">
                            Choisir {{ $subscription->name }}
                        </button>

                    </form>

                </div>

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
