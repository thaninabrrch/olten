@extends('layouts.connected')
@section('title', 'Suivi de réservation - Olten')

@section('content')

<div class="breadcrumb">
    <a href="{{ url()->previous() }}">les réservations</a>
    <span>></span>
    <span>Détail réservation</span>
</div>

<h1 class="page-title">Suivi de la réservation</h1>

<div class="annonces-container">

    <div class="annonce-card">

        <div class="annonce-details">

            <h2 class="annonce-title">
                {{ $booking->ad->title }}
            </h2>

            <div class="annonce-stats flex-wrap">

                <span class="stat-item">
                    <i class="fas fa-user"></i>
                    Propriétaire :
                    {{ $booking->ad->user->fullname ?? $booking->ad->user->email }}
                </span>

                <span class="stat-item">
                    <i class="fas fa-calendar-alt"></i>
                    Début :
                    {{ \Carbon\Carbon::parse($booking->start_date)->format('d/m/Y') }}
                </span>

                <span class="stat-item">
                    <i class="fas fa-calendar-check"></i>
                    Fin :
                    {{ \Carbon\Carbon::parse($booking->end_date)->format('d/m/Y') }}
                </span>

                <span class="stat-item">
                    <i class="fas fa-money-bill"></i>
                    Total :
                    {{ number_format($booking->total_price, 2, ',', ' ') }} €
                </span>

                @php
                    $bookingStatusFr = [
                        'pending' => 'En attente',
                        'confirmed' => 'Confirmée',
                        'completed' => 'Terminée',
                        'cancelled' => 'Annulée',
                    ];

                    $paymentStatusFr = [
                        'pending' => 'En attente',
                        'paid' => 'Payé',
                        'refunded' => 'Remboursé',
                        'failed' => 'Échoué',
                    ];
                @endphp

                <span class="stat-item">
                    <i class="fas fa-check-circle"></i>
                    Réservation :
                    <strong>
                        {{ $bookingStatusFr[$booking->booking_status] ?? $booking->booking_status }}
                    </strong>
                </span>

                <span class="stat-item">
                    <i class="fas fa-credit-card"></i>
                    Paiement :
                    <strong>
                        {{ $paymentStatusFr[$booking->status] ?? $booking->status }}
                    </strong>
                </span>

                @if($booking->delivery_requested)
                    <span class="stat-item">
                        <i class="fas fa-map-marker-alt"></i>
                        Adresse :
                        {{ $booking->delivery_address }}
                    </span>
                @endif

            </div>

        </div>

    </div>

    @if($booking->delivery_requested)

        <div class="annonce-card mt-4">

            <div class="annonce-details">

                <h3 class="section-title">
                    <i class="fas fa-truck"></i>
                    Suivi de livraison
                </h3>

                @if($booking->delivery)

                    @php
                        $deliveryStatusFr = [
                            'pending' => 'En attente de prise en charge',
                            'picked_up' => 'Colis récupéré',
                            'in_transit' => 'En cours de livraison',
                            'delivered' => 'Livré',
                            'cancelled' => 'Livraison annulée',
                        ];
                    @endphp

                    <div class="annonce-stats flex-wrap">

                        <span class="stat-item">
                            <i class="fas fa-truck"></i>
                            Statut :
                            <strong>
                                {{ $deliveryStatusFr[$booking->delivery->status] ?? $booking->delivery->status }}
                            </strong>
                        </span>

                        @if($booking->delivery->deliveryPerson)
                            <span class="stat-item">
                                <i class="fas fa-user"></i>
                                Livreur :
                                {{ $booking->delivery->deliveryPerson->fullname ?? $booking->delivery->deliveryPerson->email }}
                            </span>
                        @endif

                        @if($booking->delivery->picked_up_at)
                            <span class="stat-item">
                                <i class="fas fa-box"></i>
                                Colis récupéré le :
                                {{ $booking->delivery->picked_up_at->format('d/m/Y H:i') }}
                            </span>
                        @endif

                        @if($booking->delivery->delivered_at)
                            <span class="stat-item">
                                <i class="fas fa-check-circle"></i>
                                Livré le :
                                {{ $booking->delivery->delivered_at->format('d/m/Y H:i') }}
                            </span>
                        @endif

                    </div>

                    <hr class="my-4">

                    <div class="tracking-timeline">

                        <p>
                            {!! in_array($booking->delivery->status, ['picked_up','in_transit','delivered']) ? '✅' : '⏳' !!}
                            Colis récupéré
                        </p>

                        <p>
                            {!! in_array($booking->delivery->status, ['in_transit','delivered']) ? '✅' : '⏳' !!}
                            Livraison en cours
                        </p>

                        <p>
                            {!! $booking->delivery->status === 'delivered' ? '✅' : '⏳' !!}
                            Livraison terminée
                        </p>

                    </div>

                @else

                    <div class="alert alert-info">
                        Aucun livreur n'a encore pris en charge cette réservation.
                    </div>

                @endif

            </div>

        </div>
            @if($booking->delivery && $booking->delivery->status === 'delivered')

        <hr class="my-4">

        @php
            $currentRating = optional($booking->delivery->reviews->where('user_id', auth()->id())->first())->rating ?? 0;

            $currentComment = optional($booking->delivery->reviews->where('user_id', auth()->id())->first())->comment ?? '';
        @endphp

        <h4>Noter la livraison</h4>

        <div class="stars-rating mt-2" data-delivery="{{ $booking->delivery->id }}">

            @for($i = 1; $i <= 5; $i++)
                <i class="fa-star rating-star {{ $i <= $currentRating ? 'fas active' : 'far' }}" data-value="{{ $i }}"></i>
            @endfor

        </div>
        <div class="mt-3">
            <input type="hidden" id="rating-input" value="{{ $currentRating }}">
            <textarea id="delivery-comment" class="form-control" rows="4" placeholder="Votre commentaire...">{{ $currentComment }}</textarea>
            <div class="d-flex justify-content-end">
                <button type="button" id="save-comment" class="btn-action btn-primary mt-3" data-delivery="{{ $booking->delivery->id }}">
                    Envoyer
                </button>
            </div>
        </div>

    @endif

    @endif
</div>
<script src="{{ asset('assets/js/delivery.js') }}"></script>
@endsection