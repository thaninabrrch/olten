{{--
    Mention de bas de contenu, posee sur un filet vertical orange : pourquoi
    ce message a ete envoye, ou ce qu'il faut savoir avant d'agir. Meme motif
    que `.olten-plans-notice` sur le site (subscriptions.css:71).

    Le filet est une cellule de 3px et non un `border-left` : le moteur Word
    ignore les bordures partielles d'un bloc.

        <x-email.note>
            Vous recevez cette notification parce que vous avez un abonnement Premium.
        </x-email.note>

    `tone="warning"` pour un remboursement ou une annulation, `tone="success"`
    pour une confirmation.
--}}
@props([
    'tone' => 'neutral',
])

@php
    $c    = config('olten.email.colors');
    $font = config('olten.email.font');

    [$filet, $fond, $encre] = match ($tone) {
        'warning' => ['#f0a500', '#fff9ec', '#7a5a12'],
        'success' => ['#12a06a', '#f0fbf6', '#12674a'],
        default   => [$c['primary'], '#fafafa', $c['inkSoft']],
    };

    $tableReset = 'border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;';
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="{{ $tableReset }} margin:0 0 8px;">
    <tr>
        <td width="3" bgcolor="{{ $filet }}" style="width:3px; background-color:{{ $filet }}; font-size:0; line-height:0;">&nbsp;</td>
        <td bgcolor="{{ $fond }}" style="padding:13px 16px; background-color:{{ $fond }}; font-family:{{ $font }}; font-size:12.5px; line-height:21px; mso-line-height-rule:exactly; color:{{ $encre }};">
            {{ $slot }}
        </td>
    </tr>
</table>
