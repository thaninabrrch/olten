{{--
    Le vendeur a accepte la commande : on previent l'acheteur.
    Envoye par App\Mail\OrderAcceptedMail, qui expose `order` (ProductSale).
--}}
@php
    // La table `users` n'a pas de colonne `fullname` et le modele n'expose pas
    // d'accesseur du meme nom : l'ancien `$order->buyer->fullname` valait
    // toujours null et retombait silencieusement sur `name`. On compose donc
    // le nom a partir des colonnes qui existent vraiment.
    $acheteur = $order->buyer;
    $prenom = $acheteur?->firstname ?: ($acheteur?->name ?? '');
@endphp

<x-email.layout
    title="Commande acceptée"
    :preheader="'Votre commande ' . ($order->product->name ?? '') . ' a été acceptée par le vendeur.'"
    eyebrow="Commande"
    heading="Votre commande est acceptée"
    subheading="Le vendeur a confirmé votre commande et prépare son envoi.">

    <x-email.text>Bonjour {{ $prenom }},</x-email.text>

    <x-email.text>
        Bonne nouvelle : votre commande de
        <strong style="color:#1f2328;">{{ $order->product->name ?? 'ce produit' }}</strong>
        a été acceptée par le vendeur.
    </x-email.text>

    <x-email.panel title="Récapitulatif">
        <x-email.row label="Référence" :value="'#' . ($order->id)" />
        <x-email.row label="Produit" :value="$order->product->name ?? '—'" />
        <x-email.row label="Quantité" :value="$order->quantity" />

        @if($order->delivery_requested && $order->delivery_address)
            <x-email.row label="Livraison à" :value="$order->delivery_address" />
        @endif

        <x-email.row label="Montant" :value="(number_format($order->total_price, 2, ',', ' ')) . ' €'" total />
    </x-email.panel>

    @if($order->delivery_requested)
        <x-email.note>
            Une demande de livraison a été enregistrée : un livreur va prendre votre commande
            en charge. Vous serez prévenu dès qu'elle sera en route.
        </x-email.note>
    @endif

    <x-email.button :url="route('orders.show', $order)">
        Suivre ma commande
    </x-email.button>

</x-email.layout>
