@extends('pages.legal.layout')

@section('title', 'Politique de confidentialité - Olten.fr')
@section('legal_title', 'Politique de confidentialité')

@php
    $legal   = config('olten.legal');
    $contact = config('olten.email.contact');
@endphp

@section('legal')
    <p>
        Cette politique explique quelles données personnelles {{ $legal['site'] }} collecte, pourquoi, combien de
        temps elles sont conservées et comment exercer vos droits, conformément au règlement général sur la
        protection des données (RGPD) et à la loi Informatique et Libertés.
    </p>

    <h2>1. Responsable du traitement</h2>
    <p>
        {{ $legal['company'] }}, {{ config('olten.email.address') }}. Pour toute question sur vos données :
        <a href="mailto:{{ $contact }}">{{ $contact }}</a>.
    </p>

    <h2>2. Données collectées</h2>
    <div class="legal-table">
        <table>
            <thead>
                <tr>
                    <th>Catégorie</th>
                    <th>Données</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Compte et profil</td>
                    <td>
                        Nom, prénom, adresse e-mail, mot de passe (chiffré), téléphone, genre, photo, présentation,
                        liens vers vos réseaux sociaux, rôles et abonnement.
                    </td>
                </tr>
                <tr>
                    <td>Justificatifs</td>
                    <td>Permis de conduire, carte VTC ou pièce d'identité transmis pour conduire ou livrer, et leur statut de validation.</td>
                </tr>
                <tr>
                    <td>Véhicule</td>
                    <td>Marque, modèle, couleur et nombre de places.</td>
                </tr>
                <tr>
                    <td>Annonces, produits et trajets</td>
                    <td>Textes, photos, prix, adresses et coordonnées géographiques, itinéraires.</td>
                </tr>
                <tr>
                    <td>Réservations, commandes et paiements</td>
                    <td>
                        Dates, montants, adresse et téléphone de contact ou de livraison, identifiants de paiement
                        Stripe. Nous ne recevons jamais vos numéros de carte.
                    </td>
                </tr>
                <tr>
                    <td>Échanges</td>
                    <td>Messages entre membres et pièces jointes, messages envoyés via le formulaire de contact.</td>
                </tr>
                <tr>
                    <td>Préférences</td>
                    <td>Alertes trajet, catégories suivies, choix de notification par e-mail, notifications reçues.</td>
                </tr>
                <tr>
                    <td>Données techniques</td>
                    <td>
                        Adresse IP et navigateur (session de connexion), adresse IP associée aux consultations
                        d'annonces et de produits (statistiques).
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <h2>3. Finalités et bases légales</h2>
    <div class="legal-table">
        <table>
            <thead>
                <tr>
                    <th>Pourquoi</th>
                    <th>Sur quelle base</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Créer et gérer votre compte</td><td>Exécution du contrat (CGU)</td></tr>
                <tr><td>Publier, réserver, acheter, payer et rembourser</td><td>Exécution du contrat</td></tr>
                <tr><td>Permettre les échanges entre membres (messagerie, coordonnées après réservation)</td><td>Exécution du contrat</td></tr>
                <tr><td>Montrer les passagers d'un trajet sur sa page (prénom, nom, photo)</td><td>Intérêt légitime : la confiance entre voyageurs</td></tr>
                <tr><td>Vous envoyer les alertes trajet et les notifications que vous avez demandées</td><td>Exécution du contrat</td></tr>
                <tr><td>Vérifier les justificatifs des conducteurs et des livreurs</td><td>Intérêt légitime : la sécurité des membres</td></tr>
                <tr><td>Traiter les signalements, modérer les contenus, prévenir la fraude</td><td>Obligation légale et intérêt légitime</td></tr>
                <tr><td>Fournir aux annonceurs des statistiques de consultation</td><td>Intérêt légitime</td></tr>
                <tr><td>Tenir la comptabilité des paiements</td><td>Obligation légale</td></tr>
                <tr><td>Répondre à vos demandes de contact</td><td>Intérêt légitime</td></tr>
            </tbody>
        </table>
    </div>

    <h2>4. Destinataires</h2>
    <ul>
        <li>
            <strong>Les autres membres</strong>, pour le strict nécessaire : votre profil public et vos annonces ;
            quand vous réservez un trajet, vos prénom, nom et photo sur la page de ce trajet, visibles de tous ses
            visiteurs ; après une réservation ou une commande, les coordonnées utiles
            pour l'organiser (en covoiturage, le conducteur et le passager voient le nom et le téléphone de l'autre).
        </li>
        <li><strong>Notre équipe</strong>, pour la validation des comptes et des justificatifs, la modération et le support.</li>
        <li>
            <strong>Nos prestataires</strong>, qui agissent sur nos instructions : Stripe (paiement),
            {{ $legal['host'] }} (hébergement), notre service d'envoi d'e-mails, la fondation OpenStreetMap
            (cartes et géocodage) et le service OSRM (calcul d'itinéraire).
        </li>
        <li>
            <strong>Les services qui livrent des ressources du site</strong> (bibliothèques, polices, images) :
            cdnjs (Cloudflare), jsDelivr, unpkg, Google Fonts, Bunny Fonts, Unsplash. Ils reçoivent votre adresse IP
            lorsque votre navigateur charge une page.
        </li>
        <li>Les autorités administratives ou judiciaires, sur réquisition légale.</li>
    </ul>
    <p>Vos données ne sont jamais vendues.</p>

    <h2>5. Transferts hors de l'Union européenne</h2>
    <p>
        Certains prestataires (paiement, diffusion de contenu, polices) peuvent traiter des données hors de l'Union
        européenne, notamment aux États-Unis. Ces transferts sont encadrés par la décision d'adéquation « Data
        Privacy Framework » ou par les clauses contractuelles types de la Commission européenne.
    </p>

    <h2>6. Durées de conservation</h2>
    <div class="legal-table">
        <table>
            <thead>
                <tr>
                    <th>Données</th>
                    <th>Durée</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Compte, profil, annonces, messages, alertes et notifications</td><td>Jusqu'à la suppression de votre compte</td></tr>
                <tr><td>Justificatifs (permis, carte VTC, pièce d'identité)</td><td>Tant que le rôle de conducteur ou de livreur est actif, au plus jusqu'à la suppression du compte</td></tr>
                <tr><td>Réservations, commandes et paiements</td><td>10 ans (obligations comptables, article L.123-22 du Code de commerce)</td></tr>
                <tr><td>Statistiques de consultation (adresse IP)</td><td>13 mois</td></tr>
                <tr><td>Session de connexion (adresse IP, navigateur)</td><td>Durée de la session</td></tr>
                <tr><td>Demandes de contact</td><td>3 ans après le dernier échange</td></tr>
            </tbody>
        </table>
    </div>

    <h2>7. Cookies et stockage local</h2>
    <p>
        {{ $legal['site'] }} n'utilise ni cookie publicitaire, ni outil de mesure d'audience. Seuls des cookies
        indispensables au fonctionnement du site sont déposés ; ils ne nécessitent pas votre consentement :
    </p>
    <ul>
        <li>le cookie de session, qui vous garde connecté pendant votre visite ;</li>
        <li>le jeton de sécurité XSRF-TOKEN, qui protège les formulaires contre les requêtes frauduleuses ;</li>
        <li>le cookie « Se souvenir de moi », si vous cochez cette option à la connexion ;</li>
        <li>les cookies de Stripe sur les pages de paiement, qui servent à prévenir la fraude.</li>
    </ul>
    <p>
        Le stockage local de votre navigateur retient aussi quelques préférences d'affichage (section ouverte du
        menu, par exemple) ; aucune donnée personnelle n'y est enregistrée.
    </p>

    <h2>8. Sécurité</h2>
    <p>
        Le site est servi en HTTPS, les mots de passe sont chiffrés, l'accès aux justificatifs est réservé à
        l'équipe chargée de leur validation, et les paiements passent par Stripe, certifié PCI-DSS.
    </p>

    <h2>9. Vos droits</h2>
    <p>
        Vous disposez d'un droit d'accès, de rectification, d'effacement, de limitation, d'opposition et de
        portabilité de vos données, ainsi que du droit de définir des directives sur leur sort après votre décès.
    </p>
    <ul>
        <li>Vous pouvez modifier votre profil et supprimer votre compte vous-même, depuis « Mon compte ».</li>
        <li>Vous choisissez les notifications que vous recevez par e-mail depuis votre profil, et gérez vos alertes trajet depuis « Mes alertes trajet ».</li>
        <li>
            Pour toute autre demande, écrivez à <a href="mailto:{{ $contact }}">{{ $contact }}</a>. Nous répondons
            dans un délai d'un mois ; une preuve d'identité peut vous être demandée en cas de doute.
        </li>
    </ul>
    <p>
        Si vous estimez que vos droits ne sont pas respectés, vous pouvez adresser une réclamation à la CNIL
        (<a href="https://www.cnil.fr" target="_blank" rel="noopener">www.cnil.fr</a>).
    </p>

    <h2>10. Mineurs</h2>
    <p>La plateforme est réservée aux personnes majeures : nous ne collectons pas sciemment de données de mineurs.</p>

    <h2>11. Modifications</h2>
    <p>
        Cette politique peut évoluer ; la date de sa dernière mise à jour figure en haut de page. Toute
        modification importante vous sera signalée.
    </p>
@endsection
