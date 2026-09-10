{{--
    Le proprietaire a accepte la reservation : on previent le locataire.
    Envoye par App\Mail\BookingAcceptedMail, qui expose `booking`.
--}}
@php
    $prenom = $booking->user?->firstname ?: ($booking->user?->name ?? '');

    $du = $booking->start_date?->format('d/m/Y');
    $au = $booking->end_date?->format('d/m/Y');
@endphp

<x-email.layout
    title="Réservation confirmée"
    :preheader="'Votre réservation ' . ($du ? 'du ' . $du . ' au ' . $au : '') . ' est confirmée.'"
    eyebrow="Réservation"
    heading="C'est confirmé !"
    subheading="Le propriétaire a accepté votre demande de réservation.">

    <x-email.text>Bonjour {{ $prenom }},</x-email.text>

    <x-email.text>
        Votre réservation pour <strong style="color:#1f2328;">{{ $booking->ad->title ?? 'cette annonce' }}</strong>
        est confirmée. Voici le récapitulatif à conserver.
    </x-email.text>

    <x-email.panel title="Votre réservation">
        <x-email.row label="Référence" :value="'#' . ($booking->id)" />
        <x-email.row label="Annonce" :value="$booking->ad->title ?? '—'" />

        @if($du)
            <x-email.row label="Début de location" :value="$du" />
        @endif

        @if($au)
            <x-email.row label="Fin de location" :value="$au" />
        @endif

        @if($booking->delivery_requested)
            <x-email.row label="Livraison" value="Prévue" />

            @if($booking->delivery_address)
                <x-email.row label="Adresse de livraison" :value="$booking->delivery_address" />
            @endif

            @if($booking->delivery_cost)
                <x-email.row label="Dont frais de livraison" :value="(number_format($booking->delivery_cost, 2, ',', ' ')) . ' €'" />
            @endif
        @endif

        @isset($booking->total_price)
            <x-email.row label="Montant total" :value="(number_format($booking->total_price, 2, ',', ' ')) . ' €'" total />
        @endisset
    </x-email.panel>

    <x-email.button :url="route('bookings.show', $booking)">
        Voir ma réservation
    </x-email.button>

    <x-email.note tone="success">
        Une question sur le bien ou sur la remise des clés ? Écrivez directement au propriétaire
        depuis la messagerie de votre espace Olten.
    </x-email.note>

</x-email.layout>
