@extends('layouts.main')

@section('title', $product->name . ' - Olten.fr')

@php
    $seller  = $product->user;
    $images  = $product->images;
    $isOwner = auth()->check() && auth()->id() === $product->user_id;
    $stock   = (int) $product->stock;
    $favorited = auth()->check() && auth()->user()->hasFavoritedProduct($product);

    $avatar = $seller?->profile_photo ? asset('storage/' . $seller->profile_photo) : null;
@endphp

@section('content')

@php
    // Reperes du fil d'Ariane : la categorie de l'offre et le service dont
    // elle depend. Le service peut manquer sur une categorie orpheline ; le
    // fil se replie alors sur la categorie seule.
    $crumbCategory = $product->category;
    $crumbService  = $crumbCategory?->service;
@endphp

<div class="dt">
    <div class="dt-wrap">

        {{-- Fil d'ariane --}}
        <nav class="dt-crumbs" aria-label="Fil d'ariane">
            <a href="{{ url('/') }}">Accueil</a>
            <i class="fa-solid fa-chevron-right"></i>

            {{-- La categorie ne renvoie plus a l'ancienne page
                 /categories/{slug} mais a la page du service, filtree sur
                 elle : c'est la que vivent desormais les offres. Le service
                 lui-meme prend un cran du fil, pour remonter d'un niveau. --}}
            @if ($crumbService)
                <a href="{{ route('services.show', $crumbService->slug) }}">{{ $crumbService->display_name }}</a>
                <i class="fa-solid fa-chevron-right"></i>
            @endif

            @if ($crumbCategory)
                <a href="{{ $crumbService
                            ? route('services.category', [$crumbService->slug, $crumbCategory->slug])
                            : route('categories.show', $crumbCategory->slug) }}">{{ $crumbCategory->nom }}</a>
                <i class="fa-solid fa-chevron-right"></i>
            @endif
            <span class="is-current">{{ $product->name }}</span>
            @if ($stock <= 0)
                <span class="dt-flag">Rupture</span>
            @endif
        </nav>

        {{-- En-tête --}}
        <header class="dt-head">
            <div>
                <h1 class="dt-title">{{ $product->name }}</h1>

                <div class="dt-chips">
                    @if ($product->category)
                        <span class="dt-chip"><i class="fa-solid fa-tag"></i> {{ $product->category->nom }}</span>
                    @endif
                    @if ($product->address)
                        <span class="dt-chip"><i class="fa-solid fa-location-dot"></i> {{ $product->address }}</span>
                    @endif
                    <span class="dt-chip"><i class="fa-regular fa-eye"></i> {{ $product->views ?? 0 }} vue{{ ($product->views ?? 0) > 1 ? 's' : '' }}</span>
                    @if ($product->delivery_available)
                        <span class="dt-chip is-solid"><i class="fa-solid fa-truck-fast"></i> Livraison possible</span>
                    @endif
                </div>
            </div>

            <div class="dt-head-price">
                <strong>{{ number_format((float) $product->price, 2, ',', ' ') }} €</strong>
                <small>l'unité</small>
            </div>
        </header>

        {{-- Haut de page : galerie et encart d'action cote a cote --}}
        <div class="dt-top">

            {{-- Galerie --}}
            <section class="dt-gallery" data-dt-gallery>
                @php
                    $sources = $images->count()
                        ? $images->map(fn ($i) => asset('storage/' . $i->image))->all()
                        : [asset('assets/images/no-image.jpg')];
                @endphp

                <div class="dt-gallery-main">
                    <img src="{{ $sources[0] }}" alt="{{ $product->name }}" data-dt-main>

                    @if (count($sources) > 1)
                        <button type="button" class="dt-gallery-nav prev" data-dt-prev aria-label="Image précédente">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <button type="button" class="dt-gallery-nav next" data-dt-next aria-label="Image suivante">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                        <span class="dt-gallery-count"><span data-dt-index>1</span> / {{ count($sources) }}</span>
                    @endif
                </div>

                @if (count($sources) > 1)
                    <div class="dt-thumbs">
                        @foreach ($sources as $i => $src)
                            <button type="button" class="dt-thumb {{ $i === 0 ? 'is-active' : '' }}"
                                    data-dt-thumb="{{ $i }}" aria-label="Image {{ $i + 1 }}">
                                <img src="{{ $src }}" alt="{{ $product->name }} — image {{ $i + 1 }}" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- ---------- Colonne droite ---------- --}}
            <div class="dt-aside">
                <div class="dt-box">

                    @if ($isOwner)
                        <div class="dt-note is-info">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>Vous consultez votre propre produit, tel que le voient les acheteurs.</span>
                        </div>
                    @endif

                    <div class="dt-box-price">
                        <strong>{{ number_format((float) $product->price, 2, ',', ' ') }} €</strong>
                        <small>l'unité</small>
                    </div>

                    <span class="dt-stock {{ $stock <= 0 ? 'is-out' : ($stock <= 5 ? 'is-low' : '') }}">
                        <span class="dot"></span>
                        @if ($stock <= 0)
                            Rupture de stock
                        @elseif ($stock <= 5)
                            Plus que {{ $stock }} en stock
                        @else
                            {{ $stock }} en stock
                        @endif
                    </span>

                    @if ($stock > 0 && ! $isOwner)
                        {{-- `data-auth-required` : un visiteur non connecte voit la
                             popin de connexion (assets/js/auth.js) au lieu d'etre
                             renvoye sur /login par le middleware `auth` de la route
                             `products.purchase`. Il revient sur ce produit une fois
                             connecte, sa quantite est simplement a resaisir. --}}
                        <form action="{{ route('products.purchase', $product) }}" method="POST"
                              style="margin-top:16px;"
                              data-auth-required data-auth-redirect="{{ url()->current() }}">
                            @csrf

                            <div class="dt-field">
                                <span class="dt-label"><i class="fa-solid fa-layer-group"></i> Quantité</span>

                                <div class="dt-qty">
                                    <button type="button" data-dt-qty="-1" aria-label="Diminuer">
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                    <input type="number" name="quantity" id="quantity" value="1"
                                           min="1" max="{{ $stock }}"
                                           data-dt-price="{{ (float) $product->price }}">
                                    <button type="button" data-dt-qty="1" aria-label="Augmenter">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="dt-total">
                                <span>Total</span>
                                <strong data-dt-total>{{ number_format((float) $product->price, 2, ',', ' ') }} €</strong>
                            </div>

                            <button type="submit" class="dt-btn">
                                <i class="fa-solid fa-bag-shopping"></i>
                                Acheter
                            </button>
                        </form>
                    @elseif ($stock <= 0)
                        <button type="button" class="dt-btn is-disabled" disabled style="margin-top:16px;">
                            <i class="fa-solid fa-ban"></i>
                            Indisponible
                        </button>
                    @endif

                    <div class="dt-actions">
                        <a class="dt-action" href="mailto:{{ $seller->email ?? '' }}">
                            <i class="fa-regular fa-comment"></i> Message
                        </a>

                        <a class="dt-action" href="tel:{{ $seller->phone ?? '' }}">
                            <i class="fa-solid fa-phone"></i> Appeler
                        </a>

                        {{-- Classe et attributs attendus par le gestionnaire global (assets/js/script.js) --}}
                        <button type="button"
                                class="dt-action favorite-btn {{ $favorited ? 'active' : '' }}"
                                data-type="product"
                                data-id="{{ $product->id }}"
                                data-favorited="{{ $favorited ? 'true' : 'false' }}">
                            <i class="{{ $favorited ? 'fas' : 'far' }} fa-heart"></i> J'aime
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail, en pleine largeur --}}
        <div class="dt-below">


            <section class="dt-card">
                <h2 class="dt-card-title">En bref</h2>

                <div class="dt-facts">
                    <div class="dt-fact">
                        <span class="dt-fact-icon"><i class="fa-solid fa-euro-sign"></i></span>
                        <span class="dt-fact-text">
                            <strong>{{ number_format((float) $product->price, 2, ',', ' ') }} €</strong>
                            <small>prix unitaire</small>
                        </span>
                    </div>

                    <div class="dt-fact">
                        <span class="dt-fact-icon {{ $stock > 0 ? 'is-green' : 'is-grey' }}">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </span>
                        <span class="dt-fact-text">
                            <strong>{{ $stock > 0 ? $stock . ' en stock' : 'Épuisé' }}</strong>
                            <small>disponibilité</small>
                        </span>
                    </div>

                    <div class="dt-fact">
                        <span class="dt-fact-icon {{ $product->delivery_available ? 'is-blue' : 'is-grey' }}">
                            <i class="fa-solid fa-truck-fast"></i>
                        </span>
                        <span class="dt-fact-text">
                            <strong>{{ $product->delivery_available ? 'Livraison possible' : 'Retrait sur place' }}</strong>
                            <small>mode de remise</small>
                        </span>
                    </div>

                    <div class="dt-fact">
                        <span class="dt-fact-icon is-grey"><i class="fa-regular fa-clock"></i></span>
                        <span class="dt-fact-text">
                            <strong>{{ $product->created_at?->translatedFormat('d M Y') ?? '—' }}</strong>
                            <small>mise en ligne</small>
                        </span>
                    </div>
                </div>
            </section>

            <section class="dt-card">
                <div class="dt-tabs">
                    <button type="button" class="dt-tab is-active" data-dt-tab="description">Description</button>
                    @if ($product->latitude && $product->longitude)
                        <button type="button" class="dt-tab" data-dt-tab="emplacement">Emplacement</button>
                    @endif
                </div>

                <div class="dt-panel is-active" data-dt-panel="description">
                    <div class="dt-prose">
                        @if (filled($product->description))
                            {!! $product->description !!}
                        @else
                            <p class="dt-empty-text">Aucune description renseignée pour ce produit.</p>
                        @endif
                    </div>
                </div>

                @if ($product->latitude && $product->longitude)
                    <div class="dt-panel" data-dt-panel="emplacement">
                        <div id="productMap" class="dt-map"
                             data-lat="{{ $product->latitude }}"
                             data-lng="{{ $product->longitude }}"
                             data-label="{{ $product->address }}"></div>
                    </div>
                @endif
            </section>

            @if ($seller)
                <section class="dt-card">
                    <h2 class="dt-card-title">Vendu par</h2>

                    <div class="dt-owner">
                        @if ($avatar)
                            <img src="{{ $avatar }}" alt="{{ $seller->name }}">
                        @else
                            <span class="dt-owner-initial">{{ strtoupper(mb_substr($seller->name, 0, 1)) }}</span>
                        @endif

                        <span class="dt-owner-info">
                            <strong>{{ $seller->name }}</strong>
                            <small>Membre depuis {{ $seller->created_at?->translatedFormat('F Y') }}</small>
                            @if ($seller->is_approved)
                                <span class="dt-owner-badge"><i class="fa-solid fa-circle-check"></i> Vendeur vérifié</span>
                            @endif
                        </span>
                    </div>
                </section>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // ---- Galerie ----
        const gallery = document.querySelector('[data-dt-gallery]');

        if (gallery) {
            const main = gallery.querySelector('[data-dt-main]');
            const thumbs = Array.from(gallery.querySelectorAll('[data-dt-thumb]'));
            const counter = gallery.querySelector('[data-dt-index]');
            const sources = thumbs.map(t => t.querySelector('img').src);
            let current = 0;

            function show(index) {
                if (!sources.length) return;

                current = (index + sources.length) % sources.length;
                main.src = sources[current];

                thumbs.forEach((t, i) => t.classList.toggle('is-active', i === current));
                if (counter) counter.textContent = current + 1;
            }

            thumbs.forEach((thumb, i) => thumb.addEventListener('click', () => show(i)));
            gallery.querySelector('[data-dt-prev]')?.addEventListener('click', () => show(current - 1));
            gallery.querySelector('[data-dt-next]')?.addEventListener('click', () => show(current + 1));
        }

        // ---- Onglets ----
        const tabs = Array.from(document.querySelectorAll('[data-dt-tab]'));
        const panels = Array.from(document.querySelectorAll('[data-dt-panel]'));
        let mapReady = false;

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(t => t.classList.remove('is-active'));
                panels.forEach(p => p.classList.remove('is-active'));

                tab.classList.add('is-active');
                document.querySelector('[data-dt-panel="' + tab.dataset.dtTab + '"]')?.classList.add('is-active');

                if (tab.dataset.dtTab === 'emplacement') initMap();
            });
        });

        function initMap() {
            const holder = document.getElementById('productMap');
            if (!holder || typeof L === 'undefined') return;

            if (mapReady) {
                setTimeout(() => holder._leafletMap?.invalidateSize(), 100);
                return;
            }

            const lat = parseFloat(holder.dataset.lat);
            const lng = parseFloat(holder.dataset.lng);
            if (isNaN(lat) || isNaN(lng)) return;

            const map = L.map(holder).setView([lat, lng], 14);
            holder._leafletMap = map;

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            L.marker([lat, lng]).addTo(map)
                .bindPopup(holder.dataset.label || 'Adresse')
                .openPopup();

            mapReady = true;
            setTimeout(() => map.invalidateSize(), 200);
        }

        // ---- Quantite et total ----
        const qty = document.getElementById('quantity');
        const totalEl = document.querySelector('[data-dt-total]');

        if (qty) {
            const price = parseFloat(qty.dataset.dtPrice || '0');
            const max = parseInt(qty.max || '1', 10);

            function refresh() {
                let value = parseInt(qty.value || '1', 10);

                if (isNaN(value) || value < 1) value = 1;
                if (value > max) value = max;

                qty.value = value;

                document.querySelector('[data-dt-qty="-1"]').disabled = value <= 1;
                document.querySelector('[data-dt-qty="1"]').disabled = value >= max;

                if (totalEl) {
                    totalEl.textContent = new Intl.NumberFormat('fr-FR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(price * value) + ' €';
                }
            }

            document.querySelectorAll('[data-dt-qty]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    qty.value = (parseInt(qty.value || '1', 10) + parseInt(btn.dataset.dtQty, 10));
                    refresh();
                });
            });

            qty.addEventListener('input', refresh);
            refresh();
        }
    });
</script>
@endpush

@endsection
