@extends('admin.layouts.app')

@section('title', 'Dashboard | ' . config('app.name'))
@section('page_title', 'Tableau de bord')

@section('content')
    <div class="p-6 lg:p-10 space-y-8">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-[800] tracking-tight text-slate-900">
                    Bonjour, <span class="accent-rouge">{{ Auth::user()->name ?? 'Admin' }}</span> 👋
                </h1>
                <p class="text-slate-500 text-sm mt-1">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }} — Vue d'ensemble de la
                    plateforme</p>
            </div>
            <a href="{{ route('admin.users.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-red-600 text-white text-xs font-bold hover:bg-red-700 transition">
                <i class="bi bi-people-fill"></i> Gérer les utilisateurs
            </a>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
            @php
                $kpis = [
                    [
                        'label' => 'Utilisateurs',
                        'val' => $totalUsers,
                        'icon' => 'people-fill',
                        'color' => 'bg-blue-50 text-blue-600',
                        'link' => route('admin.users.index'),
                    ],
                    [
                        'label' => 'Covoiturages',
                        'val' => $totalCovoit,
                        'icon' => 'car-front-fill',
                        'color' => 'bg-violet-50 text-violet-600',
                        'link' => route('admin.rides.index'),
                    ],
                    [
                        'label' => 'Livraisons',
                        'val' => $totalLivraisons,
                        'icon' => 'box-seam-fill',
                        'color' => 'bg-amber-50 text-amber-600',
                        'link' => '#',
                    ],
                    [
                        'label' => 'Messages',
                        'val' => $totalMessages,
                        'icon' => 'chat-dots-fill',
                        'color' => 'bg-rose-50 text-rose-600',
                        'link' => route('admin.contact_messages.index'),
                    ],
                    [
                        'label' => 'Services',
                        'val' => $totalServices,
                        'icon' => 'grid-fill',
                        'color' => 'bg-slate-100 text-slate-600',
                        'link' => route('admin.services.index'),
                    ],
                ];
            @endphp

            @foreach ($kpis as $k)
                <a href="{{ $k['link'] }}"
                    class="group bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition flex flex-col gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $k['color'] }} flex items-center justify-center">
                        <i class="bi bi-{{ $k['icon'] }} text-lg"></i>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-black text-slate-400 tracking-widest">{{ $k['label'] }}</p>
                        <p class="text-2xl font-black text-slate-900">{{ number_format($k['val']) }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Livraisons breakdown --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @php
                $livBreak = [
                    ['label' => 'Colis', 'val' => $totalLivColis, 'icon' => 'box2-fill', 'color' => 'text-amber-500'],
                    [
                        'label' => 'Repas',
                        'val' => $totalLivRepas,
                        'icon' => 'cup-hot-fill',
                        'color' => 'text-orange-500',
                    ],
                    ['label' => 'VTC', 'val' => $totalLivVtc, 'icon' => 'taxi-front-fill', 'color' => 'text-sky-500'],
                ];
            @endphp
            @foreach ($livBreak as $lb)
                <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                    <i class="bi bi-{{ $lb['icon'] }} text-3xl {{ $lb['color'] }}"></i>
                    <div>
                        <p class="text-[10px] uppercase font-black text-slate-400 tracking-widest">Livraison
                            {{ $lb['label'] }}</p>
                        <p class="text-xl font-black text-slate-900">{{ number_format($lb['val']) }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Charts + Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- Graphe rôles --}}
            <div class="lg:col-span-4 bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-4">Répartition des rôles</h3>
                <div id="roleChart" style="height:220px;"></div>
                <div class="mt-5 space-y-2">
                    @php
                        $roleColors = [
                            'particulier' => '#e91d28',
                            'livreur' => '#1a1c1e',
                            'conducteur' => '#94a3b8',
                            'admin' => '#3b82f6',
                            'locateur' => '#f59e0b',
                        ];
                        $roleTotal = array_sum($roles);
                    @endphp
                    @foreach ($roles as $roleName => $roleCount)
                        <div
                            class="flex justify-between items-center text-xs py-1.5 px-3 rounded-lg hover:bg-slate-50 transition">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full"
                                    style="background:{{ $roleColors[$roleName] ?? '#cbd5e1' }}"></span>
                                <span class="font-semibold text-slate-700 capitalize">{{ $roleName }}</span>
                            </div>
                            <span class="font-black text-slate-900">{{ $roleCount }} <span
                                    class="font-normal text-slate-400">({{ $roleTotal ? round(($roleCount / $roleTotal) * 100) : 0 }}%)</span></span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Graphe inscriptions --}}
            <div class="lg:col-span-8 bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">Inscriptions — 6 derniers
                            mois</h3>
                        <p class="text-2xl font-black text-slate-900 mt-1">{{ $totalUsers }} <span
                                class="text-sm font-medium text-slate-400">utilisateurs au total</span></p>
                    </div>
                    @if ($covoitPending > 0)
                        <a href="{{ route('admin.rides.index') }}"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 text-[11px] font-black border border-amber-100 hover:bg-amber-100 transition">
                            <i class="bi bi-clock-fill"></i> {{ $covoitPending }} en attente
                        </a>
                    @endif
                </div>
                <div id="inscriptionsChart" style="height:220px;"></div>
            </div>
        </div>

        {{-- Activité récente + Nouveaux users --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- Activité récente --}}
            <div class="lg:col-span-8 bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">Activité récente</h3>
                    <span class="text-[10px] text-slate-400 font-medium">8 dernières entrées</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50">
                            <tr class="text-[10px] uppercase font-black text-slate-400 text-left tracking-widest">
                                <th class="px-6 py-3">Type</th>

                                <th class="px-6 py-3">Utilisateur</th>
                                <th class="px-6 py-3 text-center">Statut</th>
                                <th class="px-6 py-3 text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($recentActivity as $item)
                                @php
                                    $badge = match ($item['statut']) {
                                        'actif',
                                        'active',
                                        'delivered',
                                        'completed'
                                            => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'pending', 'en_attente' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        'inactif', 'cancelled', 'refusee' => 'bg-red-50 text-red-600 border-red-100',
                                        default => 'bg-slate-50 text-slate-500 border-slate-100',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-6 py-4">
                                        <a href="{{ $item['link'] }}"
                                            class="flex items-center gap-2 font-bold text-slate-700 hover:text-red-600 transition">
                                            <i class="bi bi-{{ $item['icon'] }}"></i>
                                            {{ $item['label'] }}
                                        </a>
                                    </td>

                                    <td class="px-6 py-4 font-medium text-slate-700">{{ $item['user'] }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="px-2 py-0.5 rounded-md border text-[10px] font-black uppercase {{ $badge }}">
                                            {{ $item['statut'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-slate-400">
                                        {{ \Carbon\Carbon::parse($item['date'])->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-slate-400">Aucune activité récente
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Nouveaux utilisateurs --}}
            <div class="lg:col-span-4 bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">Nouveaux membres</h3>
                    <a href="{{ route('admin.users.index') }}"
                        class="text-[10px] font-bold text-red-500 hover:underline">Voir tout</a>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse($recentUsers as $u)
                        <a href="{{ route('admin.users.show', $u) }}"
                            class="flex items-center gap-3 px-6 py-4 hover:bg-slate-50 transition">
                            <div
                                class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-black text-sm shrink-0 overflow-hidden">
                                @if ($u->profile_photo)
                                    <img src="{{ asset('storage/' . $u->profile_photo) }}"
                                        class="w-full h-full object-cover" alt="">
                                @else
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 truncate">{{ $u->name }}</p>
                                <p class="text-[10px] text-slate-400 truncate">{{ $u->email }}</p>
                            </div>
                            <span
                                class="ml-auto text-[10px] font-bold px-2 py-0.5 rounded-lg bg-slate-100 text-slate-500 capitalize shrink-0">
                                {{ $u->role ?? 'N/A' }}
                            </span>
                        </a>
                    @empty
                        <p class="px-6 py-8 text-center text-xs text-slate-400">Aucun utilisateur</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- Graphe rôles (donut) ---
            var roleChart = echarts.init(document.getElementById('roleChart'));
            var roleColors = {
                particulier: '#e91d28',
                livreur: '#1a1c1e',
                conducteur: '#94a3b8',
                admin: '#3b82f6',
                locateur: '#f59e0b'
            };
            var rolesData = @json($roles);
            var roleSeries = Object.entries(rolesData).map(([name, value]) => ({
                name,
                value,
                itemStyle: {
                    color: roleColors[name] ?? '#cbd5e1'
                }
            }));
            roleChart.setOption({
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} ({d}%)'
                },
                series: [{
                    type: 'pie',
                    radius: ['65%', '88%'],
                    avoidLabelOverlap: false,
                    itemStyle: {
                        borderRadius: 5,
                        borderColor: '#fff',
                        borderWidth: 3
                    },
                    label: {
                        show: false
                    },
                    data: roleSeries
                }]
            });

            // --- Graphe inscriptions (bar) ---
            var inscChart = echarts.init(document.getElementById('inscriptionsChart'));
            var inscData = @json($inscriptions);
            var moisLabels = Object.keys(inscData);
            var moisValeurs = Object.values(inscData);
            inscChart.setOption({
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                grid: {
                    left: 0,
                    right: 0,
                    top: 10,
                    bottom: 0,
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: moisLabels,
                    axisLine: {
                        show: false
                    },
                    axisTick: {
                        show: false
                    },
                    axisLabel: {
                        fontSize: 10,
                        color: '#94a3b8',
                        fontWeight: 700
                    }
                },
                yAxis: {
                    type: 'value',
                    splitLine: {
                        lineStyle: {
                            color: '#f1f5f9'
                        }
                    },
                    axisLabel: {
                        fontSize: 10,
                        color: '#94a3b8'
                    }
                },
                series: [{
                    type: 'bar',
                    data: moisValeurs,
                    barMaxWidth: 40,
                    itemStyle: {
                        color: '#e91d28',
                        borderRadius: [6, 6, 0, 0]
                    },
                    emphasis: {
                        itemStyle: {
                            color: '#c01020'
                        }
                    }
                }]
            });

            window.addEventListener('resize', () => {
                roleChart.resize();
                inscChart.resize();
            });
        });
    </script>
@endsection
