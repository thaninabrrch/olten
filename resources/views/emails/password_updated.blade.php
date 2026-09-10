{{--
    L'administrateur a change le mot de passe d'un membre : on le previent.
    Envoye par App\Mail\PasswordUpdatedByAdmin, qui expose `user`.

    C'est un e-mail de securite : il doit dire ce qui a change, quand, et
    donner un chemin immediat pour reprendre la main. L'ancienne version
    n'avait aucun lien — le membre apprenait qu'il ne pouvait plus se
    connecter sans savoir quoi faire.
--}}
@php
    $prenom = $user->firstname ?: $user->name;
@endphp

<x-email.layout
    title="Votre mot de passe a été modifié"
    preheader="Votre mot de passe Olten a été modifié par un administrateur."
    eyebrow="Sécurité"
    heading="Votre mot de passe a été modifié"
    subheading="Un administrateur Olten vient de le réinitialiser.">

    <x-email.text>Bonjour {{ $prenom }},</x-email.text>

    <x-email.text>
        Le mot de passe du compte associé à
        <strong style="color:#1f2328;">{{ $user->email }}</strong>
        a été modifié par un administrateur de la plateforme.
        Votre ancien mot de passe ne fonctionne plus.
    </x-email.text>

    <x-email.panel title="Détail de l'opération">
        <x-email.row label="Compte" :value="$user->email" />
        <x-email.row label="Modification" value="Mot de passe réinitialisé" />
        <x-email.row label="Effectuée le" :value="now()->translatedFormat('j F Y à H\\hi')" />
    </x-email.panel>

    <x-email.text>
        Le nouveau mot de passe vous est communiqué séparément par l'administrateur.
        Si vous ne l'avez pas reçu, ou si vous préférez en choisir un vous-même,
        lancez une réinitialisation depuis la page de connexion.
    </x-email.text>

    <x-email.button :url="route('login')">
        Me connecter
    </x-email.button>

    <x-email.button :url="route('password.request')" variant="ghost">
        Choisir un nouveau mot de passe
    </x-email.button>

    <x-email.note tone="warning">
        <strong>Vous n'êtes pas à l'origine de cette demande ?</strong>
        Contactez-nous immédiatement à
        <a href="mailto:{{ config('olten.email.contact') }}" style="color:#7a5a12; text-decoration:underline;">{{ config('olten.email.contact') }}</a>
        afin que nous sécurisions votre compte.
    </x-email.note>

</x-email.layout>
