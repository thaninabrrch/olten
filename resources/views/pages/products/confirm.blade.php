@extends('layouts.main')

@section('title', 'Finaliser votre achat - Olten.fr')

@section('content')

<h2>Finaliser votre achat</h2>

<form id="payment-form">
    @csrf

    <div class="d-flex p-3 gap-3">
        <!-- Infos client & produit -->
        <div class="w-50 border p-2 rounded">
            <div class="form-group">
                <label class="form-label">Nom</label>
                <input type="text" value="{{ auth()->user()->lastname }}" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Prénom</label>
                <input type="text" value="{{ auth()->user()->firstname }}" readonly>
            </div>

            <div class="form-group">
                <label class="form-label">Téléphone</label>
                <input type="tel" id="phone" class="w-100" required>
                <input type="hidden" id="phone_full" name="phone">
            </div>

            <!-- <div class="form-group">
                <label class="form-label">Adresse</label>
                <input type="text" id="address" name="address" class="w-100" required>
            </div> -->

            <div class="form-group">
                <label class="form-label">Produit</label>
                <input type="text" value="{{ $product->name }}" readonly>
            </div>

            <div class="form-group">
                <label class="form-label">Quantité</label>
                <input type="number" value="{{ $quantity }}" readonly>
            </div>

            <input type="hidden" id="sellerLat" value="{{ $sellerLat ?? '' }}">
            <input type="hidden" id="sellerLng" value="{{ $sellerLng ?? '' }}">

            <input type="hidden" name="delivery_requested"  id="deliveryRequested"  value="0">
            <input type="hidden" name="delivery_cost"        id="deliveryCostInput"  value="0">
            <input type="hidden" name="delivery_distance"    id="deliveryDistInput"  value="0">
            <input type="hidden" name="delivery_address"     id="deliveryAddrInput"  value="">

            <!-- SECTION LIVRAISON -->
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
                        <span id="distanceLabel">Distance : -- km</span><br>
                        <span id="deliveryBaseLabel"></span>
                        <strong id="deliveryCostLabel">Coût livraison : -- Euro</strong>
                    </div>
                </div>
            </div>

            {{-- Récapitulatif total mis à jour dynamiquement --}}
            <div class="total-recap">
                <div class="total-line">
                    <span>Sous-total produit</span>
                    <span id="productTotal">{{ number_format($product->price * $quantity, 2) }} EUR</span>
                </div>
                <div class="total-line" id="deliveryLine" style="display:none;">
                    <span>Livraison</span>
                    <span id="deliveryTotalDisplay">0.00 EUR</span>
                </div>
                <div class="total-line total-grand">
                    <span>Total</span>
                    <span id="grandTotal">{{ number_format($product->price * $quantity, 2) }} EUR</span>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Prix total</label>
                <input type="text" id="finalPriceInput" value="{{ number_format($product->price * $quantity, 2) }} €" readonly>
            </div>
        </div>

        <!-- Stripe -->
        <div class="w-50 border p-2 rounded h-25">
            <div class="form-group">
                <label class="form-label">Carte bancaire</label>
                <div id="card-element"></div>
                <div id="card-errors" style="color:red;"></div>
            </div>
            <div class="text-end">
                <button id="submit" class="btn-submit w-auto">Payer & Acheter</button>
            </div>
        </div>
    </div>
</form>

<script src="https://js.stripe.com/v3/"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>

<script>
/* TELEPHONE */
const phoneInput = document.querySelector("#phone");
const iti = window.intlTelInput(phoneInput, {
    initialCountry: "fr",
    separateDialCode: true,
    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js"
});

phoneInput.addEventListener("blur", () => {
    document.getElementById("phone_full").value = iti.getNumber();
});

/* STRIPE */
const stripe = Stripe("{{ config('services.stripe.key') }}");
const elements = stripe.elements();
const card = elements.create("card");
card.mount("#card-element");

const form = document.getElementById("payment-form");

