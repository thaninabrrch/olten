@extends('layouts.connected')

@section('title', 'Planification du trajet | ' . config('app.name'))

@section('content')
    <div class="app-content-area">


        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <nav class="flex items-center space-x-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">
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
                    <a href="{{ route('covoiturage.edititen.edit', $covoiturage->covoiturage_id) }}"
                        class="hover:text-orange-600 transition-colors">Détails de l'itinéraire</a>
                    <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-slate-900">Date et heure</span>
                </nav>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                    Modifier le parcours <span class="text-orange-600">#TR-{{ $covoiturage->covoiturage_id }}</span>
                </h1>
            </div>
        </div>
        <form action="{{ route('covoiturage.update-date-time', $covoiturage->covoiturage_id) }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Panneau Principal de Saisie -->
                <div class="lg:col-span-8 space-y-6">
                    <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-8 md:p-10">

                            <div class="space-y-10">
                                <!-- Bloc Date -->
                                <div class="group">
                                    <div class="flex items-center space-x-4 mb-4">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 group-focus-within:text-orange-600 group-focus-within:bg-orange-50 group-focus-within:border-orange-100 transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-black text-slate-900 tracking-tight">Date du
                                                voyage</h3>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                                Calendrier</p>
                                        </div>
                                    </div>

                                    <div class="relative">
                                        <input type="date" name="date_depart" id="date_depart"
                                            class="w-full bg-slate-50 border border-slate-200 focus:border-orange-500 focus:bg-white rounded-2xl p-4 text-lg font-bold text-slate-900 transition-all outline-none @error('date_depart') border-red-200 bg-red-50 @enderror"
                                            value="{{ old('date_depart', $covoiturage->date_depart?->format('Y-m-d')) }}">
                                        @error('date_depart')
                                            <p class="mt-2 ml-2 text-[10px] font-black text-red-500 uppercase tracking-widest">
                                                {{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Bloc Heure -->
                                <div class="group">
                                    <div class="flex items-center space-x-4 mb-4">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 group-focus-within:text-slate-900 group-focus-within:bg-slate-100 group-focus-within:border-slate-200 transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-black text-slate-900 tracking-tight">Heure exacte
                                            </h3>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                                Horloge</p>
                                        </div>
                                    </div>

                                    <div class="relative">
                                        <input type="time" name="heure_depart" id="heure_depart"
                                            class="w-full bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-2xl p-4 text-lg font-bold text-slate-900 transition-all outline-none @error('heure_depart') border-red-200 bg-red-50 @enderror"
                                            value="{{ old('heure_depart', $covoiturage->heure_depart) }}">
                                        @error('heure_depart')
                                            <p class="mt-2 ml-2 text-[10px] font-black text-red-500 uppercase tracking-widest">
                                                {{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Footer d'Actions (Boutons minimisés) -->
                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <button type="submit"
                            class="w-full sm:flex-1 px-8 py-4 bg-slate-900 text-white rounded-2xl font-black text-[11px] uppercase tracking-[0.2em] shadow-lg shadow-slate-200 hover:bg-orange-600 transition-all active:scale-95 flex items-center justify-center space-x-3">
                            <span>Confirmer les changements</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>

                    </div>
                </div>

                <!-- Colonne Latérale : Aide & Récap -->
                <div class="lg:col-span-4 space-y-6">
                    <!-- Preview Card (Net sans Glassmorphism) -->
                    <div class="bg-slate-900 rounded-[32px] p-6 text-white shadow-xl shadow-slate-200">
                        <h4 class="text-[9px] font-black uppercase tracking-[0.2em] mb-6 text-slate-500">Récapitulatif
                        </h4>

                        <div class="space-y-4">
                            <div class="flex items-center space-x-4 p-3 bg-white/5 rounded-2xl border border-white/5">
                                <div class="w-2 h-2 rounded-full bg-orange-500 shrink-0"></div>
                                <div class="overflow-hidden">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-500">Départ</p>
                                    <p class="text-xs font-bold truncate">{{ $covoiturage->depart }}</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-4 p-3 bg-white/5 rounded-2xl border border-white/5">
                                <div class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></div>
                                <div class="overflow-hidden">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-500">
                                        Destination</p>
                                    <p class="text-xs font-bold truncate">{{ $covoiturage->destination }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-white/5 flex items-center justify-between">
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-500">Statut du
                                trajet</span>
                            <span class="text-[10px] font-black text-orange-500 uppercase tracking-tighter">En
                                cours</span>
                        </div>
                    </div>

                    <!-- Note UX Simplifiée -->
                    <div class="bg-white rounded-[24px] p-6 border border-slate-100 shadow-sm">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="text-blue-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h5 class="text-[11px] font-black text-slate-900 uppercase tracking-widest">Conseil</h5>
                        </div>
                        <p class="text-slate-400 text-[11px] leading-relaxed">
                            Fixer une heure 5 min à l'avance aide à garantir un départ à l'heure malgré les retards
                            mineurs.
                        </p>
                    </div>
                </div>

            </div>
        </form>
    </div>
    <style>
        /* Masquage de l'icône par défaut pour stylisation personnalisée si besoin */
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            padding: 10px;
            filter: invert(0.5);
        }

        @keyframes fade-in-down {
            0% {
                opacity: 0;
                transform: translateY(-8px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-down {
            animation: fade-in-down 0.4s ease-out forwards;
        }
    </style>

@endsection
