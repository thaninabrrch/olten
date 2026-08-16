@extends('layouts.main')

@section('content')

<div class="container py-5">

    <div class="text-center mb-5">

        <h1 class="fw-bold mb-3">
            Choisissez votre abonnement
        </h1>

        <p class="text-muted fs-5 mb-4">
            Sélectionnez l'offre qui correspond le mieux à vos besoins.
        </p>

        <div class="alert d-inline-flex align-items-center shadow-sm px-4 py-3"
             style="background:#fff8f4;border:1px solid #ff5a1f;border-radius:12px;max-width:900px;">

            <i class="fas fa-exclamation-circle me-3"
               style="font-size:24px;color:#ff5a1f;"></i>

            <div class="text-start">
                <strong style="color:#ff5a1f;">
                    Cette étape est importante et obligatoire.
                </strong>
                <br>
                Vous devez choisir un abonnement afin d'accéder aux fonctionnalités d'Olten et de commencer à utiliser la plateforme.
            </div>

        </div>

    </div>

    <div class="row justify-content-center g-4">

        @foreach($subscriptions as $subscription)

            <div class="col-lg-4">

                <div class="card h-100 shadow-sm border-0 rounded-4"
                    @if($subscription->slug == 'premium')
                        style="border: solid #ff5a1f !important;"
                    @endif>

                    <div class="card-body p-5">

                        <div class="text-center">

                            @if($subscription->slug == 'premium')
                                <span class="badge rounded-pill px-3 py-2 mb-4"
                                    style="background:#ff5a1f;font-size:.9rem;">
                                    Le plus populaire
                                </span>
                            @endif

                            <h2 class="fw-bold mb-4">
                                {{ $subscription->name }}
                            </h2>

                            <h1 class="fw-bold mb-4">
                                {{ number_format($subscription->price, 2, ',', ' ') }} €/mois
                            </h1>

                        </div>

                        <ul class="list-unstyled mb-5">

                            @switch($subscription->slug)

                                @case('standard')

                                    <li class="mb-3">✅ Consultation illimitée des annonces</li>
                                    <li class="mb-3">✅ Dépôt d'annonces illimité</li>
                                    <li class="mb-3">✅ Répondre aux annonces via la messagerie</li>
                                    <li class="mb-3">✅ Photos illimitées</li>
                                    <li class="mb-3 text-muted">❌ Mise en avant gratuite</li>
                                    <li class="mb-3 text-muted">❌ Notifications des nouvelles offres</li>

                                @break

                                @case('premium')

                                    <li class="mb-3">✅ Consultation illimitée des annonces</li>
                                    <li class="mb-3">✅ Dépôt d'annonces illimité</li>
                                    <li class="mb-3">✅ Répondre aux annonces via la messagerie</li>
                                    <li class="mb-3">✅ Photos illimitées</li>
                                    <li class="mb-3">✅ 2 mises en avant gratuites / mois</li>
                                    <li class="mb-3 text-muted">❌ Notifications des nouvelles offres</li>

                                @break

                                @case('vip')

                                    <li class="mb-3">✅ Consultation illimitée des annonces</li>
                                    <li class="mb-3">✅ Dépôt d'annonces illimité</li>
                                    <li class="mb-3">✅ Répondre aux annonces via la messagerie</li>
                                    <li class="mb-3">✅ Photos illimitées</li>
                                    <li class="mb-3">✅ 5 mises en avant gratuites / mois</li>
                                    <li class="mb-3">✅ Notifications des nouvelles offres</li>
                                    <li class="mb-3">✅ Tous les rôles débloqués</li>
                                    <li class="mb-3">✅ Support prioritaire</li>

                                @break

                            @endswitch

                        </ul>

                        <form action="{{ route('subscriptions.select', $subscription->slug) }}" method="POST">

                            @csrf

                            <button class="btn btn-olten w-100 py-3">
                                Choisir
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        @endforeach

    </div>

    <div class="d-flex justify-content-center mt-4">

        <form action="{{ route('subscriptions.select', 'free') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-secondary px-5 py-3">
                Continuer avec un compte gratuit
            </button>
        </form>

    </div>

</div>

<style>

.btn-olten{
    background:#ff5a1f;
    border:1px solid #ff5a1f;
    color:#fff;
    font-size:18px;
    font-weight:600;
    border-radius:12px;
    transition:.3s;
}

.btn-olten:hover{
    background:#e84f17;
    border-color:#e84f17;
    color:#fff;
    transform:translateY(-2px);
}

.card{
    transition:.3s;
}

.card:hover{
    transform:translateY(-6px);
    box-shadow:0 15px 40px rgba(0,0,0,.08)!important;
}

</style>

@endsection