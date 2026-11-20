{{-- resources/views/bookings/confirm-livraison.blade.php --}}

@extends('layouts.main')

@section('title', 'Finaliser votre livraison - Olten.fr')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/css/intlTelInput.css">

<style>
    .delivery-checkout {
        max-width: 1050px;
        margin: 40px auto;
        padding: 0 18px;
    }

    .checkout-title {
        margin-bottom: 25px;
    }

    .checkout-title h1 {
        font-size: 27px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .checkout-grid {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 25px;
        align-items: start;
    }

    .checkout-card {
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 14px;
        padding: 25px;
        margin-bottom: 20px;
    }

    .checkout-card h2 {
        font-size: 19px;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .checkout-field {
        margin-bottom: 18px;
        position: relative;
    }

    .checkout-field label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .checkout-field input {
        width: 100%;
        min-height: 48px;
        border: 1px solid #ddd;
        border-radius: 9px;
        padding: 11px 13px;
        outline: none;
    }

    .checkout-field input:focus {
        border-color: #e85b91;
        box-shadow: 0 0 0 3px rgba(232, 91, 145, .1);
    }

    .address-suggestions {
        display: none;
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 50;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 9px;
        box-shadow: 0 8px 20px rgba(0,0,0,.08);
        max-height: 220px;
        overflow-y: auto;
    }

    .address-suggestion {
        padding: 12px 14px;
        cursor: pointer;
        font-size: 14px;
        border-bottom: 1px solid #f1f1f1;
    }

    .address-suggestion:last-child {
        border-bottom: none;
    }

    .address-suggestion:hover {
        background: #fff3f7;
    }

    .address-help {
        display: block;
        color: #777;
        font-size: 12px;
        margin-top: 6px;
    }

    .route-result {
        display: none;
        background: #f7f8fa;
        border-radius: 10px;
        padding: 16px;
        margin-top: 15px;
    }

    .route-result-row {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        margin: 8px 0;
    }

    .route-result-row strong {
        text-align: right;
    }

    .route-error {
        color: #d93025;
        font-size: 13px;
        margin-top: 10px;
    }

    .summary-line {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        margin: 13px 0;
        font-size: 14px;
    }

    .summary-line.total {
        border-top: 1px solid #eee;
        padding-top: 17px;
        margin-top: 18px;
        font-size: 19px;
        font-weight: 700;
    }

    .summary-address {
        max-width: 65%;
        text-align: right;
        overflow-wrap: anywhere;
    }

    #card-element {
        border: 1px solid #ddd;
        border-radius: 9px;
        padding: 15px;
        min-height: 48px;
        background: #fff;
    }

    #card-errors {
        color: #d93025;
        font-size: 13px;
        margin-top: 8px;
    }

    .phone-wrapper {
        width: 100%;
    }

    .phone-wrapper input {
        width: 100%;
    }

    .iti {
        width: 100%;
    }

    .checkout-button {
        display: block;
        width: 100%;
        border: none;
        border-radius: 10px;
        padding: 15px 20px;
        background: #e85b91;
        color: #fff;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: .2s;
    }

    .checkout-button:hover {
        background: #d94880;
    }

    .checkout-button:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    .checkout-notice {
        color: #777;
        font-size: 12px;
        margin-top: 12px;
        text-align: center;
    }

    .ad-summary-title {
        font-weight: 700;
        font-size: 16px;
        margin-bottom: 8px;
    }

    .ad-summary-price {
        color: #e85b91;
        font-weight: 700;
        font-size: 19px;
    }

    @media(max-width: 768px) {
        .checkout-grid {
            grid-template-columns: 1fr;
        }

        .checkout-card {
            padding: 18px;
        }

        .checkout-title h1 {
            font-size: 22px;
        }
    }
</style>
@endpush

@section('content')

