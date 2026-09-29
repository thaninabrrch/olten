@extends('pages.legal.layout')

@section('title', 'Conditions générales d\'utilisation - Olten.fr')
@section('legal_title', 'Conditions générales d\'utilisation')

@php
    $legal   = config('olten.legal');
    $contact = config('olten.email.contact');
    $shift   = \App\Models\Covoiturage::bookedTimeShift();
@endphp

@section('legal')
    <p>
        Les présentes conditions générales d'utilisation (CGU) encadrent l'accès et l'usage de la plateforme
        {{ $legal['site'] }}, éditée par {{ $legal['company'] }} (voir les
        <a href="{{ route('legal.mentions') }}">mentions légales</a>). En créant un compte, vous les acceptez
        sans réserve. Les services payants sont en outre régis par nos
        <a href="{{ route('legal.cgv') }}">conditions générales de vente</a>.
    </p>

    <h2>1. Objet de la plateforme</h2>
    <p>
        {{ $legal['site'] }} met en relation des particuliers qui souhaitent louer des biens, vendre des objets,
        partager un trajet en covoiturage ou faire livrer un bien. Les contrats de location, de vente, de
        covoiturage ou de livraison sont conclus directement entre les membres :
        {{ $legal['site'] }} n'en est pas partie, sauf pour ses propres services (frais de service, abonnements)
        décrits dans les CGV.
    </p>

    <h2>2. Inscription et compte</h2>
    <ul>
        <li>L'inscription est réservée aux personnes majeures et capables de contracter.</li>
        <li>
            Vous vous engagez à fournir des informations exactes et à les tenir à jour. L'adresse e-mail doit
            être confirmée avant de publier ou de réserver.
        </li>
        <li>
            Les comptes livreur et conducteur sont validés par notre équipe. Publier un trajet ou accepter une
            livraison exige un permis de conduire valide, vérifié à partir du justificatif que vous transmettez.
        </li>
        <li>
            Vos identifiants sont personnels et confidentiels. Toute action effectuée depuis votre compte est
            réputée faite par vous.
        </li>
    </ul>

    <h2>3. Publication d'annonces, de produits et de trajets</h2>
    <p>En publiant sur la plateforme, vous vous engagez à ce que votre contenu :</p>
    <ul>
        <li>décrive fidèlement le bien, l'objet ou le trajet proposé, son état et son prix ;</li>
        <li>porte sur un bien que vous avez le droit de louer ou de vendre ;</li>
        <li>n'utilise que des photos dont vous détenez les droits ;</li>
        <li>respecte la loi et les droits des tiers (pas de contenu trompeur, injurieux ou illicite).</li>
    </ul>
    <p>
        Les annonces peuvent être soumises à validation avant leur mise en ligne. {{ $legal['site'] }} peut
        refuser ou retirer un contenu contraire aux présentes CGU ou signalé comme illicite.
    </p>

    <h2>4. Règles propres au covoiturage</h2>
    <h3>Pour le conducteur</h3>
    <ul>
        <li>
            Le covoiturage est un partage des frais du trajet, sans but lucratif (article L.3132-1 du Code des
            transports) : le prix demandé par place ne doit pas dépasser votre part des frais.
        </li>
        <li>Vous devez détenir un permis valide, une assurance couvrant le transport de passagers et un véhicule en bon état.</li>
        <li>
            <strong>Un trajet réservé vous engage.</strong> Dès qu'une place est payée, vous ne pouvez plus
            annuler le trajet, ni en modifier la date, l'itinéraire ou le prix. Seul l'horaire peut encore être
            décalé de {{ $shift }} minutes au plus ; les passagers concernés en sont alors prévenus.
        </li>
        <li>
            En validation manuelle, vous acceptez ou refusez chaque demande depuis « Réservations reçues » : le
            passager a déjà payé et sa place est bloquée. Une demande restée sans réponse à l'heure du départ est
            annulée et le passager remboursé.
        </li>
        <li>En cas d'empêchement grave, prévenez vos passagers et contactez-nous sans attendre.</li>
    </ul>
    <h3>Pour le passager</h3>
    <ul>
        <li>Vous réservez une ou plusieurs places, pour l'aller, le retour ou les deux.</li>
        <li>
            Une fois votre place réservée, vos prénom, nom et photo apparaissent sur la page du trajet, visible
            de tous ses visiteurs. Votre téléphone n'est communiqué qu'au conducteur.
        </li>
        <li>Soyez ponctuel au point de rendez-vous et respectez le véhicule et le conducteur.</li>
        <li>Vous pouvez annuler votre réservation avant le départ, dans les conditions prévues par les CGV.</li>
    </ul>

    <h2>5. Location et vente entre membres</h2>
    <ul>
        <li>
            Le propriétaire accepte ou refuse chaque demande de location. Il remet un bien conforme à l'annonce ;
            le locataire le restitue dans l'état où il l'a reçu, à la date convenue.
        </li>
        <li>Le vendeur expédie ou remet un objet conforme à sa description.</li>
        <li>
            Les litiges relatifs au bien (état, restitution, conformité) se règlent en priorité entre les membres.
            Notre équipe peut aider à trouver une solution : écrivez-nous à
            <a href="mailto:{{ $contact }}">{{ $contact }}</a>.
        </li>
    </ul>

    <h2>6. Livraison</h2>
    <p>
        Les livreurs sont des membres validés par notre équipe. Ils s'engagent à prendre soin des biens confiés,
        à respecter les délais annoncés et à confirmer chaque étape de la mission sur la plateforme.
    </p>

    <h2>7. Messagerie</h2>
    <p>
        La messagerie sert à organiser vos locations, ventes, trajets et livraisons. Il est interdit d'y tenir
        des propos injurieux ou discriminatoires, d'y démarcher d'autres membres, ou d'y proposer un paiement en
        dehors de la plateforme pour une offre qui y est publiée.
    </p>

    <h2>8. Alertes et notifications</h2>
    <p>
        Les notifications s'affichent dans la cloche, en haut de page : nouveaux trajets sur les liaisons que
        vous suivez (« alertes trajet »), changement d'horaire d'un trajet que vous avez réservé, etc. Vous
        gérez vos alertes depuis « Mes alertes trajet ». Certains messages indispensables au service
        (confirmation de réservation, remboursement, changement d'horaire) vous sont aussi envoyés par e-mail.
    </p>

    <h2>9. Comportements interdits</h2>
    <ul>
        <li>créer plusieurs comptes ou usurper l'identité d'un tiers ;</li>
        <li>publier des offres fictives, trompeuses ou portant sur des biens illicites ;</li>
        <li>contourner les paiements de la plateforme pour une offre qui y est publiée ;</li>
        <li>perturber le fonctionnement du site (robots, collecte automatisée, tentative d'intrusion).</li>
    </ul>

    <h2>10. Signalement, suspension et résiliation</h2>
    <p>
        Tout membre peut signaler une annonce depuis sa page. En cas de manquement aux présentes CGU,
        {{ $legal['site'] }} peut retirer un contenu, suspendre ou supprimer un compte, après en avoir informé le
        membre et lui avoir permis de s'expliquer, sauf urgence ou manquement grave.
    </p>
    <p>
        Vous pouvez supprimer votre compte à tout moment depuis « Mon compte ». Les réservations et commandes
        en cours doivent être menées à leur terme ou annulées au préalable.
    </p>

    <h2>11. Responsabilité</h2>
    <p>
        {{ $legal['site'] }} met tout en œuvre pour assurer l'accès au site, sans pouvoir garantir une
        disponibilité continue. La plateforme n'étant pas partie aux contrats entre membres, elle ne garantit
        ni la qualité des biens et des trajets proposés, ni l'exécution des engagements des membres. Sa
        responsabilité reste engagée pour ses propres manquements.
    </p>

    <h2>12. Modification des CGU, droit applicable et litiges</h2>
    <p>
        Les CGU peuvent évoluer : la version en vigueur est celle publiée sur cette page, et toute modification
        importante vous est signalée. Les présentes CGU sont soumises au droit français. En cas de litige, vous
        pouvez recourir gratuitement au médiateur de la consommation : {{ $legal['mediator'] }}. À défaut
        d'accord amiable, les tribunaux français sont compétents.
    </p>
@endsection
