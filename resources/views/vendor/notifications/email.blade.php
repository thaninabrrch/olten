{{--
    Gabarit des notifications construites avec MailMessage (->greeting(),
    ->line(), ->action()) : la confirmation d'adresse e-mail passe par ici.

    Publie depuis le framework pour deux raisons :

      - les textes de repli de Laravel sont en anglais et la locale de
        l'application reste `en` (voir AppServiceProvider, ou seule la mise en
        forme des dates est passee en francais) : « Hello! », « Regards, » et
        « If you're having trouble clicking... » sortaient tels quels au milieu
        d'un message francais ;

      - le pied de page signait « © 2026 Laravel » parce qu'il reprend
        config('app.name'), et que APP_NAME vaut encore « Laravel » dans le
        .env. La marque est ecrite ici en clair, elle ne depend plus d'une
        variable d'environnement.
--}}
<x-mail::message>
{{-- Salutation d'ouverture --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# Oups !
@else
# Bonjour,
@endif
@endif

{{-- Lignes d'introduction --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Bouton d'action --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Lignes de conclusion --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Signature --}}
@if (! empty($salutation))
{{ $salutation }}
@else
À bientôt,<br>
L'équipe Olten
@endif

{{-- Repli sous le bouton : l'adresse en clair, au cas ou le bouton ne
     serait pas cliquable (client texte, VML desactive, images bloquees). --}}
@isset($actionText)
<x-slot:subcopy>
Le bouton « {{ $actionText }} » ne fonctionne pas ? Copiez l'adresse ci-dessous et collez-la dans votre navigateur : <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>
