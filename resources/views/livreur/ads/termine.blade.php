@extends('layouts.connected')
@section('title', 'Historique des Livraisons | ' . config('app.name'))

@section('content')
    <section class="animate-fade min-h-screen pb-20">
        <div class="px-6 lg:px-16 pt-12 mb-16">
            <nav aria-label="Breadcrumb" class="mb-8">
                <ol class="flex items-center space-x-2 text-sm font-medium">
                    <li>
                        <a href="#"
                            class="text-slate-400 hover:text-slate-600 transition-colors text-xs uppercase tracking-[0.2em] font-black">
                            Livreur
                        </a>
                    </li>
                    <li><i data-lucide="chevron-right" class="w-4 h-4 text-slate-200"></i></li>
                    <li class="text-slate-900 font-black uppercase tracking-[0.2em] text-xs italic">Historique</li>
                </ol>
            </nav>
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-8">

                <div class="mb-10">
                    <p class="text-slate-500 mt-2"> Archives de vos performances passées.</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="bg-white border border-slate-100 p-6 rounded-[2.5rem] shadow-sm flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-2xl bg-slate-900 flex items-center justify-center text-white shadow-lg shadow-slate-200">
                            <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">
                                Total</p>
                            <p class="text-xl font-[1000] text-slate-900 leading-none">{{ $totalLivres }}</p>
                        </div>
                    </div>
                    <div class="bg-white border border-slate-100 p-6 rounded-[2.5rem] shadow-sm flex items-center gap-4">
                        <div class="w-10 h-10 rounded-2xl bg-[#ff3c00]/10 flex items-center justify-center text-[#ff3c00]">
                            <i data-lucide="banknote" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p
                                class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1 text-nowrap">
                                Revenus</p>
                            <p class="text-xl font-[1000] text-slate-900 leading-none">
                                {{ number_format($revenusCumules, 0) }}€</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-6 lg:px-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse ($livraisonsTerminees as $livraison)

                @php
                    $client = $livraison->booking?->ad?->user ?? $livraison->order?->product?->user;

                    $titre = $livraison->booking?->ad?->title ?? $livraison->order?->product?->name ?? 'Livraison';

                    $date = $livraison->delivered_at ?? $livraison->created_at;
                @endphp

                <div onclick='openHistoryModal(@json($livraison))' class="group cursor-pointer bg-white rounded-[3rem] border border-slate-100 p-8 hover:border-[#ff3c00] hover:-translate-y-1 hover:shadow-[0_30px_60px_-15px_rgba(0,0,0,0.08)] transition-all duration-500 flex flex-col h-full"
