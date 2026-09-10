{{--
    Une ligne « libellé / valeur » dans une <x-email.panel>.

    Deux cellules et non deux <span> : sur une adresse de livraison qui passe
    a la ligne, la version en spans repliait la valeur sous le libelle et la
    colonne se perdait.

    La valeur passe par l'attribut `value`, ou par le slot quand elle contient
    du balisage.

        <x-email.row label="Annonce" value="Perceuse Bosch" />
        <x-email.row label="Montant total" value="120,00 €" total />
--}}
@props([
    'label',
    'value' => null,

    // Derniere ligne d'un recapitulatif : separee par un filet et mise en
    // avant, comme le total d'une facture.
    'total' => false,
])

@php
    $c    = config('olten.email.colors');
    $font = config('olten.email.font');

    $cellule = $total
        ? 'padding:14px 0 0; border-top:1px solid ' . $c['tintBorder'] . ';'
        : 'padding:5px 0;';

    $valeur = $total
        ? 'font-size:17px; line-height:24px; font-weight:800; color:' . $c['primary'] . ';'
        : 'font-size:14px; line-height:22px; font-weight:600; color:' . $c['ink'] . ';';
@endphp

<tr>
    <td width="40%" valign="top" style="{{ $cellule }} font-family:{{ $font }}; font-size:13px; line-height:22px; mso-line-height-rule:exactly; color:{{ $c['inkSoft'] }};">
        {{ $label }}
    </td>
    <td width="60%" valign="top" align="right" style="{{ $cellule }} font-family:{{ $font }}; mso-line-height-rule:exactly; text-align:right; {{ $valeur }}">
        {{ $value ?? $slot }}
    </td>
</tr>
