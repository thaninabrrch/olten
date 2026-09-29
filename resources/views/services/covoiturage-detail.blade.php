@extends('layouts.main')

@section('title', $trip->depart_ville . ' → ' . $trip->destination_ville . ' - Détails du trajet - Olten.fr')

@php
    $driver  = $trip->conducteur;
    $vehicle = $driver?->vehicle;
    $avatar  = $trip->photo_conducteur
        ?: ($driver?->profile_photo ? asset('storage/' . $driver->profile_photo) : null);

    $modes = [
        'womenOnly'    => ['fa-venus', 'Femmes uniquement', 'Ce trajet est réservé aux passagères.'],
        'maxBackSeats' => ['fa-user-group', 'Maximum 2 à l\'arrière', 'Plus de place pour voyager confortablement.'],
        'mixed'        => ['fa-users', 'Trajet mixte', 'Ouvert à tous les passagers.'],
    ];
    $mode = $modes[$trip->passenger_mode] ?? null;
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/trip-passengers.css') }}?v={{ @filemtime(public_path('assets/css/trip-passengers.css')) ?: 1 }}">
@endpush

@section('content')

<div class="cv-page cvd">

    <div class="cvd-wrap">

        {{-- Fil de titre --}}
        <div class="cvd-head">
            <a href="{{ route('covoiturage.trips', ['from' => $trip->depart_ville, 'to' => $trip->destination_ville]) }}"
               class="cvd-back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <span class="cvd-head-label">Détails du trajet</span>
            <span class="cvd-head-route">
                {{ $trip->depart_ville }} <i class="fa-solid fa-arrow-right"></i> {{ $trip->destination_ville }}
            </span>
        </div>

        {{-- Ligne 1 : carte + récapitulatif --}}
        <div class="cvd-row cvd-row--map">

            <div class="cvd-map-card">
                <div id="cvdMap" class="cvd-map"
                     data-legs="{{ json_encode(collect($legs)->map(fn ($leg) => ['key' => $leg['label'], 'path' => $leg['path']])->values()) }}"></div>

                <div class="cvd-map-driver">
                    @if ($avatar)
                        <img src="{{ $avatar }}" alt="{{ $driver->name ?? 'Conducteur' }}">
                    @else
                        <span class="cvd-avatar-initial">{{ strtoupper(mb_substr($driver->name ?? '?', 0, 1)) }}</span>
                    @endif
                    <span class="cvd-map-driver-info">
                        <strong>{{ $driver->name ?? 'Conducteur' }}</strong>
                        @if ($driver?->is_approved)
                            <small class="is-verified"><i class="fa-solid fa-circle-check"></i> Vérifié</small>
                        @else
                            <small>Conducteur</small>
                        @endif
                    </span>
                </div>
            </div>

            <aside class="cvd-side">

                @php
                    $isOwner   = auth()->check() && auth()->id() === $driver?->id;
                    $bookUrl   = route('covoiturage.checkout', $trip);
                    $seatsLeft = $trip->seatsLeft();
                    $isFull    = $trip->statut === 'complet' || $seatsLeft < 1;

                    // Places payées : une réservation peut en compter plusieurs.
                    $bookedByLeg = collect($trip->legKeys())->mapWithKeys(fn ($l) => [$l => $trip->seatsBooked($l)]);
                    $booked      = (int) $bookedByLeg->max();

                    // Places libres par sens : un sens complet ne peut plus être coché.
                    $leftByLeg = collect($legs)->map(fn ($leg, $key) => $trip->seatsLeft($key));

                    // Montants de départ (une place, sens encore libres) ; le script
                    // les recalcule à chaque changement, avec la même règle d'arrondi
                    // que le paiement.
                    $rate     = \App\Models\Covoiturage::serviceRate();
                    $subtotal = collect($legs)->filter(fn ($leg, $key) => $leftByLeg[$key] > 0)->sum('total');
                    $fee      = \App\Models\Covoiturage::serviceFee($subtotal);
                    $euros    = fn ($amount) => number_format((float) $amount, 2, ',', ' ') . ' €';
                @endphp

                <div class="cvd-recap" data-cvd-recap data-cvd-rate="{{ $rate }}">
                    <span class="cvd-recap-label">Récapitulatif</span>

                    {{-- Chaque sens se coche : le passager choisit l'aller, le retour ou les deux. --}}
                    @foreach ($legs as $key => $leg)
                        <label class="cvd-recap-leg {{ $leftByLeg[$key] < 1 ? 'is-off is-full' : '' }}">
                            <input type="checkbox" class="cvd-recap-check"
                                   @checked($leftByLeg[$key] > 0) @disabled($leftByLeg[$key] < 1)
                                   data-cvd-leg="{{ $key }}"
                                   data-cvd-price="{{ $leg['total'] }}"
                                   data-cvd-seats-left="{{ $leftByLeg[$key] }}">
                            <span class="cvd-recap-box"><i class="fa-solid fa-check"></i></span>

                            <span class="cvd-recap-icon {{ $key === 'retour' ? 'is-return' : '' }}">
                                <i class="fa-solid {{ $key === 'retour' ? 'fa-arrow-left-long' : 'fa-arrow-right-long' }}"></i>
                            </span>

                            <span class="cvd-recap-leg-info">
                                <strong>{{ $key === 'retour' ? 'Retour' : 'Aller' }}</strong>
                                <small>
                                    {{ $leg['from'] }} → {{ $leg['to'] }}
                                    @if ($leg['date'])
                                        · {{ $leg['date']->translatedFormat('d M') }}
                                    @endif
                                    @if ($leg['time'])
                                        {{ $leg['time'] }}
                                    @endif
                                    @if ($leftByLeg[$key] < 1)
                                        · Complet
                                    @endif
                                </small>
                            </span>

                            <span class="cvd-recap-leg-price">
                                {{ $euros($leg['total']) }}
                            </span>
                        </label>
                    @endforeach

                    @unless ($isOwner || $isFull)
                        {{-- Nombre de places, sens par sens : chacun est borné par
                             ses propres places libres (le serveur revérifie au
                             paiement). On peut ainsi prendre 2 places à l'aller et
                             1 au retour. --}}
                        @foreach ($legs as $key => $leg)
                            <div class="cvd-recap-seats" data-cvd-seats-row="{{ $key }}" @if ($leftByLeg[$key] < 1) hidden @endif>
                                <span class="cvd-recap-seats-info">
                                    <strong>{{ count($legs) > 1 ? ($key === 'retour' ? 'Places au retour' : "Places à l'aller") : 'Places' }}</strong>
                                    <small>{{ $leftByLeg[$key] }} disponible{{ $leftByLeg[$key] > 1 ? 's' : '' }}</small>
                                </span>

                                <div class="cvd-stepper">
                                    <button type="button" data-cvd-step="-1" aria-label="Retirer une place" disabled>
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                    <input type="number" value="1" min="1" max="{{ max(1, $leftByLeg[$key]) }}"
                                           inputmode="numeric" data-cvd-seats="{{ $key }}"
                                           aria-label="Nombre de places {{ $key === 'retour' ? 'au retour' : "à l'aller" }}">
                                    <button type="button" data-cvd-step="1" aria-label="Ajouter une place"
                                            @disabled($leftByLeg[$key] <= 1)>
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @endunless

                    <div class="cvd-recap-lines">
                        <div class="cvd-recap-line">
                            <span data-cvd-subtotal-label>Trajet · 1 place</span>
                            <span data-cvd-subtotal>{{ $euros($subtotal) }}</span>
                        </div>
                        <div class="cvd-recap-line">
                            <span>Frais de service ({{ rtrim(rtrim(number_format($rate, 2, ',', ''), '0'), ',') }} %)</span>
                            <span data-cvd-fee>{{ $euros($fee) }}</span>
                        </div>
                    </div>

                    <div class="cvd-recap-total">
                        <span>Total</span>
                        <strong data-cvd-total>{{ $euros($subtotal + $fee) }}</strong>
                    </div>

                    {{-- La destination reste la meme pour tous : un visiteur non
                         connecte voit la popin de connexion (data-auth-required,
                         gere par assets/js/auth.js) et arrive ici une fois
                         connecte, sens et places selectionnes compris. --}}
                    @if ($isOwner)
                        <p class="cvd-recap-hint">C'est votre trajet.</p>
                    @elseif ($isFull)
                        <p class="cvd-recap-hint">Ce trajet est complet.</p>
                    @else
                        <a href="{{ $bookUrl }}" class="cvd-recap-btn" data-cvd-book
                           data-cvd-href="{{ $bookUrl }}" data-auth-required>
                            {{ $trip->isManual() ? 'Demander à réserver' : 'Réserver maintenant' }}
                        </a>

                        {{-- Validation manuelle : le passager le sait avant de payer --}}
                        @if ($trip->isManual())
                            <p class="cvd-recap-hint">
                                Le conducteur accepte chaque demande. S'il refuse, ou s'il ne répond pas
                                avant le départ, vous êtes intégralement remboursé.
                            </p>
                        @endif

                        <p class="cvd-recap-hint" data-cvd-hint hidden>
                            Sélectionnez au moins un sens pour réserver.
                        </p>
                    @endif
                </div>

                <div class="cvd-vehicle">
                    <span class="cvd-vehicle-icon"><i class="fa-solid fa-car-side"></i></span>
                    <span class="cvd-vehicle-info">
                        <strong>
                            {{ $vehicle ? \Illuminate\Support\Str::title(trim($vehicle->marque . ' ' . $vehicle->modele)) : 'Véhicule non renseigné' }}
                        </strong>
                        <small>
                            {{ $vehicle?->couleur ? ucfirst($vehicle->couleur) . ' · ' : '' }}
                            {{ $isFull ? 'Complet' : $trip->seatsLeftLabel() }}
                        </small>
                    </span>
                </div>

            </aside>
        </div>

        {{-- Ligne 2 : segments + infos voyage --}}
        <div class="cvd-row cvd-row--legs">

            <div class="cvd-legs">
                @if (count($legs) > 1)
                    <div class="cvd-tabs">
                        @foreach ($legs as $key => $leg)
                            <button type="button" class="cvd-tab {{ $loop->first ? 'is-active' : '' }}"
                                    data-cvd-tab="{{ $key }}">
                                {{ $leg['label'] }}
                            </button>
                        @endforeach
                    </div>
                @endif

                @foreach ($legs as $key => $leg)
                    <div class="cvd-leg {{ $loop->first ? 'is-active' : '' }}" data-cvd-panel="{{ $key }}">

                        <div class="cvd-leg-grid">
                            <div class="cvd-leg-main">
                                <h2 class="cvd-leg-title">
                                    {{ $leg['from'] }} <i class="fa-solid fa-arrow-right"></i> {{ $leg['to'] }}
                                </h2>

                                <p class="cvd-leg-meta">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ $leg['date']?->translatedFormat('D d M Y') ?? 'Date à confirmer' }}
                                    @if ($leg['duration'] > 0)
                                        · <i class="fa-regular fa-clock"></i>
                                        {{ intdiv($leg['duration'], 3600) }}h{{ str_pad((string) intdiv($leg['duration'] % 3600, 60), 2, '0', STR_PAD_LEFT) }}
                                    @endif
                                    @if ($leg['distance'] > 0)
                                        · <i class="fa-solid fa-road"></i>
                                        {{ number_format($leg['distance'] / 1000, 1, ',', ' ') }} km
                                    @endif
                                </p>

                                <div class="cvd-leg-stop">
                                    <span class="cvd-leg-time">{{ $leg['time'] ?: '--:--' }}</span>
                                    <span class="cvd-leg-dot is-start"></span>
                                    <span class="cvd-leg-place">
                                        <strong>{{ $leg['from'] }}</strong>
                                        <small>{{ $leg['address']['from'] }}</small>
                                    </span>
                                </div>

                                <div class="cvd-leg-rail"><span></span></div>

                                <div class="cvd-leg-stop">
                                    <span class="cvd-leg-time">
                                        {{ $leg['arrival'] ?: '--:--' }}
                                        @if ($leg['next_day'])
                                            <em>+1j</em>
                                        @endif
                                    </span>
                                    <span class="cvd-leg-dot is-end"></span>
                                    <span class="cvd-leg-place">
                                        <strong>{{ $leg['to'] }}</strong>
                                        <small>{{ $leg['address']['to'] }}</small>
                                    </span>
                                </div>
                            </div>

                            <div class="cvd-leg-pricing">
                                <span class="cvd-pricing-label">
                                    {{ count($leg['segments']) > 1 ? 'Escales et tarifs' : 'Tarif' }}
                                </span>

                                @forelse ($leg['segments'] as $segment)
                                    @php
                                        // Les trajets anterieurs au formulaire actuel n'ont pas
                                        // toujours les villes de leurs segments : on retombe alors
                                        // sur un libelle d'etape plutot que sur une fleche vide.
                                        $segFrom = \App\Models\Covoiturage::villeCourte($segment['from'] ?? '');
                                        $segTo   = \App\Models\Covoiturage::villeCourte($segment['to'] ?? '');
                                    @endphp
                                    <div class="cvd-price-row">
                                        <span>
                                            @if ($segFrom && $segTo)
                                                {{ $segFrom }} → {{ $segTo }}
                                            @else
                                                Étape {{ $loop->iteration }}
                                            @endif
                                        </span>
                                        <strong>{{ number_format((float) ($segment['price'] ?? 0), 0, ',', ' ') }}€</strong>
                                    </div>
                                @empty
                                    <div class="cvd-price-row">
                                        <span>Trajet complet</span>
                                        <strong>{{ number_format($leg['total'], 0, ',', ' ') }}€</strong>
                                    </div>
                                @endforelse

                                <div class="cvd-price-total">
                                    <span>Total {{ $leg['key'] === 'retour' ? 'retour' : 'aller' }}</span>
                                    <strong>{{ number_format($leg['total'], 2, ',', ' ') }}€</strong>
                                </div>

                                <div class="cvd-price-note">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span>
                                        Prix par place, fixé par le conducteur. Le paiement se règle
                                        directement avec lui, aucun frais n'est ajouté par Olten.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <aside class="cvd-infos">
                <span class="cvd-infos-label">Infos voyage</span>

                @if ($mode)
                    <div class="cvd-info">
                        <span class="cvd-info-icon is-pink"><i class="fa-solid {{ $mode[0] }}"></i></span>
                        <span class="cvd-info-text">
                            <strong>{{ $mode[1] }}</strong>
                            <small>{{ $mode[2] }}</small>
                        </span>
                    </div>
                @endif

                <div class="cvd-info">
                    <span class="cvd-info-icon is-green">
                        <i class="fa-solid {{ $trip->retour ? 'fa-arrow-right-arrow-left' : 'fa-arrow-right-long' }}"></i>
                    </span>
                    <span class="cvd-info-text">
                        <strong>{{ $trip->retour ? 'Aller-retour' : 'Aller simple' }}</strong>
                        <small>
                            {{ $trip->retour && $trip->return_date
                                ? 'Retour le ' . $trip->return_date->translatedFormat('d F Y')
                                : 'Trajet sans retour prévu' }}
                        </small>
                    </span>
                </div>

                <div class="cvd-info">
                    <span class="cvd-info-icon is-orange">
                        <i class="fa-solid {{ $trip->booking_mode === 'instant' ? 'fa-bolt' : 'fa-hourglass-half' }}"></i>
                    </span>
                    <span class="cvd-info-text">
                        <strong>{{ $trip->booking_mode === 'instant' ? 'Réservation immédiate' : 'Accord du conducteur' }}</strong>
                        <small>
                            {{ $trip->booking_mode === 'instant'
                                ? 'Votre place est confirmée sans attente.'
                                : 'Le conducteur valide chaque demande.' }}
                        </small>
                    </span>
                </div>
                <div class="cvd-info">
                    <span class="cvd-info-icon is-blue"><i class="fa-solid fa-user-group"></i></span>
                    <span class="cvd-info-text">
                        <strong>{{ $seatsLeft }} place{{ $seatsLeft > 1 ? 's' : '' }} restante{{ $seatsLeft > 1 ? 's' : '' }}</strong>
                        <small>
                            @if ($isFull)
                                Trajet complet
                            @elseif ($trip->retour)
                                Aller : {{ $trip->seatsLeft('aller') }} · Retour : {{ $trip->seatsLeft('retour') }}
                            @else
                                Réservez la vôtre avant qu'elle parte.
                            @endif
                        </small>
                    </span>
                </div>
                <div class="cvd-info">
    <span class="cvd-info-icon is-green"><i class="fa-solid fa-ticket"></i></span>
    <span class="cvd-info-text">
        @if ($booked > 0)
            <strong>{{ $booked }} place{{ $booked > 1 ? 's' : '' }} réservée{{ $booked > 1 ? 's' : '' }}</strong>
            <small>
                @if ($trip->retour)
                    Aller : {{ $bookedByLeg['aller'] }} · Retour : {{ $bookedByLeg['retour'] }}
                @else
                    Par d'autres passagers sur ce trajet.
                @endif
            </small>
        @else
            <strong>Aucune place réservée</strong>
            <small>Soyez le premier à réserver ce trajet.</small>
        @endif
    </span>
