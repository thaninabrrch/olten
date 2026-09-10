{{--
    Le proprietaire d'une annonce vient de recevoir une reservation.
    Envoye par App\Notifications\NewBookingNotification, qui passe le
    `booking` et le `owner` (le notifiable).
--}}
@php
    $prenom = $owner->firstname ?: $owner->name;

    // Le proprietaire peut avoir plusieurs reservations sur la meme annonce :
    // sans le nom du locataire ni la reference, il ne sait pas laquelle
    // ouvrir dans sa liste.
    $locataire = $booking->user;
    $nomLocataire = $locataire?->firstname
        ? trim($locataire->firstname . ' ' . ($locataire->lastname ?? ''))
        : $locataire?->name;

    // `start_date` et `end_date` sont castees en date sur le modele Booking :
    // ce sont donc deja des Carbon, pas des chaines a reparser.
    $du = $booking->start_date?->format('d/m/Y');
    $au = $booking->end_date?->format('d/m/Y');
@endphp

<x-email.layout
    title="Nouvelle réservation"
    :preheader="($booking->ad->title ?? 'Une de vos annonces') . ' vient d\'être réservée' . ($du ? ' du ' . $du . ' au ' . $au : '') . '.'"
    eyebrow="Réservation"
    heading="Vous avez une nouvelle réservation"
    subheading="Un locataire vient de réserver une de vos annonces sur Olten.">

    <x-email.text>Bonjour {{ $prenom }},</x-email.text>

    <x-email.text>
        Votre annonce <strong style="color:#1f2328;">{{ $booking->ad->title ?? 'sans titre' }}</strong>
        vient d'être réservée. Retrouvez le détail de la demande ci-dessous.
    </x-email.text>

    <x-email.panel title="Détails de la réservation">
        <x-email.row label="Référence" :value="'#' . ($booking->id)" />
        <x-email.row label="Annonce" :value="$booking->ad->title ?? '—'" />

        @if($nomLocataire)
            <x-email.row label="Locataire" :value="$nomLocataire" />
        @endif

        @if($du)
            <x-email.row label="Début de location" :value="$du" />
        @endif

        @if($au)
            <x-email.row label="Fin de location" :value="$au" />
        @endif

        @if($booking->delivery_requested)
            <x-email.row label="Livraison" value="Demandée par le locataire" />

            @if($booking->delivery_address)
                <x-email.row label="Adresse de livraison" :value="$booking->delivery_address" />
            @endif
        @endif

        @isset($booking->total_price)
            <x-email.row label="Montant total" :value="(number_format($booking->total_price, 2, ',', ' ')) . ' €'" total />
        @endisset
    </x-email.panel>

    <x-email.text>
        Acceptez ou refusez cette demande depuis votre espace : tant qu'elle est en attente,
        le locataire ne sait pas si son séjour est confirmé.
    </x-email.text>

    <x-email.button :url="route('bookings.receivedBookings')">
        Voir la réservation
    </x-email.button>

    <x-email.note>
        Vous recevez cette notification parce que vous bénéficiez d'un
        <strong>abonnement Premium</strong> sur Olten.
    </x-email.note>

</x-email.layout>
