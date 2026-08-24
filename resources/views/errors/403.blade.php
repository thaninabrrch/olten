{{-- Un 403 peut tomber sur un visiteur non connecte : le layout connecte,
     dont le header lit auth()->user(), ne convient que si une session existe. --}}
@extends(auth()->check() ? 'layouts.connected' : 'layouts.main')

@section('title', 'Accès refusé - Olten.fr')

@php
    $user = auth()->user();

    // Revenir en arriere ne doit pas ramener sur la page refusee.
    $back = url()->previous();
    if (! $back || $back === url()->current()) {
        $back = url('/');
    }

    $pendingAccount = $user && (! $user->hasVerifiedEmail() || ! $user->is_approved);
@endphp

@section('content')
<style>
    .err {
        position: relative;
        overflow: hidden;
        min-height: 74vh;
        display: flex;
        align-items: center;
        padding: 60px 24px 80px;
        background:
            radial-gradient(820px 480px at 88% 8%, rgba(255, 60, 0, .10), transparent 62%),
            radial-gradient(660px 440px at 4% 92%, rgba(255, 176, 32, .12), transparent 60%),
            linear-gradient(180deg, #fffaf7 0%, var(--color-bg-body, #f8f9fa) 100%);
    }

    .err-inner {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 1080px;
        margin: 0 auto;
    }

    .err-content { max-width: 540px; }

    .err-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 7px 15px;
        border-radius: 999px;
        background: rgba(255, 60, 0, .10);
        color: var(--color-primary-dark, #e13800);
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 20px;
    }

    .err-title {
        font-size: clamp(1.9rem, 3.6vw, 2.7rem);
        font-weight: 800;
        line-height: 1.14;
        letter-spacing: -.02em;
        color: var(--color-black, #111);
        margin-bottom: 16px;
    }

    .err-title em {
        font-style: normal;
        color: var(--color-primary, #ff3c00);
    }

    .err-lead {
        font-size: 1.02rem;
        line-height: 1.7;
        color: var(--color-grey-dark, #555);
        margin-bottom: 26px;
    }

    /* ---- Raisons possibles ---- */
    .err-reasons {
        list-style: none;
        margin: 0 0 30px;
        padding: 0;
        display: grid;
        gap: 10px;
        max-width: 520px;
    }

    .err-reasons li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 13px 15px;
        background: #fff;
        border: 1px solid var(--color-divider, #e9ecef);
        border-radius: 14px;
        font-size: .91rem;
        line-height: 1.55;
        color: var(--color-grey-dark, #555);
    }

    .err-reasons i {
        flex: 0 0 30px;
        width: 30px;
        height: 30px;
        border-radius: 9px;
        background: #f1f3f5;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem;
    }

    .err-reasons li.is-lead i {
        background: rgba(255, 176, 32, .18);
        color: #a35f00;
    }

    .err-reasons strong {
        display: block;
        color: var(--color-black, #111);
        font-size: .94rem;
        margin-bottom: 2px;
    }

    .err-reasons a {
        color: var(--color-primary, #ff3c00);
        font-weight: 600;
        text-decoration: none;
    }

    .err-reasons a:hover { text-decoration: underline; }

    /* ---- Actions ---- */
    .err-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }

    .btn-err {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        background: var(--color-primary, #ff3c00);
        color: #fff;
        border: 0;
        border-radius: 14px;
        padding: 14px 26px;
        font-weight: 600;
        font-size: .96rem;
        text-decoration: none;
        transition: background .2s ease, transform .15s ease, box-shadow .2s ease;
        box-shadow: 0 10px 24px rgba(255, 60, 0, .22);
    }

    .btn-err:hover {
        background: var(--color-primary-dark, #e13800);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(255, 60, 0, .28);
    }

    .btn-err-ghost {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        background: #fff;
        color: var(--color-grey-dark, #555);
        border: 1px solid var(--color-divider, #ddd);
        border-radius: 14px;
        padding: 13px 24px;
        font-weight: 500;
        font-size: .94rem;
        text-decoration: none;
        transition: background .2s ease, border-color .2s ease, color .2s ease;
    }

    .btn-err-ghost:hover {
        background: var(--color-grey-light, #f6f7f9);
        border-color: #cfd4da;
        color: var(--color-black, #111);
    }

    .err-help {
        margin: 26px 0 0;
        font-size: .86rem;
        color: #8a9099;
    }

    .err-help a {
        color: var(--color-primary, #ff3c00);
        font-weight: 600;
        text-decoration: none;
    }

    .err-help a:hover { text-decoration: underline; }

    /* ---- Illustration ---- */
    .err-illu {
        display: flex;
        justify-content: center;
    }

    .err-illu svg {
        width: 100%;
        max-width: 440px;
        height: auto;
    }

    .err-float   { animation: err-float 5.2s ease-in-out infinite; transform-box: view-box; transform-origin: center; }
    .err-float-2 { animation: err-float 6.6s ease-in-out infinite .7s; transform-box: view-box; transform-origin: center; }
    .err-shackle { animation: err-shackle 4.6s ease-in-out infinite; transform-box: view-box; transform-origin: 240px 150px; }
    .err-twinkle { animation: err-twinkle 3.2s ease-in-out infinite; }

    @keyframes err-float {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-12px); }
    }

    @keyframes err-shackle {
        0%, 88%, 100% { transform: rotate(0deg); }
        92%           { transform: rotate(-4deg); }
        96%           { transform: rotate(4deg); }
    }

    @keyframes err-twinkle {
        0%, 100% { opacity: .25; }
        50%      { opacity: 1; }
    }

    @media (prefers-reduced-motion: reduce) {
        .err-float, .err-float-2, .err-shackle, .err-twinkle { animation: none; }
    }

    @media (max-width: 991.98px) {
        .err { min-height: auto; padding: 40px 20px 60px; text-align: center; }
        .err-content { max-width: 620px; margin: 0 auto; }
        .err-reasons { margin-inline: auto; }
        .err-reasons li { text-align: left; }
        .err-actions { justify-content: center; }
        .err-illu { margin-bottom: 30px; }
        .err-illu svg { max-width: 320px; }
    }

    @media (max-width: 575.98px) {
        .err-actions { flex-direction: column; align-items: stretch; }
        .err-actions .btn-err,
        .err-actions .btn-err-ghost { justify-content: center; width: 100%; }
    }
</style>

<section class="err">
    <div class="err-inner">
        <div class="row align-items-center g-5 flex-lg-row flex-column-reverse">

            {{-- ---------- Texte ---------- --}}
            <div class="col-lg-6">
                <div class="err-content">

                    <span class="err-eyebrow">
                        <i class="fa-solid fa-lock"></i>
                        Erreur 403
                    </span>

                    <h1 class="err-title">
                        Cette page vous est <em>refusée</em>
                    </h1>

                    <p class="err-lead">
                        Vous n'avez pas les autorisations nécessaires pour accéder à cette page.
                        Rien n'est cassé : votre compte n'a simplement pas les droits requis ici.
                    </p>

                    <ul class="err-reasons">
                        @guest
                            <li class="is-lead">
                                <i class="fa-solid fa-right-to-bracket"></i>
                                <span>
                                    <strong>Vous n'êtes pas connecté</strong>
                                    Cette page est réservée aux membres.
                                    <a href="{{ route('login') }}">Se connecter</a>
                                </span>
                            </li>
                        @endguest

                        @auth
                            @if ($pendingAccount)
                                <li class="is-lead">
                                    <i class="fa-regular fa-clock"></i>
                                    <span>
                                        <strong>Votre compte est en cours de validation</strong>
                                        L'accès s'ouvrira dès que votre dossier sera validé.
                                        <a href="{{ route('account.pending') }}">Suivre ma validation</a>
                                    </span>
                                </li>
                            @endif

                            <li>
                                <i class="fa-solid fa-user-shield"></i>
                                <span>
                                    <strong>Cette page est réservée à un autre type de compte</strong>
                                    Locateur, vendeur, conducteur ou livreur n'accèdent pas aux mêmes espaces.
                                </span>
                            </li>
                        @endauth

                        <li>
                            <i class="fa-solid fa-link-slash"></i>
                            <span>
                                <strong>Le lien est peut-être obsolète</strong>
                                Il pointe vers une ressource déplacée ou qui ne vous appartient plus.
                            </span>
                        </li>
                    </ul>

                    <div class="err-actions">
                        @guest
                            <a href="{{ route('login') }}" class="btn-err">
                                <i class="fa-solid fa-right-to-bracket"></i>
                                Se connecter
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="btn-err">
                                <i class="fa-solid fa-table-columns"></i>
                                Mon tableau de bord
                            </a>
                        @endguest

                        <a href="{{ $back }}" class="btn-err-ghost">
                            <i class="fa-solid fa-arrow-left"></i>
                            Retour
                        </a>

                        <a href="{{ url('/') }}" class="btn-err-ghost">
                            <i class="fa-solid fa-house"></i>
                            Accueil
                        </a>
                    </div>

                    <p class="err-help">
                        Vous pensez qu'il s'agit d'une erreur ?
                        <a href="{{ route('contact') }}">Contactez notre équipe</a>
                    </p>

                </div>
            </div>

            {{-- ---------- Illustration ---------- --}}
            <div class="col-lg-6">
                <div class="err-illu">
                    <svg viewBox="0 0 480 420" fill="none" xmlns="http://www.w3.org/2000/svg"
                         role="img" aria-label="Illustration : accès verrouillé">

                        <circle cx="240" cy="212" r="168" fill="#ff3c00" fill-opacity="0.06"/>
                        <circle cx="240" cy="212" r="126" fill="#ff3c00" fill-opacity="0.05"/>

                        <g class="err-twinkle" fill="#ffb020">
                            <circle cx="66" cy="122" r="5"/>
                            <circle cx="420" cy="322" r="6"/>
                        </g>
                        <g class="err-twinkle" fill="#ff3c00" style="animation-delay:1.1s">
                            <circle cx="418" cy="104" r="5"/>
                            <circle cx="58" cy="312" r="4"/>
                        </g>

                        {{-- Bouclier en arriere-plan, portant le code d'erreur --}}
                        <g class="err-float-2">
                            <path d="M240 52 L346 96 V196 C346 262 300 314 240 338
                                     C180 314 134 262 134 196 V96 Z"
                                  fill="#ffffff" stroke="#f0d5cb" stroke-width="2"/>
                            <text x="240" y="132" text-anchor="middle"
                                  font-family="Helvetica, Arial, sans-serif" font-size="42" font-weight="800"
                                  fill="#ff3c00" fill-opacity="0.22">403</text>
                        </g>

                        {{-- Cadenas --}}
                        <g class="err-float">
                            <g class="err-shackle">
                                <path d="M198 196 v-30 a42 42 0 0 1 84 0 v30"
                                      stroke="#c9ced6" stroke-width="17" stroke-linecap="round" fill="none"/>
                            </g>

                            <rect x="164" y="192" width="152" height="122" rx="24" fill="#ff3c00"/>
                            <rect x="164" y="192" width="152" height="122" rx="24" fill="#ffffff" fill-opacity="0.08"/>

                            <circle cx="240" cy="242" r="17" fill="#ffffff"/>
                            <path d="M240 254 v26" stroke="#ffffff" stroke-width="11" stroke-linecap="round"/>
                        </g>

                        <ellipse cx="240" cy="374" rx="132" ry="13" fill="#111111" fill-opacity="0.05"/>
                    </svg>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection
