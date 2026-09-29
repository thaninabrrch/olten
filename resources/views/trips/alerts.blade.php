@extends('layouts.connected')
@section('title', 'Mes alertes trajet - Olten')

@php
    /*
     | Alertes trajet du membre : « prevenez-moi quand un Paris → Lyon est
     | publie ». La notification arrive dans la cloche du header a la
     | publication du trajet (TripAlert::notifyFor).
     |
     | Le bandeau explique le principe, avec un exemple de la notification
     | recue : sans lui, la page ne montrait qu'un formulaire et on ne
     | comprenait pas a quoi il servait. Chaque alerte s'affiche ensuite en
     | billet : la liaison a gauche, son etat a droite ($alert->matches :
     | trajets deja en ligne qui lui correspondent). Les alertes dont la
     | date est passee ferment la liste.
     */
    $today  = today()->toDateString();
    $count  = $alerts->count();
    $full   = $count >= $max;
    $alerts = $alerts->sortBy(fn ($alert) => $alert->isExpired())->values();
    $live   = $alerts->reject(fn ($alert) => $alert->isExpired());
    $online = $live->sum('matches');

    // L'exemple de notification reprend la premiere liaison suivie : le
    // membre reconnait ce qu'il recevra.
    $sample     = $live->first();
    $sampleFrom = $sample->depart ?? 'Paris';
    $sampleTo   = $sample->destination ?? 'Lyon';
    $sampleDay  = $sample?->date ?? today()->next(\Illuminate\Support\Carbon::SATURDAY);
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/trip-alerts.css') }}?v={{ @filemtime(public_path('assets/css/trip-alerts.css')) ?: 1 }}">
@endpush

