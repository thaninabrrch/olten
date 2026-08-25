@php
    /*
     | Barre laterale de l'espace connecte.
     |
     | Le menu etait une liste plate de dix-huit a vingt-trois entrees : il
     | fallait faire defiler la colonne pour atteindre le bas. Les entrees sont
     | desormais regroupees dans des sections repliables (<details>), ce qui
     | ramene la barre a neuf lignes au repos. Aucun lien n'a disparu et aucune
     | destination n'a change : seul le regroupement est nouveau.
     |
     | Une section s'ouvre d'elle-meme quand elle contient la page courante, et
     | cette ouverture est calculee cote serveur : pas de clignotement au
     | chargement. Le reste de l'etat (sections ouvertes a la main) est conserve
     | d'une page a l'autre par le script en bas de fichier.
     |
     | « Mon compte » et « Se deconnecter » ne sont plus ici : ils vivent dans
     | le menu utilisateur du header, visible depuis toutes les pages. Les
     | garder aux deux endroits etait une redondance pure.
     */
    $user     = auth()->user();
    $approved = (bool) $user->is_approved;

    /* Liens de premier niveau : les trois destinations les plus frequentes
       restent accessibles en un clic, sans avoir a deplier quoi que ce soit. */
    $raccourcis = [
        ['Tableau de bord', 'fa-gauge-high',   url('/dashboard'),    request()->is('dashboard')],
        ['Messages',        'fa-comment-dots', route('messages'),    request()->routeIs('messages*')],
        ['Portefeuille',    'fa-wallet',       url('/portefeuille'), request()->routeIs('walt.index')],
    ];

    /* Sections repliables. Chaque entree : [libelle, icone, url, actif, verrouille] */
    $sections = [];

    $sections[] = [
        'label' => 'Annonces',
        'icon'  => 'fa-bullhorn',
        'items' => [
            ['Mes annonces',        'fa-list',        route('ads.index'),                   request()->routeIs('ads.index') || request()->routeIs('ads.edit'), false],
            ['Déposer une annonce', 'fa-circle-plus', $approved ? route('ads.create') : '#', request()->routeIs('ads.create'), ! $approved],
            ['Statistiques',        'fa-chart-line',  route('statistiques'),                request()->is('statistiques'), false],
        ],
    ];

    $sections[] = [
        'label' => 'Locations',
        'icon'  => 'fa-calendar-check',
        'items' => [
            ['Réservations reçues', 'fa-calendar-plus',  url('/mes-reservations-recues'), request()->routeIs('bookings.receivedBookings'), false],
            ['Mes réservations',    'fa-calendar-check', url('/mes-reservations'),        request()->routeIs('bookings.myBookings'), false],
        ],
    ];

    $sections[] = [
        'label' => 'Ma boutique',
        'icon'  => 'fa-store',
        'items' => [
            ['Mes produits',      'fa-box',     route('seller.produits.index'), request()->is('vendeur/produits*'), false],
            ['Mes ventes',        'fa-receipt', route('seller.sales'),          request()->is('vendeur/ventes*'), false],
            ['Commandes clients', 'fa-inbox',   route('seller.clientOrders'),   request()->is('vendeur/commandes-clients*'), false],
        ],
    ];

    $sections[] = [
        'label' => 'Mes achats',
        'icon'  => 'fa-bag-shopping',
        'items' => [
            ['Mes commandes', 'fa-bag-shopping', route('orders'),  request()->is('mes-commandes*'), false],
            ['Favoris',       'fa-heart',        route('favoris'), request()->is('favoris'), false],
        ],
    ];

    /* Livraison : tout le monde peut demander une livraison, seuls les livreurs
       voient les missions. Les deux tenaient auparavant deux sections. */
    $livraison = [
        ['Demandes de livraison', 'fa-truck-ramp-box', $approved ? route('livreur.ads.index') : '#', request()->routeIs('livreur.ads.index'), ! $approved],
    ];

    if ($user->hasRole('livreur')) {
        $livraison[] = ['Missions de livraison', 'fa-route',           route('livreur.missions'), request()->routeIs('livreur.missions') || request()->routeIs('livreur.demandes') || request()->routeIs('livreur.livraisons'), false];
        $livraison[] = ['Livraisons terminées',  'fa-clipboard-check', route('liv_termine'),      request()->routeIs('liv_termine'), false];

        /* Le livreur doit pouvoir atteindre ses documents : sans permis
           valide, tout son espace livraison est ferme. L'entree n'apparait
           ici que s'il n'est pas aussi chauffeur VTC, auquel cas elle figure
           deja dans la section « Chauffeur VTC ». */
        if (! $user->is_vtc_driver) {
            $livraison[] = ['Documents requis', 'fa-id-card', route('livreur.carte.vtc'), request()->routeIs('livreur.carte.vtc'), false];
        }
    }

    $sections[] = ['label' => 'Livraison', 'icon' => 'fa-truck', 'items' => $livraison];

    if ($user->is_vtc_driver) {
        $sections[] = [
            'label' => 'Chauffeur VTC',
            'icon'  => 'fa-car-side',
            'items' => [
                ['Documents requis',         'fa-id-card',          route('livreur.carte.vtc'),  request()->routeIs('livreur.carte.vtc'), false],
                ['Mes trajets',       'fa-map-location-dot', route('covoiturage.index'),  request()->routeIs('covoiturage.index'), false],
                ['Ajouter un trajet', 'fa-circle-plus',      route('covoiturage.create'), request()->routeIs('covoiturage.create'), false],
            ],
        ];
    }