>                    <div class="flex justify-between items-start mb-8">
                        <div class="space-y-2">
                            <span class="px-3 py-1 bg-green-50 text-green-600 rounded-lg font-black text-[8px] uppercase tracking-widest flex items-center gap-1.5 w-fit">
                                <i data-lucide="check" class="w-3 h-3"></i>
                                Terminé
                            </span>

                            <p class="text-[9px] font-bold text-slate-300 uppercase">
                                {{ $date?->translatedFormat('d M Y') }}
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="text-2xl font-[1000] text-slate-900 tracking-tighter leading-none">
                                {{ number_format($livraison->total_price, 2) }}€
                            </p>

                            <p class="text-[8px] font-black text-slate-300 uppercase tracking-widest mt-1">
                                Revenus
                            </p>
                        </div>
                    </div>

                    <h3
                        class="text-xl font-black text-slate-900 uppercase tracking-tighter leading-tight mb-8 group-hover:text-[#ff3c00] transition-colors line-clamp-2 min-h-[3rem]">
                        {{ $titre }}
                    </h3>

                    <div class="space-y-6 relative mb-8">

                        <div class="absolute left-[9px] top-2 bottom-2 w-[1px] bg-slate-100"></div>

                        <div
                            class="flex items-start gap-4 relative z-10 opacity-40 group-hover:opacity-60 transition-opacity">
                            <div class="w-5 h-5 rounded-full bg-slate-100 border-4 border-white shadow-sm flex-none mt-1">
                            </div>

                            <div class="min-w-0">
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                    Départ
                                </p>

                                <p class="text-[11px] font-bold text-slate-500 truncate">
                                    {{ $livraison->pickup_address }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 relative z-10">
                            <div class="w-5 h-5 rounded-full bg-slate-900 border-4 border-white shadow-md flex-none mt-1">
                            </div>

                            <div class="min-w-0">
                                <p class="text-[8px] font-black text-[#ff3c00] uppercase tracking-widest mb-1 italic">
                                    Arrivée
                                </p>

                                <p class="text-[11px] font-black text-slate-900 truncate">
                                    {{ $livraison->delivery_address }}
                                </p>
                            </div>
                        </div>

                    </div>

                    <div class="mt-auto pt-6 border-t border-slate-50 flex items-center justify-between">

                        <div class="flex items-center gap-3">

                            <div
                                class="w-10 h-10 rounded-2xl bg-slate-50 flex items-center justify-center overflow-hidden border border-slate-100">

                                <img
                                    src="https://ui-avatars.com/api/?name={{ urlencode($client?->name ?? 'C') }}&background=f8fafc&color=0f172a&bold=true"
                                    alt="">
                            </div>

                            <div>
                                <p class="text-[8px] font-black text-slate-300 uppercase tracking-widest">
                                    Client
                                </p>

                                <p class="text-xs font-black text-slate-900 uppercase tracking-tighter">
                                    {{ $client?->name ?? 'Anonyme' }}
                                </p>
                            </div>
                        </div>

                        <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center">
                            <i data-lucide="arrow-up-right" class="w-3 h-3 text-slate-300 group-hover:text-slate-900 transition-colors"></i>
                        </div>
                        
                    </div>
                @if($livraison->reviews->count())

                    <div class="delivery-reviews-card">

                        <div class="reviews-header">
                            <i class="fas fa-star"></i>
                            <h4>Avis reçus ({{ $livraison->reviews->count() }})</h4>
                        </div>

                        @foreach($livraison->reviews as $review)

                            <div class="review-item">

                                <div class="review-top">

                                    <div class="review-stars">

                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fa-star {{ $i <= $review->rating ? 'fas active-star' : 'far empty-star' }}"></i>
                                        @endfor

                                    </div>

                                </div>

                                @if($review->comment)
                                    <div class="review-comment">
                                        "{{ $review->comment }}"
                                    </div>
                                @endif

                                <div class="review-date">
                                    {{ $review->updated_at->format('d/m/Y H:i') }}
                                </div>

                            </div>

                        @endforeach

                    </div>

                @endif
                </div>
            @empty

                <div class="col-span-full">
                    <div
                        class="border border-dashed border-slate-200 rounded-[2rem] py-16 text-center text-slate-400">
                        Aucune livraison terminée.
                    </div>
                </div>

            @endforelse
        </div>
    </section>
    <div id="historyModal" class="fixed inset-0 z-[99999] items-center justify-center hidden bg-black/60 backdrop-blur-sm">

        <div class="flex items-center justify-center min-h-screen p-4">

            <div class="bg-white w-full max-w-2xl rounded-[2rem] overflow-auto h-[30rem] ">

                <div class="p-8 border-b border-slate-100 flex justify-between items-center">

                    <div>
                        <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 font-black">
                            Livraison terminée
                        </p>

                        <h2 id="modalTitle"
                            class="text-2xl font-black text-slate-900 mt-2">
                        </h2>
                    </div>

                    <button onclick="closeHistoryModal()">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>

                </div>

                <div class="p-8">

                    <div id="historyContent"></div>

                </div>

            </div>

        </div>

    </div>
    <script>
        function formatDate(date)
        {
            if (!date) return '-';

            return new Date(date).toLocaleString('fr-FR');
        }

        function openHistoryModal(livraison)
        {
            document.getElementById('modalTitle').innerText =
                'Livraison #' + livraison.id;

            document.getElementById('historyContent').innerHTML = `
                <div class="grid grid-cols-2 gap-4 mb-8">

                    <div class="bg-slate-50 rounded-2xl p-4">
                        <p class="text-[10px] uppercase tracking-widest text-slate-400 font-black mb-2">
                            Statut
                        </p>

                        <p class="font-black text-green-600 uppercase">
                            ${livraison.status}
                        </p>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-4">
                        <p class="text-[10px] uppercase tracking-widest text-slate-400 font-black mb-2">
                            Montant
                        </p>

                        <p class="font-black text-[#ff3c00]">
                            ${livraison.total_price} €
                        </p>
                    </div>

                </div>

                <div class="space-y-8 relative">

                    <div class="absolute left-[23px] top-8 bottom-8 w-[2px] bg-slate-200"></div>

                    <div class="flex gap-5 relative z-10">

                        <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center">
                            <i data-lucide="clipboard-check" class="w-5 h-5 text-white"></i>
                        </div>

                        <div>
                            <p class="font-black text-slate-900">
                                Mission créée
                            </p>

                            <p class="text-sm text-slate-500">
                                ${formatDate(livraison.created_at)}
                            </p>
                        </div>

                    </div>

                    ${livraison.picked_up_at ? `
                        <div class="flex gap-5 relative z-10">

                            <div class="w-12 h-12 rounded-2xl bg-amber-500 flex items-center justify-center">
                                <i data-lucide="package-check" class="w-5 h-5 text-white"></i>
                            </div>

                            <div>
                                <p class="font-black text-slate-900">
                                    Colis récupéré
                                </p>

                                <p class="text-sm text-slate-500">
                                    ${formatDate(livraison.picked_up_at)}
                                </p>
                            </div>

                        </div>
                    ` : ''}

                    ${livraison.delivered_at ? `
                        <div class="flex gap-5 relative z-10">

                            <div class="w-12 h-12 rounded-2xl bg-green-600 flex items-center justify-center">
                                <i data-lucide="check-circle" class="w-5 h-5 text-white"></i>
                            </div>

                            <div>
                                <p class="font-black text-slate-900">
                                    Livraison terminée
                                </p>

                                <p class="text-sm text-slate-500">
                                    ${formatDate(livraison.delivered_at)}
                                </p>
                            </div>

                        </div>
                    ` : ''}

                </div>

                <div class="mt-10 border-t border-slate-100 pt-6">

                    <div class="mb-5">
                        <p class="text-[10px] uppercase tracking-widest text-slate-400 font-black mb-2">
                            Adresse de départ
                        </p>

                        <p class="font-semibold text-slate-700">
                            ${livraison.pickup_address}
                        </p>
                    </div>

                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-[#ff3c00] font-black mb-2">
                            Adresse d'arrivée
                        </p>

                        <p class="font-semibold text-slate-900">
                            ${livraison.delivery_address}
                        </p>
                    </div>

                </div>
            `;

            document.getElementById('historyModal').classList.remove('hidden');
            document.getElementById('historyModal').classList.add('flex');

            lucide.createIcons();
        }

        function closeHistoryModal()
        {
            document.getElementById('historyModal').classList.add('hidden');
            document.getElementById('historyModal').classList.remove('flex');
        }

        document.getElementById('historyModal').addEventListener('click', function(e)
        {
            if (e.target === this)
            {
                closeHistoryModal();
            }
        });
    </script>
@endsection
