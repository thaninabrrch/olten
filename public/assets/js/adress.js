let map, marker;

document.addEventListener('DOMContentLoaded', function () {
    map = L.map('map').setView([46.6, 1.8], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    marker = L.marker([46.6, 1.8], { draggable: true }).addTo(map);

    const latitude = document.getElementById('latitude');
    const longitude = document.getElementById('longitude');
    const adresseVendeur = document.getElementById('adresseVendeur');
    const suggestionsVendeur = document.getElementById('adresseSuggestions');

    const livraisonActive = document.getElementById('livraisonActive');
    const livraisonDetails = document.getElementById('livraisonDetails');
    const adresseClient = document.getElementById('adresseClient');
    const suggestionsClient = document.getElementById('adresseClientSuggestions');
    const tarifKm = document.getElementById('tarifKm');
    const distanceKm = document.getElementById('distanceKm');
    const deliveryCost = document.getElementById('deliveryCost');
    const distanceResult = document.getElementById('distanceResult');

    let clientLat = null;
    let clientLon = null;
    let distance = null;

    marker.on('dragend', () => {
        const pos = marker.getLatLng();
        setPosition(pos.lat, pos.lng);
    });

    map.on('click', e => {
        setPosition(e.latlng.lat, e.latlng.lng);
    });

    async function setPosition(lat, lng) {
        latitude.value = lat;
        longitude.value = lng;
        marker.setLatLng([lat, lng]);

        try {
            const res = await fetch(`/ads/reverse-geocode?lat=${lat}&lng=${lng}`);
            const data = await res.json();
            if (data.display_name) adresseVendeur.value = data.display_name;
        } catch (err) { console.error(err); }

        if (clientLat && clientLon) {
            calculerDistance(clientLat, clientLon);
        }
    }

    adresseVendeur.addEventListener('input', async () => {
        if (adresseVendeur.value.length < 3) return;

        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&q=${encodeURIComponent(adresseVendeur.value)}`);
        const data = await res.json();

        suggestionsVendeur.innerHTML = '';
        data.forEach(item => {
            const li = document.createElement('li');
            li.textContent = item.display_name;
            li.onclick = () => {
                adresseVendeur.value = item.display_name;
                setPosition(parseFloat(item.lat), parseFloat(item.lon));
                map.setView([item.lat, item.lon], 14);
                suggestionsVendeur.innerHTML = '';
            };
            suggestionsVendeur.appendChild(li);
        });
    });

    livraisonActive.addEventListener('change', () => {
        livraisonDetails.style.display = livraisonActive.checked ? 'block' : 'none';
    });

    adresseClient.addEventListener('input', async () => {
        if (adresseClient.value.length < 3) return;

        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&q=${encodeURIComponent(adresseClient.value)}`);
        const data = await res.json();

        suggestionsClient.innerHTML = '';
        data.forEach(item => {
            const li = document.createElement('li');
            li.textContent = item.display_name;
            li.onclick = () => {
                adresseClient.value = item.display_name;
                clientLat = parseFloat(item.lat);
                clientLon = parseFloat(item.lon);
                suggestionsClient.innerHTML = '';
                calculerDistance(clientLat, clientLon);
            };
            suggestionsClient.appendChild(li);
        });
    });

    tarifKm.addEventListener('input', () => {
        if (distance !== null) {
            calculerCout(distance);
        }
    });

    function calculerDistance(lat2, lon2) {
        const lat1 = parseFloat(latitude.value);
        const lon1 = parseFloat(longitude.value);

        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(dLat/2)**2 +
                Math.cos(lat1 * Math.PI/180) *
                Math.cos(lat2 * Math.PI/180) *
                Math.sin(dLon/2)**2;

        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        distance = R * c;

        calculerCout(distance);
    }

    function calculerCout(distance) {
        const prixKm = parseFloat(tarifKm.value) || 0;
        const cout = distance * prixKm;

        distanceKm.value = distance.toFixed(2);
        deliveryCost.value = cout.toFixed(2);

        distanceResult.innerHTML = `
            Distance : ${distance.toFixed(2)} km<br>
            Coût total livraison : ${cout.toFixed(2)} € 
        `;
    }
});