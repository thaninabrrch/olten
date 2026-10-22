{{--
    Assistant de trajet : itinéraire, route, escales, date, passagers,
    réservation, prix, retour, infos conducteur. Publication par
    POST /covoiturage/publish (wizard-scripts.blade.php).

    Inclus par livreur/covoiturage/create.blade.php et par
    pages/locateur/deposer_annonce.blade.php (service covoiturage) : les
    deux entrées publient exactement le même trajet.

    Chaque étape suit le même gabarit : en-tête (wizard-head), contenu, puis
    barre d'actions collée en bas du panneau (Retour à gauche, action à droite).
    Les id, name, data-* et onclick sont ceux qu'attendent les scripts : ne
    pas les renommer.
--}}
    <div class="app-content-area bg-white">

        <!-- ========== POPIN AJOUT ESCALE ALLER ========== -->
        <div id="popin-add-stop" class="popin-overlay" role="dialog" aria-modal="true" aria-labelledby="popin-stop-title">
            <div class="wz-dialog">
                <button onclick="closeStopPopin('aller')" class="wz-dialog-close" aria-label="Fermer">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
                <span class="wz-dialog-icon" aria-hidden="true"><i class="fa-solid fa-map-pin"></i></span>
                <h3 id="popin-stop-title" class="wz-dialog-title">Ajouter une escale</h3>
                <p class="wz-dialog-lede">Recherchez une ville ou une adresse sur votre trajet.</p>
                <div class="relative">
                    <div class="wz-control">
                        <i class="wz-control-icon fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <input id="popin-stop-input" type="text" placeholder="Ex. Mâcon, Valence, Montélimar…"
                            autocomplete="off" aria-label="Ville ou adresse de l'escale">
                    </div>
                    <div id="popin-stop-results" class="popin-autocomplete-results hidden"></div>
                </div>
                <div id="popin-stop-selected" class="mt-4 hidden">
                    <div class="wz-selected">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                        <span id="popin-stop-selected-name" class="wz-selected-name"></span>
                        <button onclick="clearPopinSelection('aller')" class="wz-selected-clear" aria-label="Effacer cette adresse">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <div class="wz-dialog-actions">
                    <button onclick="closeStopPopin('aller')" class="wz-btn-back">Annuler</button>
                    <button id="popin-stop-confirm" onclick="confirmManualStop('aller')" disabled class="wz-btn-primary">
                        Ajouter l'escale
                    </button>
                </div>
            </div>
        </div>

        <!-- ========== POPIN AJOUT ESCALE RETOUR ========== -->
        <div id="popin-add-return-stop" class="popin-overlay" role="dialog" aria-modal="true" aria-labelledby="popin-return-stop-title">
            <div class="wz-dialog">
                <button onclick="closeStopPopin('retour')" class="wz-dialog-close" aria-label="Fermer">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
                <span class="wz-dialog-icon" aria-hidden="true"><i class="fa-solid fa-map-pin"></i></span>
                <h3 id="popin-return-stop-title" class="wz-dialog-title">Escale du retour</h3>
                <p class="wz-dialog-lede">Ajoutez un point de passage sur le trajet retour.</p>
                <div class="relative">
                    <div class="wz-control">
                        <i class="wz-control-icon fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <input id="popin-return-stop-input" type="text" placeholder="Ex. Mâcon, Valence, Montélimar…"
                            autocomplete="off" aria-label="Ville ou adresse de l'escale">
                    </div>
                    <div id="popin-return-stop-results" class="popin-autocomplete-results hidden"></div>
                </div>
                <div id="popin-return-stop-selected" class="mt-4 hidden">
                    <div class="wz-selected">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                        <span id="popin-return-stop-selected-name" class="wz-selected-name"></span>
                        <button onclick="clearPopinSelection('retour')" class="wz-selected-clear" aria-label="Effacer cette adresse">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <div class="wz-dialog-actions">
                    <button onclick="closeStopPopin('retour')" class="wz-btn-back">Annuler</button>
                    <button id="popin-return-stop-confirm" onclick="confirmManualStop('retour')" disabled class="wz-btn-primary">
                        Ajouter l'escale
                    </button>
                </div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- ÉTAPE 1: ITINÉRAIRE                                    -->
        <!-- ===================================================== -->
        <section id="view-itinerary" class="step-view active">
            <div class="sidebar-panel">
                @include('livreur.covoiturage.partials.wizard-head', [
                    'section' => 1,
                    'tag' => 'h1',
                    'title' => 'Où allez-vous ?',
                    'lede' => 'Indiquez votre point de départ et votre destination.',
                ])

                <div class="wz-body">
                    {{-- Départ et arrivée réunis comme sur un billet, reliés par le tracé --}}
                    <div class="wz-trip">
                        <div class="wz-trip-row">
                            <span class="wz-trip-dot" aria-hidden="true"></span>
                            <label for="input-start" class="wz-trip-label">Départ</label>
                            <input id="input-start" type="text" placeholder="Ville ou adresse de départ"
                                autocomplete="off">
                            <div id="start-results" class="autocomplete-results hidden"></div>
                        </div>
                        <div class="wz-trip-row">
                            <span class="wz-trip-dot is-end" aria-hidden="true"></span>
                            <label for="input-end" class="wz-trip-label">Arrivée</label>
                            <input id="input-end" type="text" placeholder="Ville ou adresse d'arrivée"
                                autocomplete="off">
                            <div id="end-results" class="autocomplete-results hidden"></div>
                        </div>
                    </div>

                    {{-- Le bouton reste inactif tant que les deux adresses ne viennent pas des suggestions --}}
                    <p class="wz-hint">
                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                        <span>Choisissez chaque adresse dans les suggestions : elle se place alors sur la carte.</span>
                    </p>
                </div>

                <footer class="wz-actions">
                    <button id="btn-next" disabled class="wz-btn-primary wz-btn-next">
                        Calculer l'itinéraire
                    </button>
                </footer>
            </div>
            <div class="map-container-traj">
                <div id="map" class="h-full w-full"></div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- ÉTAPE 2: CHOIX DE LA ROUTE                             -->
        <!-- ===================================================== -->
        <section id="view-route" class="step-view">
            <div class="sidebar-panel">
                @include('livreur.covoiturage.partials.wizard-head', [
                    'section' => 1,
                    'title' => 'Choisissez votre route',
                    'lede' => "Celle que vous sélectionnez s'affiche sur la carte.",
                ])

                <div class="wz-body">
                    <div id="routes-list" class="wz-list" role="radiogroup" aria-label="Routes proposées">
                        <p class="wz-loading"><i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i> Calcul des routes…</p>
                    </div>
                </div>

                <footer class="wz-actions">
                    <button onclick="changeView('view-itinerary')" class="wz-btn-back">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour
                    </button>
                    {{-- Activé dès que les routes sont affichées (script en fin de wizard-scripts) --}}
                    <button id="btn-validate-route" disabled class="wz-btn-primary wz-btn-next">
                        Choisir cette route
                    </button>
                </footer>
            </div>
            <div class="map-container-traj">
                <div id="map-route" class="h-full w-full"></div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- ÉTAPE 3: ÉTAPES INTERMÉDIAIRES (ALLER)                 -->
        <!-- ===================================================== -->
        <section id="view-steps" class="step-view">
            <div class="sidebar-panel">
                @include('livreur.covoiturage.partials.wizard-head', [
                    'section' => 1,
                    'title' => 'Ajoutez des escales',
                    'lede' => 'Facultatif. Des passagers peuvent monter ou descendre à chaque escale ; vous fixerez un prix par tronçon.',
                ])

                <div class="wz-body">
                    <section class="wz-group">
                        <p class="wz-group-title">
                            <i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i> Villes sur votre route
                        </p>
                        <div id="intermediate-cities" class="wz-list"></div>
                    </section>

                    <section class="wz-group">
                        <p class="wz-group-title">
                            <i class="fa-solid fa-map-pin" aria-hidden="true"></i> Vos escales
                        </p>
                        <div id="manual-stops-list" class="wz-list"></div>
                        <button onclick="openStopPopin('aller')" class="wz-btn-add">
                            <i class="fa-solid fa-plus" aria-hidden="true"></i> Ajouter une escale
                        </button>
                    </section>
                </div>

                <footer class="wz-actions">
                    <button onclick="changeView('view-route')" class="wz-btn-back">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour
                    </button>
                    <button id="btn-confirm-steps" class="wz-btn-primary wz-btn-next">
                        Valider les escales
                    </button>
                </footer>
            </div>
            <div class="map-container-traj">
                <div id="map-steps" class="h-full w-full"></div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- ÉTAPE 4: RÉCAPITULATIF                                 -->
        <!-- ===================================================== -->
        <section id="view-summary" class="step-view">
            <div class="sidebar-panel">
                @include('livreur.covoiturage.partials.wizard-head', [
                    'section' => 1,
                    'title' => 'Vérifiez votre itinéraire',
                    'lede' => "Contrôlez le parcours et ses escales avant de choisir l'horaire.",
                ])

                <div class="wz-body">
                    <div class="wz-card">
                        <div class="wz-card-head">
                            <p class="wz-card-title">Parcours</p>
                            {{-- Distance et durée de la route choisie --}}
                            <p class="wz-card-meta" data-route-metrics></p>
                        </div>
                        <ul id="selected-steps" class="summary-stops"></ul>
                    </div>
                </div>

                <footer class="wz-actions">
                    <button onclick="changeView('view-steps')" class="wz-btn-back">
                        <i class="fa-solid fa-pen" aria-hidden="true"></i> Modifier
                    </button>
                    <button id="btn-final" onclick="changeView('view-datetime')" class="wz-btn-primary wz-btn-next">
                        Continuer
                    </button>
                </footer>
            </div>
            <div class="map-container-traj">
                <div id="map-summary" class="h-full w-full"></div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- DATE & HEURE DE DÉPART                                 -->
        <!-- ===================================================== -->
        <section id="view-datetime" class="step-view">
            <div class="sidebar-panel">
                @include('livreur.covoiturage.partials.wizard-head', [
                    'section' => 2,
                    'title' => 'Quand partez-vous ?',
                    'lede' => 'Date et heure du rendez-vous au point de départ.',
                ])

                <div class="wz-body">
                    <div class="wz-grid-2">
                        <div class="wz-field">
                            <label for="input-date" class="wz-label">Date</label>
                            <div class="wz-control">
                                <i class="wz-control-icon fa-regular fa-calendar" aria-hidden="true"></i>
                                <input type="date" id="input-date" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="wz-field">
                            <label for="input-time" class="wz-label">Heure</label>
                            <div class="wz-control">
                                <i class="wz-control-icon fa-regular fa-clock" aria-hidden="true"></i>
                                <input type="time" id="input-time" value="08:30">
                            </div>
                        </div>
                    </div>
                </div>

                <footer class="wz-actions">
                    <button onclick="changeView('view-summary')" class="wz-btn-back">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour
                    </button>
                    <button onclick="changeView('view-datetimeed')" class="wz-btn-primary wz-btn-next">
                        Continuer
                    </button>
                </footer>
            </div>

            <div class="right-showcase hidden md:flex">
                {{-- Récapitulatif du trajet, rempli par le script en fin de wizard-scripts --}}
                <div class="trip-recap" data-trip-recap></div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- PASSAGERS ET OPTIONS                                   -->
        <!-- ===================================================== -->
        <section id="view-datetimeed" class="step-view">
            <div class="sidebar-panel">
                @include('livreur.covoiturage.partials.wizard-head', [
                    'section' => 3,
                    'title' => 'Qui voyage avec vous ?',
                    'lede' => 'Le nombre de places proposées et vos préférences.',
                ])

                <div class="wz-body">
                    <div class="wz-field">
                        <p class="wz-label">Places proposées</p>
                        <div class="wz-qty">
                            <div>
                                <p class="wz-qty-title">Passagers</p>
                                <p class="wz-qty-hint">De 1 à 8 places</p>
                            </div>
                            <div class="wz-qty-control">
                                <button onclick="updateQty('passengers', -1)" aria-label="Retirer une place">
                                    <i class="fa-solid fa-minus" aria-hidden="true"></i>
                                </button>
                                <span id="qty-passengers" aria-live="polite">3</span>
                                <button onclick="updateQty('passengers', 1)" aria-label="Ajouter une place">
                                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Le script coche la carte via la classe radio-card et l'icône check-icon --}}
                    <fieldset class="wz-field">
                        <legend class="wz-label">Préférences</legend>
                        <div class="wz-options">
                            <label class="relative cursor-pointer block">
                                <input type="radio" name="passengerMode" value="mixed" checked class="radio-custom sr-only">
                                <div class="radio-card">
                                    <span class="radio-card-mark" aria-hidden="true"><span class="check-icon"></span></span>
                                    <span class="radio-card-text">
                                        <span class="label-text">Mixte</span>
                                        <span class="radio-card-hint">Tout le monde peut réserver</span>
                                    </span>
                                    <i class="radio-card-icon fa-solid fa-user-group" aria-hidden="true"></i>
                                </div>
                            </label>
                            <label class="relative cursor-pointer block">
                                <input type="radio" name="passengerMode" value="womenOnly" class="radio-custom sr-only">
                                <div class="radio-card">
                                    <span class="radio-card-mark" aria-hidden="true"><span class="check-icon"></span></span>
                                    <span class="radio-card-text">
                                        <span class="label-text">Entre femmes uniquement</span>
                                        <span class="radio-card-hint">Trajet réservé aux passagères</span>
                                    </span>
                                    <i class="radio-card-icon fa-solid fa-venus" aria-hidden="true"></i>
                                </div>
                            </label>
                            <label class="relative cursor-pointer block">
                                <input type="radio" name="passengerMode" value="maxBackSeats" class="radio-custom sr-only">
                                <div class="radio-card">
                                    <span class="radio-card-mark" aria-hidden="true"><span class="check-icon"></span></span>
                                    <span class="radio-card-text">
                                        <span class="label-text">2 places maximum à l'arrière</span>
                                        <span class="radio-card-hint">Plus de confort sur la banquette</span>
                                    </span>
                                    <i class="radio-card-icon fa-solid fa-couch" aria-hidden="true"></i>
                                </div>
                            </label>
                        </div>
                    </fieldset>
                </div>

                <footer class="wz-actions">
                    <button onclick="changeView('view-datetime')" class="wz-btn-back">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour
                    </button>
                    <button onclick="changeView('view-booking')" class="wz-btn-primary wz-btn-next">
                        Continuer
                    </button>
                </footer>
            </div>

            <div class="right-showcase hidden md:flex">
                {{-- Récapitulatif du trajet, rempli par le script en fin de wizard-scripts --}}
                <div class="trip-recap" data-trip-recap></div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- RÉSERVATION                                            -->
        <!-- ===================================================== -->
        <section id="view-booking" class="step-view">
            <div class="sidebar-panel">
                @include('livreur.covoiturage.partials.wizard-head', [
                    'section' => 4,
                    'title' => 'Comment valider les réservations ?',
                    'lede' => 'Une réservation peut être confirmée tout de suite ou après votre accord.',
                ])

                <div class="wz-body">
                    <div class="wz-options" role="radiogroup" aria-label="Mode de réservation">
                        <div onclick="selectBooking(this)" data-mode="instant" class="card-option selected" role="radio" tabindex="0">
                            <span class="card-option-icon" aria-hidden="true"><i class="fa-solid fa-bolt"></i></span>
                            <span class="card-option-text">
                                <span class="card-option-title">
                                    Réservation instantanée <span class="card-option-badge">Recommandé</span>
                                </span>
                                <span class="card-option-desc">Les réservations sont confirmées automatiquement, sans action de votre part.</span>
                            </span>
                            <span class="card-option-mark" aria-hidden="true"></span>
                        </div>

                        <div onclick="selectBooking(this)" data-mode="manual" class="card-option" role="radio" tabindex="0">
                            <span class="card-option-icon" aria-hidden="true"><i class="fa-solid fa-user-check"></i></span>
                            <span class="card-option-text">
                                <span class="card-option-title">Validation manuelle</span>
                                <span class="card-option-desc">Vous acceptez ou refusez chaque demande. Sans réponse avant le départ, le passager est remboursé.</span>
                            </span>
                            <span class="card-option-mark" aria-hidden="true"></span>
                        </div>
                    </div>
                </div>

                <footer class="wz-actions">
                    <button onclick="changeView('view-datetimeed')" class="wz-btn-back">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour
                    </button>
                    <button onclick="goToPricing()" class="wz-btn-primary wz-btn-next">
                        Continuer
                    </button>
                </footer>
            </div>

            <div class="right-showcase hidden md:flex">
                {{-- Récapitulatif du trajet, rempli par le script en fin de wizard-scripts --}}
                <div class="trip-recap" data-trip-recap></div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- PRIX ALLER                                             -->
        <!-- ===================================================== -->
        <section id="view-pricing" class="step-view">
            <div class="sidebar-panel">
                @include('livreur.covoiturage.partials.wizard-head', [
                    'section' => 5,
                    'title' => 'Fixez votre prix',
                    'lede' => 'Prix par passager pour chaque tronçon de votre trajet.',
                ])

                <div class="wz-body">
                    <div id="pricing-steps-container" class="wz-list"></div>

                    {{-- Sur mobile, le total de droite est masqué : il se lit ici --}}
                    <p class="wz-total">
                        <span>Trajet complet, par passager</span>
                        <strong data-trip-total>0 €</strong>
                    </p>
                </div>

                <footer class="wz-actions">
                    <button onclick="changeView('view-booking')" class="wz-btn-back">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour
                    </button>
                    <button onclick="changeView('view-final')" class="wz-btn-primary wz-btn-next">
                        Continuer
                    </button>
                </footer>
            </div>

            <div class="right-showcase hidden md:flex">
                <div class="trip-recap">
                    <div class="trip-recap-card price-showcase">
                        <p class="trip-recap-eyebrow">Trajet complet</p>
                        {{-- .text-6xl : le script y écrit le total (#view-pricing .text-6xl) --}}
                        <div class="price-showcase-amount text-6xl">0<span class="text-[#ff3c00] ml-1 text-4xl">€</span></div>
                        <p class="price-showcase-note">
                            <i class="fa-solid fa-route" aria-hidden="true"></i> Par passager, somme de vos tronçons
                        </p>
                    </div>
                    <p class="trip-recap-note">Le total suit vos curseurs.</p>
                </div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- RETOUR ET PUBLICATION                                  -->
        <!-- ===================================================== -->
        <section id="view-final" class="step-view">
            <div class="sidebar-panel">
                @include('livreur.covoiturage.partials.wizard-head', [
                    'section' => 6,
                    'title' => 'Proposez-vous un retour ?',
                    'lede' => 'Le trajet inverse peut être publié en même temps, avec sa date et ses prix.',
                ])

                <div class="wz-body">
                    <div class="wz-options" role="radiogroup" aria-label="Trajet retour">
                        <div onclick="selectReturn(this, true)" class="card-option" role="radio" tabindex="0">
                            <span class="card-option-icon" aria-hidden="true"><i class="fa-solid fa-rotate-left"></i></span>
                            <span class="card-option-text">
                                <span class="card-option-title">Oui, j'ajoute un retour</span>
                                <span class="card-option-desc">Vous choisissez ensuite la date, la route et les prix du retour.</span>
                            </span>
                            <span class="card-option-mark" aria-hidden="true"></span>
                        </div>

                        <div onclick="selectReturn(this, false)" class="card-option" role="radio" tabindex="0">
                            <span class="card-option-icon" aria-hidden="true"><i class="fa-solid fa-arrow-right-long"></i></span>
                            <span class="card-option-text">
                                <span class="card-option-title">Non, aller simple</span>
                                <span class="card-option-desc">Seul ce trajet est publié.</span>
                            </span>
                            <span class="card-option-mark" aria-hidden="true"></span>
                        </div>
                    </div>
                </div>

                <footer class="wz-actions">
                    <button onclick="changeView('view-pricing')" class="wz-btn-back">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour
                    </button>
                    {{-- Sans choix, le bouton n'a pas encore de destination : selectReturn l'active --}}
                    <button id="btn-final-action" disabled class="wz-btn-primary wz-btn-next">
                        Continuer
                    </button>
                </footer>
            </div>

            <div class="right-showcase hidden md:flex">
                {{-- Récapitulatif du trajet, rempli par le script en fin de wizard-scripts --}}
                <div class="trip-recap" data-trip-recap></div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- DATE & HEURE DU RETOUR                                 -->
        <!-- ===================================================== -->
        <section id="view-return-datetime" class="step-view">
            <div class="sidebar-panel">
                @include('livreur.covoiturage.partials.wizard-head', [
                    'section' => 6,
                    'title' => 'Quand rentrez-vous ?',
                    'lede' => 'Date et heure de départ du trajet retour.',
                ])

                <div class="wz-body">
                    <div class="wz-grid-2">
                        <div class="wz-field">
                            <label for="return-date" class="wz-label">Date</label>
                            <div class="wz-control">
                                <i class="wz-control-icon fa-regular fa-calendar" aria-hidden="true"></i>
                                <input type="date" id="return-date" min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="wz-field">
                            <label for="return-time" class="wz-label">Heure</label>
                            <div class="wz-control">
                                <i class="wz-control-icon fa-regular fa-clock" aria-hidden="true"></i>
                                <input type="time" id="return-time">
                            </div>
                        </div>
                    </div>
                </div>

                <footer class="wz-actions">
                    <button onclick="changeView('view-final')" class="wz-btn-back">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour
                    </button>
                    <button id="btn-go-return-pricing" class="wz-btn-primary wz-btn-next">
                        Continuer
                    </button>
                </footer>
            </div>

            <div class="right-showcase hidden md:flex">
                {{-- Récapitulatif du trajet, rempli par le script en fin de wizard-scripts --}}
                <div class="trip-recap" data-trip-recap></div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- ROUTE RETOUR                                           -->
        <!-- ===================================================== -->
        <section id="view-return-route" class="step-view">
            <div class="sidebar-panel">
                @include('livreur.covoiturage.partials.wizard-head', [
                    'section' => 6,
                    'title' => 'Choisissez la route du retour',
                    'lede' => "Celle que vous sélectionnez s'affiche sur la carte.",
                ])

                <div class="wz-body">
                    <div id="return-routes-list" class="wz-list" role="radiogroup" aria-label="Routes proposées pour le retour">
                        <p class="wz-loading"><i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i> Calcul des routes…</p>
                    </div>
                </div>

                <footer class="wz-actions">
                    <button onclick="changeView('view-return-datetime')" class="wz-btn-back">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour
                    </button>
                    {{-- Activé dès que les routes sont affichées (script en fin de wizard-scripts) --}}
                    <button id="btn-validate-return-route" disabled class="wz-btn-primary wz-btn-next">
                        Choisir cette route
                    </button>
                </footer>
            </div>
            <div class="map-container-traj">
                <div id="map-return-route" class="h-full w-full"></div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- ESCALES RETOUR                                         -->
        <!-- ===================================================== -->
        <section id="view-return-steps" class="step-view">
            <div class="sidebar-panel">
                @include('livreur.covoiturage.partials.wizard-head', [
                    'section' => 6,
                    'title' => 'Escales du retour',
                    'lede' => 'Facultatif. Cochez les villes traversées ou ajoutez vos propres escales.',
                ])

                <div class="wz-body">
                    <section class="wz-group">
                        <p class="wz-group-title">
                            <i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i> Villes sur la route du retour
                        </p>
                        <div id="return-intermediate-cities" class="wz-list"></div>
                    </section>

                    <section class="wz-group">
                        <p class="wz-group-title">
                            <i class="fa-solid fa-map-pin" aria-hidden="true"></i> Vos escales
                        </p>
                        <div id="return-manual-stops-list" class="wz-list"></div>
                        <button onclick="openStopPopin('retour')" class="wz-btn-add">
                            <i class="fa-solid fa-plus" aria-hidden="true"></i> Ajouter une escale
                        </button>
                    </section>
                </div>

                <footer class="wz-actions">
                    <button onclick="changeView('view-return-route')" class="wz-btn-back">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour
                    </button>
                    <button id="btn-confirm-return-steps" class="wz-btn-primary wz-btn-next">
                        Valider les escales
                    </button>
                </footer>
            </div>
            <div class="map-container-traj">
                <div id="map-return-steps" class="h-full w-full"></div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- PRIX RETOUR                                            -->
        <!-- ===================================================== -->
        <section id="view-return-pricing" class="step-view">
            <div class="sidebar-panel">
                @include('livreur.covoiturage.partials.wizard-head', [
                    'section' => 6,
                    'title' => 'Prix du retour',
                    'lede' => 'Prix par passager pour chaque tronçon du retour.',
                ])

                <div class="wz-body">
                    {{-- Les curseurs du retour partent de 0 € (generateReturnPricingSteps) --}}
                    <p class="wz-hint wz-hint-top">
                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                        <span>Les curseurs partent de 0 € : placez chacun sur le prix voulu.</span>
                    </p>

                    <div id="return-pricing-steps-container" class="wz-list"></div>

                    <p class="wz-total">
                        <span>Retour complet, par passager</span>
                        <strong data-return-total>0 €</strong>
                    </p>
                </div>

                <footer class="wz-actions">
                    <button onclick="changeView('view-return-steps')" class="wz-btn-back">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour
                    </button>
                    <button id="btn-go-driver-info" class="wz-btn-primary wz-btn-next">
                        Continuer
                    </button>
                </footer>
            </div>

            <div class="right-showcase hidden md:flex">
                <div class="trip-recap">
                    <div class="trip-recap-card price-showcase">
                        <p class="trip-recap-eyebrow">Retour complet</p>
                        <div id="return-total-price" class="price-showcase-amount">0<span class="text-[#ff3c00] ml-1 text-4xl">€</span></div>
                        <p class="price-showcase-note">
                            <i class="fa-solid fa-route" aria-hidden="true"></i> Par passager, somme de vos tronçons
                        </p>
                    </div>
                    <p class="trip-recap-note">Le total suit vos curseurs.</p>
                </div>
            </div>
        </section>

        <!-- ===================================================== -->
        <!-- DRIVER INFO                                            -->
        <!-- ===================================================== -->
        <section id="view-driver-info" class="step-view">
            <div class="sidebar-panel">
                @include('livreur.covoiturage.partials.wizard-head', [
                    'section' => 7,
                    'title' => 'Présentez-vous aux passagers',
                    'lede' => 'Une photo et quelques mots inspirent davantage confiance.',
                ])

                <div class="wz-body">
                    <div class="wz-field">
                        <p class="wz-label">Photo de profil <span class="wz-optional">Facultatif</span></p>
                        <div class="wz-photo">
                            <div id="photo-preview-container" class="wz-photo-preview">
                                <i class="fa-solid fa-user" aria-hidden="true"></i>
                            </div>
                            <div>
                                <label class="wz-btn-secondary">
                                    <i class="fa-solid fa-camera" aria-hidden="true"></i>
                                    <span data-photo-label>Ajouter une photo</span>
                                    <input type="file" id="driver-photo" class="sr-only" accept="image/*"
                                        onchange="handleImagePreview(this)">
                                </label>
                                {{-- Même limite que la validation du contrôleur (image, 2 Mo) --}}
                                <p class="wz-photo-hint" data-photo-hint>JPG, PNG ou WebP, 2 Mo maximum.</p>
                            </div>
                        </div>
                    </div>

                    <div class="wz-field">
                        <label for="driver-message" class="wz-label">
                            Message aux passagers <span class="wz-optional">Facultatif</span>
                        </label>
                        <textarea id="driver-message" maxlength="2000" class="wz-textarea"
                            placeholder="Ex. Conducteur calme, j'aime discuter et je suis flexible sur les points de dépose…"></textarea>
                        <p class="wz-counter"><span data-count-for="driver-message">0</span> / 2000</p>
                    </div>
                </div>

                <footer class="wz-actions">
                    <button onclick="changeView('view-final')" class="wz-btn-back">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour
                    </button>
                    <button id="btn-publish" class="wz-btn-primary wz-btn-publish">
                        <i class="fa-solid fa-check" aria-hidden="true"></i> Publier le trajet
                    </button>
                </footer>
            </div>

            <div class="right-showcase hidden md:flex">
                {{-- Récapitulatif du trajet, rempli par le script en fin de wizard-scripts --}}
                <div class="trip-recap" data-trip-recap></div>
            </div>
        </section>

        <script>
            // Le contrôleur refuse une photo de plus de 2 Mo : on l'écarte ici,
            // plutôt que d'échouer à la publication avec un message générique.
            const PHOTO_MAX_BYTES = 2 * 1024 * 1024;

            function handleImagePreview(input) {
                const container = document.getElementById('photo-preview-container');
                const hint = document.querySelector('[data-photo-hint]');
                const file = input.files && input.files[0];
                if (!file) return;

                if (file.size > PHOTO_MAX_BYTES) {
                    input.value = '';
                    hint.textContent = 'Cette image dépasse 2 Mo : choisissez-en une plus légère.';
                    hint.classList.add('is-error');
                    return;
                }

                hint.textContent = file.name;
                hint.classList.remove('is-error');

                const reader = new FileReader();
                reader.onload = function(e) {
                    container.innerHTML = `<img src="${e.target.result}" alt="">`;
                    container.classList.add('has-photo');
                    document.querySelector('[data-photo-label]').textContent = 'Changer la photo';
                }
                reader.readAsDataURL(file);
            }
        </script>

    </div>
