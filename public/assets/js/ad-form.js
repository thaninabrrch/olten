
/*
 | Formulaire d'annonce (depot et modification)
 |
 | location     : disponibilites
 | vente        : prix et stock
 | prestation   : aucune de ces deux sections
 */
(function () {
    const categorie = document.getElementById('category_id');
    if (!categorie) return;

    // Slug du service de la categorie => mode du formulaire
    const MODES = {
        'vente': 'vente',
        'prestations-de-services': 'prestation',
    };

    const textes = document.querySelectorAll('[data-mode-text]');
    const placeholders = document.querySelectorAll('[data-mode-placeholder]');
    const blocs = document.querySelectorAll('[data-mode-hide]');

    // Conserver les textes d'origine (location)
    textes.forEach(el => {
        el.dataset.location = el.textContent;
    });

    placeholders.forEach(el => {
        el.dataset.location = el.placeholder;
    });

    // Memoriser les champs obligatoires
    blocs.forEach(bloc => {
        bloc.querySelectorAll('[required]').forEach(champ => {
            champ.dataset.required = '1';
        });
    });

    function appliquer() {
        const option = categorie.selectedOptions[0];

        // Aucun mode tant qu'aucune categorie n'est selectionnee
        const mode = option && option.value
            ? MODES[option.dataset.service] || 'location'
            : null;

        // Modifier les textes
        textes.forEach(el => {
            el.textContent = mode
                ? el.dataset[mode] ?? el.dataset.location
                : el.dataset.location;
        });

        // Modifier les placeholders
        placeholders.forEach(el => {
            el.placeholder = mode
                ? el.dataset[mode] ?? el.dataset.location
                : el.dataset.location;
        });

        // Afficher ou masquer les sections
        blocs.forEach(bloc => {
            const modesMasques = bloc.dataset.modeHide.split(' ');

            const masque = !mode || modesMasques.includes(mode);

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

    // Renumeroter les sections visibles
    function renumeroter() {
        let numero = 0;

        document.querySelectorAll('.sp-form-section').forEach(section => {
            if (section.hidden) return;

            const puce = section.querySelector('.sp-step');

            if (puce) {
                puce.textContent = ++numero;
            }
        });
    }

    // Changement de categorie
    categorie.addEventListener('change', () => {
        const lien = categorie.selectedOptions[0]?.dataset.href;

        if (lien) {
            window.location.href = lien;
            return;
        }

        appliquer();
    });

    // Initialisation
    appliquer();

})();
