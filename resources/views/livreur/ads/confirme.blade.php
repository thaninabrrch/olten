@extends('layouts.connected')
@section('title', 'Demandes | ' . config('app.name'))

@section('content')
    <section class="min-h-screen pb-20 px-4 sm:px-6 lg:px-16 pt-8 sm:pt-10">

        {{-- Header --}}
        <div class="mb-8 sm:mb-10">
            <nav class="flex items-center gap-2 text-[11px] text-slate-400 uppercase tracking-widest mb-3">
                <a href="#" class="hover:text-slate-600 transition-colors">Annonces</a>
                <span>›</span>
                <span class="text-slate-600">Demandes reçues</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Demandes reçues</h1>
            <p class="text-sm text-slate-400 mt-1">Interface de décision en temps réel</p>
        </div>

        {{-- Annonces --}}
        <div class="space-y-8 sm:space-y-10">
            @forelse($requests as $request)
                <div class="bg-white border border-slate-100 rounded-2xl p-5">

                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-base font-semibold text-slate-900">
                                {{ $request->booking?->ad?->title ?? $request->productSale?->product?->name }}
                            </h2>

                            <p class="text-xs text-slate-400 mt-1">
                                {{ $request->booking?->address ?? $request->productSale?->address }}
                                →
                                {{ $request->booking?->delivery_address ?? $request->productSale?->delivery_address }}
                            </p>
                        </div>

                        <span class="text-[11px] font-medium bg-slate-100 text-slate-700 px-3 py-1 rounded-full">
                            {{ $request->booking?->delivery_cost ?? $request->productSale?->delivery_cost }} €
                        </span>
                    </div>

                    {{-- Livreur --}}
                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="w-9 h-9 rounded-xl bg-slate-900 flex items-center justify-center text-white text-xs font-semibold">
                            {{ strtoupper(substr($request->deliveryPerson->name, 0, 2)) }}
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-slate-900">
                                {{ $request->deliveryPerson->name }}
                            </p>

                            <span class="text-[10px] font-medium text-[#ff3c00]">
                                ● Certifié
                            </span>
                        </div>
                    </div>

                    {{-- Type --}}
                    <div class="mb-4">
                        <span class="text-xs text-slate-500">
                            {{ $request->booking_id ? 'Location' : 'Vente' }}
                        </span>
                    </div>

                    {{-- Actions --}}
                    @if($request->status === 'pending')
                        <div class="flex gap-2">
                            <form action="{{ route('delivery.request.accept', $request) }}" method="POST" class="flex-1">
                                @csrf

                                <button class="w-full py-2.5 bg-slate-900 text-white rounded-xl text-[11px] font-semibold">
                                    Recruter →
                                </button>
                            </form>

                            <form action="{{ route('delivery.request.refuse', $request) }}" method="POST">
                                @csrf

                                <button
                                    class="w-9 h-9 flex items-center justify-center border border-slate-100 rounded-xl">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    @else
                        <div
                            class="py-2.5 bg-slate-50 rounded-xl text-center text-[10px] font-medium text-slate-400 uppercase tracking-widest">
                            {{ $request->status }}
                        </div>
                    @endif

                </div>
            @empty
                <div
                    class="border border-dashed border-slate-200 rounded-2xl py-16 text-center text-xs text-slate-300 font-medium uppercase tracking-widest">
                    Aucune demande reçue
                </div>
            @endforelse
        </div>
    </section>
@endsection