<div class="delivery-checkout">

    <div class="checkout-title">
        <h1>Finaliser votre livraison</h1>
        <p>Renseignez votre trajet pour calculer le prix de votre livraison.</p>
    </div>

    <div class="checkout-grid">

        {{-- COLONNE GAUCHE --}}
        <div>

            {{-- ADRESSES --}}
            <div class="checkout-card">
                <h2>1. Votre trajet</h2>

                <div class="checkout-field">
                    <label for="departure_address">
                        Adresse de départ
                    </label>

                    <input
                        type="text"
                        id="departure_address"
                        placeholder="Saisissez l'adresse de départ"
                        autocomplete="off"
                    >

                    <div id="departureSuggestions" class="address-suggestions"></div>

                    <span class="address-help">
                        Indiquez l'adresse où le livreur devra récupérer le colis.
                    </span>
                </div>

                <div class="checkout-field">
                    <label for="arrival_address">
                        Adresse d'arrivée
                    </label>

                    <input
                        type="text"
                        id="arrival_address"
                        placeholder="Saisissez l'adresse de livraison"
                        autocomplete="off"
                    >

                    <div id="addressSuggestions" class="address-suggestions"></div>

                    <span class="address-help">
                        Indiquez l'adresse où le colis devra être livré.
                    </span>
                </div>

                <div id="routeError" class="route-error"></div>

                <div id="distanceResult" class="route-result">
                    <div class="route-result-row">
                        <span>Distance estimée</span>
                        <strong id="distanceDisplay">-- km</strong>
                    </div>

                    <div class="route-result-row">
                        <span>Tarif par kilomètre</span>
                        <strong>{{ number_format((float) $ad->price_per_km, 2, ',', ' ') }} €</strong>
                    </div>

                    <div class="route-result-row">
                        <span>Coût de la livraison</span>
                        <strong id="distanceLabel">-- €</strong>
                    </div>
                </div>
            </div>

            {{-- TELEPHONE --}}
            <div class="checkout-card">
                <h2>2. Vos coordonnées</h2>

                <div class="checkout-field">
                    <label for="phone">Numéro de téléphone</label>

                    <div class="phone-wrapper">
                        <input
                            type="tel"
                            id="phone"
                            placeholder="Votre numéro de téléphone"
                        >
                    </div>
                </div>
            </div>

            {{-- PAIEMENT --}}
            <div class="checkout-card">
                <h2>3. Paiement</h2>

                <div class="checkout-field">
                    <label>Informations de votre carte bancaire</label>

                    <div id="card-element"></div>

                    <div id="card-errors" role="alert"></div>
                </div>

                <p class="checkout-notice">
                    Le paiement est traité de manière sécurisée.
                </p>
            </div>

        </div>

        {{-- COLONNE DROITE --}}
        <div>

            <div class="checkout-card">
                <h2>Récapitulatif</h2>

                <div class="ad-summary-title">
                    {{ $ad->title }}
                </div>

                <div class="ad-summary-price">
                    {{ number_format((float) $ad->price_per_km, 2, ',', ' ') }} € / km
                </div>

                <hr style="border:0;border-top:1px solid #eee;margin:20px 0;">

                <div class="summary-line">
                    <span>Départ</span>
                    <strong class="summary-address" id="recapDeparture">
                        À renseigner
                    </strong>
                </div>

                <div class="summary-line">
                    <span>Arrivée</span>
                    <strong class="summary-address" id="recapArrival">
                        À renseigner
                    </strong>
                </div>

                <div class="summary-line">
                    <span>Distance</span>
                    <strong id="recapDistance">-- km</strong>
                </div>

                <div class="summary-line">
                    <span>Tarif kilométrique</span>
                    <strong>{{ number_format((float) $ad->price_per_km, 2, ',', ' ') }} € / km</strong>
                </div>

                <div class="summary-line total">
                    <span>Total</span>
                    <strong id="grandTotal">-- €</strong>
                </div>

                <button
                    type="button"
                    id="submitBtn"
                    class="checkout-button"
                    disabled
                >
                    Procéder au paiement
                </button>

                <p class="checkout-notice">
                    Le montant sera calculé après validation des deux adresses.
                </p>
            </div>

        </div>
    </div>
</div>

{{-- FORMULAIRE DE DONNÉES --}}
<input type="hidden" id="departure_lat">
<input type="hidden" id="departure_lng">

<input type="hidden" id="arrival_lat">
<input type="hidden" id="arrival_lng">

<input type="hidden" id="delivery_distance" value="0">
<input type="hidden" id="delivery_cost" value="0">
<input type="hidden" id="finalPriceInput" value="0">

@endsection

@push('scripts')

