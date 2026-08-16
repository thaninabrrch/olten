@extends('layouts.connected')
@section('title', 'Livraisons en Cours | ' . config('app.name'))

@section('content')
    <section class="tab-content active animate-fade  min-h-screen">
        <div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-12">
                <nav aria-label="Breadcrumb" class="flex-1">
                    <ol class="flex items-center space-x-3 text-sm font-medium">
                        <li>
                            <a href="#"
                                class="text-slate-400 hover:text-[#ff3c00] transition-colors flex items-center gap-2">
                                <i class="fa-solid fa-truck-ramp-box text-xs"></i>
                                Livreur
                            </a>
                        </li>
                        <li><i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i></li>
                        <li class="text-slate-900 font-black uppercase tracking-tight">Missions actives</li>
                    </ol>
                </nav>
                <div class="flex items-center bg-white p-2 rounded-[2rem] border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-4 px-6 py-2">
                        <div
                            class="w-10 h-10 rounded-xl bg-[#ff3c00] flex items-center justify-center text-white shadow-lg shadow-orange-100">
                            <i class="fa-solid fa-spinner text-sm"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase leading-none mb-1 text-nowrap">En cours
                            </p>
                            <p class="text-xl font-black text-slate-900 leading-none">3</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">

                <div
                    class="group relative bg-white rounded-[3rem] border-2 border-slate-900 shadow-[0_20px_40px_-15px_rgba(0,0,0,0.1)] flex flex-col h-full overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-slate-100">
                        <div class="h-full bg-[#ff3c00] animate-progress-glow" style="width: 75%;"></div>
                    </div>

                    <div class="p-8 pb-0 flex justify-between items-start mt-2">
                        <div class="flex flex-col gap-2">
                            <span
                                class="px-3 py-1 bg-orange-50 text-[#ff3c00] rounded-lg font-black text-[9px] uppercase tracking-widest flex items-center gap-2 w-fit border border-orange-100">
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="absolute inline-flex h-full w-full rounded-full bg-[#ff3c00] opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-[#ff3c00]"></span>
                                </span>
                                En Transit
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter text-nowrap">Prise
                                en charge à 14:20</span>
                        </div>
                        <div class="text-right">
                            <p class="text-[9px] font-black text-slate-300 uppercase">Gain</p>
                            <p class="text-2xl font-black text-slate-900 tracking-tighter">45.00€</p>
                        </div>
                    </div>

                    <div class="p-8">
                        <h3
                            class="text-xl font-bold text-slate-800 leading-snug mb-8 group-hover:text-[#ff3c00] transition-colors">
                            Livraison Express : Équipements Gaming
                        </h3>
                        <div class="space-y-6 relative">
                            <div class="absolute left-[11px] top-3 bottom-3 w-[2px] bg-dashed border-l-2 border-slate-100">
                            </div>
                            <div class="flex items-start gap-5 relative z-10">
                                <div
                                    class="w-[24px] h-[24px] rounded-full bg-slate-100 border-2 border-slate-300 flex items-center justify-center flex-shrink-0 text-[10px] text-slate-400">
                                    <i class="fa-solid fa-location-dot"></i></div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase leading-none mb-1">Dépôt</p>
                                    <p class="text-sm font-bold text-slate-600 truncate">Entrepôt A-12, Paris</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-5 relative z-10">
                                <div
                                    class="w-[24px] h-[24px] rounded-full bg-slate-900 border-4 border-white shadow-md flex items-center justify-center flex-shrink-0 text-[10px] text-white">
                                    <i class="fa-solid fa-flag-checkered"></i></div>
                                <div>
                                    <p class="text-[10px] font-black text-[#ff3c00] uppercase leading-none mb-1">Destination
                                    </p>
                                    <p class="text-sm font-bold text-slate-800">88 Boulevard Haussmann</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-8 pt-0 flex gap-3 mt-auto">
                        <a href="#"
                            class="flex-1 h-14 bg-white border-2 border-slate-900 text-slate-900 rounded-2xl font-black hover:bg-slate-900 hover:text-white transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-diamond-turn-right text-sm"></i>
                        </a>
                        <button
                            class="flex-[3] h-14 bg-slate-900 text-white rounded-2xl font-black text-[11px] uppercase tracking-widest hover:bg-[#ff3c00] transition-all flex items-center justify-center gap-3">
                            <i class="fa-solid fa-clipboard-check"></i> Finaliser
                        </button>
                    </div>

                    <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[10px] text-slate-500">
                                <i class="fa-solid fa-user"></i></div>
                            <span class="text-xs font-bold text-slate-600">Marc Lavigne</span>
                        </div>
                        <button
                            class="text-[#ff3c00] text-xs font-black uppercase tracking-tighter hover:underline">Contacter</button>
                    </div>
                </div>

                <div
                    class="group relative bg-white rounded-[3rem] border-2 border-slate-900 shadow-[0_20px_40px_-15px_rgba(0,0,0,0.1)] flex flex-col h-full overflow-hidden opacity-90 scale-[0.98]">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-slate-100">
                        <div class="h-full bg-[#ff3c00] animate-progress-glow" style="width: 30%;"></div>
                    </div>

                    <div class="p-8 pb-0 flex justify-between items-start mt-2">
                        <div class="flex flex-col gap-2">
                            <span
                                class="px-3 py-1 bg-orange-50 text-[#ff3c00] rounded-lg font-black text-[9px] uppercase tracking-widest flex items-center gap-2 w-fit border border-orange-100">
                                En Transit
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Prise en charge à
                                15:05</span>
                        </div>
                        <div class="text-right">
                            <p class="text-[9px] font-black text-slate-300 uppercase">Gain</p>
                            <p class="text-2xl font-black text-slate-900 tracking-tighter">18.50€</p>
                        </div>
                    </div>

                    <div class="p-8">
                        <h3
                            class="text-xl font-bold text-slate-800 leading-snug mb-8 group-hover:text-[#ff3c00] transition-colors">
                            Documents RH - Signature urgente
                        </h3>
                        <div class="space-y-6 relative">
                            <div class="absolute left-[11px] top-3 bottom-3 w-[2px] bg-dashed border-l-2 border-slate-100">
                            </div>
                            <div class="flex items-start gap-5 relative z-10">
                                <div
                                    class="w-[24px] h-[24px] rounded-full bg-slate-100 border-2 border-slate-300 flex items-center justify-center flex-shrink-0 text-[10px] text-slate-400">
                                    <i class="fa-solid fa-location-dot"></i></div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase leading-none mb-1">Dépôt</p>
                                    <p class="text-sm font-bold text-slate-600 truncate">Cabinet Juridique, Lyon</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-5 relative z-10">
                                <div
                                    class="w-[24px] h-[24px] rounded-full bg-slate-900 border-4 border-white shadow-md flex items-center justify-center flex-shrink-0 text-[10px] text-white">
                                    <i class="fa-solid fa-flag-checkered"></i></div>
                                <div>
                                    <p class="text-[10px] font-black text-[#ff3c00] uppercase leading-none mb-1">Destination
                                    </p>
                                    <p class="text-sm font-bold text-slate-800">Quartier Confluence</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-8 pt-0 flex gap-3 mt-auto">
                        <a href="#"
                            class="flex-1 h-14 bg-white border-2 border-slate-900 text-slate-900 rounded-2xl font-black hover:bg-slate-900 hover:text-white transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-diamond-turn-right text-sm"></i>
                        </a>
                        <button
                            class="flex-[3] h-14 bg-slate-900 text-white rounded-2xl font-black text-[11px] uppercase tracking-widest hover:bg-[#ff3c00] transition-all flex items-center justify-center gap-3">
                            <i class="fa-solid fa-clipboard-check"></i> Finaliser
                        </button>
                    </div>

                    <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[10px] text-slate-500">
                                <i class="fa-solid fa-user"></i></div>
                            <span class="text-xs font-bold text-slate-600">Sophie Bertrand</span>
                        </div>
                        <button
                            class="text-[#ff3c00] text-xs font-black uppercase tracking-tighter hover:underline">Contacter</button>
                    </div>
                </div>

                <div
                    class="group relative bg-white rounded-[3rem] border-2 border-slate-900 shadow-[0_20px_40px_-15px_rgba(0,0,0,0.1)] flex flex-col h-full overflow-hidden opacity-90 scale-[0.98]">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-slate-100">
                        <div class="h-full bg-[#ff3c00] animate-progress-glow" style="width: 50%;"></div>
                    </div>

                    <div class="p-8 pb-0 flex justify-between items-start mt-2">
                        <div class="flex flex-col gap-2">
                            <span
                                class="px-3 py-1 bg-orange-50 text-[#ff3c00] rounded-lg font-black text-[9px] uppercase tracking-widest flex items-center gap-2 w-fit border border-orange-100">
                                En Transit
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter text-nowrap">Prise
                                en charge à 14:50</span>
                        </div>
                        <div class="text-right">
                            <p class="text-[9px] font-black text-slate-300 uppercase">Gain</p>
                            <p class="text-2xl font-black text-slate-900 tracking-tighter">12.00€</p>
                        </div>
                    </div>

                    <div class="p-8">
                        <h3
                            class="text-xl font-bold text-slate-800 leading-snug mb-8 group-hover:text-[#ff3c00] transition-colors">
                            Repas Chaud - Commande #992
                        </h3>
                        <div class="space-y-6 relative">
                            <div class="absolute left-[11px] top-3 bottom-3 w-[2px] bg-dashed border-l-2 border-slate-100">
                            </div>
                            <div class="flex items-start gap-5 relative z-10">
                                <div
                                    class="w-[24px] h-[24px] rounded-full bg-slate-100 border-2 border-slate-300 flex items-center justify-center flex-shrink-0 text-[10px] text-slate-400">
                                    <i class="fa-solid fa-location-dot"></i></div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase leading-none mb-1">Dépôt</p>
                                    <p class="text-sm font-bold text-slate-600 truncate">Restaurant "La Dolce Vita"</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-5 relative z-10">
                                <div
                                    class="w-[24px] h-[24px] rounded-full bg-slate-900 border-4 border-white shadow-md flex items-center justify-center flex-shrink-0 text-[10px] text-white">
                                    <i class="fa-solid fa-flag-checkered"></i></div>
                                <div>
                                    <p class="text-[10px] font-black text-[#ff3c00] uppercase leading-none mb-1">
                                        Destination</p>
                                    <p class="text-sm font-bold text-slate-800">Résidence Les Pins, Appt 4</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-8 pt-0 flex gap-3 mt-auto">
                        <a href="#"
                            class="flex-1 h-14 bg-white border-2 border-slate-900 text-slate-900 rounded-2xl font-black hover:bg-slate-900 hover:text-white transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-diamond-turn-right text-sm"></i>
                        </a>
                        <button
                            class="flex-[3] h-14 bg-slate-900 text-white rounded-2xl font-black text-[11px] uppercase tracking-widest hover:bg-[#ff3c00] transition-all flex items-center justify-center gap-3">
                            <i class="fa-solid fa-clipboard-check"></i> Finaliser
                        </button>
                    </div>

                    <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[10px] text-slate-500">
                                <i class="fa-solid fa-user"></i></div>
                            <span class="text-xs font-bold text-slate-600">Thomas Legrand</span>
                        </div>
                        <button
                            class="text-[#ff3c00] text-xs font-black uppercase tracking-tighter hover:underline">Contacter</button>
                    </div>
                </div>

            </div>
        </div>
    </section>


@endsection
