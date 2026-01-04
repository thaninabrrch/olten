@extends('admin.layouts.app')
@section('title', 'Validation des cartes VTC')
@section('page_title', 'Validation des cartes VTC')

@section('content')

    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Cartes VTC à valider</h1>
    </div>

    {{-- Recherche --}}
    <div class="card-white p-6 mb-8">
        <form method="GET" class="flex flex-col md:flex-row gap-4 md:items-center">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par conducteur..."
                class="w-full md:w-1/3 px-4 py-3 rounded-lg border focus:ring-2 focus:ring-red-500">
            <select name="status" class="px-4 py-3 rounded-lg border focus:ring-2 focus:ring-red-500">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approuvé</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejeté</option>
            </select>
            <button class="px-6 py-3 bg-red-600 text-white rounded-lg">Filtrer</button>
        </form>
    </div>

    {{-- Tableau --}}
    <div class="card-white p-4">
        <div class="table-wrapper">
            <table class="min-w-full table-rounded divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left">Conducteur</th>
                        <th class="px-6 py-3 text-left">Type</th>
                        <th class="px-6 py-3 text-left">Document</th>
                        <th class="px-6 py-3 text-left">Statut</th>
                        <th class="px-6 py-3 text-left">Raison du rejet</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($documents as $doc)
                        <tr>
                            <td class="px-6 py-4 font-semibold">{{ $doc->user->name }}</td>

                            {{-- Type --}}
                            <td class="px-6 py-4 font-medium">
                                {{ $doc->name === 'vtc_card' ? 'Carte VTC' : ($doc->name === 'identity_card' ? 'Pièce d\'identité' : $doc->name) }}
                            </td>

                            {{-- Document --}}
                            <td class="px-6 py-4">
                                <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                                    class="text-blue-500 underline">Voir le document</a>
                            </td>

                            {{-- Statut --}}
                            <td class="px-6 py-4">
                                @if ($doc->status === 'approved')
                                    <span class="text-green-600 font-bold">Approuvé</span>
                                @elseif($doc->status === 'rejected')
                                    <span class="text-red-600 font-bold">Rejeté</span>
                                @else
                                    <span class="text-yellow-600 font-bold">En attente</span>
                                @endif
                            </td>

                            {{-- Raison du rejet --}}
                            <td class="px-6 py-4">{{ $doc->rejection_reason ?? '-' }}</td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 text-right">
                                <div class="relative inline-block">
                                    <button
                                        class="action-btn px-2 py-2 rounded-full border bg-white hover:bg-gray-100 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-700"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6h.01M12 12h.01M12 18h.01" />
                                        </svg>
                                    </button>

                                    <div class="dropdown-menu-white absolute right-9 w-36 divide-y divide-gray-200">
                                        <form action="{{ route('admin.vtc_cards.approve', $doc) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="w-full flex items-center px-4 py-3 text-green-600 hover:bg-gray-100">Approuver</button>
                                        </form>
                                        <button type="button" onclick="openRejectModal({{ $doc->id }})"
                                            class="w-full flex items-center px-4 py-3 text-red-600 hover:bg-gray-100">Rejeter</button>
                                    </div>
                                </div>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $documents->links() }}
    </div>




    {{-- Modal rejet --}}
    <div id="rejectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="bg-white p-6 rounded-xl w-full max-w-md">
            <h3 class="text-xl font-bold mb-4">Raison du rejet</h3>
            <form id="rejectForm" method="POST">
                @csrf
                <textarea name="rejection_reason" rows="4" class="w-full border rounded-lg p-2" required></textarea>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" class="px-4 py-2 bg-gray-300 rounded-lg"
                        onclick="closeRejectModal()">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">Rejeter</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Dropdown actions
            const buttons = document.querySelectorAll(".action-btn");
            const closeAll = () => {
                document.querySelectorAll(".dropdown-menu-white")
                    .forEach(m => m.classList.remove("active"));
            };
            buttons.forEach(btn => {
                btn.addEventListener("click", e => {
                    e.stopPropagation();
                    const menu = btn.nextElementSibling;
                    closeAll();
                    menu.classList.toggle("active");
                });
            });
            document.addEventListener("click", closeAll);
        });
        let rejectModal = document.getElementById('rejectModal');
        let rejectForm = document.getElementById('rejectForm');

        function openRejectModal(id) {
            rejectForm.action = `/admin/vtc-cards/${id}/reject`;
            rejectModal.classList.remove('hidden');
            rejectModal.classList.add('flex');
        }

        function closeRejectModal() {
            rejectModal.classList.add('hidden');
            rejectModal.classList.remove('flex');
        }
    </script>

@endsection
