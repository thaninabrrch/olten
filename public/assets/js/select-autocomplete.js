/*
 | Listes avec recherche (Tom Select) : tout <select data-autocomplete> de
 | l'espace membre, aujourd'hui le choix de la categorie dans les formulaires
 | d'annonce et de produit. La valeur de l'attribut sert de texte d'invite.
 |
 | La recherche porte sur le nom de la categorie et sur celui de son service
 | (data-service-name sur chaque option) : taper « location » montre toutes
 | les categories de la location. Elle ignore les accents. L'icone de la
 | categorie (data-icon) s'affiche devant son nom.
 |
 | Le <select> d'origine reste dans le formulaire et garde sa valeur : Tom
 | Select y redeclenche « change », dont dependent assets/js/ad-form.js
 | (bascule location / vente / prestation) et l'assistant de redaction.
 | Si la librairie ne charge pas, le select natif reste utilisable.
 */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof TomSelect === 'undefined') return;

    document.querySelectorAll('select[data-autocomplete]').forEach(function (select) {
        const icon = function (data, escape) {
            return data.icon ? '<i class="' + escape(data.icon) + '" aria-hidden="true"></i>' : '';
        };

        new TomSelect(select, {
            maxOptions: null,
            searchField: ['text', 'serviceName'],
            placeholder: select.dataset.autocomplete || 'Rechercher…',
            // Le menu est pose sur la page : un bloc du formulaire ne peut
            // pas le couper
            dropdownParent: 'body',
            wrapperClass: 'ts-wrapper olt-ts',
            dropdownClass: 'ts-dropdown olt-ts-dropdown',
            render: {
                option: function (data, escape) {
                    return '<div>' + icon(data, escape) + '<span>' + escape(data.text) + '</span></div>';
                },
                item: function (data, escape) {
                    return '<div>' + icon(data, escape) + '<span>' + escape(data.text) + '</span>'
                        + (data.serviceName ? '<small>' + escape(data.serviceName) + '</small>' : '')
                        + '</div>';
                },
                no_results: function () {
                    return '<div class="no-results">Aucune catégorie ne correspond à votre recherche.</div>';
                }
            }
        });
    });
});