form.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (livraisonToggle.checked) {
        const adresseLivraison = adresseInput.value.trim();

        if (!adresseLivraison) {
            alert("Veuillez renseigner une adresse de livraison.");
            return;
        }

        // BONUS : vérifier que distance calculée
        if (!deliveryDistInput.value || deliveryDistInput.value == 0) {
            alert("Veuillez sélectionner une adresse valide dans la liste.");
            return;
        }
    }

    const {paymentMethod, error} = await stripe.createPaymentMethod({
        type: "card",
        card: card,
    });

    if (error) {
        document.getElementById("card-errors").textContent = error.message;
        return;
    }

    fetch("{{ route('products.pay') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('input[name=_token]').value
        },
        body: JSON.stringify({
            payment_method: paymentMethod.id,
            phone: document.getElementById("phone_full").value,
            address: document.getElementById("adresseClient").value,
            product_id: "{{ $product->id }}",
            quantity: "{{ $quantity }}",

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
// ─── Constantes ────────────────────────────────────────────────────────────
const DELIVERY_BASE      = 50;
const PRICE_PER_KM       = 0.5;
const PRODUCT_TOTAL      = {{ $product->price * $quantity }};

// ─── Elements DOM ──────────────────────────────────────────────────────────
const livraisonToggle    = document.getElementById('livraisonToggle');
const livraisonDetails   = document.getElementById('livraisonDetails');
const adresseInput       = document.getElementById('adresseClient');
const suggestionsEl      = document.getElementById('adresseSuggestions');
const distanceResult     = document.getElementById('distanceResult');

// Hidden inputs pour le controller
const deliveryRequested  = document.getElementById('deliveryRequested');
const deliveryCostInput  = document.getElementById('deliveryCostInput');
const deliveryDistInput  = document.getElementById('deliveryDistInput');
const deliveryAddrInput  = document.getElementById('deliveryAddrInput');

// Affichage total
const deliveryLine       = document.getElementById('deliveryLine');
const deliveryTotalDisp  = document.getElementById('deliveryTotalDisplay');
const grandTotalEl       = document.getElementById('grandTotal');

// Coordonnees vendeur (injectees depuis le controller)
const sellerLat = parseFloat(document.getElementById('sellerLat').value);
const sellerLng = parseFloat(document.getElementById('sellerLng').value);

// ─── Toggle livraison ───────────────────────────────────────────────────────
livraisonToggle.addEventListener('change', () => {
    const checked = livraisonToggle.checked;
    livraisonDetails.style.display = checked ? 'block' : 'none';
    deliveryRequested.value        = checked ? '1' : '0';

    if (!checked) {
        resetDelivery();
    }
});

// ─── Autocomplete adresse (Nominatim) ──────────────────────────────────────
let debounceTimer;

adresseInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    const query = adresseInput.value.trim();

    if (query.length < 3) {
        suggestionsEl.innerHTML = '';
        return;
    }

    debounceTimer = setTimeout(async () => {
        try {
            const res  = await fetch(
                `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&addressdetails=1`,
                { headers: { 'Accept-Language': 'fr' } }
            );
            const data = await res.json();

            suggestionsEl.innerHTML = '';
            data.forEach(place => {
                const li = document.createElement('li');
                li.textContent  = place.display_name;
                li.style.cssText = 'padding:8px 12px;cursor:pointer;border-bottom:1px solid #eee;font-size:0.9rem;';

                li.addEventListener('mouseenter', () => li.style.background = '#f5f5f5');
                li.addEventListener('mouseleave', () => li.style.background = 'white');

                li.addEventListener('click', () => {
                    adresseInput.value       = place.display_name;
                    deliveryAddrInput.value  = place.display_name;
                    suggestionsEl.innerHTML  = '';
                    calculateDelivery(parseFloat(place.lat), parseFloat(place.lon));
                });

                suggestionsEl.appendChild(li);
            });
        } catch (err) {
            console.error('Erreur geocoding:', err);
        }
    }, 400); // debounce 400ms pour ne pas spammer l'API
});

// ─── Fermer suggestions si clic ailleurs ───────────────────────────────────
document.addEventListener('click', (e) => {
    if (!adresseInput.contains(e.target) && !suggestionsEl.contains(e.target)) {
        suggestionsEl.innerHTML = '';
    }
});

// ─── Calcul distance (Haversine) + cout livraison ──────────────────────────
function haversineKm(lat1, lon1, lat2, lon2) {
    const R    = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(dLat / 2) ** 2 +
              Math.cos(lat1 * Math.PI / 180) *
              Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon / 2) ** 2;

    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function calculateDelivery(buyerLat, buyerLon) {
    if (!sellerLat || !sellerLng) {
        alert('Coordonnées du vendeur manquantes.');
        return;
    }

    const dist = haversineKm(sellerLat, sellerLng, buyerLat, buyerLon);

    const PRICE_PER_KM = 1;
    
    const deliveryCost = Math.ceil(dist) * PRICE_PER_KM;

    const grandTotal = PRODUCT_TOTAL + deliveryCost;

    // ─── Affichage ─────────────────────────────────────
    document.getElementById('distanceLabel').textContent = `Distance : ${dist.toFixed(2)} km`;

    document.getElementById('deliveryBaseLabel').textContent = `${dist.toFixed(2)} km x 1 EUR/km`;

    document.getElementById('deliveryCostLabel').textContent = `Coût livraison : ${deliveryCost.toFixed(2)} EUR`;

    distanceResult.style.display = 'block';

    // ─── Hidden inputs (pour backend) ──────────────────
    deliveryCostInput.value = deliveryCost.toFixed(2);
    deliveryDistInput.value = dist.toFixed(2);

    // ─── Mise à jour total ─────────────────────────────
    deliveryLine.style.display    = 'flex';
    deliveryTotalDisp.textContent = `${deliveryCost.toFixed(2)} EUR`;
    grandTotalEl.textContent      = `${grandTotal.toFixed(2)} EUR`;
    document.getElementById('finalPriceInput').value = `${grandTotal.toFixed(2)} €`;
}

function resetDelivery() {
    deliveryCostInput.value      = '0';
    deliveryDistInput.value      = '0';
    deliveryAddrInput.value      = '';

    deliveryLine.style.display   = 'none';
    distanceResult.style.display = 'none';

    grandTotalEl.textContent     = `${PRODUCT_TOTAL.toFixed(2)} EUR`;
}
</script>

@endsection