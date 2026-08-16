@extends('layouts.connected')
@section('title', 'Missions Livreur | ' . config('app.name'))

@section('content')
<section class="tab-content active animate-fade min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <nav aria-label="Breadcrumb" class="flex-1">
                <ol class="flex items-center space-x-2 text-sm font-medium">
                    <li>
                        <a href="#" class="text-slate-400 hover:text-slate-600 transition-colors">
                            Espace Driver
                        </a>
                    </li>
                    <li>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
                    </li>
                    <li class="text-slate-900 font-bold uppercase tracking-tight text-xs">
                        Missions disponibles
                    </li>
                </ol>
            </nav>

            <div class="flex bg-slate-100 p-1 rounded-full border border-slate-200">
                <a href="{{ route('livreur.missions') }}" class="tab-btn-vtc {{ request()->routeIs('livreur.missions') ? 'active' : '' }}">
                    Disponibles
                </a>
                <a href="{{ route('livreur.demandes') }}" class="tab-btn-vtc {{ request()->routeIs('livreur.demandes') ? 'active' : '' }}">
                    En attente
                </a>
                <a href="{{ route('livreur.livraisons') }}" class="tab-btn-vtc {{ request()->routeIs('livreur.livraisons') ? 'active' : '' }}">
                    En cours
                </a>
            </div>
        </div>

        <div class="mb-10">
            <p class="text-slate-500 mt-2">
                Parcourez les missions et proposez vos services de livraison.
            </p>
        </div>

        <div id="tab-dispo" class="tab-pane-vtc">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                @forelse ($missions as $mission)
                    <div
                        class="group relative bg-white rounded-[2.8rem] p-8 border border-slate-200 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.05)] hover:shadow-[0_20px_50px_-12px_rgba(255,60,0,0.12)] hover:-translate-y-2 transition-all duration-500 flex flex-col justify-between min-h-[480px] overflow-hidden">
                        <div
                            class="absolute -top-24 -right-24 w-48 h-48 bg-slate-50 rounded-full group-hover:bg-orange-50 transition-colors duration-500 -z-10">
                        </div>
                        <div>
                            <div class="flex justify-between items-start mb-8">
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        <div
                                            class="w-14 h-14 rounded-2xl bg-gradient-to-br from-slate-50 to-slate-100 p-[2px] shadow-sm group-hover:rotate-6 transition-transform duration-500">
                                            <div
                                                class="w-full h-full rounded-2xl bg-white overflow-hidden flex items-center justify-center">
                                                @if ($mission->user && $mission->user->avatar)
                                                    <img src="{{ $mission->user->avatar }}" alt=""
                                                        class="w-full h-full object-cover">
                                                @else
                                                    <i data-lucide="user" class="w-6 h-6 text-slate-300"></i>
                                                @endif
                                            </div>
                                        </div>
                                        <div
                                            class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 border-4 border-white rounded-full">
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-[#ff3c00] uppercase tracking-[0.15em] leading-none mb-1.5">
                                            Expéditeur
                                        </p>
                                        <p class="text-sm font-[900] text-slate-900 tracking-tight">
                                            {{ Str::limit ($mission->ad?->user?->name ?? $mission->product?->user?->name ?? '') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-3xl font-[1000] text-slate-900 tracking-tighter leading-none">
                                        {{ number_format($mission->delivery_cost, 0) }}
                                        <span class="text-lg ml-0.5 text-[#ff3c00]">€</span>
                                    </div>
                                    <span
                                        class="inline-block px-2 py-1 rounded-lg bg-slate-900 text-[8px] font-black text-white uppercase tracking-widest mt-2 shadow-lg shadow-slate-200">Net</span>
                                </div>
                            </div>
                           
                            <h3 class="text-xl font-[1000] text-slate-900 uppercase tracking-tighter leading-[1.1] mb-8 line-clamp-2 min-h-[2.2em] group-hover:text-[#ff3c00] transition-colors">
                                {{ Str::limit ($mission->ad?->title ?? $mission->product?->name ?? '') }}
                            </h3>
                            <div class="space-y-7 relative px-1">
                                <div
                                    class="absolute left-[11px] top-3 bottom-3 w-[2px] bg-gradient-to-b from-slate-200 via-slate-100 to-[#ff3c00] rounded-full">
                                </div>
                                <div class="flex items-start gap-5 relative z-10">
                                    <div
                                        class="w-[22px] h-[22px] rounded-full bg-white border-[3px] border-slate-900 flex items-center justify-center shrink-0 shadow-sm">
                                        <div class="w-1.5 h-1.5 rounded-full bg-slate-900"></div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Point de
                                            départ</p>
                                        <p class="text-[12px] font-bold text-slate-600 leading-tight uppercase tracking-tight group-hover:text-slate-900 transition-colors">
                                            {{ $mission->address }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-5 relative z-10">
                                    <div
                                        class="w-[22px] h-[22px] rounded-full bg-[#ff3c00] border-[3px] border-white shadow-[0_0_15px_rgba(255,60,0,0.4)] flex items-center justify-center shrink-0">
                                        <i data-lucide="map-pin" class="w-2.5 h-2.5 text-white"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[8px] font-black text-[#ff3c00] uppercase tracking-widest mb-1">Arrivée</p>
                                        <p class="text-[12px] font-[900] text-slate-900 leading-tight uppercase tracking-tight">
                                            {{ $mission->delivery_address }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-10">
                            <form action="{{ route('delivery.ads.request', ['ad' => $mission->id, 'type' => $mission->ad_id ? 'ad' : 'product' ]) }}" method="POST">
                                @csrf
                                <button
                                    class="w-full h-12 bg-slate-900 text-white rounded-xl font-black text-[10px] uppercase tracking-[0.15em] flex items-center justify-center gap-2 transition-all relative overflow-hidden group/btn shadow-lg shadow-slate-200 active:scale-95">
                                    <span class="relative z-10">Prendre la mission</span>
                                    <i data-lucide="bolt"
                                        class="w-4 h-4 relative z-10 group-hover/btn:fill-white group-hover/btn:scale-125 transition-all"></i>
                                    <div
                                        class="absolute inset-0 bg-[#ff3c00] -translate-x-full group-hover/btn:translate-x-0 transition-transform duration-500 ease-out">
                                    </div>
                                </button>
                            </form>
                            <br>
                            <button type="button" onclick='openMissionModal(@json($mission))' class="w-full h-10 bg-slate-100 text-slate-700 rounded-xl font-black text-[9px] uppercase tracking-[0.12em] flex items-center justify-center gap-2 hover:bg-slate-200 transition-all">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                                Voir les détails
                            </button>
                        </div>
                    </div>

                @empty
                    <div
                        class="col-span-full py-24 flex flex-col items-center justify-center text-center bg-white rounded-[3rem] border-2 border-dashed border-slate-100 shadow-sm">
                        <div class="relative mb-8">
                            <div class="w-32 h-32 bg-slate-50 rounded-[3.5rem] flex items-center justify-center animate-float">
                                <i data-lucide="ghost" class="w-16 h-16 text-slate-200"></i>
                            </div>
                            <div
                                class="absolute -bottom-2 -right-2 w-12 h-12 bg-white border-4 border-slate-50 rounded-full flex items-center justify-center shadow-lg">
                                <i data-lucide="search" class="w-5 h-5 text-[#ff3c00]"></i>
                            </div>
                        </div>

                        <h3 class="text-2xl font-[1000] text-slate-900 uppercase tracking-tighter italic">
                            C'est bien <span class="text-[#ff3c00]">calme</span> ici...
                        </h3>
                        <p class="text-slate-400 text-sm mt-3 max-w-xs mx-auto font-bold uppercase tracking-tight leading-relaxed opacity-70">
                            Aucune mission n'est disponible pour le moment. Revenez d'ici quelques minutes !
                        </p>
                        <button onclick="window.location.reload()"
                            class="mt-10 px-10 py-4 bg-slate-900 hover:bg-[#ff3c00] text-white rounded-full font-black text-[10px] uppercase tracking-[0.2em] transition-all shadow-xl shadow-slate-200 active:scale-95 flex items-center gap-3">
                            <i data-lucide="refresh-cw" class="w-3 h-3"></i> Actualiser le flux
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</section>

<div id="missionModal" class="fixed inset-0 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-2  z-[9999] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/60"
         onclick="closeMissionModal()"></div>
    <div class="relative z-10 bg-white w-full max-w-xl rounded-[2rem] shadow-2xl m-auto">
        <div class="flex items-center justify-between px-4 py-2 border-b overflow-auto">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-[#ff3c00]">
                    Mission
                </p>

                <h2 id="modalTitle"
                    class="text-2xl font-[1000] text-slate-900 uppercase tracking-tight">
                </h2>
            </div>

            <button onclick="closeMissionModal()"
                class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="p-8 space-y-6 overflow-auto h-[25rem]">

            <div class="grid grid-cols-2 gap-4">

                <div class="bg-slate-50 rounded-2xl p-4">
                    <p class="text-[10px] uppercase font-black text-slate-400">
                        Type
                    </p>
                    <p id="modalType" class="font-bold text-slate-900"></p>
                </div>

                <div class="bg-slate-50 rounded-2xl p-4">
                    <p class="text-[10px] uppercase font-black text-slate-400">
                        Prix
                    </p>
                    <p id="modalPrice"
                        class="font-black text-xl text-[#ff3c00]">
                    </p>
                </div>

            </div>

            <div>
                <p class="text-[10px] uppercase font-black text-slate-400 mb-1">
                    Expéditeur
                </p>

                <p id="modalSender"
                    class="font-bold text-slate-900">
                </p>
            </div>

            <div>
                <p class="text-[10px] uppercase font-black text-slate-400 mb-1">
                    Adresse de départ
                </p>

                <p id="modalPickup"
                    class="font-bold text-slate-700">
                </p>
            </div>

            <div>
                <p class="text-[10px] uppercase font-black text-slate-400 mb-1">
                    Adresse de livraison
                </p>

                <p id="modalDelivery"
                    class="font-bold text-slate-700">
                </p>
            </div>

            <div id="datesBlock" class="hidden">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-50 rounded-2xl p-4">
                        <p class="text-[10px] uppercase font-black text-slate-400">
                            Début
                        </p>
                        <p id="modalStartDate"></p>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-4">
                        <p class="text-[10px] uppercase font-black text-slate-400">
                            Fin
                        </p>
                        <p id="modalEndDate"></p>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-[10px] uppercase font-black text-slate-400 mb-1">
                    Description
                </p>

                <div id="modalDescription" class="bg-slate-50 rounded-2xl p-4 text-slate-700">
                </div>
            </div>

        </div>

    </div>
</div>
<script>
    function openMissionModal(ad) {
        document.getElementById('modalTitle').innerText = ad.product?.name ?? ad.ad?.title ?? 'Mission';
        document.getElementById('modalPrice').innerText = (ad.delivery_cost ?? 0) + ' €';
        document.getElementById('modalSender').innerText = ad.user?.name ?? 'Client';
        document.getElementById('modalPickup').innerText = ad.address ?? '-';
        document.getElementById('modalDelivery').innerText = ad.delivery_address ?? '-';
        document.getElementById('modalDescription').innerHTML = ad.product?.description ?? ad.ad?.description ?? 'Aucune description';
        document.getElementById('modalType').innerText = ad.ad_id ? 'Location' : 'Vente';

        if (ad.start_date) {
            document.getElementById('datesBlock').classList.remove('hidden');
            document.getElementById('modalStartDate').innerText = new Date(ad.start_date).toLocaleDateString('fr-FR');
            document.getElementById('modalEndDate').innerText = new Date(ad.end_date).toLocaleDateString('fr-FR');
        } else {
            document.getElementById('datesBlock').classList.add('hidden');
        }

        document.getElementById('missionModal').classList.remove('hidden');

        lucide.createIcons();
    }

    function closeMissionModal() {
        document.getElementById('missionModal').classList.add('hidden');
    }

    document.getElementById('missionModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeMissionModal();
        }
    });
</script>
@endsection