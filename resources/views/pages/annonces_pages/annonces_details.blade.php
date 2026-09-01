@extends('layouts.main')

@section('title', $ad->title . ' - Olten.fr')

@php
    $owner    = $ad->user;
    $images   = $ad->images;
    $expired  = $ad->expires_at && \Carbon\Carbon::parse($ad->expires_at)->toDateString() < now()->toDateString();
    $isOwner  = auth()->check() && auth()->id() === $ad->user_id;
    $bookable = ! $expired && ! $isOwner;
    $favorited = auth()->check() && auth()->user()->hasFavorited($ad);

    $avatar = $owner?->profile_photo ? asset('storage/' . $owner->profile_photo) : null;
@endphp

@section('content')

@php
    // Reperes du fil d'Ariane : la categorie de l'offre et le service dont
    // elle depend. Le service peut manquer sur une categorie orpheline ; le
    // fil se replie alors sur la categorie seule.
    $crumbCategory = $ad->category;
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
            <span class="is-current">{{ $ad->title }}</span>
            @if ($expired)
                <span class="dt-flag">Expirée</span>
            @endif
        </nav>

        {{-- En-tête --}}
        <header class="dt-head">
            <div>
                <h1 class="dt-title">{{ $ad->title }}</h1>

                <div class="dt-chips">
                    @if ($ad->category)
                        <span class="dt-chip"><i class="fa-solid fa-tag"></i> {{ $ad->category->nom }}</span>
                    @endif
                    @if ($ad->address)
                        <span class="dt-chip"><i class="fa-solid fa-location-dot"></i> {{ $ad->address }}</span>
                    @endif
                    <span class="dt-chip"><i class="fa-regular fa-eye"></i> {{ $ad->views ?? 0 }} vue{{ ($ad->views ?? 0) > 1 ? 's' : '' }}</span>
                    @if ($ad->delivery_active)
                        <span class="dt-chip is-solid"><i class="fa-solid fa-truck-fast"></i> Livraison possible</span>
                    @endif
                </div>
            </div>

            <div class="dt-head-price">
                <strong>{{ number_format((float) $ad->price_per_day, 2, ',', ' ') }} €</strong>
                <small>par jour</small>
            </div>
        </header>

        {{-- Haut de page : galerie et encart d'action cote a cote --}}
        <div class="dt-top">

            {{-- Galerie --}}
            <section class="dt-gallery" data-dt-gallery>
                @php
                    $sources = $images->count()
                        ? $images->map(fn ($i) => asset('storage/' . $i->path))->all()
                        : [asset('assets/images/no-image.jpg')];
                @endphp

                <div class="dt-gallery-main">
                    <img src="{{ $sources[0] }}" alt="{{ $ad->title }}" data-dt-main>

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
                                <img src="{{ $src }}" alt="{{ $ad->title }} — image {{ $i + 1 }}" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- ---------- Colonne droite ---------- --}}
            <div class="dt-aside">
                <div class="dt-box">

                    @if (is_null($ad->user_id))
                        <div class="dt-note is-warn">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>Annonce non vérifiée. Vous en êtes le propriétaire ? Revendiquez-la.</span>
                        </div>
                    @endif

                    @if ($expired)
                        <div class="dt-note is-danger">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span>Cette annonce a expiré le {{ \Carbon\Carbon::parse($ad->expires_at)->translatedFormat('d F Y') }} : la réservation n'est plus possible.</span>
                        </div>
                    @elseif ($isOwner)
                        <div class="dt-note is-info">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>Vous consultez votre propre annonce, telle que la voient les visiteurs.</span>
                        </div>
                    @endif

                    <div class="dt-box-price">
                        <strong>{{ number_format((float) $ad->price_per_day, 2, ',', ' ') }} €</strong>
                        <small>/ jour</small>
                    </div>

                    @if ($bookable)
                        {{-- `data-auth-required` : un visiteur non connecte voit la
                             popin de connexion (assets/js/auth.js) au lieu d'etre
                             renvoye sur /login par le middleware. Il revient sur
                             cette annonce une fois connecte. --}}
                        <form action="{{ route('bookings.store', $ad) }}" method="POST"
                              data-auth-required data-auth-redirect="{{ url()->current() }}">
                            @csrf

                            <div class="dt-field">
                                <label class="dt-label" for="reservation_dates">
                                    <i class="fa-solid fa-calendar-days"></i>
                                    Dates de réservation
                                </label>

                                <input type="text" id="reservation_dates" class="dt-input"
                                       placeholder="Sélectionner vos dates" readonly>

                                <input type="hidden" name="start_date" id="start_date">
                                <input type="hidden" name="end_date" id="end_date">
                            </div>

                            <button type="submit" class="dt-btn">
                                <i class="fa-regular fa-calendar-check"></i>
                                Réserver maintenant
                            </button>
                        </form>
                    @endif

                    <div class="dt-actions">
                        <a class="dt-action" href="mailto:{{ $owner->email ?? '' }}">
                            <i class="fa-regular fa-comment"></i> Message
                        </a>

                        <a class="dt-action" href="tel:{{ $owner->phone ?? '' }}">
                            <i class="fa-solid fa-phone"></i> Appeler
                        </a>

                        {{-- Classe et attributs attendus par le gestionnaire global (assets/js/script.js) --}}
                        <button type="button"
                                class="dt-action favorite-btn {{ $favorited ? 'active' : '' }}"
                                data-type="ad"
                                data-id="{{ $ad->id }}"
                                data-favorited="{{ $favorited ? 'true' : 'false' }}">
                            <i class="{{ $favorited ? 'fas' : 'far' }} fa-heart"></i> J'aime
                        </button>
                    </div>

                    <button type="button" class="dt-report" data-dt-report="{{ $ad->id }}">
                        <i class="fa-solid fa-flag"></i> Signaler cette annonce
                    </button>
                </div>
            </div>
        </div>

        {{-- Detail, en pleine largeur --}}
        <div class="dt-below">


            {{-- Points clés --}}
            <section class="dt-card">
                <h2 class="dt-card-title">En bref</h2>

                <div class="dt-facts">
                    <div class="dt-fact">
                        <span class="dt-fact-icon"><i class="fa-solid fa-euro-sign"></i></span>
                        <span class="dt-fact-text">
                            <strong>{{ number_format((float) $ad->price_per_day, 2, ',', ' ') }} €</strong>
                            <small>par jour de location</small>
                        </span>
                    </div>

                    <div class="dt-fact">
                        <span class="dt-fact-icon is-blue"><i class="fa-regular fa-calendar-check"></i></span>
                        <span class="dt-fact-text">
                            <strong>
                                @if ($ad->available_from && $ad->available_until)
                                    {{ $ad->available_from->translatedFormat('d M') }} → {{ $ad->available_until->translatedFormat('d M Y') }}
                                @else
                                    Sur demande
                                @endif
                            </strong>
                            <small>période de disponibilité</small>
                        </span>
                    </div>

                    <div class="dt-fact">
                        <span class="dt-fact-icon {{ $ad->delivery_active ? 'is-green' : 'is-grey' }}">
                            <i class="fa-solid fa-truck-fast"></i>
                        </span>
                        <span class="dt-fact-text">
                            <strong>
                                @if ($ad->delivery_active && $ad->price_per_km)
                                    {{ number_format((float) $ad->price_per_km, 2, ',', ' ') }} € / km
                                @elseif ($ad->delivery_active)
                                    Disponible
                                @else
                                    Retrait sur place
                                @endif
                            </strong>
                            <small>livraison</small>
                        </span>
                    </div>

                    <div class="dt-fact">
                        <span class="dt-fact-icon is-grey"><i class="fa-regular fa-clock"></i></span>
                        <span class="dt-fact-text">
                            <strong>{{ $ad->created_at?->translatedFormat('d M Y') ?? '—' }}</strong>
                            <small>mise en ligne</small>
                        </span>
                    </div>
                </div>
            </section>

            {{-- Onglets --}}
            <section class="dt-card">
                <div class="dt-tabs">
                    <button type="button" class="dt-tab is-active" data-dt-tab="apercu">Aperçu</button>
                    <button type="button" class="dt-tab" data-dt-tab="description">Description</button>
                    <button type="button" class="dt-tab" data-dt-tab="emplacement">Emplacement</button>
                </div>

                <div class="dt-panel is-active" data-dt-panel="apercu">
                    <div class="dt-prose">
                        @if (filled($ad->summary))
                            {!! $ad->summary !!}
                        @else
                            <p class="dt-empty-text">Aucun aperçu renseigné pour cette annonce.</p>
                        @endif
                    </div>
                </div>

                <div class="dt-panel" data-dt-panel="description">
                    <div class="dt-prose">
                        @if (filled($ad->description))
                            {!! $ad->description !!}
                        @else
                            <p class="dt-empty-text">Aucune description renseignée pour cette annonce.</p>
                        @endif
                    </div>
                </div>

                <div class="dt-panel" data-dt-panel="emplacement">
                    @if ($ad->latitude && $ad->longitude)
                        <div id="adMap" class="dt-map"
                             data-lat="{{ $ad->latitude }}"
                             data-lng="{{ $ad->longitude }}"
                             data-label="{{ $ad->address }}"></div>
                    @else
                        <p class="dt-empty-text">Adresse non disponible pour cette annonce.</p>
                    @endif
                </div>
            </section>

            {{-- Propriétaire --}}
            @if ($owner)
                <section class="dt-card">
                    <h2 class="dt-card-title">Proposé par</h2>

                    <div class="dt-owner">
                        @if ($avatar)
                            <img src="{{ $avatar }}" alt="{{ $owner->name }}">
                        @else
                            <span class="dt-owner-initial">{{ strtoupper(mb_substr($owner->name, 0, 1)) }}</span>
                        @endif

                        <span class="dt-owner-info">
                            <strong>{{ $owner->name }}</strong>
                            <small>Membre depuis {{ $owner->created_at?->translatedFormat('F Y') }}</small>
                            @if ($owner->is_approved)
                                <span class="dt-owner-badge"><i class="fa-solid fa-circle-check"></i> Profil vérifié</span>
                            @endif
                        </span>
                    </div>
                </section>
            @endif
        </div>
    </div>