@endphp

<aside class="connected-sidebar">

    {{-- Logo --}}
    <div class="sidebar-logo">
        <a href="{{ url('/') }}" class="logo" aria-label="Accueil Olten">
            <img src="{{ asset('assets/images/logo/olten_location.png') }}" alt="Olten">
        </a>
    </div>

    <nav class="sidebar-menu" aria-label="Navigation principale">

        {{-- Acces directs --}}
        <ul class="menu-shortcuts">
            @foreach ($raccourcis as [$label, $icon, $url, $active])
                <li class="{{ $active ? 'active' : '' }}">
                    <a href="{{ $url }}">
                        <i class="fa-solid {{ $icon }}"></i>
                        <span>{{ $label }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <p class="menu-section">Mon activité</p>

        {{-- Sections repliables --}}
        @foreach ($sections as $section)
            @php
                $open = collect($section['items'])->contains(fn ($i) => $i[3]);
                $key  = \Illuminate\Support\Str::slug($section['label']);
            @endphp

            <details class="menu-group" data-group="{{ $key }}" @if($open) open @endif>
                <summary>
                    <i class="fa-solid {{ $section['icon'] }}"></i>
                    <span>{{ $section['label'] }}</span>
                    <i class="fa-solid fa-chevron-down menu-caret" aria-hidden="true"></i>
                </summary>

                <ul>
                    @foreach ($section['items'] as [$label, $icon, $url, $active, $locked])
                        <li class="{{ $active ? 'active' : '' }}">
                            <a href="{{ $url }}" class="{{ $locked ? 'is-locked' : '' }}"
                               @if($locked) aria-disabled="true" tabindex="-1" @endif>
                                <i class="fa-solid {{ $icon }}"></i>
                                <span>{{ $label }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </details>
        @endforeach
    </nav>
</aside>

<script>
    /*
     | Accordeon : une seule section ouverte a la fois. Plusieurs sections
     | deployees repoussaient les titres suivants hors de l'ecran, et il
     | fallait faire defiler pour retrouver le menu — soit exactement ce que
     | le repliage devait eviter.
     |
     | On ne memorise donc plus une liste de sections ouvertes mais une seule
     | cle. L'ancienne cle « olten.sidebar.groups » est nettoyee au passage.
     */
    (function () {
        var CLE    = 'olten.sidebar.group';
        var groups = document.querySelectorAll('.connected-sidebar .menu-group');
        if (!groups.length) return;

        try { localStorage.removeItem('olten.sidebar.groups'); } catch (e) {}

        function lire() {
            try { return localStorage.getItem(CLE); } catch (e) { return null; }
        }

        function ecrire(valeur) {
            try {
                valeur ? localStorage.setItem(CLE, valeur) : localStorage.removeItem(CLE);
            } catch (e) {}
        }

        /* La section de la page courante est ouverte par le serveur et prime
           sur la memoire : c'est la seule qui compte a l'arrivee. */
        var imposee = null;
        groups.forEach(function (g) {
            if (g.hasAttribute('open')) imposee = g;
        });

        if (! imposee) {
            var memorisee = lire();
            groups.forEach(function (g) {
                if (g.dataset.group === memorisee) {
                    g.setAttribute('open', '');
                    imposee = g;
                }
            });
        }

        /* « toggle » est emis de facon asynchrone : refermer les voisines
           ci-dessous declenchera leur propre evenement, plus tard. Aucun
           drapeau synchrone ne pourrait donc les filtrer.
           On s'appuie sur la cle memorisee : quand la fermeture d'une voisine
           nous revient, la cle porte deja le nom de la section qu'on vient
           d'ouvrir, et la branche de fermeture ne touche a rien. */
        groups.forEach(function (g) {
            g.addEventListener('toggle', function () {
                if (g.open) {
                    groups.forEach(function (autre) {
                        if (autre !== g) autre.removeAttribute('open');
                    });

                    ecrire(g.dataset.group);
                    revelerSection(g, 'smooth');
                } else if (lire() === g.dataset.group) {
                    ecrire(null);
                }
            });
        });

        /* Une section ouverte tout en bas de la colonne restait sous la ligne
           de flottaison : on la ramene dans la zone visible. */
        function revelerSection(g, comportement) {
            var zone = g.closest('.sidebar-menu');
            if (! zone) return;

            requestAnimationFrame(function () {
                var bas     = g.offsetTop + g.offsetHeight;
                var visible = zone.scrollTop + zone.clientHeight;

                if (bas > visible) {
                    zone.scrollTo({
                        top: bas - zone.clientHeight + 14,
                        behavior: comportement
                    });
                }
            });
        }

        if (imposee) revelerSection(imposee, 'auto');
    })();
</script>
