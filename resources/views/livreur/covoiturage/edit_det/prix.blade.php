@extends('layouts.connected')

@section('title', 'Modifier le prix | ' . config('app.name'))

@section('content')
    <div class="min-h-screen">
        <div class="max-w-5xl mx-auto px-4">


            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <nav
                        class="flex items-center space-x-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">
                        <a href="{{ route('covoiturage.index') }}" class="hover:text-orange-600 transition-colors">Mes
                            trajets</a>
                        <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                        </svg>
                        <a href="{{ route('covoiturage.edit', $covoiturage->covoiturage_id) }}"
                            class="hover:text-orange-600 transition-colors">Édition trajet</a>
                        <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="text-slate-900">Prix & paiement</span>
                    </nav>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                        Modifier les tarif du trajet <span class="text-orange-600">#TR-13</span>
                    </h1>
                </div>


            </div>
            <form action="{{ route('covoiturage.prix.update', $covoiturage->covoiturage_id) }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    <!-- COLONNE GAUCHE : RÉSUMÉ & TOTAL -->
                    <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-8">
                        <div class="bg-slate-900 rounded-[40px] p-10 shadow-2xl shadow-slate-200 relative overflow-hidden">
                            <!-- Décoration -->
                            <div
                                class="absolute top-0 right-0 w-32 h-32 bg-orange-600/20 blur-3xl rounded-full -mr-16 -mt-16">
                            </div>

                            <div class="relative z-10">
                                <span
                                    class="text-[10px] font-black text-orange-500/80 uppercase tracking-[0.4em] block mb-6">Estimation
                                    de gain</span>

                                <div class="flex items-center space-x-4">
                                    <div class="flex items-baseline">
                                        <span class="text-8xl font-black text-white tracking-tighter"
                                            id="total-price-display">
                                            {{ $covoiturage->prix_total_affiche ?? 0 }}
                                        </span>
                                        <span class="text-3xl font-black text-orange-500 ml-2">€</span>
                                        <input type="hidden" name="prix_total_affiche" id="total-price-input"
                                            value="{{ $covoiturage->prix_total_affiche ?? 0 }}">
                                    </div>
                                </div>

                                <div class="mt-10 pt-8 border-t border-white/10">
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="text-slate-400 text-xs font-bold uppercase tracking-wider">Passagers
                                            max.</span>
                                        <span class="text-white font-black">4 places</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-400 text-xs font-bold uppercase tracking-wider">Gain
                                            potentiel</span>
                                        <span class="text-orange-500 font-black" id="potential-gain">-- €</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Card -->
                        <div class="bg-orange-50 rounded-3xl p-6 border border-orange-100">
                            <div class="flex space-x-4">
                                <div
                                    class="w-10 h-10 rounded-2xl bg-orange-600 flex items-center justify-center text-white shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-slate-900 font-black text-sm uppercase tracking-tight">Mise à jour
                                        instantanée</p>
                                    <p class="text-slate-500 text-[11px] leading-relaxed mt-1">
                                        Vos nouveaux prix seront visibles immédiatement par les futurs voyageurs.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- COLONNE DROITE : CONTRÔLES -->
                    <div class="lg:col-span-7 space-y-6">

                        <!-- Section Aller -->
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3 mb-2 px-2">
                                <div class="w-1.5 h-6 bg-orange-600 rounded-full"></div>
                                <h2 class="text-xl font-black text-slate-900 tracking-tight">Configuration de l'aller</h2>
                            </div>

                            @foreach ($segments as $index => $segment)
                                <div
                                    class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 hover:border-orange-200 transition-all group">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                                        <div class="flex items-start space-x-4">
                                            <div class="mt-1 flex flex-col items-center">
                                                <div class="w-4 h-4 rounded-full border-4 border-orange-600 bg-white"></div>
                                                <div
                                                    class="w-0.5 h-10 bg-slate-100 my-1 group-hover:bg-orange-100 transition-colors">
                                                </div>
                                                <div class="w-4 h-4 rounded-full bg-slate-100"></div>
                                            </div>
                                            <div>
                                                <span
                                                    class="text-[9px] font-black text-slate-300 uppercase tracking-widest block mb-1">Segment
                                                    {{ $index + 1 }}</span>
                                                <h4 class="text-base font-black text-slate-900 leading-tight">
                                                    {{ $segment['from'] ?? 'Départ' }}
                                                </h4>
                                                <p class="text-slate-400 text-xs font-bold mt-1">vers
                                                    {{ $segment['to'] ?? 'Destination' }}</p>
                                            </div>
                                        </div>

                                        <div
                                            class="flex items-center justify-between sm:justify-end bg-slate-50 rounded-[24px] p-2 border border-slate-100 min-w-[160px]">
                                            <button type="button"
                                                onclick="updateSegmentPrice({{ $index }}, -1, 'aller')"
                                                class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-orange-600 transition-all active:scale-90 shadow-sm">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                        d="M20 12H4" />
                                                </svg>
                                            </button>

                                            <div class="px-4 text-center">
                                                <span class="text-xl font-black text-slate-900"
                                                    id="price-display-aller-{{ $index }}">
                                                    {{ $segment['price'] ?? 0 }}
                                                </span>
                                                <span class="text-xs font-black text-orange-600 ml-1">€</span>
                                                <input type="hidden" name="segments[{{ $index }}][price]"
                                                    id="price-input-aller-{{ $index }}"
                                                    value="{{ $segment['price'] ?? 0 }}">
                                            </div>

                                            <button type="button"
                                                onclick="updateSegmentPrice({{ $index }}, 1, 'aller')"
                                                class="w-10 h-10 rounded-xl bg-orange-600 flex items-center justify-center text-white hover:bg-orange-700 transition-all active:scale-90 shadow-lg shadow-orange-200">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                        d="M12 4v16m8-8H4" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Section Retour (Optionnel) -->
                        @if (isset($returnSegments) && count($returnSegments) > 0)
                            <div class="space-y-4 pt-6">
                                <div class="flex items-center space-x-3 mb-2 px-2">
                                    <div class="w-1.5 h-6 bg-slate-300 rounded-full"></div>
                                    <h2 class="text-xl font-black text-slate-900 tracking-tight">Configuration du retour
                                    </h2>
                                </div>

                                @foreach ($returnSegments as $index => $segment)
                                    <div
                                        class="bg-white/60 rounded-3xl border border-slate-100 p-6 flex items-center justify-between group">
                                        <div class="flex items-center space-x-4">
                                            <div
                                                class="w-10 h-10 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                        d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-black text-slate-700">{{ $segment['from'] ?? '' }}
                                                </h4>
                                                <p class="text-slate-400 text-[11px] font-bold tracking-tight">vers
                                                    {{ $segment['to'] ?? '' }}</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-3">
                                            <button type="button"
                                                onclick="updateSegmentPrice({{ $index }}, -1, 'retour')"
                                                class="text-slate-300 hover:text-orange-600 font-bold p-2">-</button>
                                            <div class="bg-slate-100 px-4 py-2 rounded-xl">
                                                <span class="text-sm font-black text-slate-900"
                                                    id="price-display-retour-{{ $index }}">{{ $segment['price'] ?? 0 }}</span>
                                                <span class="text-[10px] font-bold text-slate-400">€</span>
                                                <input type="hidden" name="return_segments[{{ $index }}][price]"
                                                    id="price-input-retour-{{ $index }}"
                                                    value="{{ $segment['price'] ?? 0 }}">
                                            </div>
                                            <button type="button"
                                                onclick="updateSegmentPrice({{ $index }}, 1, 'retour')"
                                                class="text-slate-300 hover:text-orange-600 font-bold p-2">+</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Submit Button -->
                        <div class="pt-10">
                            <button type="submit"
                                class="w-full bg-slate-900 text-white py-6 rounded-[32px] font-black text-xs uppercase tracking-[0.3em] shadow-2xl hover:bg-orange-600 transition-all hover:-translate-y-1 flex items-center justify-center space-x-4">
                                <span>Valider la nouvelle tarification</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateSegmentPrice(index, delta, type) {
            const input = document.getElementById(`price-input-${type}-${index}`);
            const display = document.getElementById(`price-display-${type}-${index}`);

            let currentPrice = parseInt(input.value);
            let newPrice = currentPrice + delta;

            if (newPrice >= 0) {
                input.value = newPrice;
                display.innerText = newPrice;
                calculateTotal();
            }
        }

        function calculateTotal() {
            let total = 0;
            const allerInputs = document.querySelectorAll('input[id*="price-input-aller-"]');
            allerInputs.forEach(input => {
                total += parseInt(input.value);
            });

            const retourInputs = document.querySelectorAll('input[id*="price-input-retour-"]');
            retourInputs.forEach(input => {
                total += parseInt(input.value);
            });

            // Mise à jour de l'affichage
            document.getElementById('total-price-display').innerText = total;

            // Mise à jour de l'input hidden
            document.getElementById('total-price-input').value = total;

            // Calcul gain potentiel (ex: 4 places)
            const potentialGain = total * 4;
            document.getElementById('potential-gain').innerText = potentialGain + ' €';
        }
        // Initialiser au chargement
        window.onload = calculateTotal;
    </script>
@endsection
