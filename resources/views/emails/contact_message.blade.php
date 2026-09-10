{{--
    Accuse de reception du formulaire de contact.

    Envoye par App\Mail\ContactMessageMail, qui recoit le tableau valide par
    ContactController::store() : name, email, subject, message, user_id.
    Ce n'est PAS un modele ContactMessage — ni `id`, ni `created_at` ne sont
    disponibles ici, donc pas de lien profond vers le message en base.

    Attention au destinataire : le controleur fait
    `Mail::to($validated['email'])`, c'est-a-dire le visiteur lui-meme. Cette
    vue s'adresse donc a lui. L'ancienne version lui envoyait la notification
    interne de l'administrateur (« Nouveau Message de Contact », « notification
    automatique envoyee par votre plateforme »), ce qui n'avait aucun sens
    pour un visiteur.
--}}
@php
    $expediteur = $contact['name'] ?? '';
    $prenom = trim(explode(' ', trim($expediteur))[0] ?? '');
@endphp

<x-email.layout
    title="Votre message a bien été reçu"
    :preheader="'Nous avons bien reçu votre message : ' . ($contact['subject'] ?? '')"
    eyebrow="Contact"
    heading="Votre message est bien arrivé"
    subheading="Notre équipe vous répond dans les meilleurs délais.">

    <x-email.text>Bonjour {{ $prenom !== '' ? $prenom : 'et merci' }},</x-email.text>

    <x-email.text>
        Nous avons bien reçu votre message et il a été transmis à l'équipe Olten.
        Vous recevrez une réponse à l'adresse <strong style="color:#1f2328;">{{ $contact['email'] ?? '' }}</strong>.
    </x-email.text>

    <x-email.panel title="Récapitulatif de votre message">
        <x-email.row label="Nom" :value="$contact['name'] ?? '—'" />
        <x-email.row label="Adresse e-mail" :value="$contact['email'] ?? '—'" />
        <x-email.row label="Objet" :value="$contact['subject'] ?? '—'" />
    </x-email.panel>

    <x-email.panel tone="plain" title="Contenu envoyé">
        <tr>
            <td style="font-family:{{ config('olten.email.font') }}; font-size:14px; line-height:23px; mso-line-height-rule:exactly; color:#4a5057;">
                {!! nl2br(e($contact['message'] ?? 'Aucun contenu fourni.')) !!}
            </td>
        </tr>
    </x-email.panel>

    <x-email.text>
        En attendant notre réponse, vous pouvez continuer à explorer les annonces,
        les produits et les trajets publiés sur la plateforme.
    </x-email.text>

    <x-email.button :url="route('search')">
        Explorer la plateforme
    </x-email.button>

    <x-email.note>
        Vous recevez cet e-mail parce qu'un message de contact a été envoyé depuis
        Olten.fr avec cette adresse. Si ce n'est pas vous, ignorez ce message.
    </x-email.note>

</x-email.layout>
