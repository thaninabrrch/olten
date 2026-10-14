/*
 | Formulaire d'annonce (depot et modification) : il suit le service de la
 | categorie choisie.
 |
 |   location (defaut) : periode de disponibilite, tarif journalier, livraison
 |   vente             : pas de periode, prix ferme
 |   prestation        : le client decrit son besoin ; la periode devient la
 |                       fenetre de realisation, le prix son budget, et il
 |                       n'y a pas de livraison
 |
 | Le balisage porte les variantes de chaque mode :
 |   data-mode-text        texte remplace : data-vente="…", data-prestation="…"
 |   data-mode-placeholder idem pour un placeholder
 |   data-mode-hide="…"    bloc masque dans les modes listes (« vente »,
 |                         « prestation ») ; ses champs cessent d'etre exiges,
 |                         sinon le navigateur bloquerait l'envoi sur un champ
 |                         invisible
 | Le texte present dans la page est celui de la location.
 |
 | Les memes regles sont rejouees cote serveur dans AdController.
 */
(function () {
    const categorie = document.getElementById('category_id');
    if (!categorie) return;

    // slug du service de la categorie => mode du formulaire
    const MODES = {
        'vente': 'vente',
        'prestations-de-services': 'prestation',
    };

    const textes       = document.querySelectorAll('[data-mode-text]');
    const placeholders = document.querySelectorAll('[data-mode-placeholder]');
    const blocs        = document.querySelectorAll('[data-mode-hide]');

    // Le texte d'origine est celui de la location : on le garde de cote.
    textes.forEach(el => { el.dataset.location = el.textContent; });
    placeholders.forEach(el => { el.dataset.location = el.placeholder; });
    blocs.forEach(bloc => {
        bloc.querySelectorAll('[required]').forEach(champ => { champ.dataset.required = '1'; });
    });

    function appliquer() {
        const option = categorie.selectedOptions[0];
        const mode   = MODES[option ? option.dataset.service : ''] || 'location';

        textes.forEach(el => {
            el.textContent = el.dataset[mode] ?? el.dataset.location;
        });

        placeholders.forEach(el => {
            el.placeholder = el.dataset[mode] ?? el.dataset.location;
        });

        blocs.forEach(bloc => {
            const masque = bloc.dataset.modeHide.split(' ').includes(mode);
            bloc.hidden = masque;

            bloc.querySelectorAll('input').forEach(champ => {
                champ.required = !masque && champ.dataset.required === '1';

                if (!masque) return;

                if (champ.type === 'checkbox') {
                    champ.checked = false;
                } else if (champ.type !== 'hidden') {
                    champ.value = '';
                }
            });
        });

        renumeroter();
    }

    // Les etapes sont numerotees a la main dans le balisage : masquer la
    // deuxieme laisserait la suite en 1, 3, 4. On renumerote ce qui reste.
    function renumeroter() {
        let numero = 0;

        document.querySelectorAll('.sp-form-section').forEach(section => {
            if (section.hidden) return;
            const puce = section.querySelector('.sp-step');
            if (puce) puce.textContent = ++numero;
        });
    }

    categorie.addEventListener('change', appliquer);
    appliquer();
})();
