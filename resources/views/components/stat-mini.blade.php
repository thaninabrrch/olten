@props(['title', 'value', 'trend', 'color' => 'orange', 'icon' => 'fa-chart-simple'])

@php
    $themes = [
        'orange' => 'text-[#ff3c00] bg-orange-50 border-orange-100',
        'blue'   => 'text-blue-600 bg-blue-50 border-blue-100',
        'yellow' => 'text-yellow-600 bg-yellow-50 border-yellow-100',
        'pink'   => 'text-pink-600 bg-pink-50 border-pink-100',
    ];
    $theme = $themes[$color] ?? $themes['orange'];
@endphp

<div class="bg-white p-6 rounded-[2.2rem] border border-slate-100 shadow-[0_2px_15px_rgba(0,0,0,0.02)] flex flex-col justify-between h-40 group hover:border-[#ff3c00]/30 transition-all">
    <div class="flex justify-between items-start">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm {{ $theme }} border">
            <i class="fa-solid {{ $icon }}"></i>
        </div>
        <span class="text-[9px] font-black uppercase {{ $theme }} px-2 py-0.5 rounded-md">
            {{ $trend }}
        </span>
    </div>
    
    <div class="space-y-1">
        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $title }}</p>
        <p class="text-2xl font-black text-slate-900 tracking-tighter truncate">{{ $value }}</p>
    </div>
</div>