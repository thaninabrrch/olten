@extends('layouts.main')

@section('title', 'Détail de l\'annonce - Olten.fr')

@section('content')

@php
$start = \Carbon\Carbon::parse(session('start_date'));
$end = \Carbon\Carbon::parse(session('end_date'));

$days = $start->diffInDays($end);
if ($days <= 0) $days = 1;

$baseTotal = $ad->price_per_day * $days;
@endphp

<h2>Finaliser votre réservation</h2>

<form id="payment-form">
    @csrf

    <div class="d-flex p-3 gap-3">

        <div class="w-50 border p-2 rounded">

            <div class="form-group">
                <label>Nom</label>
                <input type="text" value="{{ auth()->user()->lastname }}" readonly>
            </div>

            <div class="form-group">
                <label>Prénom</label>
                <input type="text" value="{{ auth()->user()->firstname }}" readonly>
            </div>

            <div class="form-group">
                <label>Téléphone</label>
                <input type="tel" id="phone" class="w-100" required>
                <input type="hidden" id="phone_full">
            </div>

            <div class="form-group">
                <label>Date début</label>
                <input type="date" value="{{ session('start_date') }}" readonly>
            </div>

            <div class="form-group">
                <label>Date fin</label>
                <input type="date" value="{{ session('end_date') }}" readonly>
            </div>

            <div class="form-group">
                <label>Jours</label>
                <input type="text" value="{{ $days }}" readonly>
            </div>

            <input type="hidden" id="sellerLat" value="{{ $ad->latitude }}">
            <input type="hidden" id="sellerLng" value="{{ $ad->longitude }}">

            <input type="hidden" id="deliveryRequested" value="0">
            <input type="hidden" id="deliveryCostInput" name="delivery_cost" value="0">
            <input type="hidden" id="deliveryDistInput" name="delivery_distance_km" value="0">
            <input type="hidden" id="deliveryAddrInput" name="delivery_address" value="">

            <div class="form-container">
                <div class="form-section-header">
                    <div class="form-section-icon d-flex align-content-center flex-wrap gap-1">
                        <i class="fa-solid fa-truck"></i>
                        <h5 class="form-section-title">Livraison</h5>
                    </div>
                </div>

                <div class="form-group d-flex gap-1">
                    <label class="form-label">Souhaitez-vous une livraison ?</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="livraisonToggle">
                        <label for="livraisonToggle" class="toggle-label"></label>
                    </div>
                </div>

                <div id="livraisonDetails" style="display:none;">
                    <div class="form-group">
                        <label class="form-label">Adresse de livraison</label>
                        <input type="text" name="address" id="adresseClient" class="form-input" placeholder="Entrez votre adresse..." autocomplete="off">
                        <ul id="adresseSuggestions" class="suggestions" style="list-style:none;padding:0;margin:0;"></ul>
                    </div>

                    <div id="distanceResult" style="display:none;" class="distance-result">
                        <span id="deliveryBaseLabel"></span>
                    </div>
                </div>
            </div>

            <div class="total-recap">

                <div class="total-line">
                    <span>Prix location ({{ $days }} jours)</span>
                    <span id="productTotal">{{ number_format($baseTotal, 2) }} €</span>
                </div>

                <div class="total-line" id="deliveryLine" style="display:none;">
                    <span>Livraison</span>
                    <span id="deliveryTotalDisplay">0.00 €</span>
                </div>

                <div class="total-line total-grand">
                    <span>Total</span>
                    <span id="grandTotal">{{ number_format($baseTotal, 2) }} €</span>
                </div>

            </div>

            <div class="form-group">
                <label>Prix total</label>
                <input type="text" id="finalPriceInput" value="{{ number_format($baseTotal, 2) }} €" readonly name="finalPrice">
            </div>

        </div>

        <div class="w-50 border p-2">

            <label>Carte bancaire</label>
            <div id="card-element"></div>

            <div id="card-errors" style="color:red;"></div>

            <button id="submit" class="btn-submit w-auto">Payer & Réserver</button>

        </div>

    </div>
</form>

<script src="https://js.stripe.com/v3/"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>

<script>
    console.log("Stripe key:", "{{ config('services.stripe.key') }}");
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
const card = elements.create("card");
card.mount("#card-element");

const form = document.getElementById("payment-form");

form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const {paymentMethod, error} = await stripe.createPaymentMethod({
        type: "card",
        card: card,
    });

    if (error) {
        document.getElementById("card-errors").textContent = error.message;
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
            alert(data.message);
        }
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
        li.style.cursor = 'pointer';

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
    finalPriceInput.value = total.toFixed(2) + ' €';

    document.getElementById('distanceResult').style.display = 'block';
}

function resetDelivery() {
    deliveryCost = 0;
    deliveryCostInput.value = 0;
    deliveryDistInput.value = 0;
    deliveryAddrInput.value = '';

    deliveryLine.style.display = 'none';

    grandTotal.textContent = BASE_TOTAL.toFixed(2) + ' €';
    finalPriceInput.value = BASE_TOTAL.toFixed(2) + ' €';
}
</script>

@endsection