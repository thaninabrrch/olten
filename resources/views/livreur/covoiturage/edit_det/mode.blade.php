@extends('layouts.connected')

@section('title', 'Mode de réservation | ' . config('app.name'))

@section('content')
<div class="min-h-screen bg-slate-50 py-10 px-4">
    <div class="max-w-2xl mx-auto">

        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-8">
            <a href="{{ route('covoiturage.index') }}" class="hover:text-orange-500 transition-colors">Mes trajets</a>
            <svg class="w-2.5 h-2.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('covoiturage.edit', $covoiturage->covoiturage_id) }}" class="hover:text-orange-500 transition-colors">Édition</a>
            <svg class="w-2.5 h-2.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-slate-600">Mode de réservation</span>
        </nav>

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Mode de réservation</h1>
                <span class="px-2.5 py-1 bg-orange-50 text-orange-600 text-[10px] font-black rounded-lg uppercase tracking-wider">
                    #TR-{{ $covoiturage->covoiturage_id }}
                </span>
            </div>
            <p class="text-slate-400 text-sm">Choisissez comment vos passagers peuvent réserver une place sur ce trajet.</p>
        </div>

        <!-- Form -->
        <form action="{{ route('covoiturage.updateMode', $covoiturage->covoiturage_id) }}" method="POST">
            @csrf

            <!-- Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

                <!-- Instantané -->
                <label class="group cursor-pointer">
                    <input type="radio" name="booking_type" value="instant" class="peer sr-only"
                        {{ $covoiturage->booking_mode === 'instant' ? 'checked' : '' }}>
                    <div class="h-full bg-white rounded-2xl border-2 border-slate-100 p-6 flex flex-col gap-5
                                transition-all duration-200
                                peer-checked:border-orange-500 peer-checked:bg-orange-50/30
                                hover:border-slate-200 hover:shadow-sm">

                        <!-- Top row: icon + radio dot -->
                        <div class="flex items-start justify-between">
                            <div class="w-11 h-11 rounded-xl bg-orange-100 text-orange-500 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="radio-dot w-5 h-5 rounded-full border-2 border-slate-200 flex items-center justify-center transition-all">
                                <div class="w-2 h-2 rounded-full bg-white scale-0 transition-transform dot-inner"></div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1.5">
                                <h3 class="text-sm font-black text-slate-900">Réservation instantanée</h3>
                            </div>
                            <p class="text-slate-400 text-xs leading-relaxed">
                                Les passagers réservent et paient immédiatement, sans attendre votre accord.
                            </p>
                        </div>

                        <!-- Features -->
                        <ul class="space-y-1.5 pt-4 border-t border-slate-100">
                            <li class="flex items-center gap-2 text-[11px] font-semibold text-slate-500">
                                <svg class="w-3.5 h-3.5 text-orange-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                Badge "Instantané" sur votre annonce
                            </li>
                            <li class="flex items-center gap-2 text-[11px] font-semibold text-slate-500">
                                <svg class="w-3.5 h-3.5 text-orange-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                Meilleure visibilité algorithmique
                            </li>
                        </ul>

                        <!-- Badge populaire -->
                        <span class="self-start px-2.5 py-1 bg-green-50 text-green-600 text-[9px] font-black rounded-lg uppercase tracking-wider">
                            Populaire
                        </span>
                    </div>
                </label>

                <!-- Manuelle -->
                <label class="group cursor-pointer">
                    <input type="radio" name="booking_type" value="manual" class="peer sr-only"
                        {{ $covoiturage->booking_mode === 'manual' ? 'checked' : '' }}>
                    <div class="h-full bg-white rounded-2xl border-2 border-slate-100 p-6 flex flex-col gap-5
                                transition-all duration-200
                                peer-checked:border-orange-500 peer-checked:bg-orange-50/30
                                hover:border-slate-200 hover:shadow-sm">

                        <!-- Top row: icon + radio dot -->
                        <div class="flex items-start justify-between">
                            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="radio-dot w-5 h-5 rounded-full border-2 border-slate-200 flex items-center justify-center transition-all">
                                <div class="w-2 h-2 rounded-full bg-white scale-0 transition-transform dot-inner"></div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1">
                            <h3 class="text-sm font-black text-slate-900 mb-1.5">Validation manuelle</h3>
                            <p class="text-slate-400 text-xs leading-relaxed">
                                Vous recevez une notification et acceptez chaque passager individuellement.
                            </p>
                        </div>

                        <!-- Features -->
                        <ul class="space-y-1.5 pt-4 border-t border-slate-100">
                            <li class="flex items-center gap-2 text-[11px] font-semibold text-slate-500">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                Contrôle total des passagers
                            </li>
                            <li class="flex items-center gap-2 text-[11px] font-semibold text-slate-500">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                Délai de réponse de quelques heures
                            </li>
                        </ul>
                    </div>
                </label>

            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('covoiturage.edit', $covoiturage->covoiturage_id) }}"
                    class="text-xs font-bold text-slate-400 hover:text-slate-700 transition-colors">
                    ← Retour
                </a>
                <button type="submit"
                    class="px-8 py-3 bg-slate-900 hover:bg-orange-500 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all duration-200 hover:-translate-y-0.5 active:scale-95">
                    Enregistrer
                </button>
            </div>
        </form>

    </div>
</div>

<style>
    input[type="radio"]:checked + div .radio-dot {
        background-color: #ea580c;
        border-color: #ea580c;
    }
    input[type="radio"]:checked + div .dot-inner {
        transform: scale(1);
    }
</style>
@endsection
