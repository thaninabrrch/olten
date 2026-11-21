@extends('layouts.main')

@section('title', 'Finaliser votre réservation - Olten.fr')

@php
    $user = auth()->user();

    $start = \Carbon\Carbon::parse(session('start_date'));
    $end   = \Carbon\Carbon::parse(session('end_date'));

    $days = $start->diffInDays($end);
    if ($days <= 0) $days = 1;

    $baseTotal = $ad->price_per_day * $days;

    $image = $ad->images->first()
        ? asset('storage/' . $ad->images->first()->path)
        : asset('assets/images/no-image.jpg');
@endphp

@section('content')

<div class="ck">
    <div class="ck-wrap">

        {{-- Fil de progression --}}
        <ol class="ck-steps">
            <li class="is-done">
                <span class="ck-step-dot"><i class="fa-solid fa-check"></i></span>
                <span class="ck-step-text">
                    <strong>Dates choisies</strong>
                    <small>{{ $start->translatedFormat('d M') }} → {{ $end->translatedFormat('d M Y') }}</small>
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
                    <small>Réservation envoyée au propriétaire</small>
                </span>
            </li>
        </ol>

        <header class="ck-head">
            <div>
                <span class="ck-eyebrow"><i class="fa-solid fa-lock"></i> Paiement sécurisé</span>
                <h1 class="ck-title">Finaliser votre <em>réservation</em></h1>
                <p class="ck-lead">
                    Vérifiez vos informations, choisissez la livraison si vous en avez besoin,
                    puis réglez en toute sécurité. Vous ne serez débité qu'une seule fois.
                </p>
            </div>

            <div class="ck-illu">
                <svg viewBox="0 0 300 210" fill="none" xmlns="http://www.w3.org/2000/svg"
                     role="img" aria-label="Illustration : paiement sécurisé">
                    <circle cx="150" cy="105" r="92" fill="#ff3c00" fill-opacity="0.07"/>
                    <circle cx="150" cy="105" r="68" fill="#ff3c00" fill-opacity="0.06"/>

                    <g class="ck-twinkle" fill="#ffb020">
                        <circle cx="34" cy="46" r="4"/>
                        <circle cx="268" cy="164" r="5"/>
                    </g>

                    {{-- Carte bancaire --}}
                    <g class="ck-float">
                        <rect x="58" y="62" width="150" height="94" rx="14" fill="#1c2026"/>
                        <rect x="58" y="86" width="150" height="17" fill="#000000" fill-opacity="0.35"/>
                        <rect x="74" y="118" width="46" height="8" rx="4" fill="#ffffff" fill-opacity="0.55"/>
                        <rect x="128" y="118" width="30" height="8" rx="4" fill="#ffffff" fill-opacity="0.3"/>
                        <rect x="74" y="136" width="62" height="7" rx="3.5" fill="#ffffff" fill-opacity="0.22"/>
                        <circle cx="180" cy="136" r="11" fill="#ff3c00"/>
                        <circle cx="193" cy="136" r="11" fill="#ffb020" fill-opacity="0.85"/>
                    </g>

                    {{-- Cadenas --}}
                    <g class="ck-float-2">
                        <circle cx="212" cy="72" r="40" fill="#ffffff"/>
                        <circle cx="212" cy="72" r="32" fill="#1a9d5c"/>
                        <path d="M200 68 v-8 a12 12 0 0 1 24 0 v8"
                              stroke="#ffffff" stroke-width="5" fill="none" stroke-linecap="round"/>
                        <rect x="197" y="68" width="30" height="22" rx="5" fill="#ffffff"/>
                        <circle cx="212" cy="77" r="3.4" fill="#1a9d5c"/>
                    </g>

                    <ellipse cx="150" cy="180" rx="92" ry="10" fill="#111111" fill-opacity="0.05"/>
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
                                <input type="tel" id="phone" class="ck-input" required>
                                <input type="hidden" id="phone_full">
                                <small class="ck-hint">Le propriétaire vous joindra à ce numéro.</small>
                            </div>
                        </div>
                    </section>

                    {{-- Livraison --}}
                    <section class="ck-card">
                        <h2 class="ck-card-title"><span>2</span> Livraison</h2>

                        <label class="ck-switch" for="livraisonToggle">
                            <span class="ck-switch-text">
                                <strong>Souhaitez-vous une livraison ?</strong>
                                <small>
                                    @if ($ad->price_per_km)
                                        Facturée {{ number_format((float) $ad->price_per_km, 2, ',', ' ') }} € par kilomètre.
                                    @else
                                        Sinon, vous récupérez le bien sur place.
                                    @endif
                                </small>
                            </span>

                            <span class="ck-switch-box">
                                <input type="checkbox" id="livraisonToggle">
                                <span class="ck-switch-track"><span class="ck-switch-knob"></span></span>
                            </span>
                        </label>

                        <div id="livraisonDetails" style="display:none;">
                            <div class="ck-field ck-field--full" style="margin-top:16px;">
                                <label class="ck-label" for="adresseClient">Adresse de livraison</label>
                                <input type="text" name="address" id="adresseClient" class="ck-input"
                                       placeholder="Commencez à saisir votre adresse..." autocomplete="off">
                                <ul id="adresseSuggestions" class="ck-suggestions"></ul>
                            </div>

                            <div id="distanceResult" style="display:none;" class="ck-note">
                                <i class="fa-solid fa-route"></i>
                                <span id="deliveryBaseLabel"></span>
                            </div>
                        </div>

                        <input type="hidden" id="sellerLat" value="{{ $ad->latitude }}">
                        <input type="hidden" id="sellerLng" value="{{ $ad->longitude }}">
                        <input type="hidden" id="deliveryRequested" value="0">
                        <input type="hidden" id="deliveryCostInput" name="delivery_cost" value="0">
                        <input type="hidden" id="deliveryDistInput" name="delivery_distance_km" value="0">
                        <input type="hidden" id="deliveryAddrInput" name="delivery_address" value="">
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
                            <li><i class="fa-solid fa-receipt"></i> Reçu envoyé par e-mail</li>
                        </ul>
                    </section>
                </div>

                {{-- ---------- Colonne droite : récapitulatif ---------- --}}
                <aside class="ck-aside">
                    <div class="ck-recap">

                        <div class="ck-recap-ad">
                            <img src="{{ $image }}" alt="{{ $ad->title }}">
                            <span class="ck-recap-ad-info">
                                <strong>{{ $ad->title }}</strong>
                                @if ($ad->address)
                                    <small><i class="fa-solid fa-location-dot"></i> {{ $ad->address }}</small>
                                @endif
                            </span>
                        </div>

                        <div class="ck-recap-dates">
                            <span>
                                <small>Début</small>
                                <strong>{{ $start->translatedFormat('d M Y') }}</strong>
                            </span>
                            <i class="fa-solid fa-arrow-right"></i>
                            <span>
                                <small>Fin</small>
                                <strong>{{ $end->translatedFormat('d M Y') }}</strong>
                            </span>
                        </div>

                        <div class="ck-line">
                            <span>Prix location ({{ $days }} jour{{ $days > 1 ? 's' : '' }})</span>
                            <span id="productTotal">{{ number_format($baseTotal, 2, ',', ' ') }} €</span>
                        </div>

                        <div class="ck-line" id="deliveryLine" style="display:none;">
                            <span>Livraison</span>
                            <span id="deliveryTotalDisplay">0.00 €</span>
                        </div>

                        <div class="ck-line ck-line--total">
                            <span>Total</span>
                            <span id="grandTotal">{{ number_format($baseTotal, 2, ',', ' ') }} €</span>
                        </div>

                        {{-- Montant transmis au paiement : valeur brute, sans separateur
                             de milliers ni symbole, pour rester castable cote serveur --}}
                        <input type="hidden" id="finalPriceInput" name="finalPrice"
                               value="{{ number_format($baseTotal, 2, '.', '') }}">

                        <button id="submit" type="submit" class="ck-submit">
                            <i class="fa-solid fa-lock"></i>
                            Payer &amp; Réserver
                        </button>

                        <p class="ck-recap-note">
                            <i class="fa-solid fa-circle-info"></i>
                            Le propriétaire dispose de 48 h pour confirmer. En cas de refus,
                            vous êtes intégralement remboursé.
                        </p>
                    </div>

                    {{-- Champs restés en lecture seule, conservés pour référence --}}
                    <div class="ck-mini">
                        <span><small>Jours</small><strong>{{ $days }}</strong></span>
                        <span><small>Prix / jour</small><strong>{{ number_format((float) $ad->price_per_day, 2, ',', ' ') }} €</strong></span>
                    </div>
                </aside>

            </div>
        </form>

    </div>
