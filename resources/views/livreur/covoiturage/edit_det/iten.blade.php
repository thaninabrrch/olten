@extends('layouts.connected')

@section('title', 'Modifier votre annonce | ' . config('app.name'))

@section('content')
    <div class="min-h-screen">
        <div class="max-w-5xl mx-auto px-4">

            <!-- Fil d'Ariane & Titre (Style harmonisé) -->
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
                        <span class="text-slate-900">Détails de l'itinéraire</span>
                    </nav>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                        Modifier le parcours <span class="text-orange-600">#TR-{{ $covoiturage->covoiturage_id }}</span>
                    </h1>
                </div>
            </div>

            <div class="w-full bg-white rounded-[40px] shadow-sm border border-slate-100 p-8 md:p-10">

                <!-- Blocs cliquables Date & Heure -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
                    <!-- Modifier la Date -->
                    <a href="{{ route('covoiturage.edit-date-time', $covoiturage->covoiturage_id) }}"
                        class="group flex items-center justify-between p-5 bg-slate-50/50 rounded-3xl border border-transparent hover:border-orange-100 hover:bg-white hover:shadow-xl hover:shadow-orange-100/20 transition-all duration-300">
                        <div class="flex items-center space-x-4">
                            <div
                                class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-orange-600 shadow-sm group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Date du trajet</p>
                                <p class="text-base font-black text-slate-800">
                                    {{ \Carbon\Carbon::parse($covoiturage->date_depart)->locale('fr')->translatedFormat('d F Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="text-slate-200 group-hover:text-orange-600 group-hover:translate-x-1 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </a>

                    <!-- Modifier l'Heure -->
                    <a href="{{ route('covoiturage.edit-date-time', $covoiturage->covoiturage_id) }}"
                        class="group flex items-center justify-between p-5 bg-slate-50/50 rounded-3xl border border-transparent hover:border-blue-100 hover:bg-white hover:shadow-xl hover:shadow-blue-100/20 transition-all duration-300">
                        <div class="flex items-center space-x-4">
                            <div
                                class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-blue-600 shadow-sm group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Heure de départ
                                </p>
                                <p class="text-base font-black text-slate-800">
                                    {{ \Carbon\Carbon::parse($covoiturage->heure_depart)->format('H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="text-slate-200 group-hover:text-blue-600 group-hover:translate-x-1 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </a>
                </div>

                <!-- Timeline du parcours -->
                <div class="relative pl-10 mb-10">
                    <!-- Ligne verticale continue -->
                    <div class="absolute left-[17px] top-2 bottom-2 w-[2px] bg-slate-100"></div>

                    <div class="space-y-10">
                        <!-- Point de Départ (Cliquable) -->

                        <a
                            href="{{ route('covoiturage.edit-route', $covoiturage->covoiturage_id) }}"class="relative flex flex-col group cursor-pointer">
                            <div
                                class="absolute -left-[32px] top-1 w-5 h-5 rounded-full border-[4px] border-white bg-orange-600 shadow-md z-10 group-hover:scale-125 transition-transform">
                            </div>
                            <div class="flex flex-col">
                                <span
                                    class="text-[10px] font-bold text-orange-600 uppercase tracking-widest mb-1 group-hover:translate-x-1 transition-transform">Point
                                    de départ</span>
                                <span
                                    class="text-sm font-black text-slate-900 leading-snug group-hover:text-orange-600 transition-colors">
                                    {{ $covoiturage->depart }}
                                </span>

                            </div>
                        </a>

                        <!-- Liste des Étapes -->
                        @if (isset($etapes) && count($etapes) > 0)
                            @foreach ($etapes as $etape)
                                <div class="relative flex flex-col opacity-70 group">
                                    <div
                                        class="absolute -left-[30px] top-1.5 w-4 h-4 rounded-full border-[3px] border-white bg-slate-300 group-hover:bg-blue-400 transition-colors z-10">
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[13px] font-bold text-slate-700">{{ $etape->ville }}</span>
                                        <span class="text-[10px] font-medium text-slate-400">Escale intermédiaire</span>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <!-- Point d'Arrivée (Cliquable) -->
                        <a
                            href="{{ route('covoiturage.edit-route', $covoiturage->covoiturage_id) }}"class="relative flex flex-col group cursor-pointer">
                            <div
                                class="absolute -left-[32px] top-1 w-5 h-5 rounded-full border-[4px] border-white bg-slate-900 shadow-md z-10 group-hover:scale-125 transition-transform">
                            </div>
                            <div class="flex flex-col">
                                <span
                                    class="text-[10px] font-bold text-slate-900 uppercase tracking-widest mb-1 group-hover:translate-x-1 transition-transform">Destination</span>
                                <span
                                    class="text-sm font-black text-slate-900 leading-snug group-hover:text-blue-600 transition-colors">
                                    {{ $covoiturage->destination }}
                                </span>

                            </div>
                        </a>
                    </div>
                </div>

                <!-- Action Gérer les étapes -->
                <div class="pt-8 border-t border-slate-50 flex justify-center">
                    <a href="{{ route('covoiturage.edit-route', $covoiturage->covoiturage_id) }}"
                        class="inline-flex
                        items-center space-x-3 px-8 py-4 bg-slate-900 hover:bg-orange-600 text-white rounded-2xl
                        transition-all duration-300 group shadow-xl shadow-slate-200 hover:shadow-orange-200
                        hover:-translate-y-1">
                        <span class="text-[11px] font-black uppercase tracking-[0.2em]">
                            Gérer les étapes du trajet
                        </span>
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>

            </div>
        </div>
    </div>


@endsection
