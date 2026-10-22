{{--
    Styles de l'assistant de trajet, partagés par /covoiturage/create et par
    « Déposer une annonce » (service covoiturage) : voir wizard.blade.php.
    Chaque page garde ses propres réglages (marges du layout, hauteur).

    Les cartes Leaflet ne sont pas stylées ici : seul leur conteneur
    (.map-container-traj) est placé.
--}}
<style>
    /* ==========================================================
       Repères : une encre, un accent, des gris
       ========================================================== */
    .app-content-area {
        --wz-ink: #0F172A;
        --wz-text: #334155;
        --wz-muted: #64748B;
        --wz-faint: #94A3B8;
        --wz-line: #E2E8F0;
        --wz-line-strong: #CBD5E1;
        --wz-line-soft: #F1F5F9;
        --wz-surface: #F8FAFC;
        --wz-accent: #ff3c00;
        --wz-accent-hover: #E63600;
        --wz-accent-soft: #FFF5F1;
        --wz-accent-ring: rgba(255, 60, 0, .16);
        --wz-radius: 12px;
        --wz-pad: 28px;

        position: relative;
        height: calc(100vh);
        display: flex;
        overflow: hidden;
    }

    /* ==========================================================
       Mise en page : panneau à gauche, carte ou récapitulatif à droite
       ========================================================== */
    .step-view {
        display: none;
        width: 100%;
        height: 100%;
    }

    .step-view.active {
        display: flex;
    }

    .map-container-traj {
        flex: 1;
        height: 100%;
        position: relative;
        z-index: 1;
    }

    .sidebar-panel {
        width: 400px;
        max-width: 100%;
        background: #fff;
        height: 100%;
        overflow-y: auto;
        border-right: 1px solid var(--wz-line);
        z-index: 10;
        display: flex;
        flex-direction: column;
    }

    .sidebar-panel::-webkit-scrollbar {
        width: 5px;
    }

    .sidebar-panel::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar-panel::-webkit-scrollbar-thumb {
        background: var(--wz-line-strong);
        border-radius: 10px;
    }

    /* Côté droit des étapes sans carte : un fond de plan discret */
    .right-showcase {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 48px;
        position: relative;
        overflow: hidden;
        background-color: var(--wz-surface);
        background-image: radial-gradient(#E2E8F0 1px, transparent 1px);
        background-size: 20px 20px;
    }

    /* ==========================================================
       En-tête d'étape (wizard-head) : ligne d'arrêts, titre
       ========================================================== */
    .wz-head {
        padding: 26px var(--wz-pad) 0;
    }

    .wz-progress {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 22px;
    }

    /* Une section = un arrêt ; le tronçon qui y mène se colore une fois atteint */
    .wz-line {
        flex: 1;
        display: flex;
        align-items: center;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .wz-line li {
        flex: 1;
        display: flex;
        align-items: center;
    }

    .wz-line li:first-child {
        flex: 0 0 auto;
    }

    .wz-line li::before {
        content: '';
        flex: 1;
        height: 2px;
        margin: 0 3px;
        border-radius: 2px;
        background: var(--wz-line);
    }

    .wz-line li:first-child::before {
        display: none;
    }

    .wz-line li::after {
        content: '';
        flex-shrink: 0;
        width: 10px;
        height: 10px;
        border: 2px solid var(--wz-line-strong);
        border-radius: 50%;
        background: #fff;
    }

    .wz-line li.is-done::before,
    .wz-line li.is-current::before {
        background: var(--wz-accent);
    }

    .wz-line li.is-done::after {
        border-color: var(--wz-accent);
        background: var(--wz-accent);
    }

    .wz-line li.is-current::after {
        width: 14px;
        height: 14px;
        border: 4px solid var(--wz-accent);
        box-shadow: 0 0 0 4px var(--wz-accent-ring);
    }

    .wz-count {
        font-size: 13px;
        font-weight: 800;
        color: var(--wz-ink);
        font-variant-numeric: tabular-nums;
    }

    .wz-count span {
        font-weight: 600;
        color: var(--wz-faint);
    }

    .wz-eyebrow {
        margin: 0 0 6px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--wz-accent);
    }

    /* La feuille du layout force .dashboard-content h1 en 400 avec 30px de marge */
    .app-content-area .wz-title {
        margin: 0;
        font-size: 24px;
        line-height: 1.2;
        font-weight: 800;
        letter-spacing: -.02em;
        color: var(--wz-ink);
        text-wrap: balance;
    }

    .wz-lede {
        margin: 8px 0 0;
        font-size: 14px;
        line-height: 1.55;
        color: var(--wz-muted);
    }

    /* ==========================================================
       Contenu et barre d'actions, collée en bas du panneau
       ========================================================== */
    .wz-body {
        flex: 1 0 auto;
        padding: 24px var(--wz-pad) 28px;
    }

    .wz-actions {
        position: sticky;
        bottom: 0;
        z-index: 20;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px var(--wz-pad) 16px;
        border-top: 1px solid var(--wz-line-soft);
        background: rgba(255, 255, 255, .96);
        backdrop-filter: blur(6px);
    }

    /* Première étape : l'action est seule et prend toute la largeur */
    .wz-actions > :only-child {
        flex: 1;
    }

    .wz-btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        height: 48px;
        padding: 0 22px;
        border-radius: var(--wz-radius);
        background: var(--wz-ink);
        font-size: 15px;
        font-weight: 700;
        color: #fff;
        white-space: nowrap;
        transition: background-color .15s, box-shadow .15s, opacity .15s;
    }

    .wz-btn-primary:hover:not(:disabled) {
        background: #1E293B;
    }

    .wz-btn-primary:disabled {
        opacity: .35;
        cursor: not-allowed;
    }

    /* Flèche en pseudo-élément : le script réécrit le texte de certains boutons */
    .wz-btn-next::after {
        content: "\f061";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        font-size: 13px;
        transition: transform .15s;
    }

    .wz-btn-next:hover:not(:disabled)::after {
        transform: translateX(3px);
    }

    .app-content-area .wz-btn-publish {
        background: var(--wz-accent);
        box-shadow: 0 10px 24px -10px rgba(255, 60, 0, .65);
    }

    .app-content-area .wz-btn-publish:hover:not(:disabled) {
        background: var(--wz-accent-hover);
    }

    /* Pendant l'envoi : roue à la place de la coche, bouton plein mais inactif */
    .app-content-area .wz-btn-publish[aria-busy="true"] {
        opacity: .85;
        cursor: progress;
    }

    .wz-btn-publish[aria-busy="true"] i {
        display: none;
    }

    .wz-btn-publish[aria-busy="true"]::before {
        content: '';
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, .45);
        border-top-color: #fff;
        border-radius: 50%;
        animation: wz-spin .7s linear infinite;
    }

    @keyframes wz-spin {
        to {
            transform: rotate(360deg);
        }
    }

    .wz-btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 48px;
        margin-left: -14px;
        padding: 0 14px;
        border-radius: var(--wz-radius);
        font-size: 14.5px;
        font-weight: 700;
        color: var(--wz-muted);
        transition: background-color .15s, color .15s;
    }

    .wz-btn-back i {
        font-size: 12px;
    }

    .wz-btn-back:hover {
        background: var(--wz-surface);
        color: var(--wz-ink);
    }

    .wz-btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 40px;
        padding: 0 14px;
        border: 1px solid var(--wz-line);
        border-radius: 10px;
        background: #fff;
        font-size: 13.5px;
        font-weight: 700;
        color: var(--wz-ink);
        cursor: pointer;
        transition: border-color .15s;
    }

    .wz-btn-secondary:hover {
        border-color: var(--wz-ink);
    }

    .wz-btn-add {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        height: 46px;
        margin-top: 8px;
        border: 1.5px dashed var(--wz-line-strong);
        border-radius: var(--wz-radius);
        font-size: 14px;
        font-weight: 700;
        color: var(--wz-text);
        transition: border-color .15s, color .15s, background-color .15s;
    }

    .wz-btn-add:hover {
        border-color: var(--wz-accent);
        background: var(--wz-accent-soft);
        color: var(--wz-accent);
    }

    .app-content-area :is(button, [role="radio"], .wz-btn-secondary):focus-visible,
    .wz-btn-secondary:focus-within {
        outline: 2px solid var(--wz-accent);
        outline-offset: 2px;
    }

    /* ==========================================================
       Champs
       ========================================================== */
    .wz-field {
        margin: 0;
        padding: 0;
        border: 0;
        min-width: 0;
    }

    .wz-field + .wz-field {
        margin-top: 24px;
    }

    .wz-label {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 700;
        color: var(--wz-text);
    }

    .wz-optional {
        font-size: 12px;
        font-weight: 500;
        color: var(--wz-faint);
    }

    .wz-control {
        position: relative;
        display: flex;
        align-items: center;
        height: 50px;
        border: 1px solid var(--wz-line);
        border-radius: var(--wz-radius);
        background: #fff;
        transition: border-color .15s, box-shadow .15s;
    }

    .wz-control:hover {
        border-color: var(--wz-line-strong);
    }

    .wz-control:focus-within {
        border-color: var(--wz-accent);
        box-shadow: 0 0 0 4px var(--wz-accent-ring);
    }

    .wz-control-icon {
        position: absolute;
        left: 16px;
        font-size: 15px;
        color: var(--wz-faint);
        pointer-events: none;
    }

    .wz-control:focus-within .wz-control-icon {
        color: var(--wz-accent);
    }

    .wz-control input {
        width: 100%;
        height: 100%;
        padding: 0 12px 0 44px;
        border: 0;
        border-radius: inherit;
        background: transparent;
        outline: none;
        font-size: 15px;
        font-weight: 600;
        color: var(--wz-ink);
    }

    .wz-control input::placeholder {
        font-weight: 500;
        color: var(--wz-faint);
    }

    .wz-control input::-webkit-calendar-picker-indicator {
        opacity: .55;
        cursor: pointer;
    }

    .wz-grid-2 {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
        gap: 12px;
    }

    .wz-grid-2 > .wz-field + .wz-field {
        margin-top: 0;
    }

    .wz-hint {
        display: flex;
        gap: 8px;
        margin-top: 14px;
        font-size: 12.5px;
        line-height: 1.5;
        color: var(--wz-muted);
    }

    .wz-hint i {
        margin-top: 2px;
        color: var(--wz-faint);
    }

    .wz-hint-top {
        margin: 0 0 16px;
    }

    .wz-textarea {
        display: block;
        width: 100%;
        min-height: 140px;
        padding: 14px 16px;
        border: 1px solid var(--wz-line);
        border-radius: var(--wz-radius);
        background: #fff;
        outline: none;
        resize: vertical;
        font-size: 14.5px;
        line-height: 1.55;
        color: var(--wz-ink);
        transition: border-color .15s, box-shadow .15s;
    }

    .wz-textarea::placeholder {
        color: var(--wz-faint);
    }

    .wz-textarea:focus {
        border-color: var(--wz-accent);
        box-shadow: 0 0 0 4px var(--wz-accent-ring);
    }

    .wz-counter {
        margin-top: 6px;
        text-align: right;
        font-size: 12px;
        color: var(--wz-faint);
        font-variant-numeric: tabular-nums;
    }

    /* Départ et arrivée : un seul bloc, les deux points reliés par le tracé */
    .wz-trip {
        position: relative;
        border: 1px solid var(--wz-line);
        border-radius: 16px;
        background: #fff;
        transition: border-color .15s, box-shadow .15s;
    }

    .wz-trip::before {
        content: '';
        position: absolute;
        left: 23px;
        top: 40px;
        bottom: 40px;
        border-left: 2px dotted var(--wz-line-strong);
    }

    .wz-trip:focus-within {
        border-color: var(--wz-accent);
        box-shadow: 0 0 0 4px var(--wz-accent-ring);
    }

    .wz-trip-row {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 68px;
        padding: 10px 16px 10px 50px;
        transition: background-color .15s;
    }

    .wz-trip-row:first-child {
        border-radius: 15px 15px 0 0;
    }

    .wz-trip-row:last-child {
        border-radius: 0 0 15px 15px;
    }

    .wz-trip-row + .wz-trip-row {
        border-top: 1px solid var(--wz-line-soft);
    }

    .wz-trip-row:focus-within {
        background: var(--wz-accent-soft);
    }

    .wz-trip-dot {
        position: absolute;
        left: 18px;
        top: 50%;
        width: 12px;
        height: 12px;
        margin-top: -6px;
        border: 3px solid var(--wz-ink);
        border-radius: 50%;
        background: #fff;
    }

    .wz-trip-dot.is-end {
        border-color: var(--wz-accent);
        background: var(--wz-accent);
    }

    .wz-trip-label {
        font-size: 12px;
        font-weight: 700;
        color: var(--wz-muted);
        cursor: text;
    }

    .wz-trip-row input {
        width: 100%;
        padding: 2px 0 0;
        border: 0;
        background: transparent;
        outline: none;
        font-size: 15px;
        font-weight: 600;
        color: var(--wz-ink);
    }

    .wz-trip-row input::placeholder {
        font-weight: 500;
        color: var(--wz-faint);
    }

    /* Suggestions d'adresses (créées par le script) */
    .autocomplete-results,
    .popin-autocomplete-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 50;
        max-height: 260px;
        margin-top: 6px;
        padding: 6px;
        overflow-y: auto;
        border: 1px solid var(--wz-line);
        border-radius: var(--wz-radius);
        background: #fff;
        box-shadow: 0 18px 40px -14px rgba(15, 23, 42, .28);
    }

    .popin-autocomplete-results {
        z-index: 200;
        max-height: 220px;
    }

    .autocomplete-item {
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 14px;
        line-height: 1.4;
        color: var(--wz-text);
        cursor: pointer;
    }

    .autocomplete-item:hover {
        background: var(--wz-surface);
        color: var(--wz-ink);
    }

    /* ==========================================================
       Listes : routes, villes, escales, tronçons
       ========================================================== */
    .wz-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .wz-group + .wz-group {
        margin-top: 28px;
    }

    .wz-group-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        font-size: 13px;
        font-weight: 700;
        color: var(--wz-text);
    }

    .wz-group-title i {
        font-size: 12px;
        color: var(--wz-accent);
    }

    .wz-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 28px 0;
        font-size: 13.5px;
        color: var(--wz-muted);
    }

    .wz-empty {
        padding: 14px;
        border-radius: var(--wz-radius);
        background: var(--wz-surface);
        text-align: center;
        font-size: 13px;
        color: var(--wz-muted);
    }

    /* Commun aux choix : route, carte d'option, préférence passager */
    .route-option,
    .card-option,
    .radio-card {
        display: flex;
        gap: 14px;
        border: 1px solid var(--wz-line);
        border-radius: var(--wz-radius);
        background: #fff;
        cursor: pointer;
        transition: border-color .15s, background-color .15s, box-shadow .15s;
    }

    .route-option:hover,
    .card-option:hover,
    .radio-card:hover {
        border-color: var(--wz-line-strong);
    }

    .route-option.selected,
    .card-option.selected,
    .radio-custom:checked + .radio-card {
        border-color: var(--wz-accent);
        background: var(--wz-accent-soft);
        box-shadow: inset 0 0 0 1px var(--wz-accent);
    }

    .route-option-radio,
    .card-option-mark,
    .radio-card-mark {
        position: relative;
        flex-shrink: 0;
        width: 18px;
        height: 18px;
        border: 2px solid var(--wz-line-strong);
        border-radius: 50%;
        background: #fff;
        transition: border-color .15s, box-shadow .15s;
    }

    .route-option.selected .route-option-radio,
    .card-option.selected .card-option-mark {
        border-color: var(--wz-accent);
        box-shadow: inset 0 0 0 4px var(--wz-accent);
    }

    .route-option {
        align-items: center;
        padding: 14px 16px;
    }

    .route-option-body {
        flex: 1;
        min-width: 0;
    }

    .route-option-title {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        font-size: 16px;
        font-weight: 800;
        color: var(--wz-ink);
        font-variant-numeric: tabular-nums;
    }

    /* Une seule façon de signaler une suggestion : la route la plus rapide,
       le mode de réservation recommandé */
    .route-option-badge,
    .card-option-badge {
        padding: 2px 8px;
        border-radius: 999px;
        background: #ECFDF5;
        font-size: 11px;
        font-weight: 700;
        color: #047857;
    }

    .route-option-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 4px 14px;
        margin-top: 3px;
        font-size: 13px;
        font-weight: 600;
        color: var(--wz-muted);
        font-variant-numeric: tabular-nums;
    }

    .route-option-meta i {
        margin-right: 5px;
        font-size: 12px;
        color: var(--wz-faint);
    }

    /* Villes détectées : la case reste le seul indicateur, toute la ligne est cliquable */
    .city-option {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border: 1px solid var(--wz-line);
        border-radius: var(--wz-radius);
        background: #fff;
        cursor: pointer;
        transition: border-color .15s, background-color .15s;
    }

    .city-option:hover {
        border-color: var(--wz-line-strong);
    }

    .city-option:has(input:checked) {
        border-color: var(--wz-accent);
        background: var(--wz-accent-soft);
    }

    .city-option-check {
        flex-shrink: 0;
        width: 18px;
        height: 18px;
        accent-color: var(--wz-accent);
        cursor: pointer;
    }

    .city-option-name {
        flex: 1;
        min-width: 0;
        font-size: 14.5px;
        font-weight: 600;
        color: var(--wz-ink);
    }

    .city-option-icon {
        font-size: 13px;
        color: var(--wz-faint);
    }

    .manual-stop-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 10px 10px 14px;
        border: 1px solid var(--wz-line);
        border-radius: var(--wz-radius);
        background: #fff;
        animation: slideIn .25s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-6px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .manual-stop-icon {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: var(--wz-accent-soft);
        font-size: 13px;
        color: var(--wz-accent);
    }

    .manual-stop-body {
        flex: 1;
        min-width: 0;
    }

    .manual-stop-name {
        overflow: hidden;
        font-size: 14.5px;
        font-weight: 600;
        color: var(--wz-ink);
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .manual-stop-meta {
        font-size: 12px;
        color: var(--wz-muted);
    }

    /* Toujours visible : au survol seulement, il échappait au tactile et au clavier */
    .manual-stop-item .delete-btn {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        font-size: 13px;
        color: var(--wz-faint);
        transition: background-color .15s, color .15s;
    }

    .manual-stop-item .delete-btn:hover {
        background: #FEF2F2;
        color: #DC2626;
    }

    /* Récapitulatif de l'itinéraire, en feuille de route */
    .wz-card {
        padding: 18px;
        border: 1px solid var(--wz-line);
        border-radius: 16px;
        background: #fff;
    }

    .wz-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        justify-content: space-between;
        gap: 4px 12px;
        margin-bottom: 16px;
    }

    .wz-card-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--wz-text);
    }

    .wz-card-meta {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--wz-muted);
        font-variant-numeric: tabular-nums;
    }

    .summary-stops {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 18px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .summary-stops::before {
        content: '';
        position: absolute;
        left: 6px;
        top: 10px;
        bottom: 10px;
        width: 2px;
        background: var(--wz-line);
    }

    .summary-stop {
        position: relative;
        display: flex;
        flex-direction: column;
        padding-left: 28px;
    }

    .summary-stop-dot {
        position: absolute;
        left: 0;
        top: 3px;
        width: 14px;
        height: 14px;
        border: 3px solid var(--wz-line-strong);
        border-radius: 50%;
        background: #fff;
    }

    .summary-stop.is-start .summary-stop-dot {
        border-color: var(--wz-ink);
    }

    .summary-stop.is-end .summary-stop-dot {
        border-color: var(--wz-accent);
        background: var(--wz-accent);
    }

    .summary-stop-kind {
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--wz-faint);
    }

    .summary-stop-name {
        font-size: 14.5px;
        font-weight: 700;
        line-height: 1.35;
        color: var(--wz-ink);
    }

    /* ==========================================================
       Passagers : compteur et préférences
       ========================================================== */
    .wz-qty {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border: 1px solid var(--wz-line);
        border-radius: var(--wz-radius);
    }

    .wz-qty-title {
        font-size: 14.5px;
        font-weight: 700;
        color: var(--wz-ink);
    }

    .wz-qty-hint {
        font-size: 12.5px;
        color: var(--wz-muted);
    }

    .wz-qty-control {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .wz-qty-control button {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border: 1px solid var(--wz-line);
        border-radius: 50%;
        background: #fff;
        font-size: 12px;
        color: var(--wz-ink);
        transition: border-color .15s;
    }

    .wz-qty-control button:hover {
        border-color: var(--wz-ink);
    }

    #qty-passengers {
        min-width: 36px;
        text-align: center;
        font-size: 20px;
        font-weight: 800;
        color: var(--wz-ink);
        font-variant-numeric: tabular-nums;
    }

    .wz-options {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .radio-card {
        align-items: center;
        padding: 14px 16px;
    }

    /* Le script affiche la pastille en réglant l'opacité de .check-icon */
    .radio-card .check-icon {
        position: absolute;
        inset: 0;
        width: 6px;
        height: 6px;
        margin: auto;
        border-radius: 50%;
        background: #fff;
        opacity: 0;
        transform: scale(.5);
        transition: opacity .15s, transform .15s;
    }

    .radio-custom:checked + .radio-card .radio-card-mark {
        border-color: var(--wz-accent);
        background: var(--wz-accent);
    }

    .radio-custom:checked + .radio-card .check-icon {
        opacity: 1;
        transform: scale(1);
    }

    .radio-custom:focus-visible + .radio-card {
        outline: 2px solid var(--wz-accent);
        outline-offset: 2px;
    }

    .radio-card-text {
        display: flex;
        flex: 1;
        flex-direction: column;
        min-width: 0;
    }

    .radio-card .label-text {
        font-size: 14.5px;
        font-weight: 700;
        color: var(--wz-ink);
    }

    .radio-card-hint {
        font-size: 12.5px;
        color: var(--wz-muted);
    }

    .radio-card-icon {
        font-size: 15px;
        color: var(--wz-faint);
        transition: color .15s;
    }

    .radio-custom:checked + .radio-card .radio-card-icon {
        color: var(--wz-accent);
    }

    /* ==========================================================
       Cartes d'option : réservation, retour
       ========================================================== */
    .card-option {
        align-items: flex-start;
        padding: 16px;
        border-radius: 14px;
    }

    .card-option-icon {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--wz-surface);
        font-size: 16px;
        color: var(--wz-text);
        transition: background-color .15s, color .15s;
    }

    .card-option.selected .card-option-icon {
        background: #fff;
        color: var(--wz-accent);
    }

    .card-option-text {
        flex: 1;
        min-width: 0;
    }

    .card-option-title {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px 8px;
        font-size: 15px;
        font-weight: 700;
        color: var(--wz-ink);
    }

    .card-option-desc {
        display: block;
        margin-top: 4px;
        font-size: 13px;
        line-height: 1.5;
        color: var(--wz-muted);
    }

    .card-option-mark {
        margin-top: 2px;
    }

    /* ==========================================================
       Prix : tronçons, curseurs, total
       ========================================================== */
    .price-segment {
        padding: 14px 16px 12px;
        border: 1px solid var(--wz-line);
        border-radius: var(--wz-radius);
        background: #fff;
    }

    .price-segment-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .price-segment-route {
        min-width: 0;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.35;
        color: var(--wz-ink);
    }

    .price-segment-route i {
        margin: 0 6px;
        font-size: 11px;
        color: var(--wz-faint);
    }

    .price-segment .price-value {
        flex-shrink: 0;
        padding: 4px 10px;
        border-radius: 8px;
        background: var(--wz-accent-soft);
        font-size: 15px;
        font-weight: 800;
        color: var(--wz-accent);
        font-variant-numeric: tabular-nums;
    }

    /* Partie remplie tenue par --fill (script en fin de wizard-scripts) */
    .price-range {
        width: 100%;
        height: 6px;
        -webkit-appearance: none;
        appearance: none;
        border-radius: 999px;
        background: linear-gradient(to right, var(--wz-accent) var(--fill, 0%), var(--wz-line) var(--fill, 0%));
        outline: none;
        cursor: pointer;
    }

    .price-range::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid var(--wz-accent);
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 2px 6px rgba(15, 23, 42, .2);
        transition: box-shadow .15s;
    }

    .price-range::-moz-range-thumb {
        width: 16px;
        height: 16px;
        border: 2px solid var(--wz-accent);
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 2px 6px rgba(15, 23, 42, .2);
    }

    .price-range:focus-visible::-webkit-slider-thumb {
        box-shadow: 0 0 0 5px var(--wz-accent-ring);
    }

    .price-range:focus-visible::-moz-range-thumb {
        box-shadow: 0 0 0 5px var(--wz-accent-ring);
    }

    .price-segment-scale {
        display: flex;
        justify-content: space-between;
        margin-top: 8px;
        font-size: 11.5px;
        color: var(--wz-faint);
        font-variant-numeric: tabular-nums;
    }

    .wz-total {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        margin-top: 16px;
        padding: 14px 16px;
        border-radius: var(--wz-radius);
        background: var(--wz-surface);
        font-size: 13px;
        font-weight: 600;
        color: var(--wz-muted);
    }

    .wz-total strong {
        font-size: 20px;
        font-weight: 800;
        color: var(--wz-ink);
        font-variant-numeric: tabular-nums;
    }

    /* ==========================================================
       Profil conducteur
       ========================================================== */
    .wz-photo {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .wz-photo-preview {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        width: 72px;
        height: 72px;
        overflow: hidden;
        border: 1.5px dashed var(--wz-line-strong);
        border-radius: 50%;
        background: var(--wz-surface);
        font-size: 24px;
        color: var(--wz-line-strong);
    }

    .wz-photo-preview.has-photo {
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px var(--wz-accent);
    }

    .wz-photo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .wz-photo-hint {
        max-width: 220px;
        margin-top: 8px;
        overflow: hidden;
        font-size: 12px;
        color: var(--wz-muted);
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .wz-photo-hint.is-error {
        white-space: normal;
        color: #DC2626;
    }

    /* ==========================================================
       Fenêtres d'ajout d'escale
       ========================================================== */
    .popin-overlay {
        position: fixed;
        inset: 0;
        z-index: 100;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .55);
        backdrop-filter: blur(4px);
    }

    .popin-overlay.active {
        display: flex;
    }

    .wz-dialog {
        position: relative;
        width: 100%;
        max-width: 440px;
        padding: 28px;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 30px 60px -20px rgba(15, 23, 42, .45);
    }

    .wz-dialog-close {
        position: absolute;
        top: 16px;
        right: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        color: var(--wz-muted);
        transition: background-color .15s, color .15s;
    }

    .wz-dialog-close:hover {
        background: var(--wz-surface);
        color: var(--wz-ink);
    }

    .wz-dialog-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        margin-bottom: 16px;
        border-radius: 12px;
        background: var(--wz-accent-soft);
        font-size: 17px;
        color: var(--wz-accent);
    }

    .wz-dialog-title {
        font-size: 20px;
        font-weight: 800;
        letter-spacing: -.01em;
        color: var(--wz-ink);
    }

    .wz-dialog-lede {
        margin: 4px 0 20px;
        font-size: 14px;
        color: var(--wz-muted);
    }

    .wz-dialog-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 24px;
    }

    .wz-dialog-actions .wz-btn-back {
        margin-left: 0;
    }

    .wz-selected {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        border: 1px solid #A7F3D0;
        border-radius: var(--wz-radius);
        background: #ECFDF5;
    }

    .wz-selected > i {
        color: #059669;
    }

    .wz-selected-name {
        flex: 1;
        min-width: 0;
        font-size: 14px;
        font-weight: 700;
        color: var(--wz-ink);
    }

    .wz-selected-clear {
        color: var(--wz-faint);
    }

    .wz-selected-clear:hover {
        color: #DC2626;
    }

    /* ==========================================================
       Récapitulatif « Votre trajet » et totaux, à droite
       ========================================================== */
    .trip-recap {
        width: 100%;
        max-width: 420px;
        text-align: left;
    }

    .trip-recap-card {
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--wz-line);
        border-radius: 24px;
        box-shadow: 0 24px 48px -24px rgba(15, 23, 42, .28);
    }

    .trip-recap-head {
        padding: 22px 24px 18px;
        border-bottom: 1px solid var(--wz-line-soft);
    }

    .trip-recap-eyebrow {
        margin-bottom: 14px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--wz-accent);
    }

    /* Départ et arrivée reliés par un trait, comme sur une feuille de route */
    .trip-recap-route {
        display: grid;
        grid-template-columns: 12px 1fr;
        column-gap: 12px;
        align-items: center;
    }

    .trip-recap-dot {
        width: 12px;
        height: 12px;
        border: 3px solid var(--wz-ink);
        border-radius: 50%;
    }

    .trip-recap-dot.is-end {
        background: var(--wz-accent);
        border-color: var(--wz-accent);
    }

    .trip-recap-line {
        align-self: stretch;
        width: 2px;
        min-height: 22px;
        margin: 2px auto;
        background: var(--wz-line);
    }

    .trip-recap-city {
        font-size: 16px;
        font-weight: 800;
        line-height: 1.3;
        color: var(--wz-ink);
    }

    .trip-recap-city.is-empty {
        font-weight: 600;
        color: var(--wz-line-strong);
    }

    .trip-recap-via {
        font-size: 12px;
        font-weight: 600;
        color: var(--wz-faint);
    }

    .trip-recap-metrics {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 14px;
        font-size: 13px;
        font-weight: 700;
        color: var(--wz-muted);
    }

    .trip-recap-rows {
        margin: 0;
        padding: 6px 24px 10px;
        list-style: none;
    }

    .trip-recap-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 0;
        border-bottom: 1px solid var(--wz-line-soft);
    }

    .trip-recap-row:last-child {
        border-bottom: 0;
    }

    .trip-recap-row > i {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: var(--wz-surface);
        font-size: 13px;
        color: var(--wz-muted);
    }

    /* La ligne que l'étape en cours est en train de remplir */
    .trip-recap-row.is-current > i {
        background: #FFF1EB;
        color: var(--wz-accent);
    }

    .trip-recap-label {
        flex: 1;
        font-size: 13px;
        font-weight: 600;
        color: var(--wz-muted);
    }

    .trip-recap-value {
        font-size: 13.5px;
        font-weight: 800;
        color: var(--wz-ink);
        text-align: right;
    }

    .trip-recap-value.is-empty {
        font-weight: 600;
        color: var(--wz-line-strong);
    }

    .trip-recap-note {
        margin-top: 14px;
        font-size: 12px;
        font-weight: 600;
        text-align: center;
        color: var(--wz-faint);
    }

    .price-showcase {
        padding: 26px 28px 24px;
    }

    .price-showcase .trip-recap-eyebrow {
        margin-bottom: 10px;
    }

    .price-showcase .price-showcase-amount {
        font-size: 64px;
        line-height: 1;
        font-weight: 800;
        letter-spacing: -.03em;
        color: var(--wz-ink);
        font-variant-numeric: tabular-nums;
    }

    .price-showcase-note {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 16px;
        font-size: 13px;
        font-weight: 600;
        color: var(--wz-muted);
    }

    /* La pastille « Publier » du layout couvrait le bouton d'action (mobile) et
       le zoom de la carte ; on est déjà en train de publier : inutile ici. */
    .publish-fab {
        display: none;
    }

    /* ==========================================================
       Écrans étroits et préférences d'animation
       ========================================================== */
    @media (min-width: 768px) {

        /* Le total se lit à droite, dans le récapitulatif du prix */
        .wz-total {
            display: none;
        }
    }

    /* Sur téléphone, le panneau (400px) ne laissait aucune place à la carte :
       elle passe au-dessus, le formulaire dessous. */
    @media (max-width: 767px) {
        .app-content-area {
            --wz-pad: 20px;
        }

        .step-view.active {
            flex-direction: column-reverse;
        }

        .sidebar-panel {
            flex: 1 1 auto;
            width: 100%;
            height: auto;
            min-height: 0;
            border-right: 0;
        }

        .map-container-traj {
            flex: 0 0 36%;
            height: auto;
            border-bottom: 1px solid var(--wz-line);
        }

        .right-showcase {
            display: none;
        }

        .app-content-area .wz-title {
            font-size: 21px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .app-content-area *,
        .app-content-area *::before,
        .app-content-area *::after {
            transition: none !important;
            animation: none !important;
        }
    }
</style>
