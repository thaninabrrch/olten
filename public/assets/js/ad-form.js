
/*
 | Formulaire d'annonce (depot et modification)
 |
 | location     : disponibilites + adresse + tarif location
 | vente        : prix et stock + adresse + prix de vente
 | prestation   : période de réalisation + adresse + budget
 | livraison    : tarif de livraison uniquement (hors informations générales)
 */

(function () {
    const categorie = document.getElementById('category_id');
    if (!categorie) return;

    // Slug du service de la categorie => mode du formulaire
    const MODES = {
        'vente': 'vente',
        'prestations-de-services': 'prestation',
        'livraison': 'livraison',
    };

    const textes = document.querySelectorAll('[data-mode-text]');
    const placeholders = document.querySelectorAll('[data-mode-placeholder]');

    // Tous les éléments conditionnels
    const elements = document.querySelectorAll('[data-mode-hide]');

    // Sections du formulaire avec gestion de visibilité
    const sections = document.querySelectorAll(
        '.sp-form-section[data-mode-hide], .sp-form-section[data-mode-only]'
    );

    // Conserver les textes d'origine (location)
    textes.forEach(el => {
        el.dataset.location = el.textContent;
    });

    placeholders.forEach(el => {
        el.dataset.location = el.placeholder;
    });

    // Memoriser les champs obligatoires et leur état disabled initial
    sections.forEach(section => {
        section.querySelectorAll('input, select, textarea, button').forEach(champ => {
            champ.dataset.originalRequired = champ.required ? '1' : '0';
            champ.dataset.originalDisabled = champ.disabled ? '1' : '0';
        });
    });

    function appliquer() {
        const option = categorie.selectedOptions[0];

        // Determiner le mode
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

        // Gestion des éléments data-mode-hide (textes, aides...)
        elements.forEach(el => {
            const modesMasques = (el.dataset.modeHide || '').split(/\s+/);

            el.hidden = !mode || modesMasques.includes(mode);
        });

        // Gestion des sections data-mode-hide et data-mode-only
        sections.forEach(section => {
            const modesMasques = (section.dataset.modeHide || '').split(/\s+/);
            const modesAutorises = (section.dataset.modeOnly || '').split(/\s+/);

            const masque =
                !mode ||
                modesMasques.includes(mode) ||
                (
                    section.hasAttribute('data-mode-only') &&
                    !modesAutorises.includes(mode)
                );

            section.hidden = masque;

            // Désactiver les champs des sections masquées
            section.querySelectorAll('input, select, textarea, button').forEach(champ => {
                champ.disabled = masque || champ.dataset.originalDisabled === '1';

                champ.required = !masque &&
                    champ.dataset.originalRequired === '1';
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
