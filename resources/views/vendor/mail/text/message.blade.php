<x-mail::layout>
    {{-- En-tete --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            Olten
        </x-mail::header>
    </x-slot:header>

    {{-- Corps --}}
    {{ $slot }}

    {{-- Repli sous le bouton --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {{ $subcopy }}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- Pied de page --}}
    <x-slot:footer>
        <x-mail::footer>
            {{ config('olten.email.contact') }} - {{ config('olten.email.address') }}
            E-mail automatique, merci de ne pas y repondre.
            (c) {{ date('Y') }} Olten.fr - Tous droits reserves.
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>