</div>

{{-- Popin de signalement --}}
@php
    $reportReasons = [
        ['value' => 'Arnaque ou tentative de fraude',   'hint' => 'Paiement hors plateforme, faux profil, demande suspecte.'],
        ['value' => 'Annonce trompeuse',                'hint' => 'Photos, description ou prix qui ne correspondent pas au bien.'],
        ['value' => 'Contenu inapproprié',              'hint' => 'Propos ou images choquants, haineux ou à caractère sexuel.'],
        ['value' => 'Objet interdit à la location',     'hint' => 'Produit illégal, contrefait ou dangereux.'],
        ['value' => 'Annonce en double ou indisponible','hint' => 'Le bien est déjà loué, ou publié plusieurs fois.'],
        ['value' => 'Autre motif',                      'hint' => 'Décrivez le problème dans le champ ci-dessous.'],
    ];
@endphp

<div class="dt-rep"
     id="dt-report-modal"
     role="dialog"
     aria-modal="true"
     aria-labelledby="dt-report-title"
     data-dt-report-guest="{{ auth()->check() ? '0' : '1' }}"
     hidden>

    <div class="dt-rep-backdrop" data-dt-report-close></div>

    <div class="dt-rep-card">
        <form class="dt-rep-form" data-dt-report-form data-dt-report-url="{{ route('ads.report', $ad) }}">
            <div class="dt-rep-head">
                <span class="dt-rep-badge"><i class="fa-solid fa-flag"></i></span>

                <div class="dt-rep-head-text">
                    <h2 class="dt-rep-title" id="dt-report-title">Signaler cette annonce</h2>
                    <p class="dt-rep-sub">
                        Votre signalement reste confidentiel : le propriétaire n'en est pas informé.
                        Notre équipe de modération examine chaque demande.
                    </p>
                </div>

                <button type="button" class="dt-rep-close" data-dt-report-close aria-label="Fermer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="dt-rep-body">
                <fieldset class="dt-rep-options">
                    <legend class="dt-rep-legend">Motif du signalement</legend>

                    @foreach ($reportReasons as $i => $reason)
                        <label class="dt-rep-option">
                            <input type="radio" name="reason" value="{{ $reason['value'] }}">
                            <span class="dt-rep-dot" aria-hidden="true"></span>
                            <span class="dt-rep-option-text">
                                {{ $reason['value'] }}
                                <small>{{ $reason['hint'] }}</small>
                            </span>
                        </label>
                    @endforeach
                </fieldset>

                <label class="dt-rep-field">
                    <span class="dt-rep-label">
                        Précisions <span class="dt-rep-count" data-dt-report-count>0 / 500</span>
                    </span>
                    <textarea class="dt-rep-textarea"
                              data-dt-report-details
                              maxlength="500"
                              placeholder="Ajoutez tout élément utile à la modération (échanges, dates, liens…)."></textarea>
                </label>

                <p class="dt-rep-alert" data-dt-report-alert hidden role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span></span>
                </p>
            </div>

            <div class="dt-rep-foot">
                <button type="button" class="dt-rep-btn is-ghost" data-dt-report-close>Annuler</button>
                <button type="submit" class="dt-rep-btn is-danger" data-dt-report-submit>
                    <i class="fa-solid fa-flag"></i>
                    <span>Envoyer le signalement</span>
                </button>
            </div>
        </form>

        <div class="dt-rep-done" data-dt-report-done tabindex="-1" hidden>
            <div class="dt-rep-done-icon"><i class="fa-solid fa-check"></i></div>
            <h3>Signalement transmis</h3>
            <p data-dt-report-done-text>Notre équipe de modération va examiner cette annonce.</p>
            <button type="button" class="dt-rep-btn is-ghost" data-dt-report-close>Fermer</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // ---- Galerie : vignette cliquee, fleches, compteur ----
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

        // ---- Carte : construite au premier affichage de l'onglet ----
        function initMap() {
            const holder = document.getElementById('adMap');
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

        // ---- Signalement ----
        // Le prompt() natif affichait l'URL du site et n'offrait aucun motif :
        // le bouton pilote desormais la popin .dt-rep.
        const reportTrigger = document.querySelector('[data-dt-report]');
        const reportModal   = document.getElementById('dt-report-modal');

        if (reportTrigger && reportModal) {
            const reportForm  = reportModal.querySelector('[data-dt-report-form]');
            const reportDone  = reportModal.querySelector('[data-dt-report-done]');
            const doneText    = reportModal.querySelector('[data-dt-report-done-text]');
            const alertBox    = reportModal.querySelector('[data-dt-report-alert]');
            const alertText   = alertBox.querySelector('span');
            const details     = reportModal.querySelector('[data-dt-report-details]');
            const counter     = reportModal.querySelector('[data-dt-report-count]');
            const submitBtn   = reportModal.querySelector('[data-dt-report-submit]');
            const submitIcon  = submitBtn.querySelector('i');
            const submitLabel = submitBtn.querySelector('span');
            const options     = Array.from(reportForm.querySelectorAll('.dt-rep-option'));
            const isGuest     = reportModal.dataset.dtReportGuest === '1';

            let lastFocused = null;

            function resetReport() {
                alertBox.hidden = true;
                reportDone.hidden = true;
                reportForm.hidden = false;
                reportForm.reset();
                options.forEach(function (opt) {
                    opt.classList.remove('is-selected');
                });
                counter.textContent = '0 / ' + details.maxLength;
            }

            function openReport() {
                resetReport();
                lastFocused = document.activeElement;
                reportModal.hidden = false;
                document.body.classList.add('dt-rep-open');
                reportForm.querySelector('input[name="reason"]').focus();
            }

            function closeReport() {
                reportModal.hidden = true;
                document.body.classList.remove('dt-rep-open');
                lastFocused?.focus();
            }

            function showReportAlert(message) {
                alertText.textContent = message;
                alertBox.hidden = false;
                alertBox.scrollIntoView({ block: 'nearest' });
            }

            reportTrigger.addEventListener('click', function () {
                // Visiteur non connecte : on ouvre la connexion, comme ailleurs
                // sur le site (favoris, depot d'annonce).
                if (isGuest) {
                    window.openAuthModal?.('login', window.location.href);
                    return;
                }

                openReport();
            });

            reportModal.querySelectorAll('[data-dt-report-close]').forEach(function (el) {
                el.addEventListener('click', closeReport);
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !reportModal.hidden) closeReport();
            });

            // Un radio natif ne se stylise pas : on marque l'etiquette choisie.
            reportForm.addEventListener('change', function () {
                options.forEach(function (opt) {
                    opt.classList.toggle('is-selected', opt.querySelector('input').checked);
                });
            });

            details.addEventListener('input', function () {
                counter.textContent = details.value.length + ' / ' + details.maxLength;
            });

            reportForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                alertBox.hidden = true;

                const checked = reportForm.querySelector('input[name="reason"]:checked');

                if (!checked) {
                    showReportAlert('Merci de choisir un motif de signalement.');
                    return;
                }

                const precision = details.value.trim();

                if (checked.value === 'Autre motif' && precision === '') {
                    showReportAlert('Merci de préciser le motif de votre signalement.');
                    details.focus();
                    return;
                }

                const reason = precision ? checked.value + ' — ' + precision : checked.value;

                submitBtn.disabled = true;
                submitIcon.className = 'fa-solid fa-spinner';
                submitLabel.textContent = 'Envoi en cours…';

                try {
                    const res = await fetch(reportForm.dataset.dtReportUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ reason: reason })
                    });

                    // Session expiree pendant la visite
                    if (res.status === 401 || res.status === 419) {
                        closeReport();
                        window.openAuthModal?.('login', window.location.href);
                        return;
                    }

                    // « verified » et « approved » redirigent au lieu de repondre en
                    // JSON : fetch suit la redirection et renvoie une page HTML en 200.
                    // Sans ce garde-fou, la popin annoncait un succes fictif.
                    if (!(res.headers.get('content-type') || '').includes('application/json')) {
                        showReportAlert("Votre compte ne permet pas encore d'envoyer un signalement. Vérifiez votre adresse e-mail, puis réessayez.");
                        return;
                    }

                    const data = await res.json().catch(() => ({}));

                    // Laravel renvoie ici un message en anglais.
                    if (res.status === 403) {
                        showReportAlert("Votre adresse e-mail doit être vérifiée avant de pouvoir signaler une annonce.");
                        return;
                    }

                    if (!res.ok) {
                        showReportAlert(data.message || "Le signalement n'a pas pu être envoyé.");
                        return;
                    }

                    doneText.textContent = data.message || 'Notre équipe de modération va examiner cette annonce.';
                    reportForm.hidden = true;
                    reportDone.hidden = false;
                    reportDone.focus();
                } catch (err) {
                    console.error(err);
                    showReportAlert('Connexion impossible. Merci de réessayer dans un instant.');
                } finally {
                    submitBtn.disabled = false;
                    submitIcon.className = 'fa-solid fa-flag';
                    submitLabel.textContent = 'Envoyer le signalement';
                }
            });
        }

        // ---- Calendrier de réservation ----
        const datesInput = document.getElementById('reservation_dates');

        if (datesInput && typeof flatpickr !== 'undefined') {
            flatpickr(datesInput, {
                mode: 'range',
                locale: 'fr',
                minDate: 'today',
                @if ($ad->available_until)
                    maxDate: '{{ \Carbon\Carbon::parse($ad->available_until)->format('Y-m-d') }}',
                @endif
                disable: @json($reservedDates ?? []),
                dateFormat: 'Y-m-d',
                onChange: function (selectedDates) {
                    if (selectedDates.length !== 2) return;

                    document.getElementById('start_date').value = flatpickr.formatDate(selectedDates[0], 'Y-m-d');
                    document.getElementById('end_date').value = flatpickr.formatDate(selectedDates[1], 'Y-m-d');
                }
            });
        }
    });
</script>
@endpush

@endsection
