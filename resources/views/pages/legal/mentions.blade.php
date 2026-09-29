@extends('pages.legal.layout')

@section('title', 'Mentions légales - Olten.fr')
@section('legal_title', 'Mentions légales')

@php
    $legal   = config('olten.legal');
    $contact = config('olten.email.contact');
    $address = config('olten.email.address');
@endphp

@section('legal')
    <p>
        Conformément à l'article 6 de la loi n° 2004-575 du 21 juin 2004 pour la confiance dans l'économie
        numérique (LCEN), voici les informations relatives à l'éditeur et à l'hébergeur du site {{ $legal['site'] }}.
    </p>

    <h2>Éditeur du site</h2>
    <dl class="legal-card">
        <dt><i class="fa-solid fa-building" aria-hidden="true"></i>Raison sociale</dt>
        <dd>{{ $legal['company'] }}</dd>

        <dt><i class="fa-solid fa-scale-balanced" aria-hidden="true"></i>Forme juridique</dt>
        <dd>{{ $legal['legal_form'] }}</dd>

        <dt><i class="fa-solid fa-location-dot" aria-hidden="true"></i>Siège social</dt>
        <dd>{{ $address }}</dd>

        <dt><i class="fa-solid fa-id-card" aria-hidden="true"></i>Immatriculation</dt>
        <dd>{{ $legal['registry'] }}</dd>

        <dt><i class="fa-solid fa-percent" aria-hidden="true"></i>TVA intracommunautaire</dt>
        <dd>{{ $legal['vat'] }}</dd>

        <dt><i class="fa-solid fa-envelope" aria-hidden="true"></i>E-mail</dt>
        <dd><a href="mailto:{{ $contact }}">{{ $contact }}</a></dd>

        <dt><i class="fa-solid fa-phone" aria-hidden="true"></i>Téléphone</dt>
        <dd>{{ $legal['phone'] }}</dd>

        <dt><i class="fa-solid fa-user-tie" aria-hidden="true"></i>Directeur de la publication</dt>
        <dd>{{ $legal['publisher'] }}</dd>
    </dl>

    <h2>Hébergement</h2>
    <p>{{ $legal['host'] }}</p>

    <h2>Activité de la plateforme</h2>
    <p>
        {{ $legal['site'] }} est une plateforme de mise en relation entre particuliers pour la location de biens,
        la vente d'objets, le covoiturage et la livraison. Les annonces, trajets, photos et messages sont publiés
        par les membres, sous leur propre responsabilité. Pour ces contenus, l'éditeur agit en qualité d'hébergeur
        au sens de l'article 6 de la LCEN et du règlement (UE) 2022/2065 sur les services numériques (DSA).
    </p>

    <h2>Signaler un contenu illicite</h2>
    <p>
        Toute personne peut signaler un contenu qu'elle estime illicite : depuis l'annonce concernée
        (bouton « Signaler »), ou en écrivant à <a href="mailto:{{ $contact }}">{{ $contact }}</a> en précisant
        l'adresse de la page, la nature du contenu et les raisons du signalement. Chaque signalement est examiné,
        et le contenu retiré s'il est manifestement illicite.
    </p>
    <p>
        Cette adresse est aussi le point de contact unique des autorités et des utilisateurs au sens des articles
        11 et 12 du règlement sur les services numériques. Les échanges peuvent avoir lieu en français.
    </p>

    <h2>Propriété intellectuelle</h2>
    <p>
        La marque Olten, le logo, la charte graphique, les textes et le code du site sont la propriété de
        l'éditeur. Toute reproduction ou représentation, totale ou partielle, sans autorisation écrite est
        interdite (articles L.335-2 et suivants du Code de la propriété intellectuelle).
    </p>
    <p>
        Les contenus publiés par les membres (textes, photos) restent leur propriété. En les publiant, le membre
        autorise l'éditeur à les afficher sur la plateforme pour la durée de leur mise en ligne, et garantit
        détenir les droits nécessaires.
    </p>

    <h2>Crédits</h2>
    <ul>
        <li>
            Cartes et géocodage : © les contributeurs
            d'<a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>
            (licence ODbL).
        </li>
        <li>
            Photographies d'illustration des destinations :
            <a href="https://unsplash.com" target="_blank" rel="noopener">Unsplash</a> (licence Unsplash).
        </li>
        <li>Icônes : Font Awesome et Bootstrap Icons.</li>
    </ul>

    <h2>Données personnelles</h2>
    <p>
        Le traitement de vos données personnelles est décrit dans notre
        <a href="{{ route('legal.privacy') }}">politique de confidentialité</a>.
    </p>
@endsection
