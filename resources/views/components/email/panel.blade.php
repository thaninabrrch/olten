{{--
    Carte de recapitulatif : le bloc « Détails de la réservation », « Détails
    de la commande »... Elle reprend la carte teintee du site (fond peche
    --cs-tint, filet orange pale, coins arrondis).

    Elle attend des <x-email.row> dans son slot.

    Outlook ignore `border-radius` : la carte y sera a angles droits, ce qui
    reste correct puisque le fond (porte aussi par `bgcolor`) et le filet,
    eux, sont respectes.

        <x-email.panel title="Détails de la réservation">
            <x-email.row label="Annonce" value="Perceuse Bosch" />
            <x-email.row label="Montant" value="120,00 €" total />
        </x-email.panel>
--}}
@props([
    'title' => null,

    // « tint » = carte peche, pour un recapitulatif de la plateforme.
    // « plain » = carte grise, pour un contenu rapporte (le message d'un
    // visiteur) qu'il ne faut pas confondre avec les donnees Olten.
    'tone' => 'tint',
])

@php
    $c    = config('olten.email.colors');
    $font = config('olten.email.font');

    [$fond, $filet] = $tone === 'plain'
        ? ['#f7f8f9', $c['line']]
        : [$c['tint'], $c['tintBorder']];

    $tableReset = 'border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;';
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="{{ $tableReset }} margin:0 0 24px;">
    <tr>
        <td bgcolor="{{ $fond }}" style="padding:20px 22px; background-color:{{ $fond }}; border:1px solid {{ $filet }}; border-radius:12px;">

            @if($title)
                <p style="margin:0 0 14px; font-family:{{ $font }}; font-size:11px; line-height:15px; mso-line-height-rule:exactly; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:{{ $c['primary'] }};">
                    {{ $title }}
                </p>
            @endif

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="{{ $tableReset }}">
                {{ $slot }}
            </table>

        </td>
    </tr>
</table>
