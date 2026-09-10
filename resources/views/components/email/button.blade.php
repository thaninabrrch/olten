{{--
    Bouton d'action : la pilule orange du site.

    Dit « bulletproof » : le moteur Word d'Outlook Windows n'applique ni
    `padding`, ni `border-radius`, ni `display:inline-block` a un <a> — les
    six CTA de la plateforme s'y reduisaient a un mot orange souligne. Le
    rectangle arrondi lui est donc dessine en VML (<v:roundrect>), que les
    autres clients ne voient jamais.

    Un lien texte de repli est ajoute sous le bouton quand l'action est
    critique (`fallback`) : si les images ou le VML sautent, l'adresse reste
    cliquable et copiable.

        <x-email.button url="{{ route('bookings.myBookings') }}">
            Voir ma réservation
        </x-email.button>
--}}
@props([
    'url',

    // « primary » = orange plein, l'action principale.
    // « ghost »   = contour gris, une action secondaire posee dessous.
    'variant' => 'primary',

    // Affiche l'adresse en clair sous le bouton (mots de passe, verification
    // de compte : le message ne doit pas devenir une impasse).
    'fallback' => false,
])

@php
    $c    = config('olten.email.colors');
    $font = config('olten.email.font');

    $primaire = $variant !== 'ghost';

    $fond  = $primaire ? $c['primary'] : $c['white'];
    $texte = $primaire ? $c['white']   : $c['ink'];
    $filet = $primaire ? $c['primary'] : '#d8dbe0';

    $libelle = trim($slot);

    $tableReset = 'border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;';
@endphp

<table role="presentation" class="ol-btn" width="100%" cellpadding="0" cellspacing="0" border="0" style="{{ $tableReset }} margin:4px 0 {{ $fallback ? '14px' : '24px' }};">
    <tr>
        <td align="center">

            <!--[if mso]>
            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word"
                         href="{{ $url }}" style="height:46px; v-text-anchor:middle; width:300px;"
                         arcsize="50%" strokecolor="{{ $filet }}" fillcolor="{{ $fond }}">
                <w:anchorlock/>
                <center style="color:{{ $texte }}; font-family:'Segoe UI',Arial,sans-serif; font-size:15px; font-weight:bold;">
                    {{ $libelle }}
                </center>
            </v:roundrect>
            <![endif]-->

            <!--[if !mso]><!-->
            <a href="{{ $url }}"
               style="display:inline-block; padding:14px 34px; background-color:{{ $fond }}; border:1px solid {{ $filet }}; border-radius:50px; font-family:{{ $font }}; font-size:15px; line-height:18px; mso-line-height-rule:exactly; font-weight:700; color:{{ $texte }}; text-decoration:none; text-align:center; mso-hide:all;">
                {{ $libelle }}
            </a>
            <!--<![endif]-->

        </td>
    </tr>
</table>

@if($fallback)
    <p style="margin:0 0 24px; font-family:{{ $font }}; font-size:11.5px; line-height:19px; mso-line-height-rule:exactly; color:{{ $c['inkFaint'] }}; text-align:center; word-break:break-all;">
        Le bouton ne fonctionne pas ? Copiez cette adresse dans votre navigateur :<br />
        <a href="{{ $url }}" style="color:{{ $c['primary'] }}; text-decoration:underline;">{{ $url }}</a>
    </p>
@endif
