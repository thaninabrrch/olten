@extends('layouts.connected')
@section('title', 'Mes Trajets | ' . config('app.name'))

@section('content')

    <style>
        .font-jakarta {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .bg-primary-orange {
            background-color: #FF4500;
        }

        .text-primary-orange {
            color: #FF4500;
        }

        .shadow-premium {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.02), 0 10px 10px -5px rgba(0, 0, 0, 0.01);
        }

        .step-line {
            background-image: linear-gradient(to bottom, #FF4500 50%, transparent 50%);
            background-position: right;
            background-size: 2px 10px;
            background-repeat: repeat-y;
        }

        .pulse-emerald {
            animation: pulse-emerald 2s infinite;
        }

        @keyframes pulse-emerald {
            0%   { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
            70%  { transform: scale(1);   box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .pulse-amber { animation: pulse-amber 2s infinite; }
        @keyframes pulse-amber {
            0%   { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.5); }
            70%  { transform: scale(1);   box-shadow: 0 0 0 6px rgba(245, 158, 11, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
        }
    </style>

    <div class="min-h-screen bg-[#F8FAFC] py-12 px-4 sm:px-6 font-jakarta text-[#0F172A]">
        <div class="max-w-7xl mx-auto">

            <!-- En-tête -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-12 gap-6">
                <div>
                    <nav aria-label="Breadcrumb" class="flex-1">
                        <ol class="flex items-center space-x-2 text-sm font-medium">
                            <li><a href="#" class="text-slate-400 hover:text-slate-600 transition-colors">Chauffeur
                                    VTC</a>
                            </li>
                            <li><i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i></li>
                            <li class="text-slate-900 font-bold">Mes trajets</li>
                        </ol>
                    </nav>

                </div>

                <a href="{{ route('covoiturage.create') }}"
                    class="py-3 px-6 bg-[#ff3c00] hover:bg-black text-white rounded-[2rem] font-black uppercase tracking-widest text-xs transition-all duration-300 shadow-lg shadow-orange-200 flex items-center justify-center gap-2 whitespace-nowrap">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    Nouveau trajet
                </a>
            </div>

            <!-- Grille de trajets (2 par ligne sur desktop) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                @forelse($trajets as $trajet)
                    <div
                        class="group bg-white rounded-[2.5rem] border border-slate-100 shadow-premium hover:shadow-2xl transition-all duration-500 p-8 flex flex-col">

                        <!-- Header de la carte -->
                        <div class="flex justify-between items-start mb-8">
                            <div class="flex flex-col gap-1">
                                @php
                                    $statusConfig = [
                                        'actif'   => ['wrap' => 'bg-emerald-50 border-emerald-200', 'dot' => 'bg-emerald-500 pulse-emerald', 'text' => 'text-emerald-700', 'label' => 'Actif'],
                                        'inactif' => ['wrap' => 'bg-slate-50 border-slate-200',   'dot' => 'bg-slate-400',                  'text' => 'text-slate-500',   'label' => 'Inactif'],
                                        'pending' => ['wrap' => 'bg-amber-50 border-amber-200',   'dot' => 'bg-amber-500 pulse-amber',      'text' => 'text-amber-700',   'label' => 'En attente'],
                                        'validé'  => ['wrap' => 'bg-blue-50 border-blue-200',     'dot' => 'bg-blue-500',                   'text' => 'text-blue-700',    'label' => 'Validé'],
                                        'complet' => ['wrap' => 'bg-rose-50 border-rose-200',     'dot' => 'bg-rose-500',                   'text' => 'text-rose-700',    'label' => 'Complet'],
                                    ];
                                    $sc = $statusConfig[$trajet->statut] ?? ['wrap' => 'bg-slate-50 border-slate-200', 'dot' => 'bg-slate-400', 'text' => 'text-slate-500', 'label' => 'Inconnu'];
                                @endphp
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full border {{ $sc['wrap'] }} w-fit">
                                    <div class="w-2 h-2 rounded-full {{ $sc['dot'] }}"></div>
                                    <span class="text-[10px] font-black uppercase tracking-widest {{ $sc['text'] }}">
                                        {{ $sc['label'] }}
                                    </span>
                                </div>
                                <span class="text-xs font-bold text-slate-400 mt-2">
                                    {{ $trajet->date_depart ? \Carbon\Carbon::parse($trajet->date_depart)->translatedFormat('d M Y • H:i') : 'Date non définie' }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase">Cagnotte</span>
                                <span
                                    class="text-2xl font-black text-primary-orange tracking-tighter">{{ number_format($trajet->prix_place, 0) }}€</span>
                            </div>
                        </div>

                        <!-- Corps : Itinéraire avec Steps -->
                        <div class="flex gap-6 mb-8">
                            <div class="flex flex-col items-center py-1">
                                <div class="w-4 h-4 rounded-full border-4 border-primary-orange bg-white"></div>
                                <div class="w-[2px] h-full bg-slate-100 my-1 relative">
                                    <!-- Indication visuelle des steps sur la ligne -->
                                    @if (isset($trajet->steps) && count($trajet->steps) > 0)
                                        <div class="absolute inset-0 flex flex-col justify-around py-2">
                                            @foreach ($trajet->steps as $step)
                                                <div class="w-1.5 h-1.5 rounded-full bg-slate-300 -ml-[2px]"></div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="w-4 h-4 rounded-full bg-[#0F172A] border-4 border-slate-200"></div>
                            </div>

                            <div class="flex-grow flex flex-col justify-between py-0.5">
                                <div>
                                    <h3 class="text-xl font-black leading-none">{{ $trajet->depart }}</h3>
                                    <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase">Point de rendez-vous</p>
                                </div>

                                <!-- Les Étapes (Steps) -->
                                @if (isset($trajet->steps) && count($trajet->steps) > 0)
                                    <div class="py-4 flex flex-wrap gap-2">
                                        @foreach ($trajet->steps as $step)
                                            <div
                                                class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 border border-slate-100 rounded-xl">
                                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span
                                                    class="text-[10px] font-extrabold text-slate-600 uppercase">{{ $step->ville }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="h-10"></div> <!-- Spacer si pas d'étapes -->
                                @endif

                                <div>
                                    <h3 class="text-xl font-black leading-none">{{ $trajet->destination }}</h3>
                                    <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase">Arrivée estimée</p>
                                </div>
                            </div>
                        </div>

                        <!-- Footer : Stats & Actions -->
                        <div class="mt-auto pt-6 border-t border-slate-50 flex items-center justify-between">
                            <div class="flex items-center gap-6">
                                <div>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase mb-1">Passagers</p>
                                    <div class="flex -space-x-2">
                                        @for ($i = 0; $i < min($trajet->nb_places, 3); $i++)
                                            <div
                                                class="w-8 h-8 rounded-full bg-slate-50 border-2 border-white flex items-center justify-center text-slate-300">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" />
                                                </svg>
                                            </div>
                                        @endfor
                                        <div
                                            class="w-8 h-8 rounded-full bg-slate-900 border-2 border-white flex items-center justify-center text-[10px] font-black text-white">
                                            {{ $trajet->nb_places }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('trajet.show', ['covoiturage' => $trajet->covoiturage_id]) }}"
                                    class="flex items-center justify-center h-12 px-6 rounded-2xl bg-slate-50 text-[#0F172A] font-bold text-[10px] uppercase tracking-widest
              hover:bg-slate-900 hover:text-white transition-all duration-300 gap-2">
                                    Détails
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>
                            </div>

                        </div>
                    </div>
                @empty
                    <!-- État vide -->
                    <div
                        class="lg:col-span-2 text-center py-24 bg-white rounded-[3rem] border-2 border-dashed border-slate-100">
                        <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black mb-2">Aucun trajet pour le moment</h3>
                        <p class="text-slate-400 mb-8 max-w-xs mx-auto">Partagez votre route et commencez à rentabiliser vos
                            déplacements.</p>
                        <a href="{{ route('covoiturage.create') }}"
                            class="inline-flex items-center px-8 py-4 bg-primary-orange text-white rounded-2xl font-bold shadow-xl shadow-orange-100">
                            Publier une annonce
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

@endsection
