@extends('admin.layouts.app')

@section('title', 'Dashboard | ' . config('app.name'))
@section('page_title', 'Tableau de bord')

@section('content')


<div class="p-6 lg:p-10">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
        <div>
            <h1 class="text-3xl font-[800] tracking-tight text-slate-900">
                Hello, <span class="accent-rouge">{{ Auth::user()->name ?? 'Admin' }}</span> 👋
            </h1>
            <p class="text-slate-500 font-medium mt-1">
                Content de vous revoir. Voici le résumé de votre activité aujourd'hui.
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="text-right hidden md:block mr-2">
                <p class="text-[10px] font-black uppercase text-slate-400 leading-none">Dernière connexion</p>
                <p class="text-xs font-bold text-slate-700">{{ now()->format('d M, H:i') }}</p>
            </div>
      
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        @php
            $stats = [
                ['label' => 'Utilisateurs', 'val' => '1,254', 'icon' => 'people', 'trend' => '+12%'],
                ['label' => 'Services Actifs', 'val' => '48', 'icon' => 'grid-1x2', 'trend' => '+3%'],
                ['label' => 'Messages', 'val' => '12', 'icon' => 'chat-square-text', 'trend' => 'Nouveau'],
                ['label' => 'Config Types', 'val' => '6', 'icon' => 'sliders', 'trend' => 'Stable'],
            ];
        @endphp

        @foreach($stats as $s)
        <div class="stat-card p-6 rounded-2xl flex items-center gap-5">
            <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:text-red-500 transition-colors border border-slate-100">
                <i class="bi bi-{{ $s['icon'] }} text-2xl"></i>
            </div>
            <div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">{{ $s['label'] }}</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-black text-slate-800">{{ $s['val'] }}</h3>
                    <span class="text-[10px] font-bold accent-rouge italic">{{ $s['trend'] }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <div class="lg:col-span-4 bg-white border border-slate-200 rounded-[2rem] p-8 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Répartition Rôles</h3>
                <span class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400"><i class="bi bi-pie-chart"></i></span>
            </div>
            
            <div id="roleChart" style="min-height: 260px;"></div>

            <div class="mt-8 space-y-3">
                @foreach(['Particulier', 'Livreur', 'Conducteur'] as $role)
                <div class="flex justify-between items-center p-3 rounded-xl border border-slate-50 hover:bg-slate-50 transition group">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-accent-rouge opacity-40 group-hover:opacity-100 transition-opacity"></span>
                        <span class="text-xs font-bold text-slate-600">{{ $role }}</span>
                    </div>
                    <span class="text-xs font-black text-slate-900">33%</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="lg:col-span-8 bg-white border border-slate-200 rounded-[2rem] shadow-sm flex flex-col overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Activité des Services</h3>
                <button class="text-slate-400 hover:text-red-500 transition"><i class="bi bi-three-dots-vertical"></i></button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50/50">
                        <tr class="text-[10px] uppercase font-black text-slate-400 text-left tracking-widest">
                            <th class="px-8 py-4">Nom du Service</th>
                            <th class="px-8 py-4">Sous-Catégorie</th>
                            <th class="px-8 py-4 text-center">Status</th>
                            <th class="px-8 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-xs">
                        @foreach([1,2,3,4,5] as $row)
                        <tr class="hover:bg-slate-50/80 transition cursor-pointer">
                            <td class="px-8 py-5">
                                <span class="font-bold text-slate-800">Livraison Express Urbaine</span>
                            </td>
                            <td class="px-8 py-5 text-slate-500 font-medium">Logistique & Transport</td>
                            <td class="px-8 py-5 text-center">
                                <span class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase border border-emerald-100">Actif</span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <button class="text-slate-300 hover:accent-rouge transition"><i class="bi bi-pencil-square text-lg"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 bg-slate-50/50 border-t border-slate-100 text-center text-[11px] font-bold text-slate-400">
                <a href="#" class="hover:text-red-500 transition">VOIR TOUTE L'ACTIVITÉ <i class="bi bi-arrow-right ml-1"></i></a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var chartDom = document.getElementById('roleChart');
    var myChart = echarts.init(chartDom);
    var option = {
        tooltip: { trigger: 'item', padding: 10, borderRadius: 10 },
        series: [{
            name: 'Rôles',
            type: 'pie',
            radius: ['75%', '95%'],
            avoidLabelOverlap: false,
            itemStyle: { borderRadius: 4, borderColor: '#fff', borderWidth: 3 },
            label: { show: false },
            data: [
                { value: 850, name: 'Particulier', itemStyle: { color: 'rgb(233, 29, 40)' } }, // Ton rouge
                { value: 120, name: 'Livreur', itemStyle: { color: '#1a1c1e' } }, // Noir/Gris foncé
                { value: 200, name: 'Conducteur', itemStyle: { color: '#94a3b8' } }, // Gris
                { value: 84,  name: 'Locataire', itemStyle: { color: '#e2e8f0' } }  // Gris clair
            ]
        }]
    };
    window.addEventListener('resize', () => myChart.resize());
    myChart.setOption(option);
});
</script>
@endsection