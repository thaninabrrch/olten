{{--
    Un acheteur vient de commander un produit : on previent le vendeur.
    Envoye par App\Notifications\NewOrderNotification, qui passe la `sale`
    (ProductSale) et le `seller` (le notifiable).
--}}
@php
    $prenom = $seller->firstname ?: $seller->name;

    // Sans le nom de l'acheteur ni la reference, le vendeur ne peut pas
    // retrouver la commande dans sa liste ni preparer l'envoi.
    $acheteur = $sale->buyer;
    $nomAcheteur = $acheteur?->firstname
        ? trim($acheteur->firstname . ' ' . ($acheteur->lastname ?? ''))
        : $acheteur?->name;
@endphp

<x-email.layout
    title="Nouvelle commande"
    :preheader="($sale->product->name ?? 'Un de vos produits') . ' vient d\'être commandé sur Olten.'"
    eyebrow="Commande"
    heading="Vous avez une nouvelle commande"
    subheading="Un acheteur vient de commander un de vos produits sur Olten.">

    <x-email.text>Bonjour {{ $prenom }},</x-email.text>

    <x-email.text>
        Votre produit <strong style="color:#1f2328;">{{ $sale->product->name ?? 'sans nom' }}</strong>
        vient d'être commandé. Voici le détail de la commande.
    </x-email.text>

    <x-email.panel title="Détails de la commande">
        <x-email.row label="Référence" :value="'#' . ($sale->id)" />
        <x-email.row label="Produit" :value="$sale->product->name ?? '—'" />
        <x-email.row label="Quantité" :value="$sale->quantity" />

        @if($nomAcheteur)
            <x-email.row label="Acheteur" :value="$nomAcheteur" />
        @endif

        @if($sale->delivery_requested)
            <x-email.row label="Livraison" value="Demandée par l'acheteur" />

            @if($sale->delivery_address)
                <x-email.row label="Adresse de livraison" :value="$sale->delivery_address" />
            @endif
        @endif

        @if($sale->phone)
            <x-email.row label="Téléphone" :value="$sale->phone" />
        @endif

        <x-email.row label="Montant total" :value="(number_format($sale->total_price, 2, ',', ' ')) . ' €'" total />
    </x-email.panel>

    <x-email.text>
        Confirmez la commande depuis votre espace vendeur pour que l'acheteur soit prévenu
        et que la préparation puisse commencer.
    </x-email.text>

    <x-email.button :url="route('seller.clientOrders')">
        Voir la commande
    </x-email.button>

    <x-email.note>
        Vous recevez cette notification parce que vous bénéficiez d'un
        <strong>abonnement Premium</strong> sur Olten.
    </x-email.note>

</x-email.layout>