</div>
            </aside>
        </div>

        {{-- Ligne 3 : les passagers, place par place et sens par sens :
             prenom, nom et photo de chacun, jamais ses coordonnees. --}}
        @php
            $viewer   = auth()->user();
            $anyRider = $passengers->contains(fn ($list) => $list->isNotEmpty());
        @endphp

        <section class="cvd-riders" id="passagers" aria-labelledby="cvd-riders-title">
            <div class="cvd-riders-head">
                <span class="cvd-riders-icon"><i class="fa-solid fa-user-group"></i></span>
                <div class="cvd-riders-titles">
                    <h2 id="cvd-riders-title">Passagers</h2>
                    <p>
                        @if ($anyRider)
                            Les membres qui ont réservé leur place{{ count($legs) > 1 ? ', sens par sens' : '' }}.
                        @else
                            Personne n'a encore réservé : les passagers apparaîtront ici, place par place.
                        @endif
                    </p>
                </div>
                @if ($isOwner && $anyRider)
                    <a href="{{ route('trips.received') }}" class="cvd-riders-link">
                        Coordonnées de vos passagers <i class="fa-solid fa-arrow-right"></i>
                    </a>
                @endif
            </div>

            <div class="cvd-riders-legs">
                @foreach ($legs as $key => $leg)
                    @php
                        // Une reservation de plusieurs places en occupe autant :
                        // la premiere porte le passager, les suivantes ses
                        // accompagnants.
                        $seats = collect();
                        foreach ($passengers[$key] ?? [] as $booking) {
                            for ($i = 0; $i < $booking->seatsOn($key); $i++) {
                                $seats->push(['booking' => $booking, 'guest' => $i > 0]);
                            }
                        }
                        $taken = $seats->count();
                        $free  = max(0, (int) $trip->nb_places - $taken);
                    @endphp

                    <div class="cvd-riders-leg">
                        <div class="cvd-riders-leg-head">
                            <span class="cvd-riders-dir {{ $key === 'retour' ? 'is-return' : '' }}">
                                <i class="fa-solid {{ $key === 'retour' ? 'fa-arrow-left-long' : 'fa-arrow-right-long' }}"></i>
                            </span>
                            <span class="cvd-riders-leg-title">
                                <strong>{{ $key === 'retour' ? 'Retour' : 'Aller' }}</strong>
                                <small>
                                    {{ $leg['from'] }} → {{ $leg['to'] }}{{ $leg['date'] ? ' · ' . $leg['date']->translatedFormat('D d M') : '' }}
                                </small>
                            </span>
                            <span class="cvd-riders-count {{ $free < 1 ? 'is-full' : '' }}">
                                <strong>{{ $taken }}</strong> / {{ (int) $trip->nb_places }} réservée{{ $taken > 1 ? 's' : '' }}
                            </span>
                        </div>

                        <ul class="cvd-seats">
                            @foreach ($seats as $seat)
                                @php
                                    $rider   = $seat['booking']->passenger;
                                    $isMe    = $viewer && $rider && (int) $rider->id === (int) $viewer->id;
                                    $name    = $rider?->public_name ?? 'Membre';
                                    // Place payée et bloquée, mais le conducteur doit encore accepter
                                    $waiting = $seat['booking']->isPending();
                                @endphp

                                <li class="cvd-seat is-taken {{ $seat['guest'] ? 'is-guest' : '' }} {{ $isMe ? 'is-me' : '' }} {{ $waiting ? 'is-pending' : '' }}">
                                    <span class="cvd-seat-avatar">
                                        @if ($rider)
                                            <span>{{ $seat['guest'] ? '+1' : $rider->initials }}</span>
                                            @if (! $seat['guest'] && $rider->avatar_url)
                                                <img src="{{ $rider->avatar_url }}" alt="" loading="lazy" onerror="this.remove()">
                                            @endif
                                        @else
                                            <i class="fa-solid fa-user"></i>
                                        @endif
                                    </span>

                                    @if ($seat['guest'])
                                        <strong>+ 1 place</strong>
                                        <small>avec {{ $name }}</small>
                                    @else
                                        <strong>{{ $name }}</strong>
                                        @if ($isMe)
                                            <small class="cvd-seat-you">Vous</small>
                                        @endif
                                        @if ($waiting)
                                            <small class="cvd-seat-wait">En attente</small>
                                        @elseif (! $isMe)
                                            <small>Passager</small>
                                        @endif
                                    @endif
                                </li>
                            @endforeach

                            @for ($i = 0; $i < $free; $i++)
                                <li class="cvd-seat is-free">
                                    <span class="cvd-seat-avatar"><i class="fa-solid fa-plus"></i></span>
                                    <strong>Libre</strong>
                                    <small>Disponible</small>
                                </li>
                            @endfor
                        </ul>
                    </div>
                @endforeach
            </div>

            @if ($anyRider)
                <p class="cvd-riders-note">
                    <i class="fa-solid fa-lock"></i>
                    <span>Les coordonnées des passagers ne sont jamais affichées : seul le conducteur les reçoit.</span>
                </p>

                @if ($passengers->contains(fn ($list) => $list->contains(fn ($booking) => $booking->isPending())))
                    <p class="cvd-riders-note">
                        <i class="fa-solid fa-hourglass-half"></i>
                        <span>« En attente » : la place est payée et bloquée, le conducteur doit encore accepter la demande.</span>
                    </p>
                @endif
            @endif
        </section>

        {{-- Ligne 4 : aide + conditions --}}
        <div class="cvd-row cvd-row--help">

            <div class="cvd-help">
                <span class="cvd-help-icon"><i class="fa-regular fa-life-ring"></i></span>
                <h3 class="cvd-help-title">Besoin d'aide ?</h3>
                <p class="cvd-help-text">
                    Une question sur ce trajet, le point de rendez-vous ou le paiement ?
                    Notre équipe vous répond avant comme après votre voyage.
                </p>
                <a href="{{ route('contact') }}" class="cvd-help-btn">Contacter le support</a>
            </div>

      <div class="cvd-msg">
    <div class="cvd-msg-head">
        <span class="cvd-msg-icon"><i class="fa-regular fa-message"></i></span>
        <div>
            <h3 class="cvd-msg-title">Message du conducteur</h3>
            <p class="cvd-msg-sub">Instructions et préférences de voyage</p>
        </div>
    </div>

    <div class="cvd-msg-box">
        @if ($trip->message_conducteur)
            « {{ $trip->message_conducteur }} »
        @else
            Prévenez le conducteur au plus tôt en cas d'empêchement : une place libérée
            à temps peut profiter à un autre passager. Les conditions d'annulation sont
            convenues directement avec lui.
        @endif
    </div>

    {{-- adapte la condition à ton champ réel --}}
    @if ($trip->bagages_autorises ?? true)
        <div class="cvd-msg-foot">
            <span class="cvd-msg-dot"></span>
            <span>À savoir avant le départ</span>
        </div>
    @endif
