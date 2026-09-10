{{--
    Le vendeur a annule la commande : on previent l'acheteur.
    Envoye par App\Mail\OrderCancelledMail, qui expose `order` (ProductSale).

    Cette vue etait la seule en Markdown : elle sortait avec l'habillage bleu
    par defaut de Laravel, sans rapport avec le reste des e-mails Olten.
--}}
@php
    $acheteur = $order->buyer;
    $prenom = $acheteur?->firstname ?: ($acheteur?->name ?? '');

    $rembourse = ($order->status ?? null) === 'refunded';
@endphp

<x-email.layout
    title="Commande annulée"
    :preheader="'Votre commande ' . ($order->product->name ?? '') . ' a été annulée par le vendeur.'"
    eyebrow="Commande"
    heading="Votre commande a été annulée"
    subheading="Le vendeur n'a pas pu honorer cette commande.">

    <x-email.text>Bonjour {{ $prenom }},</x-email.text>

    <x-email.text>
        Votre commande de <strong style="color:#1f2328;">{{ $order->product->name ?? 'ce produit' }}</strong>
        a été annulée par le vendeur.
    </x-email.text>

    <x-email.panel title="Commande concernée">
        <x-email.row label="Référence" :value="'#' . ($order->id)" />
        <x-email.row label="Produit" :value="$order->product->name ?? '—'" />
        <x-email.row label="Quantité" :value="$order->quantity" />
        <x-email.row label="Montant" :value="(number_format($order->total_price, 2, ',', ' ')) . ' €'" total />
    </x-email.panel>

    @if($rembourse)
        <x-email.note tone="success">
            Le paiement a été <strong>remboursé</strong>. Comptez quelques jours ouvrés
            pour le voir apparaître sur votre relevé bancaire.
        </x-email.note>
    @else
        <x-email.note tone="warning">
            Si un paiement a été effectué, le remboursement est traité automatiquement.
            Comptez quelques jours ouvrés pour le voir apparaître sur votre relevé bancaire.
        </x-email.note>
    @endif

    <x-email.text>
        D'autres vendeurs proposent des produits similaires sur la plateforme.
    </x-email.text>

    <x-email.button :url="route('search', array_filter(['category' => $order->product?->category?->slug, 'type' => 'produit']))">
        Voir des produits similaires
    </x-email.button>

    <x-email.button :url="route('orders.show', $order)" variant="ghost">
        Voir la commande annulée
    </x-email.button>

</x-email.layout>
