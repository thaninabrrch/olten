{{-- resources/views/bookings/confirm-livraison.blade.php --}}
{{--
 | Reservation d'une offre de livraison : le client saisit son trajet, le prix
 | se calcule sur la distance au tarif du livreur, puis il paie.
 |
 | Meme tunnel visuel que la location (pages/annonces_pages/confirm_booking),
 | styles partages dans assets/css/checkout.css. Le montant affiche ici n'est
 | qu'indicatif : BookingController::pay() le recalcule au tarif de l'annonce.
--}}

@extends('layouts.main')

@section('title', 'Réserver une livraison - Olten.fr')

@php
    $user    = auth()->user();
    $livreur = $ad->user;
    $rate    = $ad->deliveryRate();

    $image = $ad->images->first()
        ? asset('storage/' . $ad->images->first()->path)
        : asset('assets/images/no-image.jpg');
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/checkout.css') }}?v={{ @filemtime(public_path('assets/css/checkout.css')) ?: 1 }}">
@endpush

@section('content')

<div class="ck">
    <div class="ck-wrap">

        {{-- Fil de progression --}}
        <ol class="ck-steps">
            <li class="is-done">
                <span class="ck-step-dot"><i class="fa-solid fa-check"></i></span>
                <span class="ck-step-text">
                    <strong>Offre choisie</strong>
                    <small>{{ \Illuminate\Support\Str::limit($ad->title, 38) }}</small>
                </span>
            </li>
            <li class="is-current">
                <span class="ck-step-dot">2</span>
                <span class="ck-step-text">
                    <strong>Trajet &amp; paiement</strong>
                    <small>Vous y êtes</small>
                </span>
            </li>
            <li>
                <span class="ck-step-dot">3</span>
                <span class="ck-step-text">
                    <strong>Confirmation</strong>
                    <small>Demande envoyée au livreur</small>
                </span>
            </li>
        </ol>

        <header class="ck-head">
            <div>
                <span class="ck-eyebrow"><i class="fa-solid fa-lock"></i> Paiement sécurisé</span>
                <h1 class="ck-title">Réserver votre <em>livraison</em></h1>
                <p class="ck-lead">
                    Indiquez où récupérer le colis et où le livrer : le prix est calculé sur la
                    distance du trajet, au tarif du livreur. Vous ne serez débité qu'une seule fois.
                </p>
            </div>

            <div class="ck-illu">
                <svg viewBox="0 0 300 210" fill="none" xmlns="http://www.w3.org/2000/svg"
                     role="img" aria-label="Illustration : un camion de livraison entre deux adresses">
                    <circle cx="150" cy="105" r="92" fill="#ff3c00" fill-opacity="0.07"/>
                    <circle cx="150" cy="105" r="68" fill="#ff3c00" fill-opacity="0.06"/>

                    <g class="ck-twinkle" fill="#ffb020">
                        <circle cx="34" cy="46" r="4"/>
                        <circle cx="268" cy="164" r="5"/>
                    </g>

                    {{-- Trajet pointille entre les deux adresses --}}
                    <path d="M44 166 C 96 132, 196 190, 252 116"
                          stroke="#1c2026" stroke-opacity="0.22" stroke-width="3"
                          stroke-dasharray="7 8" stroke-linecap="round"/>

                    {{-- Depart --}}
                    <path d="M44 138 a14 14 0 0 1 14 14 c0 10 -14 24 -14 24 s-14 -14 -14 -24 a14 14 0 0 1 14 -14z" fill="#ff3c00"/>
                    <circle cx="44" cy="152" r="5" fill="#ffffff"/>

                    {{-- Arrivee --}}
                    <path d="M252 86 a14 14 0 0 1 14 14 c0 10 -14 24 -14 24 s-14 -14 -14 -24 a14 14 0 0 1 14 -14z" fill="#1a9d5c"/>
                    <circle cx="252" cy="100" r="5" fill="#ffffff"/>

                    {{-- Camion --}}
                    <g class="ck-float">
                        <rect x="96" y="92" width="80" height="50" rx="8" fill="#1c2026"/>
                        <rect x="110" y="106" width="22" height="20" rx="3" fill="#ffb020"/>
                        <rect x="119" y="106" width="4" height="20" fill="#1c2026" fill-opacity="0.25"/>
                        <path d="M176 104 h22 l16 18 v20 h-38 z" fill="#ff3c00"/>
                        <path d="M182 109 h13 l10 12 h-23 z" fill="#ffffff" fill-opacity="0.85"/>
                        <circle cx="118" cy="144" r="10" fill="#1c2026"/>
                        <circle cx="118" cy="144" r="4" fill="#ffffff"/>
                        <circle cx="192" cy="144" r="10" fill="#1c2026"/>
                        <circle cx="192" cy="144" r="4" fill="#ffffff"/>
                    </g>

                    {{-- Colis --}}
                    <g class="ck-float-2">
                        <rect x="196" y="34" width="34" height="30" rx="5" fill="#ffb020"/>
                        <rect x="210" y="34" width="6" height="30" fill="#ffffff" fill-opacity="0.55"/>
                    </g>

                    <ellipse cx="150" cy="180" rx="92" ry="10" fill="#111111" fill-opacity="0.05"/>
                </svg>
            </div>
        </header>

        <form id="payment-form" novalidate>
            @csrf

            <div class="ck-grid">

                {{-- ---------- Colonne gauche : informations ---------- --}}
                <div class="ck-col">

                    {{-- Trajet --}}
                    <section class="ck-card">
                        <h2 class="ck-card-title"><span>1</span> Votre trajet</h2>

                        <div class="ck-route">
                            <div class="ck-route-stop">
                                <span class="ck-route-pin"><i class="fa-solid fa-box"></i></span>
                                <label class="ck-label" for="departure_address">
                                    Adresse de départ <span class="ck-required">*</span>
                                </label>
                                <input type="text" id="departure_address" class="ck-input"
                                       placeholder="Où le livreur récupère-t-il le colis ?" autocomplete="off">
                                <ul id="departureSuggestions" class="ck-suggestions"></ul>
                            </div>

                            <div class="ck-route-stop is-end">
                                <span class="ck-route-pin"><i class="fa-solid fa-flag-checkered"></i></span>
                                <label class="ck-label" for="arrival_address">
                                    Adresse d'arrivée <span class="ck-required">*</span>
                                </label>
                                <input type="text" id="arrival_address" class="ck-input"
                                       placeholder="Où le colis doit-il être livré ?" autocomplete="off">
                                <ul id="arrivalSuggestions" class="ck-suggestions"></ul>
                            </div>
                        </div>

                        <div id="routeResult" class="ck-note" hidden>
                            <i class="fa-solid fa-route"></i>
                            <span id="routeLabel"></span>
                        </div>

                        <div id="routeError" class="ck-error" role="alert"></div>
                    </section>

                    {{-- Coordonnees --}}
                    <section class="ck-card">
                        <h2 class="ck-card-title"><span>2</span> Vos coordonnées</h2>

                        <div class="ck-fields">
                            <div class="ck-field">
                                <label class="ck-label" for="ck-lastname">Nom</label>
                                <input type="text" id="ck-lastname" class="ck-input"
                                       value="{{ $user->lastname ?: $user->name }}" readonly>
                            </div>

                            <div class="ck-field">
                                <label class="ck-label" for="ck-firstname">Prénom</label>
                                <input type="text" id="ck-firstname" class="ck-input"
                                       value="{{ $user->firstname }}" readonly>
                            </div>

                            <div class="ck-field ck-field--full">
                                <label class="ck-label" for="phone">
                                    Téléphone <span class="ck-required">*</span>
                                </label>
                                <input type="tel" id="phone" class="ck-input" value="{{ $user->phone }}">
                                <small class="ck-hint">Le livreur vous joindra à ce numéro.</small>
                                <div id="phone-error" class="ck-error" role="alert"></div>
                            </div>
                        </div>
                    </section>

                    {{-- Paiement --}}
                    <section class="ck-card">
                        <h2 class="ck-card-title"><span>3</span> Paiement</h2>

                        <div class="ck-field ck-field--full">
                            <label class="ck-label" for="card-element">Carte bancaire</label>
                            <div id="card-element" class="ck-card-element"></div>
                            <div id="card-errors" class="ck-error" role="alert"></div>
                        </div>

                        <ul class="ck-trust">
                            <li><i class="fa-solid fa-shield-halved"></i> Paiement chiffré via Stripe</li>
                            <li><i class="fa-regular fa-credit-card"></i> Aucune donnée bancaire stockée</li>
                            <li><i class="fa-solid fa-rotate-left"></i> Remboursé si le livreur refuse</li>
                        </ul>
                    </section>
                </div>

                {{-- ---------- Colonne droite : recapitulatif ---------- --}}
                <aside class="ck-aside">
                    <div class="ck-recap">

                        <div class="ck-recap-ad">
                            <img src="{{ $image }}" alt="{{ $ad->title }}">
                            <span class="ck-recap-ad-info">
                                <strong>{{ $ad->title }}</strong>
                                @if ($livreur)
                                    <small>
                                        <i class="fa-solid fa-truck-fast"></i>
                                        {{ trim(($livreur->firstname ?? '') . ' ' . ($livreur->lastname ?? '')) ?: ($livreur->name ?? 'Livreur') }}
                                    </small>
                                @endif
                            </span>
                        </div>

                        <ul class="ck-recap-route">
                            <li class="is-empty" id="recapDepartureItem">
                                <i class="fa-solid fa-circle-dot"></i>
                                <span>
                                    <small>Départ</small>
                                    <strong id="recapDeparture">À renseigner</strong>
                                </span>
                            </li>
                            <li class="is-empty" id="recapArrivalItem">
                                <i class="fa-solid fa-location-dot"></i>
                                <span>
                                    <small>Arrivée</small>
                                    <strong id="recapArrival">À renseigner</strong>
                                </span>
                            </li>
                        </ul>

                        <div class="ck-line">
                            <span>Tarif du livreur</span>
                            <span>{{ number_format($rate, 2, ',', ' ') }} € / km</span>
                        </div>

                        <div class="ck-line">
                            <span>Distance</span>
                            <span id="recapDistance">—</span>
                        </div>

                        <div class="ck-line ck-line--total">
                            <span>Total</span>
                            <span id="grandTotal">—</span>
                        </div>

                        <button id="submit" type="submit" class="ck-submit" disabled>
                            <i class="fa-solid fa-lock"></i>
                            Payer &amp; Réserver
                        </button>

                        <p class="ck-recap-note">
                            <i class="fa-solid fa-circle-info"></i>
                            <span id="submitHint">Le montant s'affiche dès que les deux adresses sont choisies dans les suggestions.</span>
                        </p>
                    </div>
                </aside>

            </div>
        </form>

    </div>
