@extends('layouts.main')

@section('title', 'Finaliser votre réservation - Olten.fr')

@php
    $user = auth()->user();

    $departVille      = \App\Models\Covoiturage::villeCourte($trip->depart);
    $destinationVille = \App\Models\Covoiturage::villeCourte($trip->destination);
    $legsCount        = $lines->count();
@endphp

@section('content')

<div class="ck">
    <div class="ck-wrap">

        {{-- Fil de progression --}}
        <ol class="ck-steps">
            <li class="is-done">
                <span class="ck-step-dot"><i class="fa-solid fa-check"></i></span>
                <span class="ck-step-text">
                    <strong>Trajet choisi</strong>
                    <small>{{ $legsCount }} sens sélectionné{{ $legsCount > 1 ? 's' : '' }}</small>
                </span>
            </li>
            <li class="is-current">
                <span class="ck-step-dot">2</span>
                <span class="ck-step-text">
                    <strong>Coordonnées &amp; paiement</strong>
                    <small>Vous y êtes</small>
                </span>
            </li>
            <li>
                <span class="ck-step-dot">3</span>
                <span class="ck-step-text">
                    <strong>Confirmation</strong>
                    <small>Réservation envoyée au conducteur</small>
                </span>
            </li>
        </ol>

        <header class="ck-head">
            <div>
                <span class="ck-eyebrow"><i class="fa-solid fa-lock"></i> Paiement sécurisé </span>
                <h1 class="ck-title">Finaliser votre <em>réservation</em></h1>
                <p class="ck-lead">
                    Vérifiez vos informations puis réglez en toute sécurité.
                    Vous ne serez débité qu'une seule fois. En cas d'annulation, vous êtes remboursé.
                </p>
            </div>

            <div class="ck-illu">
                <svg viewBox="0 0 300 210" fill="none" xmlns="http://www.w3.org/2000/svg"
                     role="img" aria-label="Illustration : réservation de trajet sécurisée">
                    <circle cx="150" cy="105" r="92" fill="#ff3c00" fill-opacity="0.07"/>
                    <circle cx="150" cy="105" r="68" fill="#ff3c00" fill-opacity="0.06"/>

                    <g class="ck-twinkle" fill="#ffb020">
                        <circle cx="34" cy="46" r="4"/>
                        <circle cx="268" cy="164" r="5"/>
                    </g>

                    {{-- Voiture --}}
                    <g class="ck-float">
                        <path d="M62 132 L62 112 Q62 104 70 102 L100 96 L120 74 Q124 70 130 70 L178 70 Q184 70 188 74 L206 96 L232 102 Q240 104 240 112 L240 132 Z" fill="#ff3c00" fill-opacity=".92"/>
                        <path d="M112 96 L128 80 L150 80 L150 96 Z" fill="#ffffff" fill-opacity=".85"/>
                        <path d="M158 96 L158 80 L178 80 L194 96 Z" fill="#ffffff" fill-opacity=".85"/>
                        <rect x="62" y="124" width="178" height="14" rx="7" fill="#cf3200"/>
                        <circle cx="102" cy="138" r="16" fill="#111111"/>
                        <circle cx="102" cy="138" r="7" fill="#e9ecef"/>
                        <circle cx="200" cy="138" r="16" fill="#111111"/>
                        <circle cx="200" cy="138" r="7" fill="#e9ecef"/>
                    </g>

                    {{-- Cadenas --}}
                    <g class="ck-float-2">
                        <circle cx="228" cy="52" r="38" fill="#ffffff"/>
                        <circle cx="228" cy="52" r="30" fill="#1a9d5c"/>
                        <path d="M217 48 v-7 a11 11 0 0 1 22 0 v7"
                              stroke="#ffffff" stroke-width="5" fill="none" stroke-linecap="round"/>
                        <rect x="214" y="48" width="28" height="21" rx="5" fill="#ffffff"/>
                        <circle cx="228" cy="57" r="3.2" fill="#1a9d5c"/>
                    </g>

                    <ellipse cx="150" cy="180" rx="86" ry="10" fill="#111111" fill-opacity="0.05"/>
                </svg>
            </div>
        </header>

        <form id="payment-form">
            @csrf

            <div class="ck-grid">

                {{-- ---------- Colonne gauche : informations ---------- --}}
                <div class="ck-col">

                    {{-- Coordonnées --}}
                    <section class="ck-card">
                        <h2 class="ck-card-title"><span>1</span> Vos coordonnées</h2>

                        <div class="ck-fields">
                            <div class="ck-field">
                                <label class="ck-label" for="ck-lastname">Nom</label>
                                <input type="text" id="ck-lastname" class="ck-input"
                                       value="{{ $user->lastname }}" readonly>
                            </div>

                            <div class="ck-field">
                                <label class="ck-label" for="ck-firstname">Prénom</label>
                                <input type="text" id="ck-firstname" class="ck-input"
                                       value="{{ $user->firstname }}" readonly>
                            </div>

                            {{-- Ce que les autres membres verront : la page du trajet
                                 liste ses passagers (services/covoiturage-detail). --}}
                            <div class="ck-field ck-field--full">
                                <small class="ck-hint">
                                    <i class="fa-solid fa-user-group"></i>
                                    Vos prénom, nom et photo apparaîtront sur la page du trajet, parmi ses passagers.
                                    Votre téléphone n'est communiqué qu'au conducteur.
                                </small>
                            </div>

                            <div class="ck-field ck-field--full">
                                <label class="ck-label" for="phone">
                                    Téléphone <span class="ck-required">*</span>
                                </label>
                                <input type="tel" id="phone" class="ck-input" required>
                                <input type="hidden" id="phone_full" name="phone">
                                <small class="ck-hint">Le conducteur vous joindra à ce numéro.</small>
                            </div>
                        </div>
                    </section>

                    {{-- Paiement --}}
                    <section class="ck-card">
                        <h2 class="ck-card-title"><span>2</span> Paiement</h2>

                        <div class="ck-field ck-field--full">
                            <label class="ck-label" for="card-element">Carte bancaire</label>
                            <div id="card-element" class="ck-card-element"></div>
                            <div id="card-errors" class="ck-error" role="alert"></div>
                        </div>

                        <ul class="ck-trust">
                            <li><i class="fa-solid fa-shield-halved"></i> Paiement chiffré via Stripe</li>
                            <li><i class="fa-regular fa-credit-card"></i> Aucune donnée bancaire stockée</li>
                            <li><i class="fa-solid fa-receipt"></i> Reçu envoyé par e-mail</li>
                        </ul>
                    </section>
                </div>

                {{-- ---------- Colonne droite : récapitulatif ---------- --}}
                <aside class="ck-aside">
                    <div class="ck-recap">

                        <div class="ck-recap-ad">
                            <span class="ck-recap-ad-ico"><i class="fa-solid fa-car-side"></i></span>
                            <span class="ck-recap-ad-info">
                                <strong>{{ $departVille }} → {{ $destinationVille }}</strong>
                                <small><i class="fa-solid fa-user"></i> Avec {{ $trip->conducteur->firstname ?? 'le conducteur' }}</small>
                            </span>
                        </div>

                        {{-- Places par sens quand elles different (2 a l'aller, 1 au retour) --}}
                        <div class="ck-recap-dates">
                            @if (count(array_unique($seats)) > 1)
                                @foreach ($lines as $line)
                                    @if (! $loop->first)
                                        <i class="fa-solid fa-plus"></i>
                                    @endif
                                    <span>
                                        <small>{{ $line['label'] }}</small>
                                        <strong>{{ $line['seats'] }} place{{ $line['seats'] > 1 ? 's' : '' }}</strong>
                                    </span>
                                @endforeach
                            @else
                                <span>
                                    <small>Places</small>
                                    <strong>{{ max($seats) }}</strong>
                                </span>
                                <i class="fa-solid fa-xmark"></i>
                                <span>
                                    <small>Sens</small>
                                    <strong>{{ $legsCount > 1 ? 'Aller & retour' : $lines->first()['label'] }}</strong>
                                </span>
                            @endif
                        </div>

                        @foreach ($lines as $line)
                            <div class="ck-line">
                                <span>
                                    {{ $line['label'] }} · {{ \App\Models\Covoiturage::villeCourte($line['from']) }}
                                    → {{ \App\Models\Covoiturage::villeCourte($line['to']) }}
                                    @if ($line['seats'] > 1)
                                        <small>({{ $line['seats'] }} × {{ number_format($line['price'], 2, ',', ' ') }} €)</small>
                                    @endif
                                </span>
                                <span>{{ number_format($line['price'] * $line['seats'], 2, ',', ' ') }} €</span>
                            </div>
                        @endforeach

                        <div class="ck-line">
                            <span>Frais de service ({{ rtrim(rtrim(number_format($amounts['rate'], 2, ',', ''), '0'), ',') }} %)</span>
                            <span>{{ number_format($amounts['commission'], 2, ',', ' ') }} €</span>
                        </div>

                        <div class="ck-line ck-line--total">
                            <span>Total</span>
                            <span>{{ number_format($amounts['total'], 2, ',', ' ') }} €</span>
                        </div>

                        <button id="submit" type="submit" class="ck-submit">
                            <i class="fa-solid fa-lock"></i>
                            Payer {{ number_format($amounts['total'], 2, ',', ' ') }} €
                        </button>

                        <p class="ck-recap-note">
                            <i class="fa-solid fa-circle-info"></i>
                            @if ($trip->isManual())
                                Votre demande part au conducteur dès le paiement accepté : il l'accepte ou la refuse.
                                S'il refuse, ou s'il ne répond pas avant le départ, vous êtes intégralement remboursé.
                            @else
                                Le conducteur est prévenu dès le paiement accepté. En cas d'annulation,
                                vous êtes intégralement remboursé.
                            @endif
                            En payant, vous acceptez nos
                            <a href="{{ route('legal.cgv') }}" target="_blank" rel="noopener">conditions générales de vente</a>.
                        </p>
                    </div>

                    <div class="ck-mini">
                        <span><small>Places</small><strong>{{ \App\Models\TripBooking::describeSeats($seats, short: true) }}</strong></span>
                        <span><small>Total</small><strong>{{ number_format($amounts['total'], 2, ',', ' ') }} €</strong></span>
                    </div>
                </aside>

            </div>
        </form>

    </div>
</div>

<style>
    /* ==========================================================================
       Tunnel de réservation d'un trajet.
       Même charte que la page d'achat produit (préfixe .ck) : mêmes cartes,
       même grille, même récapitulatif collé à droite.
       ========================================================================== */
    .ck {
        background:
            radial-gradient(720px 420px at 92% 4%, rgba(255, 60, 0, .08), transparent 62%),
            linear-gradient(180deg, #fffaf7 0%, var(--color-bg-body, #f8f9fa) 46%);
        padding: 32px 0 90px;
    }

    .ck-wrap {
        max-width: 1140px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* ---- Progression ---- */
    .ck-steps {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        list-style: none;
        margin: 0 0 28px;
        padding: 0;
    }

    .ck-steps li {
        flex: 1 1 220px;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        background: #fff;
        border: 1px solid var(--color-divider, #e9ecef);
        border-radius: 14px;
    }

    .ck-step-dot {
        flex: 0 0 30px;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f3f5;
        color: #9aa0a6;
        font-size: 12.5px;
        font-weight: 700;
    }

    .ck-steps li.is-done { border-color: #b7e4c7; }
    .ck-steps li.is-done .ck-step-dot { background: #2f9e5f; color: #fff; }

    .ck-steps li.is-current { border-color: rgba(255, 60, 0, .45); box-shadow: 0 0 0 3px rgba(255, 60, 0, .08); }
    .ck-steps li.is-current .ck-step-dot { background: var(--color-primary, #ff3c00); color: #fff; }

    .ck-step-text strong { display: block; font-size: 13.5px; font-weight: 700; line-height: 1.3; }
    .ck-step-text small { font-size: 11.5px; color: #8a9099; }

    /* ---- En-tête ---- */
    .ck-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 30px;
        margin-bottom: 26px;
    }

    .ck-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 999px;
        background: rgba(26, 157, 92, .12);
        color: #14794a;
        font-size: .73rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        margin-bottom: 14px;
    }

    .ck-title {
        font-size: clamp(1.6rem, 3vw, 2.3rem);
        font-weight: 800;
        letter-spacing: -.02em;
        line-height: 1.16;
        margin: 0 0 12px;
    }

    .ck-title em { font-style: normal; color: var(--color-primary, #ff3c00); }

    .ck-lead {
        font-size: .98rem;
        line-height: 1.7;
        color: var(--color-grey-dark, #555);
        margin: 0;
        max-width: 560px;
    }

    .ck-illu { flex: 0 0 300px; }
    .ck-illu svg { width: 100%; height: auto; }

    .ck-float   { animation: ck-float 5.2s ease-in-out infinite; transform-box: view-box; transform-origin: center; }
    .ck-float-2 { animation: ck-float 6.4s ease-in-out infinite .6s; transform-box: view-box; transform-origin: center; }
    .ck-twinkle { animation: ck-twinkle 3.2s ease-in-out infinite; }

    @keyframes ck-float {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-9px); }
    }

    @keyframes ck-twinkle {
        0%, 100% { opacity: .25; }
        50%      { opacity: 1; }
    }

    @media (prefers-reduced-motion: reduce) {
        .ck-float, .ck-float-2, .ck-twinkle { animation: none; }
    }

    /* ---- Grille ---- */
    .ck-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) minmax(0, 1fr);
        gap: 20px;
        align-items: start;
    }

    .ck-col { display: flex; flex-direction: column; gap: 20px; min-width: 0; }

    .ck-card {
        background: #fff;
        border: 1px solid var(--color-divider, #e9ecef);
        border-radius: 18px;
        padding: 22px;
    }

    .ck-card-title {
        display: flex;
        align-items: center;
        gap: 11px;
        font-size: 1rem;
        font-weight: 700;
        margin: 0 0 18px;
    }

    .ck-card-title span {
        width: 26px;
        height: 26px;
        flex: 0 0 26px;
        border-radius: 9px;
        background: rgba(255, 60, 0, .1);
        color: var(--color-primary, #ff3c00);
        font-size: 12.5px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* ---- Champs ---- */
    .ck-fields {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .ck-field--full { grid-column: 1 / -1; }

    .ck-label {
        display: block;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--color-grey-dark, #555);
        margin-bottom: 7px;
    }

    .ck-required { color: var(--color-primary, #ff3c00); }

    .ck-input {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid var(--color-divider, #e9ecef);
        border-radius: 11px;
        font-size: 13.5px;
        background: #fff;
        color: var(--color-black, #111);
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .ck-input:focus {
        outline: none;
        border-color: var(--color-primary, #ff3c00);
        box-shadow: 0 0 0 3px rgba(255, 60, 0, .12);
    }

    .ck-input[readonly] { background: var(--color-grey-light, #f6f7f9); color: #6b7280; }

    .ck-hint { display: block; margin-top: 6px; font-size: 11.5px; color: #8a9099; }

    /* intl-tel-input occupe toute la largeur du champ */
    .iti { width: 100%; }

    /* ---- Stripe ---- */
    .ck-card-element {
        padding: 14px;
        border: 1px solid var(--color-divider, #e9ecef);
        border-radius: 11px;
        background: #fff;
    }

    .ck-card-element.StripeElement--focus {
        border-color: var(--color-primary, #ff3c00);
        box-shadow: 0 0 0 3px rgba(255, 60, 0, .12);
    }

    .ck-error {
        margin-top: 8px;
        font-size: 12.5px;
        color: #b42318;
        min-height: 16px;
    }

    .ck-trust {
        list-style: none;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 16px 0 0;
        padding: 0;
    }

    .ck-trust li {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 12px;
        border-radius: 999px;
        background: var(--color-grey-light, #f6f7f9);
        font-size: 11.5px;
        font-weight: 600;
        color: var(--color-grey-dark, #555);
    }

    .ck-trust i { color: #1a9d5c; font-size: 11px; }

    /* ---- Récapitulatif ---- */
    .ck-aside { position: sticky; top: 20px; display: flex; flex-direction: column; gap: 14px; }

    .ck-recap {
        background: #fff;
        border: 1px solid var(--color-divider, #e9ecef);
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 12px 32px rgba(17, 17, 17, .06);
    }

    .ck-recap-ad {
        display: flex;
        gap: 12px;
        align-items: center;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--color-divider, #e9ecef);
        margin-bottom: 14px;
    }

    /* Pas de photo pour un trajet : pastille icône à la place de l'image produit */
    .ck-recap-ad-ico {
        width: 66px;
        height: 52px;
        flex: 0 0 66px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 60, 0, .1);
        color: var(--color-primary, #ff3c00);
        font-size: 20px;
    }

    .ck-recap-ad-info { min-width: 0; }

    .ck-recap-ad-info strong {
        display: block;
        font-size: 13.5px;
        font-weight: 700;
        line-height: 1.35;
    }

    .ck-recap-ad-info small {
        display: block;
        font-size: 11px;
        color: #8a9099;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ck-recap-dates {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 14px;
        border-radius: 12px;
        background: var(--color-grey-light, #f6f7f9);
        margin-bottom: 16px;
    }

    .ck-recap-dates small { display: block; font-size: 10.5px; color: #8a9099; text-transform: uppercase; letter-spacing: .05em; }
    .ck-recap-dates strong { font-size: 13px; font-weight: 700; }
    .ck-recap-dates i { color: var(--color-primary, #ff3c00); font-size: 11px; }

    .ck-line {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 9px 0;
        font-size: 13px;
        color: var(--color-grey-dark, #555);
    }

    /* Le libellé d'un sens peut être long : il passe à la ligne, le prix reste en place */
    .ck-line span:first-child { min-width: 0; }
    .ck-line span:last-child  { flex: 0 0 auto; white-space: nowrap; }

    .ck-line--total {
        margin-top: 6px;
        padding-top: 14px;
        border-top: 1px solid var(--color-divider, #e9ecef);
        font-size: 15px;
        font-weight: 800;
        color: var(--color-black, #111);
    }

    .ck-line--total span:last-child { font-size: 21px; }

    .ck-submit {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        width: 100%;
        margin-top: 16px;
        padding: 15px 20px;
        border: 0;
        border-radius: 13px;
        background: var(--color-primary, #ff3c00);
        color: #fff;
        font-size: 14.5px;
        font-weight: 700;
        cursor: pointer;
        transition: background .2s ease, transform .15s ease, box-shadow .2s ease;
        box-shadow: 0 10px 24px rgba(255, 60, 0, .24);
    }

    .ck-submit:hover {
        background: var(--color-primary-dark, #e13800);
        transform: translateY(-1px);
        box-shadow: 0 14px 30px rgba(255, 60, 0, .3);
    }

    .ck-submit:disabled { background: #e9ecef; color: #9aa0a6; box-shadow: none; cursor: not-allowed; transform: none; }

    .ck-recap-note {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin: 14px 0 0;
        font-size: 11.5px;
        line-height: 1.6;
        color: #8a9099;
    }

    .ck-recap-note i { color: var(--color-primary, #ff3c00); margin-top: 2px; }

    .ck-mini { display: flex; gap: 10px; }

    .ck-mini span {
        flex: 1;
        background: #fff;
        border: 1px solid var(--color-divider, #e9ecef);
        border-radius: 13px;
        padding: 12px 14px;
    }

    .ck-mini small { display: block; font-size: 10.5px; color: #8a9099; text-transform: uppercase; letter-spacing: .05em; }
    .ck-mini strong { font-size: 15px; font-weight: 800; }

    /* ---- Responsive ---- */
    @media (max-width: 991.98px) {
        .ck-grid { grid-template-columns: 1fr; }
        .ck-aside { position: static; }
        .ck-head { flex-direction: column-reverse; align-items: flex-start; gap: 16px; }
        .ck-illu { flex: none; width: 220px; align-self: center; }
    }

    @media (max-width: 575.98px) {
        .ck { padding: 22px 0 64px; }
        .ck-fields { grid-template-columns: 1fr; }
        .ck-card, .ck-recap { padding: 17px; }
        .ck-steps li { flex: 1 1 100%; }
    }
</style>

<script src="https://js.stripe.com/v3/"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>

<script>
/* ---------- Téléphone ---------- */
const phoneInput = document.querySelector("#phone");
const iti = window.intlTelInput(phoneInput, {
    initialCountry: "fr",
    separateDialCode: true,
    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js"
});

/* ---------- Stripe ---------- */
const stripe   = Stripe("{{ config('services.stripe.key') }}");
const elements = stripe.elements();

const card = elements.create("card", {
    style: {
        base: { fontSize: "14px", color: "#111", fontFamily: "inherit", "::placeholder": { color: "#9aa0a6" } },
        invalid: { color: "#b42318" }
    }
});
card.mount("#card-element");

const errorsEl    = document.getElementById("card-errors");
const form        = document.getElementById("payment-form");
const submitBtn   = document.getElementById("submit");
const submitLabel = submitBtn.innerHTML;

card.on("change", (e) => { errorsEl.textContent = e.error ? e.error.message : ""; });

const releaseButton = () => { submitBtn.disabled = false; submitBtn.innerHTML = submitLabel; };
const fail = (msg) => { errorsEl.textContent = msg; releaseButton(); };

const LEGS    = "{{ implode(',', $legs) }}";
// Places par sens : { seats_aller: 2, seats_retour: 1 }
const SEATS   = {!! json_encode(collect($seats)->mapWithKeys(fn ($count, $leg) => ['seats_' . $leg => $count])) !!};
const PAY_URL = "{{ route('trips.pay', $trip) }}";
const CSRF    = document.querySelector('input[name=_token]').value;

async function postPayment(body) {
    const res = await fetch(PAY_URL, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": CSRF
        },
        body: JSON.stringify({ legs: LEGS, ...SEATS, ...body })
    });
    return res.json();
}

form.addEventListener("submit", async (e) => {
    e.preventDefault();
    errorsEl.textContent = "";

    if (!iti.isValidNumber()) {
        errorsEl.textContent = "Numéro de téléphone invalide.";
        return;
    }
    const phone = iti.getNumber();
    document.getElementById("phone_full").value = phone;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Paiement en cours...';

    try {
        const { paymentMethod, error } = await stripe.createPaymentMethod({ type: "card", card });
        if (error) return fail(error.message);

        let data = await postPayment({ payment_method: paymentMethod.id, phone });

        // 3D Secure : la banque demande une authentification
        if (data.requires_action) {
            const result = await stripe.handleCardAction(data.client_secret);
            if (result.error) return fail(result.error.message);

            data = await postPayment({ payment_intent_id: result.paymentIntent.id, phone });
        }

        if (data.success) {
            window.location.href = data.redirect;
        } else {
            fail(data.message || "Le paiement n'a pas pu aboutir.");
        }
    } catch (err) {
        fail("Le paiement n'a pas pu aboutir. Réessayez.");
    }
});
</script>

@endsection