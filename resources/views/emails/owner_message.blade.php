{{--
    Un membre a ecrit au proprietaire d'une offre depuis la popin « Message »
    de la fiche. Envoye par App\Mail\OwnerMessageMail, qui expose `sender`,
    `owner`, `listingTitle`, `listingUrl` et `messageText`.

    L'adresse de l'expediteur n'apparait pas : la reponse passe par la
    messagerie Olten, comme le rappelle la note de securite.
--}}
@php
    $prenom      = $owner->firstname ?: $owner->name;
    $expediteur  = $sender->firstname ?: $sender->name;
@endphp

<x-email.layout
    title="Nouveau message"
    :preheader="$expediteur . ' vous a écrit à propos de « ' . $listingTitle . ' ».'"
    eyebrow="Messagerie"
    heading="Vous avez un nouveau message"
    :subheading="'À propos de « ' . $listingTitle . ' »'">

    <x-email.text>Bonjour {{ $prenom }},</x-email.text>

    <x-email.text>
        <strong style="color:#1f2328;">{{ $sender->name }}</strong> vous a écrit au sujet de votre offre
        <strong style="color:#1f2328;">{{ $listingTitle }}</strong>.
    </x-email.text>

    <x-email.panel tone="plain" title="Son message">
        <tr>
            <td style="font-family:{{ config('olten.email.font') }}; font-size:14px; line-height:23px; mso-line-height-rule:exactly; color:#4a5057;">
                {!! nl2br(e($messageText)) !!}
            </td>
        </tr>
    </x-email.panel>

    <x-email.button :url="route('messages', ['avec' => $sender->id])">
        Répondre sur Olten
    </x-email.button>

    <x-email.button :url="$listingUrl" variant="ghost">
        Voir mon offre
    </x-email.button>

    <x-email.note>
        Pour votre sécurité, échangez et réglez vos transactions sur Olten.
        Ne communiquez jamais vos coordonnées bancaires ni un code reçu par SMS.
    </x-email.note>

</x-email.layout>
