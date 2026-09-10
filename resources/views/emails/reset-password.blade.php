{{--
    Lien de reinitialisation du mot de passe.
    Envoye par App\Mail\ResetPasswordMail, qui expose `token`, `email`
    et `name`.

    L'adresse passe desormais par route('password.reset', [...]) et non par
    une concatenation : l'ancienne version collait l'e-mail brut dans la
    query string, si bien qu'une adresse contenant « + » arrivait avec un
    espace a la place et que le lien etait refuse.
--}}
@php
    $prenom = trim(explode(' ', trim((string) $name))[0] ?? '');

    $lien = route('password.reset', ['token' => $token, 'email' => $email]);

    // Duree de validite du lien, lue dans la configuration plutot qu'ecrite
    // en dur : elle se regle dans config/auth.php.
    $minutes = config('auth.passwords.users.expire', 60);
@endphp

<x-email.layout
    title="Réinitialisation de votre mot de passe"
    :preheader="'Votre lien de réinitialisation est valable ' . ($minutes) . ' minutes.'"
    eyebrow="Sécurité"
    heading="Réinitialisez votre mot de passe"
    :subheading="'Un lien personnel vous attend, valable ' . ($minutes) . ' minutes.'">

    <x-email.text>Bonjour {{ $prenom !== '' ? $prenom : '' }},</x-email.text>

    <x-email.text>
        Vous avez demandé à réinitialiser le mot de passe du compte
        <strong style="color:#1f2328;">{{ $email }}</strong>.
        Cliquez sur le bouton ci-dessous pour en choisir un nouveau.
    </x-email.text>

    <x-email.button :url="$lien" fallback>
        Réinitialiser mon mot de passe
    </x-email.button>

    <x-email.note>
        Ce lien est valable <strong>{{ $minutes }} minutes</strong> et ne peut servir qu'une fois.
        Passé ce délai, relancez une demande depuis la page de connexion.
    </x-email.note>

    <x-email.note tone="warning">
        Vous n'avez pas fait cette demande ? Ignorez simplement ce message :
        votre mot de passe actuel reste valable et rien n'a été modifié.
    </x-email.note>

</x-email.layout>
