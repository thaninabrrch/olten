@extends('admin.layouts.app')

@section('title', 'Remboursements')
@section('page_title', 'Remboursements')

@php
    /*
     | Remboursements de la plateforme, tous services confondus
     | (Admin\RefundController) : covoiturage annule par le passager,
     | location refusee par le proprietaire, commande annulee par le vendeur.
     |
     | Chaque ligne renvoie au paiement dans le tableau de bord Stripe, qui
     | fait foi pour le montant reellement rembourse.
     */
    $euros = fn ($amount) => number_format((float) $amount, 2, ',', ' ') . ' €';

    $badges = [
        'covoiturage' => ['bi-car-front-fill', 'bg-orange-100 text-orange-700'],
        'location'    => ['bi-key-fill', 'bg-blue-100 text-blue-700'],
        'vente'       => ['bi-bag-fill', 'bg-green-100 text-green-700'],
    ];

    $objectUrl = fn ($row) => match ($row->service) {
        'covoiturage' => route('covoiturage.trip', $row->object_id),
        'location'    => route('ads.show', $row->object_id),
        'vente'       => route('products.show', $row->object_id),
        default       => null,
    };
@endphp

@section('content')

    <div class="page-inner">

        <div class="flex flex-col md:flex-row justify-between md:items-center gap-2 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Remboursements</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Réservations et commandes remboursées aux clients, sur tous les services.
                </p>
            </div>
        </div>

        {{-- Indicateurs : sur les filtres en cours --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
            <div class="card-white p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase">Total remboursé</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $euros($total) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $count }} remboursement{{ $count > 1 ? 's' : '' }}</p>
            </div>

            @foreach ($services as $key => $label)
                @php $line = $totals->get($key); @endphp
                <div class="card-white p-5">
                    <p class="text-xs font-semibold text-gray-500 uppercase">
                        <i class="bi {{ $badges[$key][0] }}"></i> {{ $label }}
                    </p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $euros($line->total ?? 0) }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ (int) ($line->count ?? 0) }} remboursement{{ ($line->count ?? 0) > 1 ? 's' : '' }}
                    </p>
                </div>
            @endforeach
        </div>

        {{-- Filtres --}}
        <div class="card-white p-6 mb-8">
            <form method="GET" action="{{ route('admin.refunds.index') }}"
                  class="flex flex-col lg:flex-row gap-4 lg:items-end">
                <div class="flex flex-col gap-1">
                    <label for="refundService" class="text-xs font-semibold text-gray-500 uppercase">Service</label>
                    <select id="refundService" name="service"
                            class="px-4 py-3 rounded-lg bg-white text-gray-700 border border-[rgba(233,29,40,1)]">
                        <option value="">Tous les services</option>
                        @foreach ($services as $key => $label)
                            <option value="{{ $key }}" @selected($filters['service'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1 flex-1">
                    <label for="refundSearch" class="text-xs font-semibold text-gray-500 uppercase">Client ou référence</label>
                    <input type="search" id="refundSearch" name="search" value="{{ $filters['search'] }}"
                           placeholder="Nom, e-mail ou pi_…"
                           class="px-4 py-3 rounded-lg bg-white text-gray-700 border border-[rgba(233,29,40,1)]">
                </div>

                <div class="flex flex-col gap-1">
                    <label for="refundFrom" class="text-xs font-semibold text-gray-500 uppercase">Du</label>
                    <input type="date" id="refundFrom" name="du" value="{{ $filters['du'] }}"
                           class="px-4 py-3 rounded-lg bg-white text-gray-700 border border-[rgba(233,29,40,1)]">
                </div>

                <div class="flex flex-col gap-1">
                    <label for="refundTo" class="text-xs font-semibold text-gray-500 uppercase">Au</label>
                    <input type="date" id="refundTo" name="au" value="{{ $filters['au'] }}"
                           class="px-4 py-3 rounded-lg bg-white text-gray-700 border border-[rgba(233,29,40,1)]">
                </div>

                <button class="px-6 py-3 bg-[rgb(233,29,40)] text-white font-semibold rounded-lg">Filtrer</button>
                <a href="{{ route('admin.refunds.index') }}"
                   class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg text-center">Réinitialiser</a>
            </form>
        </div>

        <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
            <div class="p-6 border-b">
                <h2 class="text-lg font-semibold text-gray-800">Historique des remboursements</h2>
                <p class="text-sm text-gray-500 mt-1">Les plus récents d'abord · le montant réellement remboursé fait foi dans Stripe</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Service</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Client</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Objet</th>
                            <th class="px-6 py-3 text-right font-semibold text-gray-500 uppercase">Montant</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Motif</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Paiement Stripe</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($refunds as $row)
                            @php
                                $subject = $row->service === 'covoiturage' && $row->subject
                                    ? \App\Models\Covoiturage::villeCourte($row->subject) . ' → ' . \App\Models\Covoiturage::villeCourte($row->subject_to)
                                    : $row->subject;
                                $url = $row->object_id && $row->subject ? $objectUrl($row) : null;
                            @endphp

                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $row->refunded_at ? \Illuminate\Support\Carbon::parse($row->refunded_at)->translatedFormat('d M Y · H:i') : '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full whitespace-nowrap {{ $badges[$row->service][1] }}">
                                        <i class="bi {{ $badges[$row->service][0] }}"></i> {{ $services[$row->service] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-800">{{ $row->client_name ?? 'Compte supprimé' }}</p>
                                    @if ($row->client_email)
                                        <p class="text-xs text-gray-500">{{ $row->client_email }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($url)
                                        <a href="{{ $url }}" target="_blank" rel="noopener" class="text-gray-800 hover:underline">{{ $subject }}</a>
                                    @else
                                        {{ $subject ?: '—' }}
                                    @endif
                                    <p class="text-xs text-gray-400">#{{ $row->source_id }}</p>
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-800 whitespace-nowrap">{{ $euros($row->amount) }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $row->reason }}</td>
                                <td class="px-6 py-4">
                                    @if ($row->reference)
                                        <a href="{{ $stripeUrl . $row->reference }}" target="_blank" rel="noopener"
                                           class="font-mono text-xs text-blue-600 hover:underline">{{ $row->reference }}</a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-6">
                                    <x-empty-state compact
                                        title="Aucun remboursement"
                                        text="Aucun remboursement ne correspond aux filtres sélectionnés." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($refunds->hasPages())
                <div class="p-4 border-t">
                    {{ $refunds->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection
