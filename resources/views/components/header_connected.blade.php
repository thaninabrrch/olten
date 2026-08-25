@php
    /*
     | En-tete de l'espace connecte.
     |
     | Le titre affiche est deduit de la route courante : le header sait donc
     | toujours ou l'on se trouve, sans qu'aucune page ait a le lui passer.
     | Les pages non listees retombent sur « Mon espace ».
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
        'livreur.carte.vtc'         => ['Documents requis', 'Chauffeur VTC'],
        'covoiturage.index'         => ['Mes trajets', 'Chauffeur VTC'],
        'covoiturage.create'        => ['Ajouter un trajet', 'Chauffeur VTC'],
    ];

    [$pageTitle, $pageSection] = $pages[request()->route()?->getName()] ?? ['Mon espace', 'Olten'];
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
