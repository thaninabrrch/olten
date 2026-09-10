{{--
    Enveloppe des messages Markdown (theme Laravel rebrande Olten).

    L'en-tete et le pied ne passent plus par config('app.name') : APP_NAME
    vaut « Laravel » dans le .env, si bien que le pied signait
    « (c) 2026 Laravel. All rights reserved. » au bas d'un message francais.
    La marque est ecrite ici, et le pied reprend les coordonnees affichees
    par les douze autres e-mails (voir config/olten.php).
--}}
<x-mail::layout>
{{-- En-tete --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
Olten
</x-mail::header>
</x-slot:header>

{{-- Corps --}}
{!! $slot !!}

{{-- Repli sous le bouton --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Pied de page --}}
<x-slot:footer>
<x-mail::footer>
[{{ config('olten.email.contact') }}](mailto:{{ config('olten.email.contact') }}) &middot; {{ config('olten.email.address') }}

E-mail automatique, merci de ne pas y répondre.
&copy; {{ date('Y') }} Olten.fr — Tous droits réservés.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
