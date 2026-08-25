@php
    /*
     | Pastille de publication, posee en bas a droite du viewport.
     |
     | Elle remplace le bouton « Publier » qui vivait dans le header. Celui-ci
     | disparaissait sous 1024px : sur tablette et sur telephone, il n'existait
     | aucun moyen direct de publier. La pastille, elle, ne depend pas de la
     | largeur d'ecran et suit le defilement.
     |
     | Bati sur <details>, comme les autres menus de l'espace connecte : il
     | s'ouvre sans JavaScript, le script ne sert qu'a le refermer.
     |
     | z-index 900 : au-dessus du contenu et du header (100), mais sous le
     | voile de la barre laterale mobile (999), sous la conversation plein
     | ecran de la messagerie (1000) et sous les fenetres modales (2000).
     */
    $user   = auth()->user();
    $locked = ! $user->is_approved;
@endphp

<details class="publish-fab {{ $locked ? 'is-locked' : '' }}"
         @if($locked) title="Votre compte doit être validé avant de pouvoir publier" @endif>

    <summary aria-label="Publier">
        <i class="fa-solid fa-plus"></i>
    </summary>

    <div class="publish-fab-menu">
        <a href="{{ route('ads.create') }}">
            <span>Déposer une annonce</span>
            <i class="fa-solid fa-bullhorn"></i>
        </a>

        <a href="{{ route('seller.produits.create') }}">
            <span>Ajouter un produit</span>
            <i class="fa-solid fa-box"></i>
        </a>
    </div>
</details>

<script>
    // Fermeture au clic a l'exterieur ou sur Echap. L'ouverture est geree par
    // <details> lui-meme.
    (function () {
        const fab = document.querySelector('.publish-fab');
        if (!fab) return;

        document.addEventListener('click', function (e) {
            if (!fab.contains(e.target)) fab.removeAttribute('open');
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') fab.removeAttribute('open');
        });
    })();
</script>
