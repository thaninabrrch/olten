@extends('layouts.connected')

@section('title', 'Modifier le retour | ' . config('app.name'))

{{--
    Le balisage et le script de cette page sont inchanges : seule la
    feuille de style ci-dessous suit desormais le design de l'espace
    connecte. Les selecteurs et les classes d'etat utilises par le
    script sont conserves tels quels.

    Leaflet et Tailwind etaient charges une seconde fois ici alors que
    layouts.connected les fournit deja : les doublons ont ete retires.
--}}

<style>
    /* Jetons repris du design system de l'espace connecte */
    .app-content-area,
    .step-view,
    .sidebar-panel {
        --tj-primary: #ff3c00;
        --tj-ink: #16191d;
        --tj-muted: #6c757d;
        --tj-border: #e9ecef;
        --tj-surface: #fbfbfc;
        --tj-radius: 14px;
        --tj-radius-sm: 10px;
        --tj-shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 4px 16px rgba(16, 24, 40, .05);
        --tj-shadow-lg: 0 8px 28px rgba(16, 24, 40, .10);
    }

    .app-content-area {
        /* Le header de l'espace connecte mesure 70px */
        height: calc(100vh - 70px);
        display: flex;
        overflow: hidden;
        color: var(--tj-ink);
    }

    .step-view {
        display: none;
        width: 100%;
        height: 100%;
    }

    .step-view.active {
        display: flex;
    }

    .map-container-traj,
    .map-container {
        flex: 1;
        height: 100%;
        position: relative;
        z-index: 1;
    }

    .sidebar-panel {
        width: 420px;
        background: #fff;
        height: 100%;
        overflow-y: auto;
        border-right: 1px solid var(--tj-border);
        z-index: 10;
        display: flex;
        flex-direction: column;
    }

    .sidebar-panel::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-panel::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar-panel::-webkit-scrollbar-thumb {
        background: #d9dde2;
        border-radius: 10px;
    }

    .right-showcase {
        flex: 1;
        background: var(--tj-surface);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    /* Choix d'itineraire */
    .route-option {
        transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
        cursor: pointer;
        border: 1.5px solid var(--tj-border);
        border-radius: var(--tj-radius);
    }

    .route-option:hover {
        border-color: #ced4da;
        background: var(--tj-surface);
    }

    .route-option.selected {
        border-color: var(--tj-primary);
        background: #fff1ec;
        box-shadow: 0 4px 14px rgba(255, 60, 0, .12);
    }

    /* Autocompletion d'adresse */
    .autocomplete-results,
    .popin-autocomplete-results {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid var(--tj-border);
        border-radius: var(--tj-radius-sm);
        box-shadow: var(--tj-shadow-lg);
        z-index: 60;
        max-height: 260px;
        overflow-y: auto;
        padding: 6px;
    }

    .autocomplete-item {
        padding: 11px 12px;
        cursor: pointer;
        font-size: 13.5px;
        line-height: 1.45;
        border-radius: 8px;
        border-bottom: none;
        transition: background .15s ease;
    }

    .autocomplete-item:hover {
        background: #f4f5f7;
    }

    /* Repere anime sur la carte */
    .marker-pulse {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--tj-primary);
        border: 3px solid #fff;
        animation: tj-pulse 2s infinite;
    }

    @keyframes tj-pulse {
        0%   { box-shadow: 0 0 0 0 rgba(255, 60, 0, .45); }
        70%  { box-shadow: 0 0 0 12px rgba(255, 60, 0, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 60, 0, 0); }
    }

    /* Cartes a cocher */
    .card-option {
        border: 1.5px solid var(--tj-border);
        border-radius: var(--tj-radius);
        cursor: pointer;
        transition: border-color .2s ease, background .2s ease;
    }

    .card-option:hover {
        border-color: #ced4da;
        background: var(--tj-surface);
    }

    .card-option.selected {
        border-color: var(--tj-primary);
        background: #fff1ec;
    }

    .radio-custom:checked + .radio-card {
        border-color: var(--tj-primary);
        background: #fff1ec;
    }

    .radio-custom:checked + .radio-card .check-icon {
        opacity: 1;
        transform: scale(1);
    }

    .radio-custom:checked + .radio-card .label-text {
        color: var(--tj-ink);
        font-weight: 600;
    }

    /* Saisie d'un prix */
    .input-price {
        width: 92px;
        text-align: right;
        padding: 9px 12px;
        border: 1px solid var(--tj-border);
        border-radius: var(--tj-radius-sm);
        background: #fff;
        font-size: 15px;
        font-weight: 600;
        color: var(--tj-ink);
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .input-price:focus {
        outline: none;
        border-color: var(--tj-primary);
        box-shadow: 0 0 0 3px rgba(255, 60, 0, .12);
    }

    /* Interrupteur */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 26px;
        flex-shrink: 0;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background: #dfe2e6;
        transition: background .25s ease;
        border-radius: 26px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background: #fff;
        transition: transform .25s ease;
        border-radius: 50%;
        box-shadow: 0 1px 4px rgba(16, 24, 40, .18);
    }

    input:checked + .slider {
        background: var(--tj-primary);
    }

    input:checked + .slider:before {
        transform: translateX(20px);
    }

    /* Fenetre d'ajout d'escale */
    .popin-overlay {
        position: fixed;
        inset: 0;
        background: rgba(16, 24, 40, .55);
        backdrop-filter: blur(3px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 2000;
        padding: 20px;
    }

    .popin-overlay.active {
        display: flex;
    }

    /* Segments de tarification */
    .pricing-segment {
        position: relative;
        padding-left: 26px;
    }

    .pricing-segment::before {
        content: '';
        position: absolute;
        left: 7px;
        top: 22px;
        bottom: -14px;
        width: 2px;
        background: var(--tj-border);
    }

    .pricing-segment:last-child::before {
        display: none;
    }

    /* Escales saisies a la main */
    .manual-stop-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 12px;
        background: var(--tj-surface);
        border: 1px solid var(--tj-border);
        border-radius: var(--tj-radius-sm);
        transition: border-color .2s ease, background .2s ease;
    }

    .manual-stop-item:hover {
        border-color: #ced4da;
        background: #fff;
    }

    .manual-stop-item .delete-btn {
        opacity: 0;
        transition: opacity .2s ease;
    }

    .manual-stop-item:hover .delete-btn,
    .manual-stop-item .delete-btn:focus-visible {
        opacity: 1;
    }

    @media (hover: none) {
        .manual-stop-item .delete-btn {
            opacity: 1;
        }
    }

    /* Reordonnancement */
    .drag-handle {
        cursor: grab;
        color: #adb5bd;
        transition: color .2s ease;
    }

    .drag-handle:hover {
        color: var(--tj-muted);
    }

    .drag-handle:active {
        cursor: grabbing;
    }

    .sortable-ghost {
        opacity: .4;
        background: #fff1ec;
        border-color: var(--tj-primary);
    }

    /* Animations decoratives */
    .animate-float {
        animation: tj-float 6s ease-in-out infinite;
    }

    .animate-float-delayed {
        animation: tj-float 6s ease-in-out 2s infinite;
    }

    @keyframes tj-float {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-12px); }
    }

    /* Harmonisation typographique, limitee a la zone de l'assistant */
    .app-content-area .font-black,
    .app-content-area .font-extrabold {
        font-weight: 700 !important;
    }

    .app-content-area .tracking-widest,
    .app-content-area .tracking-\[0\.2em\],
    .app-content-area .tracking-\[0\.15em\] {
        letter-spacing: .08em !important;
    }

    .app-content-area .rounded-3xl,
    .app-content-area .rounded-\[2rem\],
    .app-content-area .rounded-\[2\.5rem\],
    .app-content-area .rounded-\[3rem\],
    .app-content-area .rounded-\[40px\] {
        border-radius: var(--tj-radius) !important;
    }

    .app-content-area .rounded-2xl {
        border-radius: var(--tj-radius-sm) !important;
    }

    .app-content-area button.bg-\[\#ff3c00\],
    .app-content-area button.bg-slate-900,
    .app-content-area a.bg-\[\#ff3c00\],
    .app-content-area a.bg-slate-900 {
        border-radius: 999px;
        box-shadow: 0 4px 12px rgba(16, 24, 40, .12);
        transition: filter .2s ease, transform .2s ease, box-shadow .2s ease;
    }

    .app-content-area button.bg-\[\#ff3c00\]:hover,
    .app-content-area a.bg-\[\#ff3c00\]:hover {
        filter: brightness(1.06);
        transform: translateY(-1px);
    }

    .dashboard-content:has(.app-content-area) {
        padding: 0;
    }

    @media (max-width: 1024px) {
        .app-content-area {
            height: auto;
            flex-direction: column;
            overflow: visible;
        }

        .step-view.active {
            flex-direction: column;
        }

        .sidebar-panel {
            width: 100%;
            height: auto;
            border-right: none;
            border-bottom: 1px solid var(--tj-border);
        }

        .map-container-traj,
        .map-container {
            min-height: 320px;
            flex: none;
        }

        .right-showcase {
            padding: 2.5rem 1.5rem;
        }
    }

    /* ── Chronologie propre a cet ecran ── */
    .step-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .timeline-line {
        position: absolute;
        left: 17px;
        top: 24px;
        bottom: 0;
        width: 2px;
        background: var(--tj-border);
    }
</style>

@section('content')
    <div class="app-content-area bg-white">

        <!-- ========== POPIN AJOUT ESCALE RETOUR ========== -->
        <div id="popin-add-return-stop" class="popin-overlay">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 relative">
                <button onclick="closeStopPopin()"
                    class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
                <div class="mb-6">
                    <div
                        class="w-14 h-14 bg-orange-100 rounded-2xl flex items-center justify-center text-[#ff3c00] text-2xl mb-4">
                        <i class="fa-solid fa-map-pin"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900">Ajouter une escale</h3>
                    <p class="text-slate-500 text-sm mt-1 font-medium">Recherchez une ville ou une adresse précise.</p>
                </div>
                <div class="relative">
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input id="popin-stop-input" type="text" placeholder="Ex: Lyon, Marseille, Aix-en-Provence..."
                            class="w-full pl-11 pr-4 py-4 rounded-xl border border-slate-200 focus:border-[#ff3c00] focus:ring-2 focus:ring-orange-100 transition-all outline-none font-medium"
                            autocomplete="off">
                    </div>
                    <div id="popin-stop-results" class="popin-autocomplete-results hidden"></div>
                </div>
                <div id="popin-stop-selected" class="mt-4 hidden">
                    <div class="flex items-center gap-3 p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                        <i class="fa-solid fa-circle-check text-emerald-500"></i>
                        <span id="popin-stop-selected-name" class="font-bold text-slate-800 flex-1"></span>
                        <button onclick="clearPopinSelection()" class="text-slate-400 hover:text-red-500">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
                <button id="popin-stop-confirm" onclick="confirmManualStop()" disabled
                    class="w-full mt-6 py-4 bg-slate-900 text-white rounded-xl font-bold text-lg disabled:opacity-30 disabled:cursor-not-allowed hover:bg-[#ff3c00] transition-all">
                    Ajouter cette escale
                </button>
            </div>
        </div>

        <!-- ========== ÉTAPE 1 : RÉSUMÉ ACTUEL DU RETOUR ========== -->
        <section id="view-recap" class="step-view active">
            <div class="sidebar-panel p-6">
                <a href="{{ url()->previous() }}"
                    class="mb-6 text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-chevron-left text-xs"></i> Retour au trajet
                </a>

                <div class="mb-6">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-2">
                        Modifier le retour
                    </span>
                    <h2 class="text-2xl font-black text-slate-800">
                        Trajet <span class="text-[#ff3c00]">retour</span> actuel
                    </h2>
                </div>

                <!-- Infos actuelles -->
                <div class="space-y-4 flex-1">
                    <!-- Date & heure -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <p class="text-[10px] font-black uppercase text-slate-400 mb-3 tracking-widest">Date & heure</p>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center text-[#ff3c00]">
                                <i class="fa-solid fa-calendar"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-800" id="recap-date">
                                    {{ $covoiturage->return_date ? $covoiturage->return_date->format('d/m/Y') : 'Non défini' }}
                                </p>
                                <p class="text-sm text-slate-500" id="recap-time">
                                    {{ $covoiturage->return_time ?? 'Non défini' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Itinéraire retour -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <p class="text-[10px] font-black uppercase text-slate-400 mb-3 tracking-widest">Itinéraire retour
                        </p>
                        <ul id="recap-itinerary" class="space-y-3 relative">
                            {{-- Injecté par JS --}}
                        </ul>
                    </div>

                    <!-- Prix retour -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <p class="text-[10px] font-black uppercase text-slate-400 mb-3 tracking-widest">Tarification retour
                        </p>
                        <div id="recap-pricing" class="space-y-2">
                            {{-- Injecté par JS --}}
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-200 flex justify-between items-center">
                            <span class="font-bold text-slate-600">Total</span>
                            <span id="recap-total" class="text-xl font-black text-[#ff3c00]">0€</span>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100">
                    <button onclick="changeView('view-edit-datetime')"
                        class="w-full py-4 bg-slate-900 text-white rounded-xl font-bold text-lg hover:bg-[#ff3c00] transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-pen"></i> Modifier le retour
                    </button>
                </div>
            </div>

            <div class="map-container-traj">
                <div id="map-recap" class="h-full w-full"></div>
            </div>
        </section>

        <!-- ========== ÉTAPE 2 : DATE & HEURE RETOUR ========== -->
        <section id="view-edit-datetime" class="step-view">
            <div class="w-full md:w-[450px] p-8 border-r border-slate-100 flex flex-col">
                <button onclick="changeView('view-recap')"
                    class="mb-6 text-slate-400 font-bold flex items-center gap-2 hover:text-slate-600">
                    <i class="fa-solid fa-arrow-left"></i> Résumé
                </button>

                <div class="mb-2">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-2">Étape
                        1/4</span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 mb-2">Quand <span class="text-[#ff3c00]">rentrez-vous</span> ?
                </h2>
                <p class="text-slate-500 mb-10 font-medium">Modifiez la date et l'heure du trajet retour.</p>

                <div class="space-y-8 flex-1">
                    <div class="space-y-3">
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest">Date de
                            retour</label>
                        <div
                            class="flex items-center gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-100 focus-within:border-[#ff3c00] transition-colors">
                            <i class="fa-solid fa-calendar text-[#ff3c00] text-xl"></i>
                            <input type="date" id="edit-return-date"
                                class="bg-transparent text-xl font-bold text-slate-900 w-full outline-none"
                                value="{{ $covoiturage->return_date ? $covoiturage->return_date->format('Y-m-d') : '' }}">
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest">Heure de
                            retour</label>
                        <div
                            class="flex items-center gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-100 focus-within:border-[#ff3c00] transition-colors">
                            <i class="fa-solid fa-clock text-[#ff3c00] text-xl"></i>
                            <input type="time" id="edit-return-time"
                                class="bg-transparent text-xl font-bold text-slate-900 w-full outline-none"
                                value="{{ $covoiturage->return_time ?? '' }}">
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-100">
                    <button onclick="goToEditRoute()"
                        class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black hover:bg-black transition-all shadow-xl text-lg">
                        Suivant
                    </button>
                </div>
            </div>

            <div
                class="hidden md:flex flex-1 bg-white items-center justify-center p-16 text-center border-l border-slate-100">
                <div class="max-w-md">
                    <div class="text-[#ff3c00] text-6xl mb-8">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <h3 class="text-4xl font-black text-slate-900 mb-6 uppercase tracking-tighter">
                        Modifiez le <span class="text-[#ff3c00]">retour</span>.
                    </h3>
                    <div class="w-16 h-1 bg-[#ff3c00] mx-auto mb-8"></div>
                    <p class="text-slate-500 text-lg font-medium leading-relaxed">
                        Ajustez votre trajet retour en quelques étapes simples.
                    </p>
                </div>
            </div>
        </section>

        <!-- ========== ÉTAPE 3 : ROUTE RETOUR ========== -->
        <section id="view-edit-route" class="step-view">
            <div class="sidebar-panel p-6">
                <button onclick="changeView('view-edit-datetime')"
                    class="mb-6 text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-chevron-left text-xs"></i> Date & heure
                </button>

                <div class="mb-6">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-2">Étape
                        2/4</span>
                    <h2 class="text-2xl font-black text-slate-800">
                        Route <span class="text-[#ff3c00]">retour</span>
                    </h2>
                </div>

                <div id="edit-routes-list" class="space-y-4 flex-1 overflow-y-auto pr-2"></div>

                <div class="pt-6 border-t border-slate-100">
                    <button onclick="goToEditStops()"
                        class="w-full py-4 bg-slate-900 text-white rounded-xl font-bold text-lg hover:bg-[#ff3c00] transition-all">
                        Confirmer la route
                    </button>
                </div>
            </div>
            <div class="map-container-traj">
                <div id="map-edit-route" class="h-full w-full"></div>
            </div>
        </section>

        <!-- ========== ÉTAPE 4 : ESCALES RETOUR ========== -->
        <section id="view-edit-stops" class="step-view">
            <div class="sidebar-panel p-6">
                <button onclick="changeView('view-edit-route')"
                    class="mb-6 text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-chevron-left text-xs"></i> Route retour
                </button>

                <div class="mb-6">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-2">Étape
                        3/4</span>
                    <h2 class="text-2xl font-black text-slate-800">Escales du <span class="text-[#ff3c00]">retour</span>
                    </h2>
                    <p class="text-sm text-slate-500 mt-2 font-medium">Sélectionnez ou ajoutez des escales pour le retour.
                    </p>
                </div>

                <!-- Auto-détectées -->
                <div class="mb-4">
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-3">
                        <i class="fa-solid fa-wand-magic-sparkles mr-1 text-[#ff3c00]"></i> Détectées sur la route
                    </p>
                    <div id="edit-intermediate-cities" class="space-y-3"></div>
                </div>

                <div class="flex items-center gap-3 my-5">
                    <div class="flex-1 h-px bg-slate-200"></div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Escales manuelles</span>
                    <div class="flex-1 h-px bg-slate-200"></div>
                </div>

                <!-- Manuelles -->
                <div id="edit-manual-stops-list" class="space-y-3 mb-4">
                    <p class="text-sm text-slate-400 italic text-center py-2">Aucune escale ajoutée</p>
                </div>

                <button onclick="openStopPopin()"
                    class="w-full py-3 border-2 border-dashed border-slate-200 rounded-xl text-slate-500 font-bold hover:border-[#ff3c00] hover:text-[#ff3c00] transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus"></i> Ajouter une escale
                </button>

                <div class="pt-6 border-t border-slate-100 mt-6">
                    <button onclick="goToEditPricing()"
                        class="w-full py-4 bg-slate-900 text-white rounded-xl font-bold text-lg hover:bg-[#ff3c00] transition-all">
                        Continuer vers les prix
                    </button>
                </div>
            </div>
            <div class="map-container-traj">
                <div id="map-edit-stops" class="h-full w-full"></div>
            </div>
        </section>

        <!-- ========== ÉTAPE 5 : PRIX RETOUR ========== -->
        <section id="view-edit-pricing" class="step-view">
            <div class="sidebar-panel p-8">
                <button onclick="changeView('view-edit-stops')"
                    class="mb-6 text-slate-400 font-bold flex items-center gap-2 hover:text-slate-600">
                    <i class="fa-solid fa-arrow-left"></i> Escales
                </button>

                <div class="mb-2">
                    <span
                        class="inline-block px-3 py-1 rounded-full bg-orange-100 text-[#ff3c00] text-xs font-bold uppercase tracking-wider mb-2">Étape
                        4/4</span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 mb-2">
                    Prix du <span class="text-[#ff3c00]">retour</span>
                </h2>
                <p class="text-slate-500 mb-8 font-medium">Prix par passager pour chaque étape.</p>

                <div id="edit-pricing-container" class="space-y-6 flex-1 overflow-y-auto"></div>

                <div class="pt-8 border-t border-slate-100">
                    <button onclick="submitReturnUpdate()" id="btn-save-return"
                        class="w-full py-4 bg-[#ff3c00] text-white rounded-2xl font-black text-lg shadow-xl shadow-orange-200 hover:shadow-orange-300 hover:-translate-y-1 active:translate-y-0 transition-all duration-300 flex items-center justify-center gap-3">
                        <i class="fa-solid fa-check"></i> Enregistrer les modifications
                    </button>
                </div>
            </div>
            <div class="flex-1 bg-white flex items-center justify-center">
                <div class="max-w-md w-full text-center">
                    <div class="mb-8 p-10 bg-white rounded-[2.5rem] shadow-xl border border-slate-100">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Prix total retour</p>
                        <div id="edit-return-total" class="text-6xl font-black text-slate-900 mb-2">
                            0<span class="text-[#ff3c00] ml-1 text-4xl">€</span>
                        </div>
                        <div class="flex items-center justify-center gap-2 text-emerald-500 font-black text-sm">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            <span>Ajustez selon vos préférences</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>

    <script>
        // ========== DONNÉES INITIALES DEPUIS LE SERVEUR ==========
        const covoiturageId = @json($covoiturage->covoiturage_id);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Itinéraire aller (pour déduire départ/destination du retour)
        const allerItinerary = @json($covoiturage->itineraire ?? []);

        // Données retour existantes
        const existingReturnItinerary = @json($covoiturage->return_itinerary ?? []);
        const existingReturnTripData = @json($covoiturage->return_trip_data ?? null);
        const existingReturnDate = "{{ $covoiturage->return_date ? $covoiturage->return_date->format('Y-m-d') : '' }}";
        const existingReturnTime = "{{ $covoiturage->return_time ?? '' }}";

        // Coordonnées départ/arrivée du retour (inversé de l'aller)
        const returnStartCoords = allerItinerary.length > 0 ?
            allerItinerary[allerItinerary.length - 1].latlng :
            null;
        const returnEndCoords = allerItinerary.length > 0 ?
            allerItinerary[0].latlng :
            null;

        // ========== STATE ==========
        let maps = {};
        let returnRoutes = [];
        let selectedReturnRouteIndex = 0;
        let returnRouteLayer = null;
        let editManualStops = [];
        let editAutoStepMarkers = [];
        let pendingStopData = null;
        let editPricingSteps = [];

        // ========== INIT ==========
        document.addEventListener('DOMContentLoaded', () => {
            initMap('map-recap', 'recap');
            renderRecap();
            setupPopinAutocomplete();
        });

        // ========== MAP ==========
        function initMap(id, key) {
            if (maps[key]) {
                maps[key].invalidateSize();
                return;
            }
            maps[key] = L.map(id, {
                zoomControl: false
            }).setView([46.2276, 2.2137], 6);
            L.control.zoom({
                position: 'bottomright'
            }).addTo(maps[key]);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '©OpenStreetMap'
            }).addTo(maps[key]);
        }

        function changeView(viewId) {
            document.querySelectorAll('.step-view').forEach(v => v.classList.remove('active'));
            document.getElementById(viewId).classList.add('active');
            // Resize maps
            Object.values(maps).forEach(m => {
                if (m) setTimeout(() => m.invalidateSize(), 150);
            });
        }

        // ========== RECAP ==========
        function renderRecap() {
            // Itinéraire
            const container = document.getElementById('recap-itinerary');
            const itinerary = existingReturnItinerary.length > 0 ?
                existingReturnItinerary :
                buildDefaultReturnItinerary();

            container.innerHTML = '';
            itinerary.forEach((stop, i) => {
                let dotColor = 'bg-slate-300';
                if (stop.type === 'start') dotColor = 'bg-emerald-500';
                if (stop.type === 'end') dotColor = 'bg-[#ff3c00]';

                const sourceTag = stop.source === 'manual' ?
                    '<span class="text-[9px] bg-orange-100 text-[#ff3c00] px-1.5 py-0.5 rounded font-bold ml-2">Manuel</span>' :
                    '';

                container.innerHTML += `
                <li class="relative pl-8 flex flex-col ${i < itinerary.length - 1 ? 'pb-3' : ''}">
                    <span class="absolute left-0 top-1 step-dot ${dotColor} border-4 border-white shadow-sm z-10"></span>
                    ${i < itinerary.length - 1 ? '<span class="timeline-line"></span>' : ''}
                    <span class="text-[10px] font-black uppercase text-slate-400">${stop.type}${sourceTag}</span>
                    <span class="font-bold text-slate-800 leading-tight text-sm">${stop.name}</span>
                </li>
            `;
            });

            // Pricing
            const pricingContainer = document.getElementById('recap-pricing');
            const tripData = existingReturnTripData;
            let total = 0;

            if (tripData && tripData.pricing && tripData.pricing.length > 0) {
                pricingContainer.innerHTML = '';
                tripData.pricing.forEach(seg => {
                    const price = seg.price || 0;
                    total += price;
                    pricingContainer.innerHTML += `
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">${seg.from} → ${seg.to}</span>
                        <span class="font-bold text-slate-800">${price}€</span>
                    </div>
                `;
                });
            } else {
                pricingContainer.innerHTML = '<p class="text-sm text-slate-400 italic">Aucun tarif défini</p>';
            }

            document.getElementById('recap-total').textContent = total + '€';

            // Carte
            if (existingReturnTripData?.trajet?.geometry) {
                const geojson = existingReturnTripData.trajet.geometry;
                const layer = L.geoJSON(geojson, {
                    style: {
                        color: '#ff3c00',
                        weight: 5,
                        opacity: 0.8
                    }
                }).addTo(maps.recap);
                maps.recap.fitBounds(layer.getBounds(), {
                    padding: [40, 40]
                });
            } else if (returnStartCoords && returnEndCoords) {
                L.marker(returnStartCoords).addTo(maps.recap);
                L.marker(returnEndCoords).addTo(maps.recap);
                maps.recap.fitBounds(L.latLngBounds([returnStartCoords, returnEndCoords]), {
                    padding: [50, 50]
                });
            }
        }

        function buildDefaultReturnItinerary() {
            if (allerItinerary.length < 2) return [];
            return [{
                    name: allerItinerary[allerItinerary.length - 1].name,
                    type: 'start',
                    latlng: allerItinerary[allerItinerary.length - 1].latlng
                },
                {
                    name: allerItinerary[0].name,
                    type: 'end',
                    latlng: allerItinerary[0].latlng
                }
            ];
        }

        // ========== ÉTAPE 2 : DATE/HEURE ==========
        function goToEditRoute() {
            const dateVal = document.getElementById('edit-return-date').value;
            const timeVal = document.getElementById('edit-return-time').value;

            if (!dateVal || !timeVal) {
                alert("Veuillez renseigner la date et l'heure de retour.");
                return;
            }

            changeView('view-edit-route');
            initMap('map-edit-route', 'editRoute');
            loadEditReturnRoutes();
        }

        // ========== ÉTAPE 3 : ROUTE ==========
        async function loadEditReturnRoutes() {
            if (!returnStartCoords || !returnEndCoords) return;

            const url =
                `https://router.project-osrm.org/route/v1/driving/${returnStartCoords[1]},${returnStartCoords[0]};${returnEndCoords[1]},${returnEndCoords[0]}?overview=full&geometries=geojson&alternatives=true`;

            const res = await fetch(url);
            const data = await res.json();
            returnRoutes = data.routes;

            renderEditRouteList();
            displayEditRouteOnMap(0);
        }

        function renderEditRouteList() {
            const container = document.getElementById('edit-routes-list');
            container.innerHTML = '';

            returnRoutes.forEach((route, i) => {
                const div = document.createElement('div');
                div.className =
                    `route-option p-4 rounded-xl bg-white ${i === selectedReturnRouteIndex ? 'selected' : ''}`;
                div.onclick = () => {
                    selectedReturnRouteIndex = i;
                    renderEditRouteList();
                    displayEditRouteOnMap(i);
                };

                const dist = (route.distance / 1000).toFixed(1);
                const dur = Math.round(route.duration / 60);

                div.innerHTML = `
                <div class="flex justify-between items-start mb-1">
                    <span class="font-black text-slate-800">Option ${i + 1}</span>
                    ${i === 0 ? '<span class="text-[9px] bg-orange-500 text-white px-2 py-0.5 rounded-full uppercase">Rapide</span>' : ''}
                </div>
                <div class="flex gap-3 text-sm font-bold text-slate-500">
                    <span><i class="fa-solid fa-road mr-1"></i> ${dist} km</span>
                    <span><i class="fa-solid fa-clock mr-1"></i> ${dur} min</span>
                </div>
            `;
                container.appendChild(div);
            });
        }

        function displayEditRouteOnMap(index) {
            const map = maps.editRoute;
            if (returnRouteLayer) map.removeLayer(returnRouteLayer);

            returnRouteLayer = L.geoJSON(returnRoutes[index].geometry, {
                style: {
                    color: '#ff3c00',
                    weight: 6,
                    opacity: 0.8
                }
            }).addTo(map);

            map.fitBounds(returnRouteLayer.getBounds(), {
                padding: [40, 40]
            });
        }

        // ========== ÉTAPE 4 : ESCALES ==========
        function goToEditStops() {
            changeView('view-edit-stops');
            initMap('map-edit-stops', 'editStops');

            // Afficher la route sélectionnée sur la carte escales
            setTimeout(() => {
                if (maps.editStops && returnRoutes[selectedReturnRouteIndex]) {
                    L.geoJSON(returnRoutes[selectedReturnRouteIndex].geometry, {
                        style: {
                            color: '#ff3c00',
                            weight: 4,
                            opacity: 0.5
                        }
                    }).addTo(maps.editStops);
                    maps.editStops.fitBounds(
                        L.geoJSON(returnRoutes[selectedReturnRouteIndex].geometry).getBounds(), {
                            padding: [40, 40]
                        }
                    );
                }
            }, 200);

            editManualStops = [];
            editAutoStepMarkers = [];
            renderEditManualStops();
            loadEditIntermediateCities();
        }

        async function loadEditIntermediateCities() {
            const container = document.getElementById('edit-intermediate-cities');
            container.innerHTML =
                '<div class="text-center py-8 text-slate-400"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Recherche...</div>';

            const route = returnRoutes[selectedReturnRouteIndex];
            if (!route) {
                container.innerHTML = '<p class="text-sm text-slate-400 italic text-center">Aucune route</p>';
                return;
            }

            const coords = route.geometry.coordinates;
            const samples = [
                coords[Math.floor(coords.length * 0.25)],
                coords[Math.floor(coords.length * 0.5)],
                coords[Math.floor(coords.length * 0.75)]
            ];

            const cities = [];
            for (let point of samples) {
                try {
                    const res = await fetch(
                        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${point[1]}&lon=${point[0]}`);
                    const data = await res.json();
                    const cityName = data.address.city || data.address.town || data.address.village;
                    if (cityName) cities.push({
                        name: cityName,
                        latlng: [point[1], point[0]]
                    });
                } catch (e) {
                    console.error(e);
                }
            }

            container.innerHTML = '';
            if (cities.length === 0) {
                container.innerHTML =
                    '<p class="text-sm text-slate-400 italic text-center py-4">Aucune ville détectée</p>';
                return;
            }

            cities.forEach((city, i) => {
                const div = document.createElement('div');
                div.className =
                    "flex items-center p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-orange-200 transition-all cursor-pointer";
                div.innerHTML = `
                <input type="checkbox" id="edit-city-${i}" class="w-5 h-5 accent-[#ff3c00] rounded">
                <label for="edit-city-${i}" class="ml-4 flex-1 font-bold text-slate-700 cursor-pointer">${city.name}</label>
                <i class="fa-solid fa-map-pin text-slate-300"></i>
            `;
                div.onclick = (e) => {
                    if (e.target.tagName !== 'INPUT') {
                        const cb = div.querySelector('input');
                        cb.checked = !cb.checked;
                    }
                    toggleEditStepMarker(city, div.querySelector('input').checked);
                };
                container.appendChild(div);
            });
        }

        function toggleEditStepMarker(city, show) {
            const map = maps.editStops;
            if (show) {
                const m = L.marker(city.latlng).addTo(map).bindPopup(city.name);
                city.marker = m;
                editAutoStepMarkers.push(city);
            } else {
                const idx = editAutoStepMarkers.findIndex(s => s.name === city.name);
                if (idx > -1) {
                    map.removeLayer(editAutoStepMarkers[idx].marker);
                    editAutoStepMarkers.splice(idx, 1);
                }
            }
        }

        // ========== POPIN ESCALE MANUELLE ==========
        function setupPopinAutocomplete() {
            const input = document.getElementById('popin-stop-input');
            const resDiv = document.getElementById('popin-stop-results');

            input.addEventListener('input', debounce(async (e) => {
                const query = e.target.value;
                if (query.length < 3) {
                    resDiv.classList.add('hidden');
                    return;
                }

                const url =
                    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`;
                try {
                    const response = await fetch(url);
                    const data = await response.json();
                    resDiv.innerHTML = '';
                    resDiv.classList.remove('hidden');

                    data.forEach(place => {
                        const item = document.createElement('div');
                        item.className = 'autocomplete-item';
                        item.innerText = place.display_name;
                        item.onclick = () => {
                            const coords = [parseFloat(place.lat), parseFloat(place.lon)];
                            const cityName = place.display_name.split(',')[0].trim();
                            pendingStopData = {
                                name: cityName,
                                fullName: place.display_name,
                                latlng: coords
                            };

                            document.getElementById('popin-stop-selected-name').textContent =
                                place.display_name;
                            document.getElementById('popin-stop-selected').classList.remove(
                                'hidden');
                            document.getElementById('popin-stop-confirm').disabled = false;
                            input.value = '';
                            resDiv.classList.add('hidden');
                        };
                        resDiv.appendChild(item);
                    });
                } catch (err) {
                    console.error(err);
                }
            }, 300));
        }

        function openStopPopin() {
            pendingStopData = null;
            document.getElementById('popin-stop-input').value = '';
            document.getElementById('popin-stop-results').classList.add('hidden');
            document.getElementById('popin-stop-selected').classList.add('hidden');
            document.getElementById('popin-stop-confirm').disabled = true;
            document.getElementById('popin-add-return-stop').classList.add('active');
        }

        function closeStopPopin() {
            pendingStopData = null;
            document.getElementById('popin-add-return-stop').classList.remove('active');
        }

        function clearPopinSelection() {
            pendingStopData = null;
            document.getElementById('popin-stop-selected').classList.add('hidden');
            document.getElementById('popin-stop-confirm').disabled = true;
        }

        function confirmManualStop() {
            if (!pendingStopData) return;

            const map = maps.editStops;
            const marker = L.marker(pendingStopData.latlng).addTo(map)
                .bindPopup(`<b>${pendingStopData.name}</b><br><span class="text-xs text-slate-500">Ajout manuel</span>`);

            editManualStops.push({
                name: pendingStopData.name,
                fullName: pendingStopData.fullName,
                latlng: pendingStopData.latlng,
                marker: marker
            });

            renderEditManualStops();
            closeStopPopin();
            pendingStopData = null;
        }

        function renderEditManualStops() {
            const container = document.getElementById('edit-manual-stops-list');
            container.innerHTML = '';

            if (editManualStops.length === 0) {
                container.innerHTML = '<p class="text-sm text-slate-400 italic text-center py-2">Aucune escale ajoutée</p>';
                return;
            }

            editManualStops.forEach((stop, i) => {
                const div = document.createElement('div');
                div.className =
                    "manual-stop-item flex items-center p-4 bg-white rounded-xl border border-slate-100 shadow-sm";
                div.innerHTML = `
                <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center text-[#ff3c00] text-sm mr-3 flex-shrink-0">
                    <i class="fa-solid fa-map-pin"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-slate-800 text-sm truncate">${stop.name}</p>
                    <p class="text-[10px] text-slate-400 uppercase font-black tracking-wider">Ajout manuel</p>
                </div>
                <button onclick="removeEditManualStop(${i})" class="delete-btn ml-2 w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
            `;
                container.appendChild(div);
            });
        }

        function removeEditManualStop(index) {
            const stop = editManualStops[index];
            if (stop.marker && maps.editStops) maps.editStops.removeLayer(stop.marker);
            editManualStops.splice(index, 1);
            renderEditManualStops();
        }

        // ========== ÉTAPE 5 : PRICING ==========
        function goToEditPricing() {
            // Construire l'itinéraire retour final
            const returnStart = allerItinerary[allerItinerary.length - 1];
            const returnEnd = allerItinerary[0];

            const allWaypoints = [
                ...editAutoStepMarkers.map(s => ({
                    name: s.name,
                    type: 'waypoint',
                    source: 'auto',
                    latlng: s.latlng
                })),
                ...editManualStops.map(s => ({
                    name: s.name,
                    type: 'waypoint',
                    source: 'manual',
                    latlng: s.latlng
                }))
            ];

            editPricingSteps = [{
                    name: returnStart.name,
                    type: 'start',
                    latlng: returnStart.latlng
                },
                ...allWaypoints,
                {
                    name: returnEnd.name,
                    type: 'end',
                    latlng: returnEnd.latlng
                }
            ];

            changeView('view-edit-pricing');
            renderEditPricing();
        }

        function renderEditPricing() {
            const container = document.getElementById('edit-pricing-container');
            container.innerHTML = '';

            if (editPricingSteps.length < 2) return;

            // Init pricing array
            const pricingData = [];

            for (let i = 0; i < editPricingSteps.length - 1; i++) {
                const from = editPricingSteps[i].name;
                const to = editPricingSteps[i + 1].name;

                pricingData.push({
                    from,
                    to,
                    price: 20
                });

                const block = document.createElement('div');
                block.className = "p-4 bg-slate-50 rounded-2xl border border-slate-100";
                block.innerHTML = `
                <div class="flex justify-between items-center mb-3">
                    <span class="font-bold text-slate-800">${from} → ${to}</span>
                    <span id="edit-price-label-${i}" class="text-[#ff3c00] font-black">20€</span>
                </div>
                <input type="range" min="0" max="150" value="20"
                    class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#ff3c00]"
                    oninput="updateEditReturnPrice(${i}, this.value)">
            `;
                container.appendChild(block);
            }

            window._editReturnPricing = pricingData;
            updateEditReturnTotal();
        }

        function updateEditReturnPrice(index, value) {
            window._editReturnPricing[index].price = parseInt(value);
            document.getElementById(`edit-price-label-${index}`).innerText = value + '€';
            updateEditReturnTotal();
        }

        function updateEditReturnTotal() {
            const total = window._editReturnPricing.reduce((sum, s) => sum + s.price, 0);
            document.getElementById('edit-return-total').innerHTML =
                `${total}<span class="text-[#ff3c00] ml-1 text-4xl">€</span>`;
        }

        // ========== SUBMIT ==========
        async function submitReturnUpdate() {
            const btn = document.getElementById('btn-save-return');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Enregistrement...';

            try {
                const returnDate = document.getElementById('edit-return-date').value;
                const returnTime = document.getElementById('edit-return-time').value;
                const route = returnRoutes[selectedReturnRouteIndex];
                const pricing = window._editReturnPricing || [];
                const total = pricing.reduce((sum, s) => sum + s.price, 0);

                const returnTripData = {
                    trajet: {
                        selectedRouteIndex: selectedReturnRouteIndex,
                        distance: route.distance,
                        duration: route.duration,
                        geometry: route.geometry
                    },
                    pricing: pricing,
                    total: total
                };

                const payload = {
                    return_date: returnDate,
                    return_time: returnTime,
                    return_itinerary: editPricingSteps,
                    return_trip_data: returnTripData
                };

                const res = await fetch(`/covoiturage/${covoiturageId}/update-retour`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                if (!res.ok) {
                    const text = await res.text();
                    console.error('Erreur serveur:', text);
                    throw new Error('Réponse invalide');
                }

                const data = await res.json();

                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Retour modifié',
                            text: 'Les modifications ont été enregistrées.',
                            confirmButtonColor: '#ff3c00',
                            timer: 2000,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = `/trajet/${covoiturageId}`;
                        });
                    } else {
                        alert('Modifications enregistrées !');
                        window.location.href = `/trajet/${covoiturageId}`;
                    }
                } else {
                    alert(data.message || 'Erreur lors de la mise à jour.');
                }

            } catch (err) {
                console.error(err);
                alert('Erreur réseau ou données invalides.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Enregistrer les modifications';
            }
        }

        // ========== UTILS ==========
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
    </script>
@endsection