</div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // ---- Recapitulatif : sens coches, nombre de places et montants ----
        // Les montants se calculent en centimes, avec le meme arrondi que le
        // paiement (TripBookingController::amounts) : la page de paiement
        // affiche donc exactement le total vu ici.
        const recap = document.querySelector('[data-cvd-recap]');
        const rate = parseFloat(recap?.dataset.cvdRate || '0');
        const checks = document.querySelectorAll('[data-cvd-leg]');
        const steps = document.querySelectorAll('[data-cvd-step]');
        const seatInputs = document.querySelectorAll('[data-cvd-seats]');
        const subtotalLabel = document.querySelector('[data-cvd-subtotal-label]');
        const subtotalEl = document.querySelector('[data-cvd-subtotal]');
        const feeEl = document.querySelector('[data-cvd-fee]');
        const totalEl = document.querySelector('[data-cvd-total]');
        const bookBtn = document.querySelector('[data-cvd-book]');
        const hintEl = document.querySelector('[data-cvd-hint]');

        function euros(cents) {
            return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                .format(cents / 100) + ' €';
        }

        function plural(n, word) {
            return n + ' ' + word + (n > 1 ? 's' : '');
        }

        function refreshTotal() {
            let subtotalCents = 0;
            const picked = [];

            checks.forEach(function (check) {
                const leg = check.dataset.cvdLeg;
                const row = document.querySelector('[data-cvd-seats-row="' + leg + '"]');

                check.closest('.cvd-recap-leg')?.classList.toggle('is-off', !check.checked);
                if (row) row.hidden = !check.checked;

                if (!check.checked) return;

                // Chaque sens a ses places, bornees par ses propres places
                // libres : 2 a l'aller et 1 au retour, par exemple.
                const max = Math.max(1, parseInt(check.dataset.cvdSeatsLeft || '1', 10));
                const input = row?.querySelector('[data-cvd-seats]');
                let seats = parseInt(input?.value || '1', 10);
                seats = Math.min(Math.max(isNaN(seats) ? 1 : seats, 1), max);

                if (input) {
                    input.max = max;
                    input.value = seats;
                    row.querySelectorAll('[data-cvd-step]').forEach(function (step) {
                        step.disabled = step.dataset.cvdStep === '-1' ? seats <= 1 : seats >= max;
                    });
                }

                subtotalCents += Math.round(parseFloat(check.dataset.cvdPrice || '0') * 100) * seats;
                picked.push({ leg: leg, seats: seats });
            });

            const selected = picked.map(p => p.leg);
            const feeCents = Math.round(subtotalCents * rate / 100);

            // « Trajet · 2 places », ou « Aller × 2 · Retour × 1 » quand les
            // deux sens different.
            if (subtotalLabel) {
                subtotalLabel.textContent = picked.length > 1 && picked[0].seats !== picked[1].seats
                    ? picked.map(p => (p.leg === 'retour' ? 'Retour' : 'Aller') + ' × ' + p.seats).join(' · ')
                    : 'Trajet · ' + plural(picked.length ? picked[0].seats : 1, 'place');
            }
            if (subtotalEl) subtotalEl.textContent = euros(subtotalCents);
            if (feeEl) feeEl.textContent = euros(feeCents);
            if (totalEl) totalEl.textContent = euros(subtotalCents + feeCents);

            if (bookBtn) {
                const base = bookBtn.dataset.cvdHref || '#';
                bookBtn.classList.toggle('is-disabled', selected.length === 0);

                bookBtn.href = selected.length
                    ? base + (base.includes('?') ? '&' : '?') + 'legs=' + selected.join(',')
                        + picked.map(p => '&seats_' + p.leg + '=' + p.seats).join('')
                    : base;
            }

            if (hintEl) hintEl.hidden = selected.length > 0;
        }

        checks.forEach(function (check) {
            check.addEventListener('change', refreshTotal);
        });

        steps.forEach(function (step) {
            step.addEventListener('click', function () {
                const input = step.closest('[data-cvd-seats-row]').querySelector('[data-cvd-seats]');
                input.value = parseInt(input.value || '1', 10) + parseInt(step.dataset.cvdStep, 10);
                refreshTotal();
            });
        });

        seatInputs.forEach(function (input) {
            input.addEventListener('change', refreshTotal);
        });

        refreshTotal();

        // ---- Onglets aller / retour ----
        const tabs = document.querySelectorAll('[data-cvd-tab]');
        const panels = document.querySelectorAll('[data-cvd-panel]');

        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () {
                tabs.forEach(t => t.classList.remove('is-active'));
                panels.forEach(p => p.classList.remove('is-active'));
                tab.classList.add('is-active');
                document.querySelector('[data-cvd-panel="' + tab.dataset.cvdTab + '"]')?.classList.add('is-active');
                showLeg(index);
            });
        });

        // ---- Carte de l'itineraire ----
        const holder = document.getElementById('cvdMap');
        if (!holder || typeof L === 'undefined') return;

        let legs = [];
        try {
            legs = JSON.parse(holder.dataset.legs || '[]');
        } catch (e) {
            legs = [];
        }

        const map = L.map(holder, { scrollWheelZoom: false });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const layers = legs.map(function (leg) {
            const group = L.layerGroup();

            if (!leg.path || leg.path.length < 2) return group;

            L.polyline(leg.path, { color: '#ff3c00', weight: 4, opacity: .9 }).addTo(group);

            const start = leg.path[0];
            const end = leg.path[leg.path.length - 1];

            L.circleMarker(start, { radius: 7, color: '#ff3c00', fillColor: '#fff', fillOpacity: 1, weight: 3 }).addTo(group);
            L.circleMarker(end, { radius: 7, color: '#1f2328', fillColor: '#1f2328', fillOpacity: 1, weight: 3 }).addTo(group);

            return group;
        });

        function showLeg(index) {
            layers.forEach(layer => map.removeLayer(layer));

            const layer = layers[index];
            if (!layer) return;

            layer.addTo(map);

            const path = legs[index]?.path || [];
            if (path.length > 1) {
                map.fitBounds(L.latLngBounds(path), { padding: [30, 30] });
            }
        }

        const first = legs.findIndex(leg => (leg.path || []).length > 1);

        if (first === -1) {
            // Aucun trace exploitable : on centre sur la France plutot que sur l'ocean.
            map.setView([46.6, 2.4], 5);
        } else {
            showLeg(first);
        }

        setTimeout(() => map.invalidateSize(), 200);
    });
</script>

@endsection
