@extends('layouts.main')

@section('title', 'Vérification de votre adresse e-mail - Olten.fr')

@section('content')
<style>
    /* ============ Page pleine largeur ============ */
    .vmail-hero {
        position: relative;
        overflow: hidden;
        min-height: 78vh;
        display: flex;
        align-items: center;
        padding: 70px 0 90px;
        background:
            radial-gradient(900px 520px at 88% 8%, rgba(255, 60, 0, .10), transparent 62%),
            radial-gradient(700px 500px at 5% 92%, rgba(255, 176, 32, .12), transparent 60%),
            linear-gradient(180deg, #fffaf7 0%, var(--color-bg-body, #f8f9fa) 100%);
    }

    .vmail-hero::before,
    .vmail-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }

    .vmail-hero::before {
        width: 460px;
        height: 460px;
        top: -190px;
        right: -140px;
        background: rgba(255, 60, 0, .06);
    }

    .vmail-hero::after {
        width: 320px;
        height: 320px;
        bottom: -150px;
        left: -110px;
        background: rgba(255, 176, 32, .10);
    }

    .vmail-hero .container {
        position: relative;
        z-index: 1;
    }

    /* ============ Colonne texte ============ */
    .vmail-content {
        max-width: 560px;
    }

    .vmail-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 15px;
        border-radius: 999px;
        background: rgba(255, 60, 0, .10);
        color: var(--color-primary-dark, #e13800);
        font-size: .76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        margin-bottom: 22px;
    }

    .vmail-title {
        font-size: clamp(1.85rem, 3.6vw, 2.7rem);
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -.02em;
        color: var(--color-black, #111);
        margin-bottom: 18px;
    }

    .vmail-title em {
        font-style: normal;
        color: var(--color-primary, #ff3c00);
    }

    .vmail-lead {
        font-size: 1.03rem;
        line-height: 1.7;
        color: var(--color-grey-dark, #555);
        margin-bottom: 22px;
    }

    .vmail-email {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 11px 18px;
        border-radius: 14px;
        background: #fff;
        border: 1px solid var(--color-divider, #e9ecef);
        box-shadow: 0 6px 18px rgba(17, 17, 17, .05);
        font-weight: 600;
        color: var(--color-black, #111);
        word-break: break-all;
        margin-bottom: 30px;
    }

    .vmail-email i {
        color: var(--color-primary, #ff3c00);
        font-size: 1.05rem;
    }

    /* ---- Étapes ---- */
    .vmail-steps {
        list-style: none;
        margin: 0 0 32px;
        padding: 0;
        display: grid;
        gap: 14px;
    }

    .vmail-steps li {
        display: flex;
        align-items: flex-start;
        gap: 13px;
        font-size: .95rem;
        line-height: 1.55;
        color: var(--color-grey-dark, #555);
    }

    .vmail-steps .num {
        flex: 0 0 27px;
        width: 27px;
        height: 27px;
        border-radius: 9px;
        background: var(--color-primary, #ff3c00);
        color: #fff;
        font-size: .78rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 1px;
    }

    /* ---- Alerte ---- */
    .vmail-alert {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        background: #e8f7ee;
        border: 1px solid #b7e4c7;
        color: #1b6b3a;
        border-radius: 14px;
        padding: 13px 17px;
        font-size: .92rem;
        line-height: 1.55;
        margin-bottom: 26px;
        max-width: 520px;
    }

    /* ---- Actions ---- */
    .vmail-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 13px;
        align-items: center;
    }

    .btn-vmail {
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

    .btn-vmail:hover,
    .btn-vmail:focus {
        background: var(--color-primary-dark, #e13800);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(255, 60, 0, .3);
    }

    .btn-vmail-ghost {
        background: transparent;
        color: var(--color-grey-dark, #555);
        border: 1px solid var(--color-divider, #ddd);
        border-radius: 14px;
        padding: 13px 24px;
        font-weight: 500;
        font-size: .95rem;
        transition: background .2s ease, border-color .2s ease, color .2s ease;
    }

    .btn-vmail-ghost:hover {
        background: #fff;
        border-color: #cfd4da;
        color: var(--color-black, #111);
    }

    .vmail-hint {
        margin: 26px 0 0;
        font-size: .87rem;
        color: #8a9099;
        line-height: 1.6;
        max-width: 500px;
    }

    /* ============ Illustration ============ */
    .vmail-illu {
        position: relative;
        display: flex;
        justify-content: center;
    }

    .vmail-illu svg {
        width: 100%;
        max-width: 520px;
        height: auto;
    }

    .vmail-float   { animation: vmail-float 5s ease-in-out infinite; transform-box: view-box; transform-origin: center; }
    .vmail-float-2 { animation: vmail-float 6.5s ease-in-out infinite .7s; transform-box: view-box; transform-origin: center; }
    .vmail-pop     { animation: vmail-pop 4.5s ease-in-out infinite; transform-box: view-box; transform-origin: center; }
    .vmail-twinkle { animation: vmail-twinkle 3.2s ease-in-out infinite; }

    @keyframes vmail-float {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-13px); }
    }

    @keyframes vmail-pop {
        0%, 100%  { transform: scale(1); }
        45%, 55%  { transform: scale(1.06); }
    }

    @keyframes vmail-twinkle {
        0%, 100% { opacity: .25; }
        50%      { opacity: 1; }
    }

    @media (prefers-reduced-motion: reduce) {
        .vmail-float, .vmail-float-2, .vmail-pop, .vmail-twinkle { animation: none; }
    }

    @media (max-width: 991.98px) {
        .vmail-hero { min-height: auto; padding: 48px 0 64px; text-align: center; }
        .vmail-content { max-width: 640px; margin: 0 auto; }
        .vmail-steps li { text-align: left; }
        .vmail-alert { margin-inline: auto; text-align: left; }
        .vmail-actions { justify-content: center; }
        .vmail-hint { margin-inline: auto; }
        .vmail-illu { margin-bottom: 34px; }
        .vmail-illu svg { max-width: 380px; }
    }

    @media (max-width: 575.98px) {
        .vmail-actions .btn-vmail,
        .vmail-actions .btn-vmail-ghost,
        .vmail-actions form { width: 100%; }
        .vmail-actions .btn-vmail-ghost { width: 100%; }
    }
</style>

<section class="vmail-hero">
    <div class="container">
        <div class="row align-items-center g-5 flex-lg-row flex-column-reverse">

            {{-- ---------- Colonne texte ---------- --}}
            <div class="col-lg-6">
                <div class="vmail-content">

                    <span class="vmail-eyebrow">
                        <i class="bi bi-shield-lock"></i>
                        Dernière étape
                    </span>

                    <h1 class="vmail-title">
                        Vérifiez votre <em>adresse e-mail</em>
                    </h1>

                    <p class="vmail-lead">
                        Merci pour votre inscription sur <strong>Olten</strong>. Pour sécuriser
                        votre compte, nous venons de vous envoyer un lien de confirmation à&nbsp;:
                    </p>

                    @auth
                        <span class="vmail-email">
                            <i class="bi bi-envelope-fill"></i>
                            {{ auth()->user()->email }}
                        </span>
                    @endauth

                    @if (session('status') == 'verification-link-sent')
                        <div class="vmail-alert" role="alert">
                            <i class="bi bi-check-circle-fill" style="margin-top:2px;"></i>
                            <span>Un nouveau lien de vérification vient d'être envoyé à votre adresse e-mail.</span>
                        </div>
                    @endif

                    <ul class="vmail-steps">
                        <li>
                            <span class="num">1</span>
                            <span>Ouvrez votre boîte de réception et repérez l'e-mail envoyé par Olten.</span>
                        </li>
                        <li>
                            <span class="num">2</span>
                            <span>Cliquez sur le bouton <strong>« Vérifier mon adresse e-mail »</strong> qu'il contient.</span>
                        </li>
                        <li>
                            <span class="num">3</span>
                            <span>Votre compte est activé : vous pouvez profiter de la plateforme.</span>
                        </li>
                    </ul>

                    <div class="vmail-actions">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn btn-vmail">
                                <i class="bi bi-arrow-repeat me-1"></i>
                                Renvoyer l'e-mail
                            </button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-vmail-ghost">
                                <i class="bi bi-box-arrow-right me-1"></i>
                                Se déconnecter
                            </button>
                        </form>
                    </div>

                    <p class="vmail-hint">
                        <i class="bi bi-info-circle me-1"></i>
                        Vous ne trouvez pas l'e-mail ? Pensez à consulter vos dossiers
                        <strong>Spam</strong> ou <strong>Courrier indésirable</strong>.
                    </p>

                </div>
            </div>

            {{-- ---------- Illustration ---------- --}}
            <div class="col-lg-6">
                <div class="vmail-illu">
                    <svg viewBox="0 0 520 440" fill="none" xmlns="http://www.w3.org/2000/svg"
                         role="img" aria-label="Illustration : e-mail de vérification en cours d'envoi">

                        <!-- fonds décoratifs -->
                        <circle cx="268" cy="216" r="176" fill="#ff3c00" fill-opacity="0.06"/>
                        <circle cx="268" cy="216" r="132" fill="#ff3c00" fill-opacity="0.05"/>

                        <!-- étincelles -->
                        <g class="vmail-twinkle" fill="#ffb020">
                            <circle cx="76" cy="120" r="5"/>
                            <circle cx="452" cy="330" r="6"/>
                        </g>
                        <g class="vmail-twinkle" fill="#ff3c00" style="animation-delay:1.1s">
                            <circle cx="440" cy="98" r="5"/>
                            <circle cx="66" cy="316" r="4"/>
                        </g>

                        <!-- lettre qui sort de l'enveloppe -->
                        <g class="vmail-float-2">
                            <rect x="158" y="74" width="204" height="132" rx="14" fill="#ffffff"
                                  stroke="#f0d5cb" stroke-width="2"/>
                            <rect x="182" y="104" width="88" height="10" rx="5" fill="#ff3c00" fill-opacity="0.75"/>
                            <rect x="182" y="128" width="156" height="8" rx="4" fill="#e6e8eb"/>
                            <rect x="182" y="148" width="132" height="8" rx="4" fill="#e6e8eb"/>
                            <rect x="182" y="168" width="100" height="8" rx="4" fill="#e6e8eb"/>
                        </g>

                        <!-- enveloppe -->
                        <g class="vmail-float">
                            <rect x="104" y="168" width="312" height="206" rx="22" fill="#ffffff"
                                  stroke="#eceff2" stroke-width="2"/>
                            <path d="M104 190 L260 300 L416 190" stroke="#ff3c00" stroke-opacity="0.35"
                                  stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M104 358 L212 268" stroke="#eceff2" stroke-width="3" stroke-linecap="round"/>
                            <path d="M416 358 L308 268" stroke="#eceff2" stroke-width="3" stroke-linecap="round"/>
                            <path d="M104 190 C104 178 114 168 126 168 L394 168 C406 168 416 178 416 190
                                     L272 292 C265 297 255 297 248 292 Z"
                                  fill="#fff2ee"/>
                        </g>

                        <!-- badge validé -->
                        <g class="vmail-pop">
                            <circle cx="382" cy="330" r="46" fill="#ffffff"/>
                            <circle cx="382" cy="330" r="38" fill="#2f9e5f"/>
                            <path d="M365 330 l12 12 l22 -24" stroke="#ffffff" stroke-width="6"
                                  stroke-linecap="round" stroke-linejoin="round"/>
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
