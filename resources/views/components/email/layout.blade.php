{{--
    Gabarit unique des e-mails Olten.

    Les douze e-mails de la plateforme avaient chacun leur mise en page :
    trois oranges differents, quatre structures HTML, la moitie sans logo ni
    pied de page. Ils partagent desormais ce gabarit, qui reprend la grammaire
    du site — en-tete blanc sur filet, bandeau sombre a lueur orange, contenu
    blanc, pied de page clair — et ses couleurs, tenues dans config/olten.php.

    Ecrit en tables et en styles en ligne : c'est la seule mise en page que le
    moteur Word d'Outlook Windows sait rendre, et la seule qui survive aux
    clients qui suppriment le <style> de tete (Gmail sur compte non Gmail).
    Le <style> ne porte donc que le responsive, qu'Outlook ignore sans
    dommage puisque l'essentiel est deja en ligne.

    Utilisation :

        <x-email.layout
            title="Réservation confirmée"
            preheader="Votre réservation du 12 au 15 mars est confirmée."
            eyebrow="Réservation"
            heading="C'est confirmé !"
            subheading="Le propriétaire a accepté votre demande.">

            <x-email.text>Bonjour Amel,</x-email.text>
            ...
        </x-email.layout>
--}}
@props([
    // Titre du document. Sert de repli au titre du bandeau.
    'title',

    // Texte d'apercu affiche par la boite de reception a cote de l'objet.
    // Sans lui, les clients y recopient le debut du HTML — souvent le logo.
    'preheader' => null,

    // Pastille en capitales au-dessus du titre (« Réservation », « Commande »).
    'eyebrow' => null,

    // Titre du bandeau sombre. Repli sur `title`.
    'heading' => null,

    // Phrase d'accroche sous le titre, facultative.
    'subheading' => null,
])

@php
    $c    = config('olten.email.colors');
    $font = config('olten.email.font');

    // Les images d'un e-mail doivent porter une adresse absolue : une adresse
    // relative n'a aucun sens une fois le message dans la boite du
    // destinataire. `asset()` s'appuie sur APP_URL, qui doit donc pointer sur
    // le domaine public en production — sinon tous les visuels et tous les
    // liens de tous les e-mails partent vers http://localhost.
    $logoDark  = asset('assets/images/logo/olten_location.png');
    $site      = config('app.url');

    $heading = $heading ?: $title;

    // Reset repete sur chaque table : Outlook Windows insere sinon deux
    // espaces parasites de part et d'autre de chaque tableau.
    $tableReset = 'border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;';
@endphp
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="x-apple-disable-message-reformatting" />
    <meta name="format-detection" content="telephone=no,address=no,email=no,date=no" />
    <meta name="color-scheme" content="light" />
    <meta name="supported-color-schemes" content="light" />
    <title>{{ $title }}</title>

    {{-- Montserrat est la police declaree par le site. Outlook Windows ne
         sait pas la charger et retomberait sur du Times : le commentaire
         conditionnel lui cache le lien, il prend alors la pile de repli
         declaree en ligne sur chaque cellule. --}}
    <!--[if !mso]><!-->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <!--<![endif]-->

    <!--[if mso]>
    <noscript><xml><o:OfficeDocumentSettings>
        <o:PixelsPerInch>96</o:PixelsPerInch>
    </o:OfficeDocumentSettings></xml></noscript>
    <![endif]-->

    <style type="text/css">
        /* Rattrapages que l'on ne peut pas poser en ligne : ils visent le
           document lui-meme ou des elements fabriques par le client. */
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }

        /* Apple Mail et Outlook.com transforment adresses, dates et numeros
           en liens bleus soulignes, y compris a l'interieur du bandeau. */
        a[x-apple-data-detectors],
        .im a { color: inherit !important; text-decoration: none !important; font-size: inherit !important; font-weight: inherit !important; }

        /* Une photo d'offre est de format libre : un portrait pris au
           telephone occuperait sinon 900px de haut a lui seul. La hauteur est
           bornee ici plutot qu'en ligne, car `max-height` sur une image n'a de
           sens que la ou il est lu — Outlook l'ignore et rendra la photo a sa
           taille naturelle, ce qui reste lisible.

           `max-width` est indispensable a cote de `width: auto` : sans lui,
           liberer la largeur laisse une photo panoramique deborder de la
           colonne, puisque le `max-width` en ligne de l'image tombe en meme
           temps que son `width`. */
        .ol-photo img { max-height: 280px !important; width: auto !important; max-width: 100% !important; }

        /* Sous 620px les gouttieres se resserrent. La largeur, elle, n'a plus
           besoin d'etre corrigee ici : la coque est fluide par construction
           (width:100% borne a 600px), donc elle s'adapte meme chez les clients
           qui suppriment ce bloc <style>. */
        @media only screen and (max-width: 620px) {
            .ol-pad { padding-left: 22px !important; padding-right: 22px !important; }
            .ol-hero-title { font-size: 24px !important; line-height: 30px !important; }
            .ol-btn a { display: block !important; }
        }
    </style>
</head>

<body style="margin:0; padding:0; width:100%; background-color:{{ $c['page'] }};">

{{-- Apercu de la boite de reception, invisible dans le corps du message.
     Les caracteres invisibles qui suivent empechent le client d'y recopier le
     debut du contenu pour combler la ligne. --}}
@if($preheader)
    <div style="display:none; font-size:1px; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden; mso-hide:all; color:{{ $c['page'] }};">
        {{ $preheader }}
        &#8199;&#65279;&#8199;&#65279;&#8199;&#65279;&#8199;&#65279;&#8199;&#65279;&#8199;&#65279;&#8199;&#65279;&#8199;&#65279;&#8199;&#65279;&#8199;&#65279;
    </div>
@endif

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="{{ $c['page'] }}" style="{{ $tableReset }} background-color:{{ $c['page'] }};">
<tr>
<td align="center" style="padding:24px 12px;">

    {{-- Table fantome : le moteur Word ignore `max-width`, la colonne
         s'etalerait sur toute la fenetre d'Outlook. --}}
    <!--[if mso]><table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;"><tr><td><![endif]-->
    {{-- Coque fluide-hybride : elle prend la largeur disponible, plafonnee a
         600px. Une largeur figee a 600px (attribut ET style) obligeait a la
         corriger dans la media query, donc a dependre du bloc <style> — que
         Gmail supprime sur un compte non-Gmail : le message y arrivait large
         de 600px sur un ecran de telephone, a faire defiler lateralement.
         Outlook, qui ignore `max-width`, prend ses 600px sur la table
         fantome ci-dessus. --}}
    <table role="presentation" class="ol-shell" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="{{ $c['white'] }}" style="{{ $tableReset }} width:100%; max-width:600px; background-color:{{ $c['white'] }}; border-radius:16px; overflow:hidden;">

        {{-- ── En-tete : le logo sur blanc, comme la barre du site ── --}}
        <tr>
            <td class="ol-pad" align="center" bgcolor="{{ $c['white'] }}" style="padding:26px 32px; background-color:{{ $c['white'] }}; border-bottom:1px solid {{ $c['lineHeader'] }};">
                <a href="{{ $site }}" style="display:inline-block; text-decoration:none;">
                    <img src="{{ $logoDark }}" width="132" height="35" alt="Olten" style="display:block; width:132px; max-width:132px; height:auto; border:0;" />
                </a>
            </td>
        </tr>

        {{-- ── Bandeau : la signature visuelle des pages du site, une bande
             sombre traversee d'une lueur orange. `bgcolor` porte la couleur
             pleine pour Outlook, qui ignore le degrade. ── --}}
        <tr>
            <td class="ol-pad" bgcolor="{{ $c['heroFrom'] }}" style="padding:30px 32px 32px; background-color:{{ $c['heroFrom'] }}; background-image:radial-gradient(90% 130% at 100% 0%, rgba(255,60,0,0.38) 0%, rgba(255,60,0,0) 58%), linear-gradient(115deg, {{ $c['heroFrom'] }} 0%, {{ $c['heroMid'] }} 52%, {{ $c['heroTo'] }} 100%);">

                @if($eyebrow)
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="{{ $tableReset }} margin-bottom:14px;">
                        <tr>
                            <td bgcolor="{{ $c['heroTagBg'] }}" style="padding:6px 13px; background-color:{{ $c['heroTagBg'] }}; border:1px solid {{ $c['heroTagLine'] }}; border-radius:50px; font-family:{{ $font }}; font-size:11px; line-height:14px; mso-line-height-rule:exactly; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:{{ $c['primarySoft'] }}; white-space:nowrap;">
                                {{ $eyebrow }}
                            </td>
                        </tr>
                    </table>
                @endif

                <h1 class="ol-hero-title" style="margin:0; font-family:{{ $font }}; font-size:27px; line-height:33px; mso-line-height-rule:exactly; font-weight:800; letter-spacing:-0.4px; color:{{ $c['white'] }};">
                    {{ $heading }}
                </h1>

                @if($subheading)
                    <p style="margin:10px 0 0; font-family:{{ $font }}; font-size:14px; line-height:23px; mso-line-height-rule:exactly; color:#b9bcc4;">
                        {{ $subheading }}
                    </p>
                @endif
            </td>
        </tr>

        {{-- ── Contenu ── --}}
        <tr>
            <td class="ol-pad" bgcolor="{{ $c['white'] }}" style="padding:32px; background-color:{{ $c['white'] }};">
                {{ $slot }}
            </td>
        </tr>

        {{-- ── Pied de page : celui du site, clair sur filet chaud ── --}}
        <tr>
            <td class="ol-pad" align="center" bgcolor="{{ $c['footer'] }}" style="padding:28px 32px; background-color:{{ $c['footer'] }}; border-top:1px solid {{ $c['lineFooter'] }};">

                <p style="margin:0 0 14px; font-family:{{ $font }}; font-size:13px; line-height:21px; mso-line-height-rule:exactly; font-weight:600; color:{{ $c['footerInk'] }};">
                    {{ config('olten.email.tagline') }}
                </p>

                <p style="margin:0 0 16px; font-family:{{ $font }}; font-size:13px; line-height:21px; mso-line-height-rule:exactly; color:{{ $c['footerInk'] }};">
                    <a href="{{ $site }}" style="color:{{ $c['footerInk'] }}; text-decoration:none;">Accueil</a>
                    <span style="color:{{ $c['lineFooter'] }};">&nbsp;&nbsp;&middot;&nbsp;&nbsp;</span>
                    <a href="{{ route('services.index') }}" style="color:{{ $c['footerInk'] }}; text-decoration:none;">Nos services</a>
                    <span style="color:{{ $c['lineFooter'] }};">&nbsp;&nbsp;&middot;&nbsp;&nbsp;</span>
                    <a href="{{ route('search') }}" style="color:{{ $c['footerInk'] }}; text-decoration:none;">Rechercher</a>
                    <span style="color:{{ $c['lineFooter'] }};">&nbsp;&nbsp;&middot;&nbsp;&nbsp;</span>
                    <a href="{{ route('contact') }}" style="color:{{ $c['footerInk'] }}; text-decoration:none;">Contact</a>
                </p>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="{{ $tableReset }} margin-bottom:14px;">
                    <tr><td height="1" bgcolor="{{ $c['lineFooter'] }}" style="height:1px; background-color:{{ $c['lineFooter'] }}; font-size:0; line-height:0;">&nbsp;</td></tr>
                </table>

                <p style="margin:0 0 6px; font-family:{{ $font }}; font-size:11.5px; line-height:19px; mso-line-height-rule:exactly; color:{{ $c['footerFaint'] }};">
                    <a href="mailto:{{ config('olten.email.contact') }}" style="color:{{ $c['primary'] }}; text-decoration:none;">{{ config('olten.email.contact') }}</a>
                    &nbsp;&middot;&nbsp; {{ config('olten.email.address') }}
                </p>

                <p style="margin:0; font-family:{{ $font }}; font-size:11.5px; line-height:19px; mso-line-height-rule:exactly; color:{{ $c['footerFaint'] }};">
                    E-mail automatique, merci de ne pas y répondre.<br />
                    &copy; {{ date('Y') }} Olten.fr — Tous droits réservés.
                </p>
            </td>
        </tr>

    </table>
    <!--[if mso]></td></tr></table><![endif]-->

</td>
</tr>
</table>

</body>
</html>
