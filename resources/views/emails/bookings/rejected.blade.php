{{--
    Le proprietaire a refuse la reservation : on previent le locataire.
    Envoye par App\Mail\BookingRejectedMail, qui expose `booking`.
--}}
@php
    $prenom = $booking->user?->firstname ?: ($booking->user?->name ?? '');

    $du = $booking->start_date?->format('d/m/Y');
    $au = $booking->end_date?->format('d/m/Y');
@endphp

<x-email.layout
    title="Réservation refusée"
    :preheader="'Votre demande pour ' . ($booking->ad->title ?? 'cette annonce') . ' n\'a pas été retenue.'"
    eyebrow="Réservation"
    heading="Votre demande n'a pas été retenue"
    subheading="Le propriétaire n'a pas pu donner suite à cette réservation.">

    <x-email.text>Bonjour {{ $prenom }},</x-email.text>

    <x-email.text>
        Votre demande de réservation pour
        <strong style="color:#1f2328;">{{ $booking->ad->title ?? 'cette annonce' }}</strong>
        a été refusée par le propriétaire.
    </x-email.text>

    <x-email.panel title="Demande concernée">
        <x-email.row label="Annonce" :value="$booking->ad->title ?? '—'" />

        @if($du)
            <x-email.row label="Début souhaité" :value="$du" />
        @endif

        @if($au)
            <x-email.row label="Fin souhaitée" :value="$au" />
        @endif
    </x-email.panel>

    <x-email.note tone="warning">
        Si un paiement a été effectué, le remboursement est traité automatiquement.
        Comptez quelques jours ouvrés pour le voir apparaître sur votre relevé.
    </x-email.note>

    <x-email.text>
        D'autres biens similaires sont disponibles sur la plateforme : la recherche vous permet
        de filtrer par ville, par dates et par budget.
    </x-email.text>

    <x-email.button :url="route('search', array_filter(['category' => $booking->ad?->category?->slug]))">
        Voir des annonces similaires
    </x-email.button>

</x-email.layout>