@section('content')
<div class="sp-page alr-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Mes alertes trajet</span>
    </nav>

    {{-- Bandeau : le principe en une phrase, en trois etapes et en exemple --}}
    <section class="alr-hero">
        <div class="alr-hero-main">
            <div class="alr-hero-text">
                <span class="alr-eyebrow"><i class="fa-solid fa-bell"></i> Covoiturage</span>
                <h1>Mes alertes trajet</h1>
                <p>
                    Pas encore de trajet sur votre liaison ? Suivez-la : dès qu'un conducteur publie un trajet
                    qui vous correspond, vous êtes prévenu dans la cloche, en haut de page.
                </p>
                <div class="alr-hero-actions">
                    <a href="#alr-new" class="alr-btn alr-btn--primary" data-alr-focus>
                        <i class="fa-regular fa-bell"></i> Suivre une liaison
                    </a>
                    <a href="{{ route('services.show', \App\Models\Covoiturage::SERVICE_SLUG) }}" class="alr-btn alr-btn--ghost">
                        Rechercher un trajet
                    </a>
                </div>
            </div>

            <div class="alr-hero-art" aria-hidden="true">
                <svg viewBox="0 0 360 250" xmlns="http://www.w3.org/2000/svg" focusable="false">
                    {{-- Plan --}}
                    <rect x="8" y="8" width="344" height="234" rx="22" fill="#fff" fill-opacity=".035" stroke="#fff" stroke-opacity=".08"/>
                    <path d="M8 76 H352 M8 166 H352 M118 8 V242 M246 8 V242 M8 226 L150 96 M196 242 L352 118"
                          stroke="#fff" stroke-opacity=".05" stroke-width="6"/>
                    <rect x="136" y="178" width="64" height="40" rx="6" fill="#fff" fill-opacity=".03"/>
                    <rect x="266" y="92" width="62" height="54" rx="6" fill="#fff" fill-opacity=".03"/>

                    {{-- Liaison suivie --}}
                    <path d="M50 182 C 110 182, 108 120, 176 124 S 258 72, 292 68" fill="none"
                          stroke="#ff3c00" stroke-opacity=".28" stroke-width="12" stroke-linecap="round"/>
                    <path d="M50 182 C 110 182, 108 120, 176 124 S 258 72, 292 68" fill="none"
                          stroke="#ff8b62" stroke-width="3.5" stroke-dasharray="1 9" stroke-linecap="round"/>
                    <circle cx="50" cy="182" r="10" fill="#12141a" stroke="#fff" stroke-width="3.5"/>
                    <path d="M292 68 C 292 68 270 46 270 32 A 22 22 0 1 1 314 32 C 314 46 292 68 292 68 Z" fill="#ff3c00"/>
                    <circle cx="292" cy="32" r="8" fill="#fff"/>

                    {{-- Le trajet publie --}}
                    <g class="alr-car">
                        <path d="M166 104 L171 95 H186 L192 104 Z" fill="#fff"/>
                        <path d="M170.5 103 L174 97.5 H184 L188 103 Z" fill="#9aa4b5"/>
                        <rect x="158" y="103" width="42" height="16" rx="6" fill="#fff"/>
                        <rect x="194" y="107" width="5" height="4" rx="1.5" fill="#ffc24b"/>
                        <circle cx="169" cy="120" r="5" fill="#1b1d25" stroke="#fff" stroke-width="2"/>
                        <circle cx="189" cy="120" r="5" fill="#1b1d25" stroke="#fff" stroke-width="2"/>
                    </g>

                    {{-- La cloche qui sonne --}}
                    <circle class="alr-ping" cx="322" cy="104" r="18" fill="none" stroke="#ff8b62" stroke-width="2"/>
                    <circle class="alr-ping alr-ping--late" cx="322" cy="104" r="18" fill="none" stroke="#ff8b62" stroke-width="2"/>
                    <circle cx="322" cy="104" r="18" fill="#fff"/>
                    <path d="M322 93 C 316.5 93 313 97 313 102 V107 L310 111 H334 L331 107 V102 C 331 97 327.5 93 322 93 Z" fill="#ff3c00"/>
                    <circle cx="322" cy="114" r="2.6" fill="#ff3c00"/>
                    <circle cx="334" cy="91" r="5" fill="#ff3c00" stroke="#1b1d25" stroke-width="2"/>
                </svg>

                <div class="alr-toast">
                    <span class="alr-toast-icon"><i class="fa-solid fa-route"></i></span>
                    <span class="alr-toast-body">
                        <strong>Nouveau trajet {{ $sampleFrom }} → {{ $sampleTo }}</strong>
                        <small>Départ le {{ $sampleDay->translatedFormat('D d M') }} à 08:00 · 18,50&nbsp;€ la place</small>
                    </span>
                    <span class="alr-toast-dot"></span>
                </div>
                <span class="alr-toast-caption">Exemple de notification</span>
            </div>
        </div>

        <ol class="alr-steps">
            <li>
                <span class="alr-step-num">1</span>
                <span>
                    <strong>Vous suivez une liaison</strong>
                    <small>Un départ, une arrivée : tous les jours ou à une date précise.</small>
                </span>
            </li>
            <li>
                <span class="alr-step-num">2</span>
                <span>
                    <strong>Un conducteur la publie</strong>
                    <small>Chaque nouveau trajet est comparé à vos alertes.</small>
                </span>
            </li>
            <li>
                <span class="alr-step-num">3</span>
                <span>
                    <strong>Vous êtes prévenu</strong>
                    <small>La notification arrive dans la cloche : il ne reste qu'à réserver.</small>
                </span>
            </li>
        </ol>
    </section>

    {{-- Nouvelle alerte --}}
    <section class="alr-builder" id="alr-new" aria-labelledby="alr-new-title">
        <div class="alr-builder-head">
            <div>
                <h2 id="alr-new-title">Suivre une nouvelle liaison</h2>
                <p>Indiquez la ville de départ et la ville d'arrivée, comme dans une recherche de trajet.</p>
            </div>
            <div class="alr-quota">
                <span><strong>{{ $count }}</strong> / {{ $max }} alertes</span>
                <span class="alr-quota-bar"><span style="width: {{ min(100, round($count / $max * 100)) }}%"></span></span>
            </div>
        </div>

        <form method="POST" action="{{ route('trips.alerts.store') }}" class="alr-form" data-alr-form>
            @csrf

            @if ($errors->any())
                <div class="alr-errors" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>
                        <strong>L'alerte n'a pas pu être créée.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="alr-fields">
                <div class="alr-field alr-field--from">
                    <label for="alertDepart">Départ</label>
                    <div class="alr-input">
                        <span class="alr-input-icon is-from"></span>
                        <input type="text" id="alertDepart" name="depart" list="alr-cities" maxlength="120" autocomplete="off"
                               value="{{ old('depart') }}" placeholder="Ex. Paris" required>
                    </div>
                </div>

                <button type="button" class="alr-swap" data-alr-swap
                        aria-label="Inverser le départ et l'arrivée" title="Inverser le départ et l'arrivée">
                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                </button>

                <div class="alr-field alr-field--to">
                    <label for="alertDestination">Arrivée</label>
                    <div class="alr-input">
                        <span class="alr-input-icon is-to"><i class="fa-solid fa-location-dot"></i></span>
                        <input type="text" id="alertDestination" name="destination" list="alr-cities" maxlength="120" autocomplete="off"
                               value="{{ old('destination') }}" placeholder="Ex. Lyon" required>
                    </div>
                </div>

                <div class="alr-field alr-field--when">
                    <span class="alr-label" id="alr-when-label">Quand ?</span>
                    <div class="alr-when {{ old('date') ? 'has-date' : '' }}" data-alr-when role="group" aria-labelledby="alr-when-label">
                        <button type="button" class="alr-when-any" data-alr-any aria-pressed="{{ old('date') ? 'false' : 'true' }}">
                            Tous les jours
                        </button>
                        <label class="alr-when-date">
                            <i class="fa-regular fa-calendar"></i>
                            <input type="date" name="date" value="{{ old('date') }}" min="{{ $today }}"
                                   aria-label="Ou une date précise (facultatif)">
                        </label>
                    </div>
                </div>

                <button type="submit" class="alr-submit" @disabled($full)>
                    <i class="fa-regular fa-bell"></i> Créer l'alerte
                </button>
            </div>

            <p class="alr-hint">
                <i class="fa-solid fa-circle-info"></i>
                <span data-alr-hint>
                    @if ($full)
                        Vous suivez déjà {{ $max }} liaisons : supprimez une alerte pour en créer une nouvelle.
                    @else
                        Sans date, vous êtes prévenu de chaque trajet publié sur la liaison ; avec une date, seulement
                        des trajets qui partent ce jour-là.
                    @endif
                </span>
            </p>

            @if ($cities->isNotEmpty())
                <datalist id="alr-cities">
                    @foreach ($cities as $city)
                        <option value="{{ $city }}"></option>
                    @endforeach
                </datalist>
            @endif
        </form>
    </section>

    {{-- Alertes en cours, en billets --}}
    <section class="alr-list" aria-labelledby="alr-list-title">
        <div class="alr-list-head">
            <h2 id="alr-list-title">Liaisons suivies</h2>
            @if ($count)
                <p>
                    {{ $live->count() }} alerte{{ $live->count() > 1 ? 's' : '' }} active{{ $live->count() > 1 ? 's' : '' }}
                    @if ($online)
                        · <strong>{{ $online }} trajet{{ $online > 1 ? 's' : '' }} en ligne</strong>
                    @endif
                </p>
            @endif
        </div>

        @if ($count)
            <ul class="alr-tickets">
                @foreach ($alerts as $alert)
                    @php
                        $expired = $alert->isExpired();
                        $state   = $expired ? 'expired' : ($alert->matches ? 'online' : 'watching');
                        $route   = $alert->depart . ' → ' . $alert->destination;
                        $filters = $alert->date
                            ? ['start_date' => $alert->date->toDateString(), 'end_date' => $alert->date->toDateString()]
                            : [];
                    @endphp

                    <li class="alr-item is-{{ $state }}">
                        <article class="alr-ticket">
                            <div class="alr-ticket-main">
                                <div class="alr-route">
                                    <span class="alr-city">
                                        <small>Départ</small>
                                        <strong>{{ $alert->depart }}</strong>
                                    </span>
                                    <span class="alr-track" aria-hidden="true"><i class="fa-solid fa-car-side"></i></span>
                                    <span class="alr-city is-end">
                                        <small>Arrivée</small>
                                        <strong>{{ $alert->destination }}</strong>
                                    </span>
                                </div>

                                <div class="alr-meta">
                                    <span class="alr-meta-info">
                                        <span class="alr-chip">
                                            <i class="fa-regular fa-calendar"></i>
                                            {{ $alert->date ? ucfirst($alert->date->translatedFormat('l d F')) : 'Tous les jours' }}
                                        </span>
                                        <span class="alr-since">Suivie depuis le {{ $alert->created_at->translatedFormat('d M') }}</span>
                                    </span>

                                    <form method="POST" action="{{ route('trips.alerts.destroy', $alert) }}" class="alr-remove"
                                          data-alr-remove data-route="{{ $route }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" aria-label="Supprimer l'alerte {{ $route }}" title="Supprimer l'alerte">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="alr-stub">
                                @if ($state === 'online')
                                    <strong class="alr-stub-num">{{ $alert->matches }}</strong>
                                    <span class="alr-stub-label">trajet{{ $alert->matches > 1 ? 's' : '' }} en ligne</span>
                                    <a href="{{ route('covoiturage.trips', ['from' => $alert->depart, 'to' => $alert->destination] + $filters) }}"
                                       class="alr-stub-btn">
                                        Voir <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                @elseif ($state === 'watching')
                                    <span class="alr-radar" aria-hidden="true"></span>
                                    <span class="alr-stub-label">En veille</span>
                                    <small>Aucun trajet pour l'instant</small>
                                @else
                                    <i class="fa-regular fa-calendar-xmark alr-stub-icon" aria-hidden="true"></i>
                                    <span class="alr-stub-label">Date passée</span>
                                    <small>Elle ne trouvera plus rien</small>
                                @endif
                            </div>
                        </article>
                    </li>
                @endforeach
            </ul>
        @else
            {{-- Aucune alerte : un billet d'exemple montre ce que donnera la premiere --}}
            <div class="alr-empty">
                <div class="alr-item is-watching is-example" aria-hidden="true">
                    <div class="alr-ticket">
                        <div class="alr-ticket-main">
                            <div class="alr-route">
                                <span class="alr-city"><small>Départ</small><strong>Paris</strong></span>
                                <span class="alr-track"><i class="fa-solid fa-car-side"></i></span>
                                <span class="alr-city is-end"><small>Arrivée</small><strong>Lyon</strong></span>
                            </div>
                            <div class="alr-meta">
                                <span class="alr-meta-info">
                                    <span class="alr-chip"><i class="fa-regular fa-calendar"></i> Tous les jours</span>
                                    <span class="alr-chip is-example">Exemple</span>
                                </span>
                            </div>
                        </div>
                        <div class="alr-stub">
                            <span class="alr-radar"></span>
                            <span class="alr-stub-label">En veille</span>
                            <small>Aucun trajet pour l'instant</small>
                        </div>
                    </div>
                </div>

                <div class="alr-empty-text">
                    <strong>Vous ne suivez aucune liaison pour l'instant</strong>
                    <p>
                        Renseignez un départ et une arrivée ci-dessus : votre alerte apparaîtra ici, comme cet
                        exemple, avec le nombre de trajets déjà en ligne sur la liaison.
                    </p>
                </div>
            </div>
        @endif

        <p class="alr-tip">
            <i class="fa-regular fa-lightbulb"></i>
            <span>
                Depuis une <a href="{{ route('services.show', \App\Models\Covoiturage::SERVICE_SLUG) }}">recherche de covoiturage</a>,
                le bouton « Me prévenir » crée aussi une alerte, en un clic.
            </span>
        </p>
    </section>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var form = document.querySelector('[data-alr-form]');

        if (form) {
            var from  = form.querySelector('#alertDepart');
            var to    = form.querySelector('#alertDestination');
            var swap  = form.querySelector('[data-alr-swap]');
            var when  = form.querySelector('[data-alr-when]');
            var any   = form.querySelector('[data-alr-any]');
            var date  = when.querySelector('input[type="date"]');
            var hint  = form.querySelector('[data-alr-hint]');
            var full  = form.querySelector('.alr-submit').disabled;

            swap.addEventListener('click', function () {
                var value = from.value;
                from.value = to.value;
                to.value = value;
                swap.classList.remove('is-spinning');
                void swap.offsetWidth; // relance l'animation a chaque clic
                swap.classList.add('is-spinning');
            });

            // « Tous les jours » et la date s'excluent : choisir l'un vide
            // l'autre, et la phrase d'aide dit ce que l'alerte fera.
            function sync() {
                var hasDate = !!date.value;
                when.classList.toggle('has-date', hasDate);
                any.setAttribute('aria-pressed', hasDate ? 'false' : 'true');
                if (full) return;

                if (hasDate) {
                    var day = new Date(date.value + 'T12:00:00')
                        .toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
                    hint.textContent = 'Vous serez prévenu seulement des trajets qui partent le ' + day + '.';
                } else {
                    hint.textContent = 'Vous serez prévenu de chaque nouveau trajet publié sur cette liaison, quel que soit le jour.';
                }
            }

            any.addEventListener('click', function () { date.value = ''; sync(); });
            date.addEventListener('input', sync);
            date.addEventListener('change', sync);

            // Toute la case ouvre le calendrier, pas seulement la petite icone.
            when.querySelector('.alr-when-date').addEventListener('click', function () {
                if (typeof date.showPicker === 'function') {
                    try { date.showPicker(); } catch (e) { /* deja ouvert */ }
                }
            });

            sync();
        }

        document.querySelectorAll('[data-alr-focus]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                var section = document.getElementById('alr-new');
                if (!section || !from) return;
                e.preventDefault();
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                setTimeout(function () { from.focus({ preventScroll: true }); }, 450);
            });
        });

        // Suppression confirmee : la corbeille est petite, un clic de
        // travers ne doit pas faire perdre une alerte.
        document.querySelectorAll('[data-alr-remove]').forEach(function (removeForm) {
            removeForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var route = removeForm.dataset.route;
                var confirmed = function () { removeForm.submit(); };

                if (window.Swal) {
                    Swal.fire({
                        title: 'Supprimer cette alerte ?',
                        text: 'Vous ne serez plus prévenu des nouveaux trajets ' + route + '.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Supprimer',
                        cancelButtonText: 'Annuler',
                        confirmButtonColor: '#ff3c00',
                        reverseButtons: true
                    }).then(function (result) { if (result.isConfirmed) confirmed(); });
                } else if (window.confirm('Supprimer l\'alerte ' + route + ' ?')) {
                    confirmed();
                }
            });
        });
    })();
</script>
@endpush
