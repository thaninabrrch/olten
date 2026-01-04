@extends('layouts.connected')
@section('title', 'Tableau de bord | ' . config('app.name'))

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">

        <header class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div class="space-y-2">
                <div class="breadcrumb">
                    <a href="#">Accueil</a>
                    <span>></span>
                    <span>Tableau de bord</span>
                </div>
                <h1 class="text-5xl font-black text-slate-900 tracking-tighter">
                    Hello, <span class="text-[#ff3c00]">{{ explode(' ', $user->name)[0] }}</span>.
                </h1>
            </div>

            <div
                class="flex items-center gap-2 bg-white p-1.5 rounded-2xl border border-slate-100 shadow-sm self-start lg:self-auto">
                @if ($user->hasRole('livreur'))
                    <div
                        class="flex items-center gap-2 px-4 py-2 bg-orange-50 text-[#ff3c00] rounded-xl text-[10px] font-black uppercase border border-orange-100">
                        <i class="fa-solid fa-bicycle"></i> Livreur
                    </div>
                @endif
                <div
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase shadow-lg shadow-blue-200">
                    <i class="fa-solid fa-key"></i> Locateur
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Left Column -->
            <div class="lg:col-span-8 space-y-6">

                <!-- Mini Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
                    @if ($user->hasRole('livreur'))
                        <x-stat-mini title="Revenus" :value="number_format($revenusTotal, 0) . '€'" trend="+12%" color="orange" icon="fa-wallet" />
                        <x-stat-mini title="Courses" :value="$totalCourses" trend="Top" color="orange" icon="fa-truck-fast" />
                    @endif
                    <x-stat-mini title="Annonces" :value="$activeAds" trend="Live" color="blue" icon="fa-layer-group" />
                    <x-stat-mini title="Vues" :value="$totalViews" trend="+5.2%" color="blue" icon="fa-chart-line" />
                    <x-stat-mini title="Note" :value="$noteClient ?? '5.0'" trend="★ 4.9" color="yellow" icon="fa-star" />
                    <x-stat-mini title="Favoris" :value="$favoritesCount ?? '0'" trend="New" color="pink" icon="fa-heart" />
                </div>

                <!-- Weekly Performance Graph -->
                <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-[0_4px_25px_rgba(0,0,0,0.02)]">
                    <div class="flex items-center justify-between mb-12">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest italic">
                            Performance Hebdomadaire
                        </h3>
                        <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-300">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                    </div>


                    <div class="h-64 flex items-end justify-between gap-3 px-4">
                        @foreach ($graphData as $day => $total)
                            @php $isToday = $day === \Carbon\Carbon::today()->format('Y-m-d'); @endphp
                            <div class="flex-1 flex flex-col items-center group h-full justify-end">
                                <div class="w-full relative rounded-t-xl transition-all duration-700 {{ $isToday ? 'bg-[#ff3c00] shadow-[0_10px_20px_rgba(255,60,0,0.2)]' : 'bg-slate-50 group-hover:bg-slate-100' }}"
                                    style="height: {{ max(($total / 200) * 100, 10) }}%">
                                    <div
                                        class="absolute -top-10 left-1/2 -translate-x-1/2 scale-0 group-hover:scale-100 transition-all bg-slate-900 text-white text-[9px] px-2 py-1 rounded font-bold z-10">
                                        {{ $total }}€
                                    </div>
                                </div>
                                <span
                                    class="text-[9px] font-black text-slate-400 mt-4 uppercase">{{ substr($day, -2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Right Column -->
            <div class="lg:col-span-4 flex flex-col gap-6">

                <!-- Activity Feed -->
                <div
                    class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-[0_10px_40px_rgba(0,0,0,0.02)] flex flex-col flex-1 overflow-hidden">
                    <div class="flex items-center justify-between mb-10 shrink-0">
                        <div class="flex flex-col">
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Flux d'activité</h3>
                            <span class="text-[9px] font-bold text-[#ff3c00] animate-pulse uppercase tracking-tighter">Live
                                Updates</span>
                        </div>
                        <div class="relative">
                            <div
                                class="w-10 h-10 bg-slate-50 rounded-2xl flex items-center justify-center border border-slate-100 cursor-default">
                                <i class="fa-solid fa-bell text-slate-400 text-xs"></i>
                            </div>
                            <span class="absolute -top-1 -right-1 flex h-3 w-3">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span
                                    class="relative inline-flex rounded-full h-3 w-3 bg-red-500 border-2 border-white"></span>
                            </span>
                        </div>
                    </div>

                    <div class="flex-grow overflow-y-auto pr-2 custom-scrollbar space-y-8 relative">
                        @forelse($recentActivities ?? [] as $activity)
                            <div class="relative flex gap-5 items-center">
                                <div class="shrink-0 relative z-10">
                                    <div
                                        class="w-11 h-11 rounded-2xl bg-white border border-slate-100 shadow-sm flex items-center justify-center">
                                        @php
                                            $isMoney =
                                                str_contains(strtolower($activity['description']), '€') ||
                                                str_contains(strtolower($activity['description']), 'revenu');
                                            $icon = $isMoney ? 'fa-wallet text-green-500' : 'fa-bolt text-orange-500';
                                        @endphp
                                        <i class="fa-solid {{ $icon }} text-[12px]"></i>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1 border-b border-slate-50 pb-4 last:border-none">
                                    <p class="text-[12px] font-bold text-slate-800 leading-tight truncate">
                                        {{ $activity['description'] }}
                                    </p>
                                    <p
                                        class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1 opacity-70">
                                        {{ $activity['time'] }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="h-full flex flex-col items-center justify-center text-center py-20">
                                <div class="relative mb-6">
                                    <div
                                        class="w-24 h-24 bg-slate-50 rounded-[2.5rem] flex items-center justify-center border border-dashed border-slate-200">
                                        <i class="fa-solid fa-bell-slash text-slate-300 text-3xl"></i>
                                    </div>
                                </div>
                                <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em]">Aucune notification
                                </h4>
                                <p class="text-[10px] text-slate-300 mt-2 font-medium italic">Il n’y a pas d’activité
                                    récente à afficher.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Last Mission -->
                @if ($user->hasRole('livreur') && $derniereMission)
                    <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-10">
                            <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4z" />
                            </svg>
                        </div>
                        <h3 class="text-orange-500 font-bold text-xs uppercase tracking-widest mb-4">Dernière course</h3>
                        <p class="text-2xl font-bold mb-1">{{ number_format($derniereMission->prix_total_affiche, 2) }} €
                        </p>
                        <p class="text-slate-400 text-sm mb-6">
                            {{ $derniereMission->restaurant_nom ?? 'Livraison effectuée' }}</p>
                        <button
                            class="w-full py-4 bg-orange-600 hover:bg-[#ff3c00] rounded-2xl font-bold text-sm transition-all">Détails
                            de la mission</button>
                    </div>
                @endif

            </div>

        </div>
    </div>
@endsection