<script src="https://js.stripe.com/v3/"></script>

<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/js/intlTelInput.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | CONFIGURATION
    |--------------------------------------------------------------------------
    */

    const adId = @json($ad->id);
    const pricePerKm = Number(@json((float) $ad->price_per_km));

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    const departureInput = document.getElementById('departure_address');
    const arrivalInput = document.getElementById('arrival_address');

    const departureSuggestions = document.getElementById('departureSuggestions');
    const arrivalSuggestions = document.getElementById('addressSuggestions');

    const departureLatInput = document.getElementById('departure_lat');
    const departureLngInput = document.getElementById('departure_lng');

    const arrivalLatInput = document.getElementById('arrival_lat');
    const arrivalLngInput = document.getElementById('arrival_lng');

    const distanceInput = document.getElementById('delivery_distance');
    const costInput = document.getElementById('delivery_cost');

    const submitBtn = document.getElementById('submitBtn');

    let selectedDeparture = false;
    let selectedDestination = false;

    let currentDistance = 0;
    let currentCost = 0;

    let departureRequestId = 0;
    let arrivalRequestId = 0;

    /*
    |--------------------------------------------------------------------------
    | TÉLÉPHONE
    |--------------------------------------------------------------------------
    */

    const phoneInput = document.querySelector('#phone');

    const iti = window.intlTelInput(phoneInput, {
        initialCountry: 'dz',
        preferredCountries: ['dz', 'fr'],
        separateDialCode: true,
        utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/js/utils.js'
    });

    /*
    |--------------------------------------------------------------------------
    | STRIPE
    |--------------------------------------------------------------------------
    */

    const stripe = Stripe(@json(config('services.stripe.key')));

    const elements = stripe.elements();

    const card = elements.create('card', {
        hidePostalCode: true,
        style: {
            base: {
                fontSize: '16px',
                color: '#32325d',
                fontFamily: 'Arial, sans-serif',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#d93025'
            }
        }
    });

    card.mount('#card-element');

    card.on('change', function(event) {
        document.getElementById('card-errors').textContent =
            event.error ? event.error.message : '';
    });

    /*
    |--------------------------------------------------------------------------
    | RÉINITIALISER LE CALCUL
    |--------------------------------------------------------------------------
    */

    function resetCalculation() {
        currentDistance = 0;
        currentCost = 0;

        distanceInput.value = 0;
        costInput.value = 0;

        document.getElementById('finalPriceInput').value = 0;

        document.getElementById('distanceResult').style.display = 'none';
        document.getElementById('distanceLabel').textContent = '';
        document.getElementById('distanceDisplay').textContent = '-- km';

        document.getElementById('recapDistance').textContent = '-- km';
        document.getElementById('grandTotal').textContent = '-- €';

        document.getElementById('routeError').textContent = '';

        submitBtn.disabled = true;
    }

    /*
    |--------------------------------------------------------------------------
    | GÉOCODAGE NOMINATIM
    |--------------------------------------------------------------------------
    */

    async function searchAddress(query) {

        const url = new URL('https://nominatim.openstreetmap.org/search');

        url.searchParams.set('format', 'jsonv2');
        url.searchParams.set('addressdetails', '1');
        url.searchParams.set('limit', '5');
        url.searchParams.set('q', query);

        const response = await fetch(url.toString(), {
            headers: {
                'Accept-Language': 'fr'
            }
        });

        if (!response.ok) {
            throw new Error('Impossible de rechercher cette adresse.');
        }

        return await response.json();
    }

    /*
    |--------------------------------------------------------------------------
    | AFFICHER LES SUGGESTIONS
    |--------------------------------------------------------------------------
    */

    function displaySuggestions(results, container, onSelect) {

        container.innerHTML = '';

        if (!results.length) {
            container.style.display = 'none';
            return;
        }

        results.forEach(function(place) {

            const item = document.createElement('div');

            item.className = 'address-suggestion';
            item.textContent = place.display_name;

            item.addEventListener('click', function() {
                onSelect(place);
                container.style.display = 'none';
                container.innerHTML = '';
            });

            container.appendChild(item);
        });

        container.style.display = 'block';
    }

    /*
    |--------------------------------------------------------------------------
    | SÉLECTION DE L'ADRESSE DE DÉPART
    |--------------------------------------------------------------------------
    */

    function selectDeparture(place) {

        departureInput.value = place.display_name;

        departureLatInput.value = place.lat;
        departureLngInput.value = place.lon;

        selectedDeparture = true;

        document.getElementById('recapDeparture').textContent =
            place.display_name;

        resetCalculation();

        if (selectedDestination) {
            calculateRoute();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SÉLECTION DE L'ADRESSE D'ARRIVÉE
    |--------------------------------------------------------------------------
    */

    function selectArrival(place) {

        arrivalInput.value = place.display_name;

        arrivalLatInput.value = place.lat;
        arrivalLngInput.value = place.lon;

        selectedDestination = true;

        document.getElementById('recapArrival').textContent =
            place.display_name;

        resetCalculation();

        if (selectedDeparture) {
            calculateRoute();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | AUTOCOMPLÉTION DÉPART
    |--------------------------------------------------------------------------
    */

    departureInput.addEventListener('input', async function() {

        const query = this.value.trim();
        const requestId = ++departureRequestId;

        selectedDeparture = false;

        departureLatInput.value = '';
        departureLngInput.value = '';

        document.getElementById('recapDeparture').textContent =
            query || 'À renseigner';

        resetCalculation();

        if (query.length < 3) {
            departureSuggestions.style.display = 'none';
            return;
        }

        try {
            const results = await searchAddress(query);

            if (requestId !== departureRequestId) return;

            displaySuggestions(
                results,
                departureSuggestions,
                selectDeparture
            );

        } catch (error) {
            if (requestId === departureRequestId) {
                document.getElementById('routeError').textContent =
                    'Erreur lors de la recherche de l’adresse de départ.';
            }
        }
    });

    /*
    |--------------------------------------------------------------------------
    | AUTOCOMPLÉTION ARRIVÉE
    |--------------------------------------------------------------------------
    */

    arrivalInput.addEventListener('input', async function() {

        const query = this.value.trim();
        const requestId = ++arrivalRequestId;

        selectedDestination = false;

        arrivalLatInput.value = '';
        arrivalLngInput.value = '';

        document.getElementById('recapArrival').textContent =
            query || 'À renseigner';

        resetCalculation();

        if (query.length < 3) {
            arrivalSuggestions.style.display = 'none';
            return;
        }

        try {
            const results = await searchAddress(query);

            if (requestId !== arrivalRequestId) return;

            displaySuggestions(
                results,
                arrivalSuggestions,
                selectArrival
            );

        } catch (error) {
            if (requestId === arrivalRequestId) {
                document.getElementById('routeError').textContent =
                    'Erreur lors de la recherche de l’adresse d’arrivée.';
            }
        }
    });

    /*
    |--------------------------------------------------------------------------
    | CALCULER LA DISTANCE AVEC OSRM
    |--------------------------------------------------------------------------
    */

    async function calculateRoute() {

        if (!selectedDeparture || !selectedDestination) {
            return;
        }

        const startLat = departureLatInput.value;
        const startLng = departureLngInput.value;

        const endLat = arrivalLatInput.value;
        const endLng = arrivalLngInput.value;

        if (
            !startLat || !startLng ||
            !endLat || !endLng
        ) {
            return;
        }

        if (
            startLat === endLat &&
            startLng === endLng
        ) {
            document.getElementById('routeError').textContent =
                'Les adresses de départ et d’arrivée doivent être différentes.';

            resetCalculation();
            return;
        }

        submitBtn.disabled = true;

        document.getElementById('routeError').textContent =
            'Calcul de votre trajet en cours...';

        try {

            const url =
                `https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${endLng},${endLat}?overview=false`;

            const response = await fetch(url);

            if (!response.ok) {
                throw new Error('Erreur lors du calcul du trajet.');
            }

            const data = await response.json();

            if (
                data.code !== 'Ok' ||
                !data.routes ||
                !data.routes.length
            ) {
                throw new Error('Aucun itinéraire trouvé entre ces deux adresses.');
            }

            const route = data.routes[0];

            // Distance en kilomètres, arrondie à deux décimales.
            const distance = route.distance / 1000;
            const roundedDistance = Math.round(distance * 100) / 100;

            const cost = roundedDistance * pricePerKm;
            const roundedCost = Math.round(cost * 100) / 100;

            currentDistance = roundedDistance;
            currentCost = roundedCost;

            distanceInput.value = roundedDistance;
            costInput.value = roundedCost;

            document.getElementById('finalPriceInput').value =
                roundedCost.toFixed(2);

            document.getElementById('distanceDisplay').textContent =
                roundedDistance.toFixed(2) + ' km';

            document.getElementById('distanceLabel').textContent =
                roundedCost.toFixed(2) + ' €';

            document.getElementById('recapDistance').textContent =
                roundedDistance.toFixed(2) + ' km';

            document.getElementById('grandTotal').textContent =
                roundedCost.toFixed(2) + ' €';

            document.getElementById('distanceResult').style.display =
                'block';

            document.getElementById('routeError').textContent = '';

            submitBtn.disabled = false;

        } catch (error) {

            resetCalculation();

            document.getElementById('routeError').textContent =
                error.message || 'Impossible de calculer votre trajet.';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FERMER LES SUGGESTIONS EN CLIQUANT AILLEURS
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', function(event) {

        if (
            !departureInput.contains(event.target) &&
            !departureSuggestions.contains(event.target)
        ) {
            departureSuggestions.style.display = 'none';
        }

        if (
            !arrivalInput.contains(event.target) &&
            !arrivalSuggestions.contains(event.target)
        ) {
            arrivalSuggestions.style.display = 'none';
        }
    });

    /*
    |--------------------------------------------------------------------------
    | PAIEMENT
    |--------------------------------------------------------------------------
    */

    submitBtn.addEventListener('click', async (e) => {

        e.preventDefault();

        if (!selectedDeparture || !selectedDestination) {
            alert('Veuillez sélectionner les deux adresses.');
            return;
        }

        // if (!currentDistance || !currentCost) {
        //     alert('Veuillez attendre le calcul de votre trajet.');
        //     return;
        // }

        if (!iti.isValidNumber()) {
            document.getElementById('card-errors').textContent =
                'Veuillez saisir un numéro de téléphone valide.';
            phoneInput.focus();
            return;
        }

        // Verrouiller le bouton pendant le paiement
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Traitement du paiement...';

        const releaseButton = () => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Procéder au paiement';
        };

        try {

            /*
            |--------------------------------------------------------------------------
            | 1. CRÉATION DU MOYEN DE PAIEMENT STRIPE
            |--------------------------------------------------------------------------
            */

            const { paymentMethod, error } = await stripe.createPaymentMethod({
                type: 'card',
                card: card
            });

            if (error) {
                document.getElementById('card-errors').textContent =
                    error.message;

                releaseButton();
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | 2. ENVOI AU CONTRÔLEUR COMMUN bookings.pay
            |--------------------------------------------------------------------------
            */

            const response = await fetch(@json(route('bookings.pay')), {

                method: 'POST',

                credentials: 'same-origin',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },

                body: JSON.stringify({

                    payment_method: paymentMethod.id,

                    phone: iti.getNumber(),

                    ad_id: adId,

                    // Livraison
                    delivery_requested: 1,

                    departure_address: departureInput.value,
                    arrival_address: arrivalInput.value,

                    delivery_address: arrivalInput.value,

                    departure_latitude: departureLatInput.value,
                    departure_longitude: departureLngInput.value,

                    arrival_latitude: arrivalLatInput.value,
                    arrival_longitude: arrivalLngInput.value,

                    delivery_distance: currentDistance,
                    delivery_cost: currentCost,

                    // Montant total de la livraison
                    finalPrice: document.getElementById('finalPriceInput').value

                })
            });

            const data = await response.json();

            /*
            |--------------------------------------------------------------------------
            | 3. RÉPONSE DU CONTRÔLEUR
            |--------------------------------------------------------------------------
            */

            if (data.success) {

                window.location.href = data.redirect;

            } else {

                document.getElementById('card-errors').textContent =
                    data.message || 'Le paiement a échoué.';

                releaseButton();
            }

        } catch (error) {

            console.error(error);

            document.getElementById('card-errors').textContent =
                "Le paiement n'a pas pu aboutir. Réessayez.";

            releaseButton();
        }
    });

});
</script>

@endpush