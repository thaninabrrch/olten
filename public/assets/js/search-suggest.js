/**
 * Autocompletion de la barre de recherche.
 *
 * Elle equipe tous les champs marques `data-search-input` : ceux du header
 * (bureau et mobile) et celui de la page de resultats. Chaque champ ouvre
 * son propre menu, alimente par /recherche/suggestions.
 *
 * Deux natures de suggestion, distinguees par la reponse du serveur :
 *   - celles qui portent une `url` menent droit a l'offre, a la categorie ou
 *     au service ;
 *   - celles qui portent un `term` (les villes) remplissent le champ, puis
 *     laissent le visiteur completer sa recherche.
 *
 * Le menu se pilote entierement au clavier (fleches, Entree, Echap) et le
 * formulaire reste un GET ordinaire : sans ce fichier, la barre fonctionne
 * exactement comme avant.
 */
(function () {
    'use strict';

    /** Delai avant d'interroger le serveur, en millisecondes. */
    var DELAI = 220;

    /** Nombre de recherches gardees dans l'historique local. */
    var HISTORIQUE_MAX = 6;

    var CLE_HISTORIQUE = 'olten.recherches';

    /* ====================================================================
       Historique local
       Il vit dans le navigateur du visiteur et n'est jamais envoye au
       serveur : c'est un confort de saisie, pas une donnee de la
       plateforme. Un navigateur qui refuse le stockage (navigation privee,
       cookies bloques) fait simplement tomber la fonction, sans casser la
       barre.
       ==================================================================== */

    function historiqueLire() {
        try {
            var brut = window.localStorage.getItem(CLE_HISTORIQUE);
            var liste = brut ? JSON.parse(brut) : [];

            return Array.isArray(liste) ? liste.filter(function (t) { return typeof t === 'string'; }) : [];
        } catch (e) {
            return [];
        }
    }

    function historiqueEcrire(terme) {
        if (! terme) {
            return;
        }

        try {
            var liste = historiqueLire().filter(function (t) {
                return t.toLowerCase() !== terme.toLowerCase();
            });

            liste.unshift(terme);

            window.localStorage.setItem(CLE_HISTORIQUE, JSON.stringify(liste.slice(0, HISTORIQUE_MAX)));
        } catch (e) {
            // Stockage indisponible : l'historique est perdu, la recherche non.
        }
    }

    function historiqueVider() {
        try {
            window.localStorage.removeItem(CLE_HISTORIQUE);
        } catch (e) {
            // Rien a faire : il n'y avait deja rien a effacer.
        }
    }

    /* ====================================================================
       Rendu
       ==================================================================== */

    function echapper(texte) {
        var noeud = document.createElement('span');
        noeud.textContent = texte == null ? '' : String(texte);

        return noeud.innerHTML;
    }

    /**
     * Souligne dans le libelle la portion saisie : le visiteur voit du
     * premier coup d'oeil pourquoi la ligne lui est proposee.
     */
    function surligner(libelle, terme) {
        var propre = echapper(libelle);

        if (! terme) {
            return propre;
        }

        var position = libelle.toLowerCase().indexOf(terme.toLowerCase());

        if (position < 0) {
            return propre;
        }

        return echapper(libelle.slice(0, position))
            + '<mark>' + echapper(libelle.slice(position, position + terme.length)) + '</mark>'
            + echapper(libelle.slice(position + terme.length));
    }

    function ligne(item, terme) {
        var vignette = item.image
            ? '<span class="sg-thumb"><img src="' + echapper(item.image) + '" alt="" loading="lazy"></span>'
            : '<span class="sg-glyph"><i class="' + echapper(item.icon || 'fa-solid fa-magnifying-glass') + '"></i></span>';

        return '<button type="button" class="sg-item" role="option" aria-selected="false"'
            + ' data-sg-url="' + echapper(item.url || '') + '"'
            + ' data-sg-term="' + echapper(item.term || item.label || '') + '"'
            + ' data-sg-navigate="' + (item.url ? '1' : '0') + '">'
            + vignette
            + '<span class="sg-text">'
            + '<span class="sg-label">' + surligner(item.label || '', terme) + '</span>'
            + (item.sub ? '<span class="sg-sub">' + echapper(item.sub) + '</span>' : '')
            + '</span>'
            + (item.price ? '<span class="sg-price">' + echapper(item.price) + '</span>' : '')
            + '</button>';
    }

    /* ====================================================================
       Un champ equipe
       ==================================================================== */

    function equiper(champ) {
        var conteneur = champ.closest('.search-field') || champ.closest('.sr-bar-field') || champ.parentElement;
        var formulaire = champ.form;
        var famille = champ.dataset.searchField || 'search';

        if (! conteneur || conteneur.querySelector('.sg-menu')) {
            return;
        }

        // Le menu est pose dans le conteneur du champ, en position absolue :
        // il suit donc le champ, y compris quand la barre mobile coulisse.
        conteneur.classList.add('sg-anchor');

        var menu = document.createElement('div');
        menu.className = 'sg-menu';
        menu.setAttribute('role', 'listbox');
        menu.hidden = true;
        conteneur.appendChild(menu);

        var minuteur = null;
        var requete = null;
        var lignes = [];
        var curseur = -1;
        var dernierTerme = null;

        champ.setAttribute('role', 'combobox');
        champ.setAttribute('aria-expanded', 'false');
        champ.setAttribute('aria-autocomplete', 'list');

        function ouvrir() {
            if (menu.innerHTML === '') {
                return;
            }

            menu.hidden = false;
            champ.setAttribute('aria-expanded', 'true');
        }

        function fermer() {
            menu.hidden = true;
            champ.setAttribute('aria-expanded', 'false');
            curseur = -1;
        }

        function marquer(index) {
            lignes.forEach(function (item, rang) {
                var actif = rang === index;
                item.classList.toggle('is-active', actif);
                item.setAttribute('aria-selected', actif ? 'true' : 'false');
            });

            if (index >= 0 && lignes[index]) {
                lignes[index].scrollIntoView({ block: 'nearest' });
            }

            curseur = index;
        }

        function choisir(item) {
            var url = item.dataset.sgUrl;
            var terme = item.dataset.sgTerm;

            // Une suggestion qui porte une adresse mene a la fiche ; les
            // autres (les villes) remplissent le champ et laissent le
            // visiteur terminer sa phrase.
            if (item.dataset.sgNavigate === '1' && url) {
                historiqueEcrire(champ.value.trim() || terme);
                window.location.href = url;

                return;
            }

            champ.value = terme;
            fermer();
            champ.focus();
        }

        function peindre(donnees, terme) {
            var groupes = (donnees && donnees.groups) || [];
            var morceaux = [];

            // L'historique n'a de sens que sur le champ mot-cle, et seulement
            // tant que rien n'est saisi : passe la premiere lettre, ce sont
            // les suggestions du catalogue qui comptent.
            if (famille === 'search' && ! terme) {
                var passees = historiqueLire();

                if (passees.length) {
                    morceaux.push(
                        '<div class="sg-group"><p class="sg-group-title">Vos recherches</p>'
                        + passees.map(function (t) {
                            return ligne({ label: t, icon: 'fa-solid fa-clock-rotate-left', term: t }, '');
                        }).join('')
                        + '</div>'
                    );
                }
            }

            groupes.forEach(function (groupe) {
                morceaux.push(
                    '<div class="sg-group"><p class="sg-group-title">' + echapper(groupe.title) + '</p>'
                    + (groupe.items || []).map(function (item) { return ligne(item, terme); }).join('')
                    + '</div>'
                );
            });

            if (! morceaux.length) {
                menu.innerHTML = terme
                    ? '<p class="sg-empty">Aucune suggestion pour « ' + echapper(terme) + ' ». Lancez la recherche pour voir tout le catalogue.</p>'
                    : '';

                if (! terme) {
                    fermer();

                    return;
                }
            } else {
                menu.innerHTML = morceaux.join('');
            }

            lignes = Array.prototype.slice.call(menu.querySelectorAll('.sg-item'));
            curseur = -1;

            lignes.forEach(function (item) {
                // `mousedown` et non `click` : le clic arrive apres le `blur`
                // du champ, qui aurait deja referme le menu.
                item.addEventListener('mousedown', function (evenement) {
                    evenement.preventDefault();
                    choisir(item);
                });
            });

            ouvrir();
        }

        function interroger() {
            var terme = champ.value.trim();

            if (terme === dernierTerme && menu.innerHTML !== '') {
                ouvrir();

                return;
            }

            dernierTerme = terme;

            // Une requete plus recente remplace la precedente : sans cela,
            // une reponse lente ecraserait un menu plus a jour.
            if (requete) {
                requete.abort();
            }

            requete = new AbortController();

            var parametres = new URLSearchParams({ q: terme, field: famille });

            fetch(SEARCH_SUGGEST_URL + '?' + parametres.toString(), {
                headers: { Accept: 'application/json' },
                signal: requete.signal
            })
                .then(function (reponse) { return reponse.ok ? reponse.json() : null; })
                .then(function (donnees) {
                    if (donnees) {
                        peindre(donnees, terme);
                    }
                })
                .catch(function () {
                    // Requete annulee ou reseau indisponible : le champ reste
                    // utilisable, le formulaire part comme un GET ordinaire.
                });
        }

        function interrogerPlusTard() {
            clearTimeout(minuteur);
            minuteur = setTimeout(interroger, DELAI);
        }

        champ.addEventListener('input', function () {
            dernierTerme = null;
            interrogerPlusTard();
        });

        champ.addEventListener('focus', interroger);

        champ.addEventListener('keydown', function (evenement) {
            if (evenement.key === 'Escape') {
                fermer();

                return;
            }

            if (menu.hidden || ! lignes.length) {
                return;
            }

            if (evenement.key === 'ArrowDown') {
                evenement.preventDefault();
                marquer((curseur + 1) % lignes.length);
            } else if (evenement.key === 'ArrowUp') {
                evenement.preventDefault();
                marquer(curseur <= 0 ? lignes.length - 1 : curseur - 1);
            } else if (evenement.key === 'Enter' && curseur >= 0) {
                // Entree sans selection laisse partir le formulaire : c'est
                // la recherche complete, et elle doit rester le geste par
                // defaut.
                evenement.preventDefault();
                choisir(lignes[curseur]);
            }
        });

        champ.addEventListener('blur', function () {
            // Laisse passer le `mousedown` d'une suggestion avant de fermer.
            setTimeout(fermer, 120);
        });

        if (formulaire) {
            formulaire.addEventListener('submit', function () {
                if (famille === 'search') {
                    historiqueEcrire(champ.value.trim());
                }
            });
        }
    }

    /* ====================================================================
       Recherches recentes affichees sur la page de resultats
       ==================================================================== */

    function peindreRecentes() {
        var bloc = document.querySelector('[data-search-recent]');

        if (! bloc) {
            return;
        }

        var liste = bloc.querySelector('[data-search-recent-list]');
        var vider = bloc.querySelector('[data-search-recent-clear]');
        var termes = historiqueLire();

        if (! termes.length) {
            bloc.hidden = true;

            return;
        }

        liste.innerHTML = termes.map(function (terme) {
            return '<a class="sr-recent-chip" href="' + SEARCH_URL + '?search=' + encodeURIComponent(terme) + '">'
                + '<i class="fa-solid fa-clock-rotate-left"></i>' + echapper(terme) + '</a>';
        }).join('');

        bloc.hidden = false;

        if (vider) {
            vider.addEventListener('click', function () {
                historiqueVider();
                bloc.hidden = true;
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof SEARCH_SUGGEST_URL === 'undefined') {
            return;
        }

        document.querySelectorAll('[data-search-input]').forEach(equiper);
        peindreRecentes();
    });
})();
