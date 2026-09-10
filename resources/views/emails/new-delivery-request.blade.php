{{--
    Un livreur propose de prendre en charge une livraison : on previent le
    proprietaire du bien ou le vendeur du produit.

    Envoye par App\Notifications\NewDeliveryRequestNotification, qui passe le
    `deliveryRequest`, le `owner` (notifiable) et `isProduct` — vrai quand la
    demande porte sur une vente, faux quand elle porte sur une location.
--}}
@php
    $prenom = $owner->firstname ?: $owner->name;

    $livreur = $deliveryRequest->deliveryPerson;
    $nomLivreur = $livreur?->firstname
        ? trim($livreur->firstname . ' ' . ($livreur->lastname ?? ''))
        : $livreur?->name;

    $vente   = $isProduct ? $deliveryRequest->productSale : null;
    $location = $isProduct ? null : $deliveryRequest->booking;

    $objet = $isProduct
        ? ($vente?->product?->name ?? 'votre produit')
        : ($location?->ad?->title ?? 'votre annonce');

    // `start_date` et `end_date` sont castees en date sur Booking.
    $du = $location?->start_date?->format('d/m/Y');
    $au = $location?->end_date?->format('d/m/Y');
@endphp

<x-email.layout
    title="Nouvelle demande de livraison"
    :preheader="'Un livreur propose de prendre en charge la livraison de ' . ($objet) . '.'"
    eyebrow="Livraison"
    heading="Une demande de livraison vous attend"
    subheading="Un livreur propose de prendre en charge l'acheminement.">

    <x-email.text>Bonjour {{ $prenom }},</x-email.text>

    <x-email.text>
        {{ $nomLivreur ? $nomLivreur . ' vient' : 'Un livreur vient' }} de vous envoyer une demande
        pour livrer <strong style="color:#1f2328;">{{ $objet }}</strong>.
    </x-email.text>

    <x-email.panel title="Détails de la livraison">
        @if($isProduct)
            <x-email.row label="Produit" :value="$vente?->product?->name ?? '—'" />
            <x-email.row label="Quantité" :value="$vente?->quantity ?? '—'" />

            @if($vente?->delivery_address)
                <x-email.row label="Adresse de livraison" :value="$vente->delivery_address" />
            @endif
        @else
            <x-email.row label="Annonce" :value="$location?->ad?->title ?? '—'" />

            @if($du && $au)
                <x-email.row label="Période de location" :value="'Du ' . ($du) . ' au ' . ($au)" />
            @endif

            @if($location?->delivery_address)
                <x-email.row label="Adresse de livraison" :value="$location->delivery_address" />
            @endif
        @endif

        @if($nomLivreur)
            <x-email.row label="Livreur" :value="$nomLivreur" />
        @endif

        <x-email.row label="Statut" value="En attente de votre réponse" />
    </x-email.panel>

    <x-email.text>
        Acceptez ou refusez la demande depuis votre espace. Tant qu'elle est en attente,
        le livreur ne peut pas se mettre en route.
    </x-email.text>

    <x-email.button :url="route('livreur.ads.index')">
        Répondre à la demande
    </x-email.button>

    <x-email.note>
        Vous recevez cette notification parce que vous bénéficiez d'un
        <strong>abonnement Premium</strong> sur Olten.
    </x-email.note>

</x-email.layout>
