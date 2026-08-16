@extends('layouts.connected')

@section('title', 'Places & Confort | ' . config('app.name'))

@section('content')
    <div class="min-h-screen bg-[#FBFBFE] py-8">
        <div class="max-w-5xl mx-auto px-4">

            <!-- Header avec Fil d'ariane -->
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
                        <span class="text-slate-900">Places & Confort</span>
                    </nav>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                        Modifier les option du trajet <span class="text-orange-600">#TR-13</span>
                    </h1>
                </div>


            </div>
            <form method="POST" action="{{ route('covoiturage.options.update', $covoiturage->covoiturage_id) }}">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    <!-- COLONNE GAUCHE : RÉSUMÉ & STATUT -->
                    <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-8">
                        <div class="bg-slate-900 rounded-[40px] p-10 shadow-2xl shadow-slate-200 relative overflow-hidden">
                            <!-- Décoration -->
                            <div
                                class="absolute top-0 right-0 w-32 h-32 bg-orange-600/20 blur-3xl rounded-full -mr-16 -mt-16">
                            </div>

                            <div class="relative z-10">
                                <span
                                    class="text-[10px] font-black text-orange-500/80 uppercase tracking-[0.4em] block mb-6">Capacité
                                    du véhicule</span>

                                <div class="flex items-center space-x-4">
                                    <div class="flex items-baseline">
                                        <span class="text-8xl font-black text-white tracking-tighter"
                                            id="display_nb_places_hero">
                                            {{ $covoiturage->nb_places }}
                                        </span>
                                        <span class="text-3xl font-black text-orange-500 ml-2">Places</span>
                                    </div>
                                </div>

                                <div class="mt-10 pt-8 border-t border-white/10 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-400 text-xs font-bold uppercase tracking-wider">Confort
                                            Arrière</span>
                                        @php $passengerMode = json_decode($covoiturage->passenger_mode); @endphp
                                        <span class="text-white font-black text-xs uppercase" id="badge-comfort">
                                            {{ $passengerMode->max_arriere ?? false ? 'Premium' : 'Standard' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="text-slate-400 text-xs font-bold uppercase tracking-wider">Visibilité</span>
                                        <span class="text-pink-500 font-black text-xs uppercase">
                                            {{ $passengerMode->entre_femmes ?? false ? 'Femmes uniquement' : 'Tous publics' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Card Style Dashboard -->
                        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
                            <div class="flex space-x-4">
                                <div
                                    class="w-10 h-10 rounded-2xl bg-orange-50 flex items-center justify-center text-orange-600 shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-slate-900 font-black text-sm uppercase tracking-tight">Le saviez-vous ?
                                    </p>
                                    <p class="text-slate-500 text-[11px] leading-relaxed mt-1 font-medium">
                                        La réservation instantanée augmente vos chances de départ de <span
                                            class="text-orange-600 font-bold text-xs">60%</span>.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- COLONNE DROITE : RÉGLAGES -->
                    <div class="lg:col-span-7 space-y-6">

                        <!-- Section Places -->
                        <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm p-8">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="w-1.5 h-6 bg-orange-600 rounded-full"></div>
                                <h2 class="text-xl font-black text-slate-900 tracking-tight">Capacité de transport</h2>
                            </div>

                            <div
                                class="flex items-center justify-between bg-slate-50 rounded-[28px] p-3 border border-slate-100">
                                <div class="pl-4">
                                    <p class="text-slate-900 font-black text-sm">Nombre de places</p>
                                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Hors conducteur
                                    </p>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <button type="button" onclick="decrementPlaces()"
                                        class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-orange-600 transition-all active:scale-90 shadow-sm">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M20 12H4" />
                                        </svg>
                                    </button>

                                    <span id="display_nb_places"
                                        class="text-3xl font-black text-slate-900 min-w-[30px] text-center">
                                        {{ $covoiturage->nb_places }}
                                    </span>
                                    <input type="hidden" name="nb_places" id="input_nb_places"
                                        value="{{ $covoiturage->nb_places }}">

                                    <button type="button" onclick="incrementPlaces()"
                                        class="w-12 h-12 rounded-2xl bg-orange-600 flex items-center justify-center text-white shadow-lg shadow-orange-200 hover:bg-orange-700 transition-all active:scale-90">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Section Réservation -->
                        <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm p-8">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="w-1.5 h-6 bg-orange-600 rounded-full"></div>
                                <h2 class="text-xl font-black text-slate-900 tracking-tight">Mode de réservation</h2>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @php $currentBookingMode = $passengerMode->booking_mode ?? 'instant'; @endphp

                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="booking_mode" value="instant" class="peer sr-only"
                                        {{ $currentBookingMode === 'instant' ? 'checked' : '' }}>
                                    <div
                                        class="h-full p-6 rounded-[24px] border-2 border-slate-50 bg-slate-50/50 peer-checked:border-orange-600 peer-checked:bg-white transition-all group-hover:bg-white">
                                        <div
                                            class="w-10 h-10 bg-orange-100 text-orange-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                                            </svg>
                                        </div>
                                        <p class="font-black text-slate-900 text-sm mb-2 leading-tight">Instantanée</p>
                                        <p class="text-[10px] text-slate-500 font-medium leading-relaxed">Validation
                                            automatique des passagers.</p>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="booking_mode" value="manual" class="peer sr-only"
                                        {{ $currentBookingMode === 'manual' ? 'checked' : '' }}>
                                    <div
                                        class="h-full p-6 rounded-[24px] border-2 border-slate-50 bg-slate-50/50 peer-checked:border-slate-900 peer-checked:bg-white transition-all group-hover:bg-white">
                                        <div
                                            class="w-10 h-10 bg-slate-200 text-slate-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <p class="font-black text-slate-900 text-sm mb-2 leading-tight">Manuelle</p>
                                        <p class="text-[10px] text-slate-500 font-medium leading-relaxed">Vous gardez le
                                            contrôle sur chaque demande.</p>
                                    </div>
                                </label>
                            </div>
                        </div>


                        <!-- Section Préférences Passagers (Design Pro) -->
                        <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm p-8">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="w-1.5 h-6 bg-orange-600 rounded-full"></div>
                                <h2 class="text-xl font-black text-slate-900 tracking-tight">Préférences passagers</h2>
                            </div>

                            <div class="grid grid-cols-1 gap-3">
                                @php $currentMode = $passengerMode->passenger_mode ?? 'mixed'; @endphp

                                <!-- Option: Mixte -->
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="passenger_mode" value="mixed" class="peer sr-only"
                                        {{ $currentMode === 'mixed' ? 'checked' : '' }} onchange="updateUI()">
                                    <div
                                        class="flex items-center justify-between p-5 rounded-[24px] border-2 border-slate-50 bg-slate-50/50 peer-checked:border-orange-600 peer-checked:bg-white transition-all hover:bg-white">
                                        <div class="flex items-center space-x-4">
                                            <div
                                                class="w-12 h-12 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center shrink-0">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2.5"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-slate-900 uppercase tracking-tight">Mixte
                                                </p>
                                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                                                    Ouvert à tous les profils</p>
                                            </div>
                                        </div>
                                        <div
                                            class="w-6 h-6 rounded-full border-2 border-slate-200 peer-checked:border-orange-600 flex items-center justify-center transition-all bg-white">
                                            <div
                                                class="w-2.5 h-2.5 rounded-full bg-orange-600 scale-0 peer-checked:scale-100 transition-transform">
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <!-- Option: Femmes Uniquement -->
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="passenger_mode" value="womenOnly" class="peer sr-only"
                                        {{ $currentMode === 'womenOnly' ? 'checked' : '' }} onchange="updateUI()">
                                    <div
                                        class="flex items-center justify-between p-5 rounded-[24px] border-2 border-slate-50 bg-slate-50/50 peer-checked:border-pink-500 peer-checked:bg-white transition-all hover:bg-white">
                                        <div class="flex items-center space-x-4">
                                            <div
                                                class="w-12 h-12 bg-pink-100 text-pink-600 rounded-2xl flex items-center justify-center shrink-0">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2.5"
                                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p
                                                    class="text-sm font-black text-slate-900 uppercase tracking-tight text-pink-700">
                                                    Entre Femmes</p>
                                                <p class="text-[10px] text-pink-400 font-bold uppercase tracking-widest">
                                                    Sécurité & Confort féminin</p>
                                            </div>
                                        </div>
                                        <div
                                            class="w-6 h-6 rounded-full border-2 border-slate-200 peer-checked:border-pink-500 flex items-center justify-center transition-all bg-white">
                                            <div
                                                class="w-2.5 h-2.5 rounded-full bg-pink-500 scale-0 peer-checked:scale-100 transition-transform">
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <!-- Option: Max 2 places arrière -->
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="passenger_mode" value="maxBackSeats"
                                        class="peer sr-only" {{ $currentMode === 'maxBackSeats' ? 'checked' : '' }}
                                        onchange="updateUI()">
                                    <div
                                        class="flex items-center justify-between p-5 rounded-[24px] border-2 border-slate-50 bg-slate-50/50 peer-checked:border-blue-600 peer-checked:bg-white transition-all hover:bg-white">
                                        <div class="flex items-center space-x-4">
                                            <div
                                                class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center shrink-0">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-slate-900 uppercase tracking-tight">
                                                    Confort Plus</p>
                                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                                                    Max 2 personnes à l'arrière</p>
                                            </div>
                                        </div>
                                        <div
                                            class="w-6 h-6 rounded-full border-2 border-slate-200 peer-checked:border-blue-600 flex items-center justify-center transition-all bg-white">
                                            <div
                                                class="w-2.5 h-2.5 rounded-full bg-blue-600 scale-0 peer-checked:scale-100 transition-transform">
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Section Message -->
                        <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm p-8">
                            <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-4">Note aux voyageurs
                            </h2>
                            <textarea name="message_conducteur" rows="3"
                                class="w-full p-5 rounded-[24px] bg-slate-50 border border-slate-100 focus:bg-white focus:border-orange-600 focus:ring-0 transition-all text-sm font-medium placeholder:text-slate-300"
                                placeholder="Bagages, pauses, musique...">{{ $covoiturage->message_conducteur }}</textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-6">
                            <button type="submit"
                                class="w-full bg-slate-900 text-white py-6 rounded-[32px] font-black text-xs uppercase tracking-[0.3em] shadow-2xl hover:bg-orange-600 transition-all hover:-translate-y-1 flex items-center justify-center space-x-4">
                                <span>Mettre à jour les options</span>
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
        function incrementPlaces() {
            const input = document.getElementById('input_nb_places');
            const display = document.getElementById('display_nb_places');
            const displayHero = document.getElementById('display_nb_places_hero');
            let value = parseInt(input.value);
            if (value < 8) {
                value++;
                input.value = value;
                display.innerText = value;
                displayHero.innerText = value;
            }
        }

        function decrementPlaces() {
            const input = document.getElementById('input_nb_places');
            const display = document.getElementById('display_nb_places');
            const displayHero = document.getElementById('display_nb_places_hero');
            let value = parseInt(input.value);
            if (value > 1) {
                value--;
                input.value = value;
                display.innerText = value;
                displayHero.innerText = value;
            }
        }

        function updateComfortBadge() {
            const checkbox = document.getElementById('max_arriere');
            const badge = document.getElementById('badge-comfort');
            badge.innerText = checkbox.checked ? 'Premium' : 'Standard';
        }
    </script>
@endsection
