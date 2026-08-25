@php
    /*
     | En-tete de l'espace connecte.
     |
     | Le titre affiche est deduit de la route courante : le header sait donc
     | toujours ou l'on se trouve, sans qu'aucune page ait a le lui passer.
     | Les pages non listees retombent sur « Mon espace ».
     |
     | x-user-dropdown est un composant partage avec le header public : il est
     | inclus tel quel, sans modification, pour ne pas impacter le site.
     |
     | Les actions de publication ne sont plus filtrees par role : elles sont
     | regroupees dans un menu « Publier » ouvert a tous, que is_approved
     | verrouille tant que le compte n'est pas valide.
     */
    $user = auth()->user();

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
        'livreur.carte.vtc'         => ['Carte VTC', 'Chauffeur VTC'],
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
        {{-- Un seul bouton porte les deux actions de publication : le header
             reste lisible, et les deux restent joignables depuis n'importe
             quelle page. <details> gere l'ouverture, aucun JS n'est requis
             pour cela — le script ne sert qu'a refermer le menu. --}}
        <details class="header-publish {{ $user->is_approved ? '' : 'is-locked' }}">
            <summary>
                <i class="fa-solid fa-plus"></i>
                <span>Publier</span>
            </summary>

            <div class="header-publish-menu">
                <a href="{{ route('ads.create') }}">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>
                        <strong>Déposer une annonce</strong>
                        Mettre un bien en location
                    </span>
                </a>

                <a href="{{ route('seller.produits.create') }}">
                    <i class="fa-solid fa-box"></i>
                    <span>
                        <strong>Ajouter un produit</strong>
                        Mettre un article en vente
                    </span>
                </a>
            </div>
        </details>

        <span class="header-divider" aria-hidden="true"></span>

        <x-user-dropdown />
    </div>
</header>

<script>
    // Le menu « Publier » s'ouvre tout seul (<details>) ; ce script ne gere
    // que sa fermeture, au clic a l'exterieur ou sur Echap.
    (function () {
        const menu = document.querySelector('.header-publish');
        if (!menu) return;

        document.addEventListener('click', function (e) {
            if (!menu.contains(e.target)) menu.removeAttribute('open');
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') menu.removeAttribute('open');
        });
    })();
</script>