</div>

{{-- Styles du tunnel de paiement, partages avec la reservation d'une
     livraison (bookings/confirm-livraison) : assets/css/checkout.css --}}
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/checkout.css') }}?v={{ @filemtime(public_path('assets/css/checkout.css')) ?: 1 }}">
@endpush

<script src="https://js.stripe.com/v3/"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>

<script>
    const phoneInput = document.querySelector("#phone");
    const iti = window.intlTelInput(phoneInput, {
        initialCountry: "fr",
        separateDialCode: true,
        utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js"
    });

    phoneInput.addEventListener("blur", () => {
        document.getElementById("phone_full").value = iti.getNumber();
    });

    const stripe = Stripe("{{ config('services.stripe.key') }}");
    const elements = stripe.elements();

    const card = elements.create("card", {
        style: {
            base: {
                fontSize: "14px",
                color: "#111",
                fontFamily: "inherit",
                "::placeholder": { color: "#9aa0a6" }
            },
            invalid: { color: "#b42318" }
        }
    });

    card.mount("#card-element");

    // Erreur de saisie remontee au fil de la frappe plutot qu'a l'envoi
    card.on("change", (event) => {
        document.getElementById("card-errors").textContent = event.error ? event.error.message : "";
    });

    const form = document.getElementById("payment-form");
    const submitBtn = document.getElementById("submit");

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        // Le paiement part une seule fois : le bouton se verrouille le temps de l'aller-retour
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Paiement en cours...';

        const releaseButton = () => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-lock"></i> Payer &amp; Réserver';
        };

        const {paymentMethod, error} = await stripe.createPaymentMethod({
            type: "card",
            card: card,
        });

        if (error) {
            document.getElementById("card-errors").textContent = error.message;
            releaseButton();
            return;
        }

        fetch("{{ route('bookings.pay') }}", {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('input[name=_token]').value
            },
            body: JSON.stringify({
                payment_method: paymentMethod.id,
                phone: document.getElementById("phone_full").value,
                start_date: "{{ session('start_date') }}",
                end_date: "{{ session('end_date') }}",
                ad_id: "{{ $ad->id }}",
                finalPrice: document.getElementById("finalPriceInput").value,
                delivery_requested: deliveryRequested.value,
                delivery_cost: deliveryCostInput.value,
                delivery_distance: deliveryDistInput.value,
                delivery_address: deliveryAddrInput.value
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                document.getElementById("card-errors").textContent = data.message || "Le paiement a échoué.";
                releaseButton();
            }
        })
        .catch(() => {
            document.getElementById("card-errors").textContent = "Le paiement n'a pas pu aboutir. Réessayez.";
            releaseButton();
        });
    });

    const BASE_TOTAL = {{ $baseTotal }};
    let deliveryCost = 0;

    const sellerLat = parseFloat(document.getElementById('sellerLat').value);
    const sellerLng = parseFloat(document.getElementById('sellerLng').value);

    const livraisonToggle = document.getElementById('livraisonToggle');
    const livraisonDetails = document.getElementById('livraisonDetails');
    const adresseInput = document.getElementById('adresseClient');

    const deliveryRequested = document.getElementById('deliveryRequested');
    const deliveryCostInput = document.getElementById('deliveryCostInput');
    const deliveryDistInput = document.getElementById('deliveryDistInput');
    const deliveryAddrInput = document.getElementById('deliveryAddrInput');

    const productTotal = document.getElementById('productTotal');
    const deliveryLine = document.getElementById('deliveryLine');
    const deliveryTotalDisplay = document.getElementById('deliveryTotalDisplay');
    const grandTotal = document.getElementById('grandTotal');
    const finalPriceInput = document.getElementById('finalPriceInput');

    livraisonToggle.addEventListener('change', () => {
        livraisonDetails.style.display = livraisonToggle.checked ? 'block' : 'none';
        deliveryRequested.value = livraisonToggle.checked ? 1 : 0;
        if (!livraisonToggle.checked) resetDelivery();
    });

    adresseInput.addEventListener('input', async () => {
        const q = adresseInput.value.trim();
        if (q.length < 3) return;

        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${q}&limit=5`);
        const data = await res.json();

        const list = document.getElementById('adresseSuggestions');
        list.innerHTML = '';

        data.forEach(p => {
            const li = document.createElement('li');
            li.textContent = p.display_name;

            li.addEventListener('click', () => {
                adresseInput.value = p.display_name;
                deliveryAddrInput.value = p.display_name;
                list.innerHTML = '';
                calculateDelivery(parseFloat(p.lat), parseFloat(p.lon));
            });

            list.appendChild(li);
        });
    });

    function haversineKm(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI/180;
        const dLon = (lon2 - lon1) * Math.PI/180;

        const a =
            Math.sin(dLat/2)**2 +
            Math.cos(lat1*Math.PI/180) *
            Math.cos(lat2*Math.PI/180) *
            Math.sin(dLon/2)**2;

        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    function calculateDelivery(lat, lon) {

        const dist = haversineKm(sellerLat, sellerLng, lat, lon);

        const pricePerKm = {{ $ad->price_per_km ?? 1 }};
        deliveryCost = Math.ceil(dist) * pricePerKm;

        const total = BASE_TOTAL + deliveryCost;

        deliveryCostInput.value = deliveryCost;
        deliveryDistInput.value = dist.toFixed(2);

        deliveryLine.style.display = 'flex';
        deliveryTotalDisplay.textContent = deliveryCost.toFixed(2) + ' €';

        grandTotal.textContent = total.toFixed(2) + ' €';
        finalPriceInput.value = total.toFixed(2);

        // Detail de la distance et du tarif applique
        document.getElementById('deliveryBaseLabel').textContent =
            `${dist.toFixed(1)} km depuis le lieu de retrait · ${pricePerKm.toFixed(2)} € / km`;

        document.getElementById('distanceResult').style.display = 'flex';
    }

    function resetDelivery() {
        deliveryCost = 0;
        deliveryCostInput.value = 0;
        deliveryDistInput.value = 0;
        deliveryAddrInput.value = '';

        deliveryLine.style.display = 'none';
        document.getElementById('distanceResult').style.display = 'none';
        document.getElementById('adresseSuggestions').innerHTML = '';

        grandTotal.textContent = BASE_TOTAL.toFixed(2) + ' €';
        finalPriceInput.value = BASE_TOTAL.toFixed(2);
    }
</script>

@endsection
