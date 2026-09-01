/**
 * Rail de filtres de la page service.
 *
 * Quatre roles : replier et deplier les sections, ranger le rail derriere un
 * bouton sur petit ecran, tenir le curseur de prix a deux poignees, et
 * annoncer en direct le nombre d'offres qu'un reglage donnerait
 * (« Afficher 842 offres ») sans recharger la page.
 *
 * Les vrais champs du formulaire vivent DANS les sections : repliees, elles
 * partent quand meme a l'envoi, il n'y a donc aucune valeur a recopier
 * ailleurs. Le retrait d'un critere pose, lui, est un simple lien : il
 * fonctionne meme si ce fichier ne se charge pas.
 */
document.addEventListener('DOMContentLoaded', function () {
    const rail = document.querySelector('[data-cs-facets]');

    if (! rail) {
        return;
    }

    /* --------------------------- Sections --------------------------- */

    // Sections independantes : ouvrir « Prix » ne doit pas replier « Ville ».
    // Le rail montre tout d'un coup, c'est sa raison d'etre.
    rail.querySelectorAll('[data-cs-facet]').forEach(function (facette) {
        const bouton = facette.querySelector('[data-cs-facet-btn]');
        const panneau = facette.querySelector('.cs-facet-panel');

        bouton.addEventListener('click', function () {
            const ouvrir = panneau.hidden;

            panneau.hidden = ! ouvrir;
            facette.classList.toggle('is-open', ouvrir);
            bouton.setAttribute('aria-expanded', String(ouvrir));
        });
    });

    /* ------------------- Repli du rail sur mobile ------------------- */

    const bascule = rail.querySelector('[data-cs-rail-toggle]');
    const corps = rail.querySelector('[data-cs-rail-body]');

    if (bascule && corps) {
        bascule.addEventListener('click', function () {
            const ouvert = rail.classList.toggle('is-unfolded');
            bascule.setAttribute('aria-expanded', String(ouvert));
        });
    }

    /* ----------------------------- Ville ---------------------------- */

    const champVille = rail.querySelector('[data-cs-city-input]');
    const villes = Array.from(rail.querySelectorAll('[data-cs-city]'));

    villes.forEach(function (ville) {
        ville.addEventListener('click', function () {
            champVille.value = ville.dataset.csCity;
            villes.forEach(function (autre) { autre.classList.toggle('is-on', autre === ville); });
            compterPlusTard();
        });
    });

    if (champVille) {
        champVille.addEventListener('input', function () {
            const saisie = champVille.value.trim().toLowerCase();

            villes.forEach(function (ville) {
                ville.hidden = saisie !== '' && ! ville.dataset.csCity.toLowerCase().includes(saisie);
            });
        });
    }

    /* ----------------------------- Prix ----------------------------- */

    const bloc = rail.querySelector('[data-cs-price]');

    if (bloc) {
        const borneBasse = Number(bloc.dataset.csPriceMin);
        const borneHaute = Number(bloc.dataset.csPriceMax);

        const nombreDe = bloc.querySelector('[data-cs-price-from]');
        const nombreA = bloc.querySelector('[data-cs-price-to]');
        const curseurDe = bloc.querySelector('[data-cs-range-from]');
        const curseurA = bloc.querySelector('[data-cs-range-to]');
        const remplissage = bloc.querySelector('[data-cs-price-fill]');
        const fourchettes = Array.from(bloc.querySelectorAll('[data-cs-bracket-min]'));

        const borner = function (valeur) {
            return Math.min(borneHaute, Math.max(borneBasse, Number(valeur)));
        };

        const peindre = function () {
            const etendue = (borneHaute - borneBasse) || 1;
            const gauche = ((Number(curseurDe.value) - borneBasse) / etendue) * 100;
            const droite = ((Number(curseurA.value) - borneBasse) / etendue) * 100;

            remplissage.style.left = gauche + '%';
            remplissage.style.width = Math.max(0, droite - gauche) + '%';
        };

        // Une borne restee sur sa valeur extreme ne filtre rien : on la
        // renvoie vide, ce qui garde l'URL propre et le critere inactif.
        const versNombres = function () {
            nombreDe.value = Number(curseurDe.value) === borneBasse ? '' : curseurDe.value;
            nombreA.value = Number(curseurA.value) === borneHaute ? '' : curseurA.value;
        };

        const versCurseurs = function () {
            curseurDe.value = nombreDe.value === '' ? borneBasse : borner(nombreDe.value);
            curseurA.value = nombreA.value === '' ? borneHaute : borner(nombreA.value);

            if (Number(curseurDe.value) > Number(curseurA.value)) {
                curseurDe.value = curseurA.value;
            }

            peindre();
        };

        const marquerFourchette = function () {
            fourchettes.forEach(function (choix) {
                choix.classList.toggle(
                    'is-on',
                    (choix.dataset.csBracketMin || '') === nombreDe.value
                        && (choix.dataset.csBracketMax || '') === nombreA.value
                );
            });
        };

        // Les poignees ne se croisent pas : celle qu'on pousse s'arrete sur
        // l'autre au lieu de passer derriere.
        curseurDe.addEventListener('input', function () {
            curseurDe.value = Math.min(Number(curseurDe.value), Number(curseurA.value));
            versNombres();
            peindre();
            marquerFourchette();
        });

        curseurA.addEventListener('input', function () {
            curseurA.value = Math.max(Number(curseurA.value), Number(curseurDe.value));
            versNombres();
            peindre();
            marquerFourchette();
        });

        [nombreDe, nombreA].forEach(function (champ) {
            champ.addEventListener('input', function () {
                versCurseurs();
                marquerFourchette();
            });
        });

        fourchettes.forEach(function (choix) {
            choix.addEventListener('click', function () {
                nombreDe.value = choix.dataset.csBracketMin || '';
                nombreA.value = choix.dataset.csBracketMax || '';
                versCurseurs();
                marquerFourchette();
                compterPlusTard();
            });
        });

        versCurseurs();
        marquerFourchette();
    }

    /* ---------------------- Effacer un critere ---------------------- */

    rail.querySelectorAll('[data-cs-clear]').forEach(function (bouton) {
        bouton.addEventListener('click', function () {
            bouton.dataset.csClear.split(',').forEach(function (nom) {
                rail.querySelectorAll('[name="' + nom.trim() + '"]').forEach(function (champ) {
                    if (champ.type === 'radio') {
                        champ.checked = champ.value === '';
                    } else {
                        champ.value = '';
                    }
                });
            });

            villes.forEach(function (ville) { ville.classList.remove('is-on'); });

            if (bloc) {
                bloc.querySelectorAll('[data-cs-bracket-min]').forEach(function (choix) {
                    choix.classList.remove('is-on');
                });

                bloc.querySelector('[data-cs-price-from]').dispatchEvent(new Event('input', { bubbles: true }));
            }

            compterPlusTard();
        });
    });

    /* ---------------------- Comptage en direct ---------------------- */

    let requete = null;
    let minuteur = null;

    function compterPlusTard() {
        clearTimeout(minuteur);
        minuteur = setTimeout(compter, 280);
    }

    function ecrireTotal(nombre) {
        const chiffre = new Intl.NumberFormat('fr-FR').format(nombre);
        const mot = nombre > 1 ? 'offres' : 'offre';

        rail.querySelectorAll('[data-cs-apply]').forEach(function (bouton) {
            bouton.textContent = 'Afficher ' + chiffre + ' ' + mot;
        });
    }

    function compter() {
        const parametres = new URLSearchParams();

        new FormData(rail).forEach(function (valeur, nom) {
            if (String(valeur).trim() !== '') {
                parametres.append(nom, valeur);
            }
        });

        parametres.set('count_only', '1');

        // Une requete plus recente remplace la precedente : sans cela, une
        // reponse lente ecraserait un total plus a jour.
        if (requete) {
            requete.abort();
        }

        requete = new AbortController();

        fetch(rail.dataset.csCountUrl + '?' + parametres.toString(), {
            headers: { Accept: 'application/json' },
            signal: requete.signal,
        })
            .then(function (reponse) {
                return reponse.ok ? reponse.json() : null;
            })
            .then(function (donnees) {
                if (donnees && typeof donnees.total === 'number') {
                    ecrireTotal(donnees.total);
                }
            })
            .catch(function () {
                // Requete annulee ou reseau indisponible : on garde le dernier
                // total connu plutot que d'afficher un chiffre faux.
            });
    }

    rail.addEventListener('input', compterPlusTard);
    rail.addEventListener('change', compterPlusTard);

    /* ------------------------- Envoi du rail ------------------------- */

    // Un formulaire GET envoie tous ses champs, meme vides : l'URL repartait
    // avec « ?search=&location=&type=&max_price=&sort= », que les liens de
    // retrait d'un critere trainaient ensuite de page en page. Un champ
    // desactive n'est pas envoye : on neutralise donc les champs sans valeur
    // juste avant le depart.
    rail.addEventListener('submit', function () {
        rail.querySelectorAll('input[name], select[name]').forEach(function (champ) {
            if (champ.type === 'radio') {
                if (champ.checked && champ.value === '') {
                    champ.disabled = true;
                }
            } else if (champ.value.trim() === '') {
                champ.disabled = true;
            }
        });
    });
});
