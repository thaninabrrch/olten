{{--
    Paragraphe du corps d'un e-mail.

    Les styles sont en ligne et non dans une classe : Outlook Windows ne lit
    pas les feuilles embarquees, et Gmail sur compte non Gmail supprime le
    bloc <style> — un paragraphe stylise par classe y ressortait en Times 12
    noir. `mso-line-height-rule:exactly` avec un interligne en pixels evite
    par ailleurs que Word ne recalcule les hauteurs a sa facon.

        <x-email.text>Bonjour Amel,</x-email.text>
        <x-email.text tone="lead">Perceuse Bosch GSB 18V</x-email.text>
        <x-email.text tone="muted">Mention discrète</x-email.text>
--}}
@props([
    'tone'  => 'body',
    'align' => 'left',
])

@php
    $c    = config('olten.email.colors');
    $font = config('olten.email.font');

    [$taille, $interligne, $couleur, $graisse] = match ($tone) {
        'lead'  => ['16px', '26px', $c['ink'],     '700'],
        'muted' => ['12.5px', '20px', $c['inkFaint'], '400'],
        default => ['14.5px', '25px', '#4a5057',   '400'],
    };
@endphp

<p style="margin:0 0 16px; font-family:{{ $font }}; font-size:{{ $taille }}; line-height:{{ $interligne }}; mso-line-height-rule:exactly; font-weight:{{ $graisse }}; color:{{ $couleur }}; text-align:{{ $align }};">
    {{ $slot }}
</p>
