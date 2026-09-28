@php
    /*
     | En-tete de l'espace connecte.
     |
     | Le titre affiche est deduit de la route courante : le header sait donc
     | toujours ou l'on se trouve, sans qu'aucune page ait a le lui passer.
     | Une page non listee reprend le titre de son onglet (@section('title')) ;
     | « Mon espace » ne reste qu'en dernier recours.
     |
     | x-user-dropdown est partage avec le header public. Il est appele ici en
     | mode « compact » : la barre laterale couvre deja la navigation, le menu
     | ne garde donc que ce qui lui est propre (identite, retour au site
     | public, compte, deconnexion).
     |
     | Les actions de publication ont quitte le header : elles vivent dans la
     | pastille flottante (<x-publish-fab />, appelee par le layout), qui reste
     | atteignable quelle que soit la largeur d'ecran.
     */
    $pages = [
        'dashboard'                 => ['Tableau de bord', 'Vue d\'ensemble'],
        'seller.produits.index'     => ['Mes produits', 'Vendeur'],
        'seller.produits.create'    => ['Ajouter un produit', 'Vendeur'],
        'seller.produits.edit'      => ['Modifier un produit', 'Vendeur'],
        'seller.sales'              => ['Mes ventes', 'Vendeur'],
        'seller.sales.show'         => ['Détail d\'une vente', 'Vendeur'],
        'seller.clientOrders'       => ['Commandes clients', 'Vendeur'],
        'orders'                    => ['Mes commandes', 'Mes achats'],
        'orders.show'               => ['Suivi de commande', 'Mes achats'],
        'bookings.receivedBookings' => ['Réservations reçues', 'Locations'],
        'bookings.myBookings'       => ['Mes réservations', 'Locations'],
        'bookings.show'             => ['Suivi de réservation', 'Locations'],
        'ads.index'                 => ['Mes annonces', 'Annonces'],
        'ads.create'                => ['Déposer une annonce', 'Annonces'],
        'ads.edit'                  => ['Modifier une annonce', 'Annonces'],
        'archives'                  => ['Archives', 'Mon activité'],
        'statistiques'              => ['Statistiques', 'Annonces'],
        'favoris'                   => ['Favoris', 'Mes envies'],
        'messages'                  => ['Messages', 'Échanges'],
        'walt.index'                => ['Portefeuille', 'Finances'],
        'profile'                   => ['Mon compte', 'Paramètres'],
        'livreur.ads.index'         => ['Demandes de livraison', 'Livraison'],
        'livreur.missions'          => ['Missions disponibles', 'Livreur'],
        'livreur.demandes'          => ['Missions en attente', 'Livreur'],
        'livreur.livraisons'        => ['Missions en cours', 'Livreur'],
        'liv_termine'               => ['Livraisons terminées', 'Livreur'],
        'livreur.documents'         => ['Documents requis', 'Chauffeur VTC'],
        'covoiturage.index'         => ['Mes trajets', 'Chauffeur VTC'],
        'covoiturage.create'        => ['Ajouter un trajet', 'Chauffeur VTC'],
        'trajet.show'               => ['Détail du trajet', 'Chauffeur VTC'],
        'covoiturage.edit'          => ['Modifier le trajet', 'Chauffeur VTC'],
        'covoiturage.edit-date-time' => ['Date et heure du trajet', 'Chauffeur VTC'],
        'covoiturage.edititen.edit' => ['Itinéraire du trajet', 'Chauffeur VTC'],
        'covoiturage.edit-route'    => ['Modifier l\'itinéraire', 'Chauffeur VTC'],
        'covoiturage.options.edit'  => ['Places et confort', 'Chauffeur VTC'],
        'covoiturage.prix.edit'     => ['Prix du trajet', 'Chauffeur VTC'],
        'covoiturage.editMode'      => ['Mode de réservation', 'Chauffeur VTC'],
        'covoiturage.add-retour'    => ['Ajouter un retour', 'Chauffeur VTC'],
        'covoiturage.edit-retour'   => ['Modifier le retour', 'Chauffeur VTC'],
        'trips.received'            => ['Réservations reçues', 'Chauffeur VTC'],
        'vehicle.edit'              => ['Mon véhicule', 'Chauffeur VTC'],
        'trips.myBookings'          => ['Mes trajets réservés', 'Mes achats'],
        'trips.myBookings.show'     => ['Détail de la réservation', 'Covoiturage'],
    ];

    // Page non listee : le titre de l'onglet, sans le nom du site
    // (« Mon véhicule | Olten » -> « Mon véhicule »). La section est
    // echappee par Blade, d'ou le decodage avant affichage.
    $tabTitle = html_entity_decode(trim($__env->yieldContent('title')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $tabTitle = trim(preg_split('/\s+[|–-]\s+/u', $tabTitle)[0] ?? '');

    [$pageTitle, $pageSection] = $pages[request()->route()?->getName()]
        ?? ($tabTitle !== '' ? [$tabTitle, 'Mon espace'] : ['Mon espace', 'Olten']);
@endphp

<header class="connected-header">

    <div class="header-left">
        <button type="button" class="btn-toggle-sidebar" aria-label="Ouvrir le menu">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <div class="header-title">
        <span class="header-eyebrow">{{ $pageSection }}</span>
        <span class="header-heading">{{ $pageTitle }}</span>
    </div>

    <div class="header-right">
        <x-user-dropdown compact />
    </div>
</header>
