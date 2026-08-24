@extends('layouts.connected')

@section('title', 'Carte VTC en attente de validation | ' . config('app.name'))

@php
    $status = $document->status ?? null;   // null = aucune carte transmise
    $rejected = $status === 'rejected';
    $pending  = $status === 'pending';
@endphp

@section('content')
<style>
    .vtcw {
        --vtcw-accent: {{ $rejected ? '#e5484d' : '#f5a524' }};
        --vtcw-accent-soft: {{ $rejected ? 'rgba(229, 72, 77, .12)' : 'rgba(245, 165, 36, .16)' }};
        position: relative;
        overflow: hidden;
        min-height: calc(100vh - 160px);
        display: flex;
        align-items: center;
        padding: 48px 32px 64px;
        background:
            radial-gradient(760px 460px at 88% 6%, var(--vtcw-accent-soft), transparent 62%),
            radial-gradient(620px 420px at 4% 94%, rgba(255, 60, 0, .07), transparent 60%),
            linear-gradient(180deg, #fffdf9 0%, var(--color-bg-body, #f8f9fa) 100%);
    }

    .vtcw-inner {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 1120px;
        margin: 0 auto;
    }

    .vtcw-content { max-width: 560px; }

    .vtcw-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 7px 15px;
        border-radius: 999px;
        background: var(--vtcw-accent-soft);
        color: {{ $rejected ? '#a3282c' : '#a35f00' }};
        font-size: .74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        margin-bottom: 22px;
    }

    .vtcw-eyebrow .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--vtcw-accent);
        animation: vtcw-pulse 1.6s ease-in-out infinite;
    }

    @keyframes vtcw-pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%      { opacity: .3; transform: scale(.75); }
    }

    .vtcw-title {
        font-size: clamp(1.75rem, 3.2vw, 2.45rem);
        font-weight: 800;
        line-height: 1.16;
        letter-spacing: -.02em;
        color: #111;
        margin-bottom: 16px;
    }

    .vtcw-title em {
        font-style: normal;
        color: var(--color-primary, #ff3c00);
    }

    .vtcw-lead {
        font-size: 1.01rem;
        line-height: 1.7;
        color: #555;
        margin-bottom: 26px;
    }

    /* ---- Carte document ---- */
    .vtcw-doc {
        display: flex;
        align-items: center;
        gap: 15px;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 16px;
        padding: 16px 18px;
        box-shadow: 0 8px 22px rgba(17, 17, 17, .05);
        margin-bottom: 24px;
        max-width: 520px;
    }

    .vtcw-doc-icon {
        flex: 0 0 44px;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background: var(--vtcw-accent-soft);
        color: var(--vtcw-accent);
    }

    .vtcw-doc-body { min-width: 0; flex: 1; }

    .vtcw-doc-body strong {
        display: block;
        font-size: .96rem;
        color: #111;
        margin-bottom: 2px;
    }

    .vtcw-doc-body span {
        font-size: .85rem;
        color: #8a9099;
    }

    .vtcw-chip {
        flex: 0 0 auto;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: .74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        background: var(--vtcw-accent-soft);
        color: {{ $rejected ? '#a3282c' : '#a35f00' }};
    }

    .vtcw-chip.is-none {
        background: #f1f3f5;
        color: #6b7280;
    }

    /* ---- Motif de refus ---- */
    .vtcw-reason {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: #fdecec;
        border: 1px solid #f5c2c3;
        border-radius: 14px;
        padding: 14px 17px;
        font-size: .91rem;
        line-height: 1.6;
        color: #8f2226;
        margin-bottom: 26px;
        max-width: 520px;
    }

    .vtcw-reason i { margin-top: 2px; }

    /* ---- Étapes ---- */
    .vtcw-steps {
        list-style: none;
        margin: 0 0 30px;
        padding: 0;
    }

    .vtcw-steps li {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        position: relative;
        padding-bottom: 20px;
    }

    .vtcw-steps li:last-child { padding-bottom: 0; }

    .vtcw-steps li::before {
        content: "";
        position: absolute;
        left: 14px;
        top: 30px;
        bottom: 0;
        width: 2px;
        background: #e6e8eb;
    }

    .vtcw-steps li:last-child::before { display: none; }

    .vtcw-steps .step-icon {
        flex: 0 0 30px;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem;
        background: #f1f3f5;
        color: #9aa0a6;
        box-shadow: 0 0 0 4px rgba(255, 255, 255, .9);
        z-index: 1;
    }

    .vtcw-steps li.is-done .step-icon { background: #2f9e5f; color: #fff; }

    .vtcw-steps li.is-current .step-icon {
        background: var(--vtcw-accent);
        color: #fff;
        box-shadow: 0 0 0 5px var(--vtcw-accent-soft);
    }

    .vtcw-steps .step-text strong {
        display: block;
        font-size: .96rem;
        color: #111;
        margin-bottom: 2px;
    }

    .vtcw-steps .step-text span {
        font-size: .87rem;
        color: #6b7280;
        line-height: 1.55;
    }

    .vtcw-steps li.is-todo .step-text strong { color: #9aa0a6; }

    /* ---- Actions ---- */
    .vtcw-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }

    .btn-vtcw {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--color-primary, #ff3c00);
        color: #fff;
        border: 0;
        border-radius: 13px;
        padding: 13px 24px;
        font-weight: 600;
        font-size: .95rem;
        text-decoration: none;
        transition: background .2s ease, transform .15s ease, box-shadow .2s ease;
        box-shadow: 0 10px 24px rgba(255, 60, 0, .22);
    }

    .btn-vtcw:hover {
        background: var(--color-primary-hover, #e13800);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(255, 60, 0, .28);
    }

    .btn-vtcw-ghost {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        color: #555;
        border: 1px solid #e0e3e7;
        border-radius: 13px;
        padding: 12px 22px;
        font-weight: 500;
        font-size: .93rem;
        text-decoration: none;
        transition: background .2s ease, border-color .2s ease, color .2s ease;
    }

    .btn-vtcw-ghost:hover {
        background: #f6f7f9;
        border-color: #cfd4da;
        color: #111;
    }

    .vtcw-help {
        margin: 24px 0 0;
        font-size: .85rem;
        color: #8a9099;
    }

    .vtcw-help a {
        color: var(--color-primary, #ff3c00);
        font-weight: 600;
        text-decoration: none;
    }

    .vtcw-help a:hover { text-decoration: underline; }

    /* ---- Illustration ---- */
    .vtcw-illu {
        display: flex;
        justify-content: center;
    }

    .vtcw-illu svg {
        width: 100%;
        max-width: 460px;
        height: auto;
    }

    .vtcw-float { animation: vtcw-float 5.4s ease-in-out infinite; transform-box: view-box; transform-origin: center; }
    .vtcw-float-2 { animation: vtcw-float 6.6s ease-in-out infinite .8s; transform-box: view-box; transform-origin: center; }
    .vtcw-spin { animation: vtcw-spin 6s linear infinite; transform-box: view-box; transform-origin: 372px 316px; }
    .vtcw-twinkle { animation: vtcw-twinkle 3.2s ease-in-out infinite; }

    @keyframes vtcw-float {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-12px); }
    }

    @keyframes vtcw-spin {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }

    @keyframes vtcw-twinkle {
        0%, 100% { opacity: .25; }
        50%      { opacity: 1; }
    }

    @media (prefers-reduced-motion: reduce) {
        .vtcw-float, .vtcw-float-2, .vtcw-spin, .vtcw-twinkle, .vtcw-eyebrow .dot { animation: none; }
    }

    @media (max-width: 991.98px) {
        .vtcw { min-height: auto; padding: 34px 18px 52px; text-align: center; }
        .vtcw-content { max-width: 640px; margin: 0 auto; }
        .vtcw-steps li, .vtcw-doc, .vtcw-reason { text-align: left; }
        .vtcw-doc, .vtcw-reason { margin-inline: auto; }
        .vtcw-actions { justify-content: center; }
        .vtcw-illu { margin-bottom: 30px; }
        .vtcw-illu svg { max-width: 330px; }
    }

    @media (max-width: 575.98px) {
        .vtcw-actions { flex-direction: column; align-items: stretch; }
        .vtcw-actions .btn-vtcw,
        .vtcw-actions .btn-vtcw-ghost { justify-content: center; width: 100%; }
    }
</style>

<div class="vtcw">
    <div class="vtcw-inner">
        <div class="row align-items-center g-5 flex-lg-row flex-column-reverse">

            {{-- ---------- Texte ---------- --}}
            <div class="col-lg-6">
                <div class="vtcw-content">

                    <span class="vtcw-eyebrow">
                        <span class="dot"></span>
                        @if ($rejected)
                            Carte refusée
                        @elseif ($pending)
                            Vérification en cours
                        @else
                            Document manquant
                        @endif
                    </span>

                    <h1 class="vtcw-title">
                        @if ($rejected)
                            Votre carte VTC a été <em>refusée</em>
                        @elseif ($pending)
                            Votre carte VTC est en <em>cours de validation</em>
                        @else
                            Transmettez votre <em>carte VTC</em> pour publier
                        @endif
                    </h1>

                    <p class="vtcw-lead">
                        @if ($rejected)
                            Le document transmis n'a pas pu être validé par notre équipe.
                            Corrigez-le puis renvoyez-le : la publication de trajets sera
                            débloquée dès sa validation.
                        @elseif ($pending)
                            La publication de trajets est réservée aux conducteurs dont la
                            carte professionnelle a été validée par notre équipe. Votre
                            document a bien été reçu et sera examiné sous peu.
                        @else
                            La publication de trajets est réservée aux conducteurs disposant
                            d'une carte professionnelle VTC validée. Transmettez la vôtre pour
                            démarrer la vérification.
                        @endif
                    </p>

                    {{-- État du document --}}
                    <div class="vtcw-doc">
                        <span class="vtcw-doc-icon">
                            <i class="fa-solid {{ $rejected ? 'fa-triangle-exclamation' : ($pending ? 'fa-hourglass-half' : 'fa-id-card') }}"></i>
                        </span>
                        <span class="vtcw-doc-body">
                            <strong>Carte professionnelle VTC</strong>
                            <span>
                                @if ($document?->identifier)
                                    Référence {{ $document->identifier }} ·
                                @endif
                                @if ($document)
                                    Transmise le {{ $document->updated_at?->format('d/m/Y à H\hi') }}
                                @else
                                    Aucun document transmis pour le moment
                                @endif
                            </span>
                        </span>
                        <span class="vtcw-chip {{ $document ? '' : 'is-none' }}">
                            {{ $rejected ? 'Refusée' : ($pending ? 'En attente' : 'Manquante') }}
                        </span>
                    </div>

                    @if ($rejected && $document?->rejection_reason)
                        <div class="vtcw-reason">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span><strong>Motif du refus :</strong> {{ $document->rejection_reason }}</span>
                        </div>
                    @endif

                    {{-- Étapes --}}
                    <ul class="vtcw-steps">
                        <li class="{{ $document ? 'is-done' : 'is-current' }}">
                            <span class="step-icon">
                                <i class="fa-solid {{ $document ? 'fa-check' : 'fa-arrow-up-from-bracket' }}"></i>
                            </span>
                            <span class="step-text">
                                <strong>Envoi de la carte professionnelle</strong>
                                <span>{{ $document ? 'Document bien reçu par nos services.' : 'Depuis votre espace « Carte VTC ».' }}</span>
                            </span>
                        </li>

                        <li class="{{ $document ? 'is-current' : 'is-todo' }}">
                            <span class="step-icon"><i class="fa-solid fa-shield-halved"></i></span>
                            <span class="step-text">
                                <strong>Vérification par l'administrateur</strong>
                                <span>
                                    @if ($rejected)
                                        Document refusé : un nouvel envoi relancera la vérification.
                                    @else
                                        Contrôle de la validité et de la lisibilité du document.
                                    @endif
                                </span>
                            </span>
                        </li>

                        <li class="is-todo">
                            <span class="step-icon"><i class="fa-solid fa-route"></i></span>
                            <span class="step-text">
                                <strong>Publication de trajets débloquée</strong>
                                <span>Vous pourrez créer et publier vos trajets de covoiturage.</span>
                            </span>
                        </li>
                    </ul>

                    {{-- Actions --}}
                    <div class="vtcw-actions">
                        <a href="{{ route('livreur.carte.vtc') }}" class="btn-vtcw">
                            <i class="fa-solid {{ $document ? 'fa-id-card' : 'fa-arrow-up-from-bracket' }}"></i>
                            {{ $rejected ? 'Renvoyer ma carte VTC' : ($document ? 'Voir mes documents' : 'Transmettre ma carte VTC') }}
                        </a>

                        <a href="{{ route('covoiturage.index') }}" class="btn-vtcw-ghost">
                            <i class="fa-solid fa-list"></i>
                            Mes trajets
                        </a>
                    </div>

                    <p class="vtcw-help">
                        Une question sur votre dossier ?
                        <a href="{{ route('contact') }}">Contactez notre équipe</a>
                    </p>

                </div>
            </div>

            {{-- ---------- Illustration ---------- --}}
            <div class="col-lg-6">
                <div class="vtcw-illu">
                    <svg viewBox="0 0 480 420" fill="none" xmlns="http://www.w3.org/2000/svg"
                         role="img" aria-label="Illustration : carte professionnelle VTC en cours de vérification">

                        <circle cx="240" cy="206" r="168" fill="var(--vtcw-accent)" fill-opacity="0.10"/>
                        <circle cx="240" cy="206" r="126" fill="var(--vtcw-accent)" fill-opacity="0.08"/>

                        <g class="vtcw-twinkle" fill="var(--vtcw-accent)">
                            <circle cx="62" cy="120" r="5"/>
                            <circle cx="424" cy="128" r="6"/>
                        </g>
                        <g class="vtcw-twinkle" fill="#ff3c00" style="animation-delay:1.2s">
                            <circle cx="56" cy="308" r="4"/>
                            <circle cx="416" cy="360" r="5"/>
                        </g>

                        <!-- badge conformité en arrière-plan -->
                        <g class="vtcw-float-2">
                            <path d="M120 86 L162 68 L204 86 V128 C204 155 185 174 162 184
                                     C139 174 120 155 120 128 Z"
                                  fill="#ffffff" stroke="#efe3d2" stroke-width="2"/>
                            <path d="M149 126 l10 11 l18 -21" stroke="#2f9e5f" stroke-width="6"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </g>

                        <!-- carte professionnelle -->
                        <g class="vtcw-float">
                            <rect x="96" y="132" width="288" height="182" rx="20" fill="#ffffff"
                                  stroke="#eceff2" stroke-width="2"/>
                            <rect x="96" y="132" width="288" height="52" rx="20" fill="var(--vtcw-accent)" fill-opacity="0.16"/>
                            <rect x="96" y="164" width="288" height="20" fill="var(--vtcw-accent)" fill-opacity="0.16"/>
                            <rect x="122" y="150" width="104" height="12" rx="6" fill="var(--vtcw-accent)" fill-opacity="0.75"/>

                            <!-- photo -->
                            <rect x="122" y="206" width="74" height="86" rx="12" fill="#eef0f3"/>
                            <circle cx="159" cy="238" r="17" fill="#c9ced6"/>
                            <path d="M132 288 C136 266 182 266 186 288 Z" fill="#c9ced6"/>

                            <!-- lignes -->
                            <rect x="212" y="212" width="142" height="12" rx="6" fill="#e6e8eb"/>
                            <rect x="212" y="238" width="112" height="12" rx="6" fill="#e6e8eb"/>
                            <rect x="212" y="264" width="86" height="12" rx="6" fill="var(--vtcw-accent)" fill-opacity="0.35"/>
                        </g>

                        <!-- horloge / statut -->
                        <g class="vtcw-float">
                            <circle cx="372" cy="316" r="48" fill="#ffffff"/>
                            <circle cx="372" cy="316" r="40" fill="var(--vtcw-accent)"/>
                            <circle cx="372" cy="316" r="31" fill="#ffffff" fill-opacity="0.22"/>
                            @if ($rejected)
                                <path d="M358 302 l28 28 M386 302 l-28 28" stroke="#ffffff" stroke-width="7"
                                      stroke-linecap="round"/>
                            @else
                                <g class="vtcw-spin">
                                    <rect x="369" y="292" width="6" height="26" rx="3" fill="#ffffff"/>
                                </g>
                                <rect x="370" y="313" width="23" height="6" rx="3" fill="#ffffff"/>
                                <circle cx="372" cy="316" r="5" fill="#ffffff"/>
                            @endif
                        </g>

                        <ellipse cx="240" cy="392" rx="140" ry="13" fill="#111111" fill-opacity="0.05"/>
                    </svg>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
