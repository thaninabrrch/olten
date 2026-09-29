@extends('pages.legal.layout')

@section('title', 'Conditions générales de vente - Olten.fr')
@section('legal_title', 'Conditions générales de vente')

@php
    /*
     | Les montants qui evoluent (taux des frais de service, prix des
     | abonnements) sont lus a la source, pour que le texte ne contredise
     | jamais ce que le site facture.
     */
    $legal   = config('olten.legal');
    $contact = config('olten.email.contact');
    $rate    = rtrim(rtrim(number_format(\App\Models\Covoiturage::serviceRate(), 2, ',', ''), '0'), ',');
    $shift   = \App\Models\Covoiturage::bookedTimeShift();
    $plans   = \App\Models\Subscription::orderBy('price')->get();
@endphp

@section('legal')
    <p>
        Les présentes conditions générales de vente (CGV) s'appliquent à tout paiement effectué sur
        {{ $legal['site'] }} : réservation d'une location, achat d'un produit, réservation d'une place en
        covoiturage et souscription d'un abonnement. Elles complètent les
        <a href="{{ route('legal.cgu') }}">conditions générales d'utilisation</a>. Tout paiement vaut
        acceptation des CGV en vigueur à sa date.
    </p>

    <h2>1. Prix</h2>
    <p>Les prix sont indiqués en euros, toutes taxes comprises, avant tout paiement.</p>
    <ul>
        <li>
            <strong>Location et vente :</strong> le prix est fixé par le membre qui publie l'annonce ou le produit.
            Une livraison d'objet acheté est facturée 1 € par kilomètre entamé, affichée avant le paiement.
        </li>
        <li>
            <strong>Covoiturage :</strong> le prix d'une place est fixé par le conducteur. La plateforme y ajoute
            des frais de service de {{ $rate }} %, affichés séparément sur la liste des trajets, sur la fiche du
            trajet et avant le paiement. Chaque sens est facturé selon le nombre de places réservées sur ce sens.
        </li>
        <li><strong>Abonnements :</strong> voir l'article 7.</li>
    </ul>

    <h2>2. Paiement</h2>
    <p>
        Le paiement s'effectue par carte bancaire, au moment de la réservation ou de la commande, par
        l'intermédiaire de notre prestataire de paiement Stripe. Votre banque peut vous demander une
        authentification (3-D Secure). {{ $legal['site'] }} ne reçoit ni ne conserve vos numéros de carte.
    </p>

    <h2>3. Covoiturage</h2>
    <ul>
        <li>
            Vous choisissez l'aller, le retour ou les deux, et le nombre de places de chaque sens : par exemple
            2 places à l'aller et 1 au retour. En réservation immédiate, la réservation est confirmée dès que le
            paiement est accepté.
        </li>
        <li>
            <strong>Validation manuelle :</strong> sur un trajet où le conducteur accepte chaque demande, vous
            payez en l'envoyant et la place vous est réservée. Si le conducteur refuse, ou s'il n'a pas répondu à
            l'heure du départ, vous êtes intégralement remboursé.
        </li>
        <li>
            <strong>Annulation par le passager :</strong> possible depuis « Mes trajets réservés » jusqu'au départ.
            Le passager est intégralement remboursé, frais de service compris.
        </li>
        <li>
            <strong>Engagement du conducteur :</strong> un trajet réservé ne peut plus être annulé par le
            conducteur, ni changer de date, d'itinéraire ou de prix.
        </li>
        <li>
            <strong>Changement d'horaire :</strong> le conducteur peut décaler l'horaire d'un trajet réservé de
            {{ $shift }} minutes au plus. Le passager en est prévenu et peut, si le nouvel horaire ne lui convient
            pas, annuler sa réservation avec remboursement intégral.
        </li>
    </ul>

    <h2>4. Location</h2>
    <ul>
        <li>Le montant de la location est payé au moment de la demande de réservation.</li>
        <li>
            Le propriétaire accepte ou refuse la demande. En cas de refus, le locataire est intégralement
            remboursé.
        </li>
    </ul>

    <h2>5. Vente</h2>
    <ul>
        <li>Le produit est payé au moment de la commande, dans la limite du stock disponible.</li>
        <li>
            Le vendeur peut annuler la commande tant qu'elle n'a pas été expédiée : l'acheteur est alors
            intégralement remboursé.
        </li>
    </ul>

    <h2>6. Remboursements</h2>
    <p>
        Les remboursements sont effectués sur la carte utilisée pour le paiement. Selon votre banque, le montant
        apparaît sur votre compte sous 5 à 10 jours ouvrés. Chaque remboursement est visible dans votre espace
        (« Annulés » dans vos réservations, statut de vos commandes).
    </p>

    <h2>7. Abonnements</h2>
    @if ($plans->isNotEmpty())
        <div class="legal-table">
            <table>
                <thead>
                    <tr>
                        <th>Formule</th>
                        <th>Prix</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($plans as $plan)
                        <tr>
                            <td>{{ $plan->name }}</td>
                            <td>{{ number_format((float) $plan->price, 2, ',', ' ') }} € par mois</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <ul>
        <li>
            Le contenu de chaque formule est décrit sur la page <a href="{{ route('subscriptions.index') }}">Abonnements</a>.
        </li>
        <li>L'abonnement est mensuel et se renouvelle automatiquement chaque mois, au même prix.</li>
        <li>
            Vous pouvez le résilier à tout moment en écrivant à <a href="mailto:{{ $contact }}">{{ $contact }}</a> :
            la résiliation prend effet à la fin du mois en cours, sans nouveau prélèvement.
        </li>
    </ul>

    <h2>8. Droit de rétractation</h2>
    <ul>
        <li>
            <strong>Abonnements :</strong> vous disposez de 14 jours à compter de la souscription pour vous
            rétracter, sans avoir à vous justifier, en écrivant à <a href="mailto:{{ $contact }}">{{ $contact }}</a>.
            Si vous avez demandé que l'abonnement commence avant la fin de ce délai, le montant correspondant au
            service déjà fourni reste dû (article L.221-25 du Code de la consommation).
        </li>
        <li>
            <strong>Covoiturage et location :</strong> ces contrats sont conclus entre particuliers, et le
            transport de passagers est par ailleurs exclu du droit de rétractation (article L.221-2 du Code de la
            consommation). Les conditions d'annulation des articles 3 et 4 s'appliquent.
        </li>
        <li>
            <strong>Achats auprès d'un particulier :</strong> le droit de rétractation du Code de la consommation
            ne s'applique pas entre particuliers. Un vendeur professionnel en reste tenu.
        </li>
    </ul>

    <h2>9. Qualité des membres et classement des offres</h2>
    <p>
        Les offres sont publiées par des particuliers : les garanties légales dues par un professionnel
        (conformité, vices cachés du Code de la consommation) ne s'appliquent pas à une vente entre
        particuliers. Un membre qui agit à titre professionnel doit l'indiquer et reste soumis au droit de la
        consommation.
    </p>
    <p>
        Par défaut, les annonces et produits sont affichés du plus récent au plus ancien ; vous pouvez les
        trier par prix ou par popularité (nombre de consultations). Les liaisons de covoiturage sont classées
        par date du prochain départ, ou par prix. À ce jour, aucun classement n'est influencé par une
        rémunération ; si une offre venait à être mise en avant contre rémunération, elle serait signalée comme
        telle.
    </p>

    <h2>10. Réclamations, médiation et droit applicable</h2>
    <p>
        Pour toute réclamation, écrivez à <a href="mailto:{{ $contact }}">{{ $contact }}</a> : nous vous
        répondons dans les meilleurs délais. Si aucune solution n'est trouvée, vous pouvez recourir gratuitement
        au médiateur de la consommation : {{ $legal['mediator'] }}. Les présentes CGV sont soumises au droit
        français ; à défaut d'accord amiable, les tribunaux français sont compétents.
    </p>
@endsection
