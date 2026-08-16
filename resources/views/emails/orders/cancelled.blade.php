@component('mail::message')
# Commande annulée

Bonjour {{ $order->buyer->fullname ?? $order->buyer->email }},

Votre commande pour **{{ $order->product->name }}** a été annulée par le vendeur.

@isset($order->status)
@if($order->status === 'refunded')
Le paiement a été remboursé.
@endif
@endisset

Merci de votre confiance,<br>
{{ config('app.name') }}
@endcomponent