</div>

@endsection

@push('scripts')
{{-- Memes versions que la page de location : la feuille intl-tel-input
     18.2.1 est deja chargee par layouts.main. --}}
<script src="https://js.stripe.com/v3/"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const AD_ID        = @json($ad->id);
    const RATE         = Number(@json($rate));
    const PAY_URL      = @json(route('bookings.pay'));
    // Stripe refuse un paiement inferieur a 50 centimes (meme seuil cote serveur).
    const MIN_TOTAL    = 0.5;

    const euros = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });
    const km    = (value) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 1 }).format(value) + ' km';
    const $     = (id) => document.getElementById(id);

    const form      = $('payment-form');
    const submitBtn = $('submit');
    const csrf      = form.querySelector('input[name=_token]').value;

    /* ------------------------------------------------------------------
       Telephone
       ------------------------------------------------------------------ */

    const phoneInput = $('phone');
    const iti = window.intlTelInput(phoneInput, {
        initialCountry: 'fr',
        separateDialCode: true,
        utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js'
    });

    phoneInput.addEventListener('input', () => { $('phone-error').textContent = ''; });

    /* ------------------------------------------------------------------
       Stripe
       ------------------------------------------------------------------ */

    const stripe   = Stripe(@json(config('services.stripe.key')));
    const elements = stripe.elements();

    const card = elements.create('card', {
        hidePostalCode: true,
        style: {
            base: {
                fontSize: '14px',
                color: '#111',
                fontFamily: 'inherit',
                '::placeholder': { color: '#9aa0a6' }
            },
            invalid: { color: '#b42318' }
        }
    });

    card.mount('#card-element');

    card.on('change', (event) => {
        $('card-errors').textContent = event.error ? event.error.message : '';
    });

    /* ------------------------------------------------------------------
       Trajet : deux adresses choisies dans les suggestions, puis distance
       routiere (OSRM) et prix au kilometre.
       ------------------------------------------------------------------ */

    const stops = {
        departure: { input: $('departure_address'), list: $('departureSuggestions'),
                     recap: $('recapDeparture'), item: $('recapDepartureItem'), place: null, timer: null, req: 0 },
        arrival:   { input: $('arrival_address'),   list: $('arrivalSuggestions'),
                     recap: $('recapArrival'),   item: $('recapArrivalItem'),   place: null, timer: null, req: 0 }
    };

    let route = null; // { distance, total } une fois le trajet calcule
    let routeReq = 0;

    function setRecap(stop, text) {
        stop.recap.textContent = text || 'À renseigner';
        stop.item.classList.toggle('is-empty', !text);
    }

    function resetRoute(message = '') {
        route = null;
        $('routeResult').hidden = true;
        $('routeError').textContent = message;
        $('recapDistance').textContent = '—';
        $('grandTotal').textContent = '—';
        $('submitHint').textContent = "Le montant s'affiche dès que les deux adresses sont choisies dans les suggestions.";
        submitBtn.disabled = true;
    }

    async function searchAddress(query) {
        const url = new URL('https://nominatim.openstreetmap.org/search');
        url.searchParams.set('format', 'jsonv2');
        url.searchParams.set('limit', '5');
        url.searchParams.set('q', query);

        const response = await fetch(url, { headers: { 'Accept-Language': 'fr' } });

        if (!response.ok) throw new Error('Recherche indisponible');

        return response.json();
    }

    function showSuggestions(stop, places) {
        stop.list.innerHTML = '';

        places.forEach((place) => {
            const li = document.createElement('li');
            li.textContent = place.display_name;
            li.addEventListener('click', () => choose(stop, place));
            stop.list.appendChild(li);
        });
    }

    function choose(stop, place) {
        stop.place = { label: place.display_name, lat: place.lat, lon: place.lon };
        stop.input.value = place.display_name;
        stop.list.innerHTML = '';
        setRecap(stop, place.display_name);

        calculateRoute();
    }

    Object.values(stops).forEach((stop) => {
        stop.input.addEventListener('input', () => {
            // Toute frappe invalide l'adresse choisie : le prix ne doit pas
            // porter sur un trajet que le client a modifie depuis.
            stop.place = null;
            setRecap(stop, stop.input.value.trim());
            resetRoute();

            clearTimeout(stop.timer);

            const query = stop.input.value.trim();

            if (query.length < 3) {
                stop.list.innerHTML = '';
                return;
            }

            // Une requete par pause de frappe, pas une par touche : Nominatim
            // limite le nombre d'appels.
            stop.timer = setTimeout(async () => {
                const req = ++stop.req;

                try {
                    const places = await searchAddress(query);
                    if (req === stop.req) showSuggestions(stop, places);
                } catch (e) {
                    if (req === stop.req) {
                        $('routeError').textContent = "La recherche d'adresse ne répond pas. Réessayez dans un instant.";
                    }
                }
            }, 350);
        });
    });

    document.addEventListener('click', (event) => {
        Object.values(stops).forEach((stop) => {
            if (!stop.input.contains(event.target) && !stop.list.contains(event.target)) {
                stop.list.innerHTML = '';
            }
        });
    });

    async function calculateRoute() {
        const a = stops.departure.place;
        const b = stops.arrival.place;

        if (!a || !b) return;

        if (a.lat === b.lat && a.lon === b.lon) {
            resetRoute("Les adresses de départ et d'arrivée doivent être différentes.");
            return;
        }

        const req = ++routeReq;

        resetRoute();
        $('submitHint').textContent = 'Calcul du trajet en cours…';

        try {
            const response = await fetch(
                `https://router.project-osrm.org/route/v1/driving/${a.lon},${a.lat};${b.lon},${b.lat}?overview=false`
            );
            const data = await response.json();

            if (req !== routeReq) return;

            if (!response.ok || data.code !== 'Ok' || !data.routes?.length) {
                throw new Error('Aucun itinéraire routier trouvé entre ces deux adresses.');
            }

            // Memes arrondis que BookingController::pay() : distance au
            // centieme de km, prix au centime.
            const distance = Math.round(data.routes[0].distance / 10) / 100;
            const total    = Math.round(distance * RATE * 100) / 100;

            if (total < MIN_TOTAL) {
                resetRoute('Ce trajet est trop court pour être payé en ligne (minimum 0,50 €).');
                return;
            }

            route = { distance, total };

            $('routeLabel').innerHTML =
                `<strong>${km(distance)}</strong> par la route · ${euros.format(RATE)} / km`;
            $('routeResult').hidden = false;
            $('routeError').textContent = '';

            $('recapDistance').textContent = km(distance);
            $('grandTotal').textContent = euros.format(total);
            $('submitHint').textContent = 'Le livreur confirme votre demande. En cas de refus, vous êtes intégralement remboursé.';

            submitBtn.disabled = false;
        } catch (error) {
            if (req === routeReq) {
                resetRoute(error.message || 'Impossible de calculer ce trajet.');
            }
        }
    }

    /* ------------------------------------------------------------------
       Paiement
       ------------------------------------------------------------------ */

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!route) return;

        if (!iti.isValidNumber()) {
            $('phone-error').textContent = 'Saisissez un numéro de téléphone valide.';
            phoneInput.focus();
            return;
        }

        // Le paiement part une seule fois : le bouton se verrouille le temps de l'aller-retour
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Paiement en cours...';

        const releaseButton = () => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-lock"></i> Payer &amp; Réserver';
        };

        const { paymentMethod, error } = await stripe.createPaymentMethod({ type: 'card', card });

        if (error) {
            $('card-errors').textContent = error.message;
            releaseButton();
            return;
        }

        try {
            const response = await fetch(PAY_URL, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({
                    payment_method: paymentMethod.id,
                    phone: iti.getNumber(),
                    ad_id: AD_ID,
                    departure_address: stops.departure.place.label,
                    arrival_address: stops.arrival.place.label,
                    delivery_distance: route.distance,
                    // Indicatif : le serveur recalcule le montant au tarif de l'annonce
                    finalPrice: route.total.toFixed(2)
                })
            });

            const data = await response.json().catch(() => ({}));

            if (data.success) {
                window.location.href = data.redirect;
                return;
            }

            $('card-errors').textContent = data.message || 'Le paiement a échoué.';
            releaseButton();
        } catch (e) {
            $('card-errors').textContent = "Le paiement n'a pas pu aboutir. Réessayez.";
            releaseButton();
        }
    });
});
</script>
@endpush
