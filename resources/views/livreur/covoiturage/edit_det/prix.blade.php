@extends('layouts.connected')
@section('title', 'Prix du trajet | ' . config('app.name'))

@php
    /*
     | Tarification segment par segment. Les identifiants des champs
     | (price-input-{type}-{index}, price-display-{type}-{index},
     | total-price-input, total-price-display, potential-gain) sont ceux
     | qu'utilise le script en bas de page : ils sont conserves tels quels,
     | comme les noms segments[i][price] et return_segments[i][price].
     |
     | Le gain potentiel etait calcule sur 4 places ecrites en dur : il suit
     | desormais le nombre de places reellement propose.
     */
    $places = max(1, (int) ($covoiturage->nb_places ?: 1));
    $retour = collect($returnSegments ?? []);
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ route('covoiturage.index') }}">Mes trajets</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('covoiturage.edit', $covoiturage->covoiturage_id) }}">Modifier</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Prix</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Prix et paiement</h1>
            <p class="sp-subtitle">Fixez le tarif de chaque portion : les passagers paient la portion qu'ils empruntent.</p>
        </div>

        <a href="{{ route('covoiturage.edit', $covoiturage->covoiturage_id) }}" class="sp-btn-primary">
            Retour au trajet
        </a>
    </header>

    <form action="{{ route('covoiturage.prix.update', $covoiturage->covoiturage_id) }}" method="POST">
        @csrf

        @if ($errors->any())
            <div class="sp-alert">
                <strong>Les tarifs n'ont pas pu être enregistrés.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Synthese --}}
        <div class="sp-balance">
            <div>
                <span class="sp-balance-label">Prix du trajet complet</span>
                <span class="sp-balance-value"><span id="total-price-display">0</span> €</span>
                <span class="sp-balance-note">
                    Somme des portions ci-dessous. C'est le prix payé par un passager qui fait le trajet entier.
                </span>

                <input type="hidden" name="prix_total_affiche" id="total-price-input" value="0">
            </div>

            <div class="sp-balance-side">
                <span>Recette si {{ $places }} place{{ $places > 1 ? 's' : '' }} vendue{{ $places > 1 ? 's' : '' }}</span>
                <strong id="potential-gain">— €</strong>
            </div>
        </div>

        {{-- Portions aller --}}
        <section class="sp-form-section">
            <div class="sp-form-head">
                <span class="sp-step">1</span>
                <div>
                    <h2>Trajet aller</h2>
                    <p>{{ count($segments) }} portion{{ count($segments) > 1 ? 's' : '' }} entre le départ et l'arrivée.</p>
                </div>
            </div>

            @if(count($segments))
                <div class="sp-segments">
                    @foreach ($segments as $index => $segment)
                        <div class="sp-segment">
                            <div class="sp-segment-way">
                                <span class="sp-segment-from">{{ $segment['from'] ?? 'Départ' }}</span>
                                <span class="sp-segment-arrow">→</span>
                                <span class="sp-segment-to">{{ $segment['to'] ?? 'Destination' }}</span>
                            </div>

                            <div class="sp-stepper">
                                <button type="button" onclick="updateSegmentPrice({{ $index }}, -1, 'aller')"
                                        aria-label="Diminuer le prix">−</button>

                                <span class="sp-stepper-value">
                                    <span id="price-display-aller-{{ $index }}">{{ $segment['price'] ?? 0 }}</span> €
                                </span>

                                <button type="button" onclick="updateSegmentPrice({{ $index }}, 1, 'aller')"
                                        aria-label="Augmenter le prix">+</button>

                                <input type="hidden" name="segments[{{ $index }}][price]"
                                       id="price-input-aller-{{ $index }}" value="{{ $segment['price'] ?? 0 }}">
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="sp-feed-empty">Aucune portion enregistrée pour l'aller.</p>
            @endif
        </section>

        {{-- Portions retour --}}
        @if($retour->count())
            <section class="sp-form-section">
                <div class="sp-form-head">
                    <span class="sp-step">2</span>
                    <div>
                        <h2>Trajet retour</h2>
                        <p>{{ $retour->count() }} portion{{ $retour->count() > 1 ? 's' : '' }} sur le trajet inverse.</p>
                    </div>
                </div>

                <div class="sp-segments">
                    @foreach ($returnSegments as $index => $segment)
                        <div class="sp-segment">
                            <div class="sp-segment-way">
                                <span class="sp-segment-from">{{ $segment['from'] ?? 'Départ' }}</span>
                                <span class="sp-segment-arrow">→</span>
                                <span class="sp-segment-to">{{ $segment['to'] ?? 'Destination' }}</span>
                            </div>

                            <div class="sp-stepper">
                                <button type="button" onclick="updateSegmentPrice({{ $index }}, -1, 'retour')"
                                        aria-label="Diminuer le prix">−</button>

                                <span class="sp-stepper-value">
                                    <span id="price-display-retour-{{ $index }}">{{ $segment['price'] ?? 0 }}</span> €
                                </span>

                                <button type="button" onclick="updateSegmentPrice({{ $index }}, 1, 'retour')"
                                        aria-label="Augmenter le prix">+</button>

                                <input type="hidden" name="return_segments[{{ $index }}][price]"
                                       id="price-input-retour-{{ $index }}" value="{{ $segment['price'] ?? 0 }}">
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="sp-form-actions">
            <a href="{{ route('covoiturage.edit', $covoiturage->covoiturage_id) }}" class="sp-act is-ghost">Annuler</a>
            <button type="submit" class="sp-btn-primary">Enregistrer les tarifs</button>
        </div>
    </form>
</div>

<script>
    // Nombre de places reellement propose sur ce trajet
    const PLACES = {{ $places }};

    function updateSegmentPrice(index, delta, type) {
        const input = document.getElementById(`price-input-${type}-${index}`);
        const display = document.getElementById(`price-display-${type}-${index}`);
        if (!input || !display) return;

        const nouveau = (parseInt(input.value, 10) || 0) + delta;
        if (nouveau < 0) return;

        input.value = nouveau;
        display.innerText = nouveau;
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;

        document.querySelectorAll('input[id*="price-input-aller-"], input[id*="price-input-retour-"]')
            .forEach(input => total += parseInt(input.value, 10) || 0);

        document.getElementById('total-price-display').innerText = total;
        document.getElementById('total-price-input').value = total;
        document.getElementById('potential-gain').innerText = (total * PLACES) + ' €';
    }

    document.addEventListener('DOMContentLoaded', calculateTotal);
</script>
@endsection
