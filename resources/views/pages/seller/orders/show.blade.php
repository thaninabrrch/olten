@extends('layouts.connected')
@section('title', 'Suivi de commande - Olten')

@section('content')
<div id="ajax-success-message" class="alert alert-success" style="display:none;">
</div>
<div class="breadcrumb">
    <a href="{{ url()->previous() }}">Les commandes</a>
    <span>></span>
    <span>Détail de la commande</span>
</div>

<h1 class="page-title">Suivi de la commande</h1>

<div class="annonces-container">

    <div class="annonce-card">

        <div class="annonce-image">
            <img src="{{ $order->product->images->first()
                ? asset('storage/' . $order->product->images->first()->image)
                : asset('assets/images/no-image.jpg') }}"
                alt="{{ $order->product->name }}">
        </div>

        <div class="annonce-details">

            <h2 class="annonce-title">
                {{ $order->product->name }}
            </h2>

            <div class="annonce-stats flex-wrap">

                <span class="stat-item">
                    <i class="fas fa-store"></i>
                    Vendeur :
                    {{ $order->seller->fullname ?? $order->seller->email }}
                </span>

                <span class="stat-item">
                    <i class="fas fa-money-bill"></i>
                    Montant :
                    {{ number_format($order->total_price, 2, ',', ' ') }} €
                </span>

                <span class="stat-item">
                    <i class="fas fa-box"></i>
                    Quantité :
                    {{ $order->quantity }}
                </span>

                <span class="stat-item">
                    <i class="fas fa-calendar-alt"></i>
                    Date :
                    {{ $order->created_at->format('d/m/Y H:i') }}
                </span>

                @php
                    $orderStatusFr = [
                        'pending'   => 'En attente',
                        'confirmed' => 'Confirmée',
                        'shipped'   => 'Expédiée',
                        'delivered' => 'Livrée',
                        'cancelled' => 'Annulée',
                    ];

                    $paymentStatusFr = [
                        'pending'  => 'En attente',
                        'paid'     => 'Payé',
                        'refunded' => 'Remboursé',
                        'failed'   => 'Échoué',
                    ];
                @endphp

                <span class="stat-item">
                    <i class="fas fa-check-circle"></i>
                    Statut commande :
                    <strong>
                        {{ $orderStatusFr[$order->order_status] ?? $order->order_status }}
                    </strong>
                </span>

                <span class="stat-item">
                    <i class="fas fa-credit-card"></i>
                    Paiement :
                    <strong>
                        {{ $paymentStatusFr[$order->status] ?? $order->status }}
                    </strong>
                </span>

                @if($order->delivery_requested)
                    <span class="stat-item">
                        <i class="fas fa-map-marker-alt"></i>
                        Adresse :
                        {{ $order->delivery_address }}
                    </span>
                @endif

            </div>
        </div>
    </div>

    @if($order->delivery_requested)

        <div class="annonce-card mt-4">

            <div class="annonce-details">

                <h3 class="section-title">
                    <i class="fas fa-truck"></i>
                    Suivi de livraison
                </h3>

                @if($order->delivery)

                    @php
                        $deliveryStatus = [
                            'pending' => 'En attente de prise en charge',
                            'picked_up' => 'Colis récupéré',
                            'in_transit' => 'En cours de livraison',
                            'delivered' => 'Livré',
                            'cancelled' => 'Livraison annulée',
                        ];
                    @endphp

                    <div class="annonce-stats flex-wrap">

                        @php
                            $deliveryStatusFr = [
                                'pending' => 'En attente de prise en charge',
                                'picked_up' => 'Colis récupéré',
                                'in_transit' => 'En cours de livraison',
                                'delivered' => 'Livré',
                                'cancelled' => 'Annulé',
                            ];
                        @endphp

                        <span class="stat-item">
                            <i class="fas fa-truck"></i>
                            Statut :
                            <strong>
                                {{ $deliveryStatusFr[$order->delivery->status] ?? $order->delivery->status }}
                            </strong>
                        </span>

                        @if($order->delivery->deliveryPerson)
                            <span class="stat-item">
                                <i class="fas fa-user"></i>
                                Livreur :
                                {{ $order->delivery->deliveryPerson->fullname ?? $order->delivery->deliveryPerson->email }}
                            </span>
                        @endif

                        @if($order->delivery->picked_up_at)
                            <span class="stat-item">
                                <i class="fas fa-box"></i>
                                Colis récupéré le :
                                {{ $order->delivery->picked_up_at->format('d/m/Y H:i') }}
                            </span>
                        @endif

                        @if($order->delivery->delivered_at)
                            <span class="stat-item">
                                <i class="fas fa-check-circle"></i>
                                Livré le :
                                {{ $order->delivery->delivered_at->format('d/m/Y H:i') }}
                            </span>
                        @endif

                    </div>

                    <hr class="my-4">

                    <div class="tracking-timeline">

                        <p>
                            {!! in_array($order->delivery->status, ['picked_up','in_transit','delivered']) ? '✅' : '⏳' !!}
                            Colis récupéré
                        </p>

                        <p>
                            {!! in_array($order->delivery->status, ['in_transit','delivered']) ? '✅' : '⏳' !!}
                            Livraison en cours
                        </p>

                        <p>
                            {!! $order->delivery->status === 'delivered' ? '✅' : '⏳' !!}
                            Livraison terminée
                        </p>

                    </div>

                @else

                    <div class="alert alert-info">
                        Aucun livreur n'a encore pris en charge cette commande.
                    </div>

                @endif

                @if($order->delivery && $order->delivery->status === 'delivered')
                    <hr class="my-4">
                    @php
                        $review = $order->delivery->reviews->where('user_id', auth()->id())->first();

                        $currentRating = $review->rating ?? 0;
                        $currentComment = $review->comment ?? '';
                    @endphp
                    <h4>Noter la livraison</h4>

                    <div class="stars-rating" data-delivery="{{ $order->delivery->id }}">

                        @for($i = 1; $i <= 5; $i++)
                            <i class="fa-star rating-star {{ $i <= $currentRating ? 'fas active' : 'far' }}" data-value="{{ $i }}"></i>
                        @endfor

                    </div>
                    <div class="mt-3">
                        <input type="hidden" id="rating-input" value="{{ $currentRating }}">
                        <textarea id="delivery-comment" class="form-control" rows="4" placeholder="Votre commentaire...">{{ $currentComment }}</textarea>
                        <div class="d-flex justify-content-end">
                            <button type="button" id="save-comment" class="btn-action btn-primary mt-3" data-delivery="{{ $order->delivery->id }}">
                                Envoyer
                            </button>
                        </div>
                    </div>
                @endif
            </div>

        </div>

    @endif

</div>
<script src="{{ asset('assets/js/delivery.js') }}"></script>
@endsection