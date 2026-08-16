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
                <button onclick="showTab('dispo', this)" class="tab-btn-vtc active">
                    Disponibles
                </button>

                <button onclick="showTab('demandees', this)" class="tab-btn-vtc">
                    En attente
                </button>

                <button onclick="showTab('encours', this)" class="tab-btn-vtc">
                    En cours
                </button>
            </div>
        </div>

        <div class="mb-10">
            <p class="text-slate-500 mt-2">
                Parcourez les missions et proposez vos services de livraison.
            </p>
        </div>

        {{-- Missions disponibles --}}
        <div id="tab-dispo" class="tab-pane-vtc">
            @include('livreur.ads.partials.ads-grid', [
                'ads' => $missionsDisponibles,
                'mode' => 'disponible',
            ])
        </div>

        {{-- Mes demandes --}}
        <div id="tab-demandees" class="tab-pane-vtc hidden">
            @include('livreur.ads.partials.ads-grid', [
                'ads' => $mesDemandes,
                'mode' => 'demande'
            ])
        </div>

        {{-- Mes livraisons --}}
        <div id="tab-encours" class="tab-pane-vtc hidden">
            @include('livreur.ads.partials.ads-grid', [
                'ads' => $mesLivraisons,
                'mode' => 'encours'
            ])
        </div>

    </div>
</section>

<script>
    lucide.createIcons();

    function showTab(id, btn) {
        document.querySelectorAll('.tab-pane-vtc')
            .forEach(p => p.classList.add('hidden'));

        document.querySelectorAll('.tab-btn-vtc')
            .forEach(b => b.classList.remove('active'));

        document.getElementById('tab-' + id)
            .classList.remove('hidden');

        btn.classList.add('active');
    }
</script>

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

                <div id="modalDescription"
                    class="bg-slate-50 rounded-2xl p-4 text-slate-700">
                </div>
            </div>

        </div>

    </div>
</div>
@endsection