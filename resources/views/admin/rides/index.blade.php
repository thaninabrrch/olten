@extends('admin.layouts.app')

@section('title', 'Trajets')
@section('page_title', 'Gestion des trajets')

@section('content')

    <div class="page-inner">

        <div class="flex flex-col md:flex-row justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Liste des trajets</h1>
        </div>

        <div class="card-white p-6 mb-8">
            <form method="GET" action="{{ route('admin.rides.index') }}"
                class="flex flex-col md:flex-row gap-4 md:items-center">
                <div class="relative w-full md:w-1/3">
                    <select id="user_select" name="user_id"
                        class="appearance-none px-4 py-3 pr-10 rounded-lg bg-white text-gray-700
        border border-[rgba(233,29,40,1)]
        focus:ring-2 focus:ring-[rgba(233,29,40,1)]
        focus:border-[rgba(233,29,40,1)]">
                        <option value="">Tous les conducteurs</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->firstname }} {{ $user->lastname }}
                            </option>
                        @endforeach
                    </select>

                    <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[rgb(233,29,40)]" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </span>
                </div>

                <div class="relative  md:w-1/3" style="width: 170px;">
                    <select name="status"
                        class="appearance-none px-4 py-3 pr-10 rounded-lg bg-white text-gray-700
        border border-[rgba(233,29,40,1)]
        focus:ring-2 focus:ring-[rgba(233,29,40,1)]
        focus:border-[rgba(233,29,40,1)]">
                        <option value="">Tous les statuts</option>
                        <option value="actif" {{ request('status') === 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ request('status') === 'inactif' ? 'selected' : '' }}>Inactif</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    </select>

                    <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[rgb(233,29,40)]" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </span>
                </div>


                <button class="px-6 py-3 bg-[rgb(233,29,40)] text-white font-semibold rounded-lg">Filtrer</button>
                <a href="{{ route('admin.rides.index') }}"
                    class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg">Réinitialiser</a>
            </form>
        </div>

        <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
            <div class="p-6 border-b">
                <h2 class="text-lg font-semibold text-gray-800">Liste des trajets</h2>
                <p class="text-sm text-gray-500 mt-1">Tous les trajets publiés par les conducteurs</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Conducteur</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Départ → Destination</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Date départ</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Statut</th>
                            <th class="px-6 py-3 text-right font-semibold text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($rides as $ride)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">{{ $ride->conducteur?->firstname ?? '—' }}
                                    {{ $ride->conducteur?->lastname ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $ride->depart }} → {{ $ride->destination }}</td>
                                <td class="px-6 py-4">{{ $ride->date_depart }} {{ $ride->heure_depart }}</td>
                                <td class="px-6 py-4">
                                    @if ($ride->statut === 'actif')
                                        <span
                                            class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Actif</span>
                                    @elseif($ride->statut === 'inactif')
                                        <span
                                            class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">Inactif</span>
                                    @else
                                        <span
                                            class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">En
                                            attente</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('admin.rides.toggle-status', $ride) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-xs font-medium rounded-xl hover:bg-blue-700 transition">
                                            @if ($ride->statut === 'actif')
                                                Désactiver
                                            @else
                                                Activer
                                            @endif
                                        </button>
                                    </form>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-gray-500">Aucun trajet trouvé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t">
                {{ $rides->links() }}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new TomSelect('#user_select', {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        });
    </script>

@endsection
