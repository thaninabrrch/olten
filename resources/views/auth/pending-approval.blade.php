@extends('layouts.main')

@section('title', 'Compte en attente de validation - Olten.fr')

@php
    $user = auth()->user();
    $emailVerified = $user && $user->hasVerifiedEmail();
@endphp

@section('content')
<style>
    /* ============ Page pleine largeur ============ */
    .pend-hero {
        position: relative;
        overflow: hidden;
        min-height: 78vh;
        display: flex;
        align-items: center;
        padding: 70px 0 90px;
        background:
            radial-gradient(880px 520px at 90% 10%, rgba(255, 176, 32, .16), transparent 62%),
            radial-gradient(700px 480px at 4% 90%, rgba(255, 60, 0, .09), transparent 60%),
            linear-gradient(180deg, #fffbf4 0%, var(--color-bg-body, #f8f9fa) 100%);
    }

    .pend-hero::before,
    .pend-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }

    .pend-hero::before {
        width: 440px;
        height: 440px;
        top: -180px;
        right: -130px;
        background: rgba(255, 176, 32, .10);
    }

    .pend-hero::after {
        width: 320px;
        height: 320px;
        bottom: -150px;
        left: -110px;
        background: rgba(255, 60, 0, .06);
    }

    .pend-hero .container {
        position: relative;
        z-index: 1;
    }

    /* ============ Colonne texte ============ */
    .pend-content {
        max-width: 580px;
    }

    .pend-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 7px 15px;
        border-radius: 999px;
        background: rgba(255, 176, 32, .18);
        color: #a35f00;
        font-size: .76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        margin-bottom: 22px;
    }

    .pend-eyebrow .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #f5a524;
        animation: pend-pulse 1.6s ease-in-out infinite;
    }

    @keyframes pend-pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%      { opacity: .3; transform: scale(.75); }
    }

    .pend-title {
        font-size: clamp(1.8rem, 3.5vw, 2.6rem);
        font-weight: 800;
        line-height: 1.16;
        letter-spacing: -.02em;
        color: var(--color-black, #111);
        margin-bottom: 18px;
    }

    .pend-title em {
        font-style: normal;
        color: var(--color-primary, #ff3c00);
    }

    .pend-lead {
        font-size: 1.03rem;
        line-height: 1.7;
        color: var(--color-grey-dark, #555);
        margin-bottom: 30px;
    }

    /* ---- Suivi d'avancement ---- */
    .pend-steps {
        list-style: none;
        margin: 0 0 28px;
        padding: 0;
    }

    .pend-steps li {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        position: relative;
        padding-bottom: 22px;
    }

    .pend-steps li:last-child { padding-bottom: 0; }

    .pend-steps li::before {
        content: "";
        position: absolute;
        left: 14px;
        top: 30px;
        bottom: 0;
        width: 2px;
        background: #e6e8eb;
    }

    .pend-steps li:last-child::before { display: none; }

    .pend-steps .step-icon {
        flex: 0 0 30px;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .82rem;
        background: #f1f3f5;
        color: #9aa0a6;
        box-shadow: 0 0 0 4px rgba(255, 255, 255, .9);
        z-index: 1;
    }

    .pend-steps li.is-done .step-icon {
        background: #2f9e5f;
        color: #fff;
    }

    .pend-steps li.is-current .step-icon {
        background: #f5a524;
        color: #fff;
        box-shadow: 0 0 0 5px rgba(245, 165, 36, .2);
    }

    .pend-steps .step-text { padding-top: 3px; }

    .pend-steps .step-text strong {
        display: block;
        color: var(--color-black, #111);
        font-size: .98rem;
        margin-bottom: 3px;
    }

    .pend-steps .step-text span {
        color: var(--color-grey-dark, #555);
        font-size: .89rem;
        line-height: 1.55;
    }

    .pend-steps li.is-todo .step-text strong { color: #9aa0a6; }

    /* ---- Encart info ---- */
    .pend-note {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: #fff;
        border: 1px solid var(--color-divider, #e9ecef);
        border-radius: 16px;
        padding: 15px 18px;
        font-size: .9rem;
        line-height: 1.6;
        color: var(--color-grey-dark, #555);
        box-shadow: 0 6px 18px rgba(17, 17, 17, .04);
        margin-bottom: 30px;
        max-width: 540px;
    }

    .pend-note i {
        color: var(--color-primary, #ff3c00);
        font-size: 1.05rem;
        margin-top: 2px;
    }

    /* ---- Actions ---- */
    .pend-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 13px;
        align-items: center;
    }

    .btn-pend {
        background: var(--color-primary, #ff3c00);
        color: #fff;
        border: 0;
        border-radius: 14px;
        padding: 14px 26px;
        font-weight: 600;
        font-size: .97rem;
        transition: background .2s ease, transform .15s ease, box-shadow .2s ease;
        box-shadow: 0 10px 24px rgba(255, 60, 0, .24);
    }

    .btn-pend:hover,
    .btn-pend:focus {
        background: var(--color-primary-dark, #e13800);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(255, 60, 0, .3);
    }

    .btn-pend-ghost {
        background: transparent;
        color: var(--color-grey-dark, #555);
        border: 1px solid var(--color-divider, #ddd);
        border-radius: 14px;
        padding: 13px 24px;
        font-weight: 500;
        font-size: .95rem;
        transition: background .2s ease, border-color .2s ease, color .2s ease;
    }

    .btn-pend-ghost:hover {
        background: #fff;
        border-color: #cfd4da;
        color: var(--color-black, #111);
    }

    .pend-help {
        margin: 26px 0 0;
        font-size: .87rem;
        color: #8a9099;
    }

    .pend-help a {
        color: var(--color-primary, #ff3c00);
        font-weight: 600;
        text-decoration: none;
    }

    .pend-help a:hover { text-decoration: underline; }

    /* ============ Illustration ============ */
    .pend-illu {
        position: relative;
        display: flex;
        justify-content: center;
    }

    .pend-illu svg {
        width: 100%;
        max-width: 520px;
        height: auto;
    }

    .pend-float   { animation: pend-float 5.4s ease-in-out infinite; transform-box: view-box; transform-origin: center; }
    .pend-float-2 { animation: pend-float 6.8s ease-in-out infinite .8s; transform-box: view-box; transform-origin: center; }
    .pend-spin    { animation: pend-spin 6s linear infinite; transform-box: view-box; transform-origin: 388px 322px; }
    .pend-twinkle { animation: pend-twinkle 3.2s ease-in-out infinite; }

    @keyframes pend-float {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-12px); }
    }

    @keyframes pend-spin {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }

    @keyframes pend-twinkle {
        0%, 100% { opacity: .25; }
        50%      { opacity: 1; }
    }

    @media (prefers-reduced-motion: reduce) {
        .pend-float, .pend-float-2, .pend-spin, .pend-twinkle,
        .pend-eyebrow .dot { animation: none; }
    }

    @media (max-width: 991.98px) {
        .pend-hero { min-height: auto; padding: 48px 0 64px; text-align: center; }
        .pend-content { max-width: 640px; margin: 0 auto; }
        .pend-steps li { text-align: left; }
        .pend-note { margin-inline: auto; text-align: left; }
        .pend-actions { justify-content: center; }
        .pend-illu { margin-bottom: 34px; }
        .pend-illu svg { max-width: 380px; }
    }

    @media (max-width: 575.98px) {
        .pend-actions { flex-direction: column; align-items: stretch; }
        .pend-actions .btn-pend,
        .pend-actions .btn-pend-ghost,
        .pend-actions form { width: 100%; }
    }
</style>

<section class="pend-hero">
    <div class="container">
        <div class="row align-items-center g-5 flex-lg-row flex-column-reverse">

            {{-- ---------- Colonne texte ---------- --}}
            <div class="col-lg-6">
                <div class="pend-content">

                    <span class="pend-eyebrow">
                        <span class="dot"></span>
                        Dossier en cours de traitement
                    </span>

                    <h1 class="pend-title">
                        @if ($emailVerified)
                            Votre compte est en <em>attente de validation</em>
                        @else
                            Confirmez d'abord votre <em>adresse e-mail</em>
                        @endif
                    </h1>

                    <p class="pend-lead">
                        @if ($emailVerified)
                            Votre adresse e-mail a bien été vérifiée. Notre équipe examine
                            maintenant votre dossier avant d'activer votre accès à la plateforme.
                        @else
                            Un lien de vérification vous a été envoyé par e-mail. Confirmez
                            votre adresse pour que votre dossier puisse être étudié par notre équipe.
                        @endif
                    </p>

                    <ul class="pend-steps">
                        <li class="is-done">
                            <span class="step-icon"><i class="bi bi-check-lg"></i></span>
                            <span class="step-text">
                                <strong>Inscription enregistrée</strong>
                                <span>Votre compte a bien été créé sur Olten.</span>
                            </span>
                        </li>

                        <li class="{{ $emailVerified ? 'is-done' : 'is-current' }}">
                            <span class="step-icon">
                                <i class="bi {{ $emailVerified ? 'bi-check-lg' : 'bi-envelope' }}"></i>
                            </span>
                            <span class="step-text">
                                <strong>Adresse e-mail vérifiée</strong>
                                <span>
                                    @if ($emailVerified)
                                        Confirmation reçue, cette étape est terminée.
                                    @else
                                        En attente : cliquez sur le lien reçu dans votre boîte mail.
                                    @endif
                                </span>
                            </span>
                        </li>

                        <li class="{{ $emailVerified ? 'is-current' : 'is-todo' }}">
                            <span class="step-icon"><i class="bi bi-shield-check"></i></span>
                            <span class="step-text">
                                <strong>Validation par l'administrateur</strong>
                                <span>Vérification de vos informations et de vos documents.</span>
                            </span>
                        </li>

                        <li class="is-todo">
                            <span class="step-icon"><i class="bi bi-unlock"></i></span>
                            <span class="step-text">
                                <strong>Accès complet à la plateforme</strong>
                                <span>Vous serez prévenu par e-mail dès l'activation de votre compte.</span>
                            </span>
                        </li>
                    </ul>

                    <div class="pend-note">
                        <i class="bi bi-info-circle-fill"></i>
                        <span>
                            Aucune action n'est requise de votre part pour le moment. Vous recevrez
                            un e-mail à l'adresse
                            @auth <strong>{{ $user->email }}</strong> @else votre adresse enregistrée @endauth
                            dès que votre compte sera validé.
                        </span>
                    </div>

                    <div class="pend-actions">
                        <a href="{{ url('/') }}" class="btn btn-pend">
                            <i class="bi bi-house-door me-1"></i>
                            Retour à l'accueil
                        </a>

                        @if (! $emailVerified)
                            <a href="{{ route('account.verify') }}" class="btn btn-pend-ghost">
                                <i class="bi bi-envelope-check me-1"></i>
                                Vérifier mon e-mail
                            </a>
                        @endif

                        @auth
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-pend-ghost w-100">
                                    <i class="bi bi-box-arrow-right me-1"></i>
                                    Se déconnecter
                                </button>
                            </form>
                        @endauth
                    </div>

                    <p class="pend-help">
                        Une question sur votre dossier ?
                        <a href="{{ route('contact') }}">Contactez notre équipe</a>
                    </p>

                </div>
            </div>

            {{-- ---------- Illustration ---------- --}}
            <div class="col-lg-6">
                <div class="pend-illu">
                    <svg viewBox="0 0 520 440" fill="none" xmlns="http://www.w3.org/2000/svg"
                         role="img" aria-label="Illustration : dossier en cours de validation par l'administrateur">

                        <!-- fonds décoratifs -->
                        <circle cx="262" cy="216" r="176" fill="#ffb020" fill-opacity="0.10"/>
                        <circle cx="262" cy="216" r="132" fill="#ffb020" fill-opacity="0.08"/>

                        <!-- étincelles -->
                        <g class="pend-twinkle" fill="#ffb020">
                            <circle cx="74" cy="126" r="5"/>
                            <circle cx="456" cy="132" r="6"/>
                        </g>
                        <g class="pend-twinkle" fill="#ff3c00" style="animation-delay:1.2s">
                            <circle cx="66" cy="320" r="4"/>
                            <circle cx="446" cy="386" r="5"/>
                        </g>

                        <!-- badge bouclier en arrière-plan -->
                        <g class="pend-float-2">
                            <path d="M138 96 L182 78 L226 96 V140 C226 168 206 188 182 198
                                     C158 188 138 168 138 140 Z"
                                  fill="#ffffff" stroke="#f0e0c8" stroke-width="2"/>
                            <path d="M168 138 l10 11 l19 -22" stroke="#2f9e5f" stroke-width="6"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </g>

                        <!-- dossier / fiche de validation -->
                        <g class="pend-float">
                            <rect x="132" y="126" width="268" height="252" rx="24" fill="#ffffff"
                                  stroke="#eceff2" stroke-width="2"/>
                            <rect x="132" y="126" width="268" height="58" rx="24" fill="#fff4e2"/>
                            <rect x="132" y="164" width="268" height="20" fill="#fff4e2"/>
                            <rect x="160" y="145" width="118" height="12" rx="6" fill="#e2a13a" fill-opacity="0.7"/>
                            <circle cx="366" cy="151" r="7" fill="#ff3c00" fill-opacity="0.35"/>

                            <!-- ligne validée -->
                            <circle cx="172" cy="222" r="14" fill="#2f9e5f"/>
                            <path d="M166 222 l5 5 l9 -10" stroke="#ffffff" stroke-width="3.4"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                            <rect x="198" y="215" width="150" height="12" rx="6" fill="#e6e8eb"/>

                            <!-- ligne validée -->
                            <circle cx="172" cy="266" r="14" fill="#2f9e5f"/>
                            <path d="M166 266 l5 5 l9 -10" stroke="#ffffff" stroke-width="3.4"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                            <rect x="198" y="259" width="122" height="12" rx="6" fill="#e6e8eb"/>

                            <!-- ligne en cours -->
                            <circle cx="172" cy="310" r="14" fill="#f5a524"/>
                            <circle cx="172" cy="310" r="20" fill="#f5a524" fill-opacity="0.18"/>
                            <rect x="169.5" y="303" width="5" height="8" rx="2.5" fill="#ffffff"/>
                            <rect x="169.5" y="316" width="5" height="4" rx="2" fill="#ffffff"/>
                            <rect x="198" y="303" width="140" height="12" rx="6" fill="#ffe2b8"/>

                            <!-- ligne à venir -->
                            <circle cx="172" cy="352" r="14" fill="#f1f3f5"/>
                            <rect x="198" y="345" width="96" height="12" rx="6" fill="#f1f3f5"/>
                        </g>

                        <!-- sablier / horloge -->
                        <g class="pend-float">
                            <circle cx="388" cy="322" r="50" fill="#ffffff"/>
                            <circle cx="388" cy="322" r="42" fill="#f5a524"/>
                            <circle cx="388" cy="322" r="33" fill="#ffffff" fill-opacity="0.22"/>
                            <g class="pend-spin">
                                <rect x="385" y="296" width="6" height="28" rx="3" fill="#ffffff"/>
                            </g>
                            <rect x="386" y="319" width="24" height="6" rx="3" fill="#ffffff"/>
                            <circle cx="388" cy="322" r="5" fill="#ffffff"/>
                        </g>

                        <!-- socle -->
                        <ellipse cx="260" cy="404" rx="150" ry="14" fill="#111111" fill-opacity="0.05"/>
                    </svg>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection
