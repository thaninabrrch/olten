@extends('admin.layouts.app')

@section('title', 'Annonces')
@section('page_title', 'Gestion des annonces')

@section('content')

    <div class="page-inner">

        <div class="flex flex-col md:flex-row justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">
                Liste des annonces

            </h1>


        </div>
        <div class="card-white p-6 mb-8">

            <form method="GET" action="{{ route('admin.admin.ads.index') }}"
                class="flex flex-col md:flex-row gap-4 md:items-center">

                <div class="relative w-full md:w-1/3">
                    <select id="user_select" name="user_id"
                        class="appearance-none px-4 py-3 pr-10 rounded-lg bg-white text-gray-700
        border border-[rgba(255,187,191,1)]
        focus:ring-2 focus:ring-[rgba(255,187,191,1)]
        focus:border-[rgba(255,187,191,1)]">

                        <option value="">Tous les utilisateurs</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
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





                <div class="relative">
                    <select name="status"
                        class="appearance-none px-4 py-3 pr-10 rounded-lg bg-white text-gray-700
                    border border-[rgba(255,187,191,1)]
                    focus:ring-2 focus:ring-[rgba(255,187,191,1)]
                    focus:border-[rgba(255,187,191,1)]">

                        <option value="">Tous les statuts</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvée</option>
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
                <div class="relative">
                    <select name="category_id"
                        class="appearance-none px-4 py-3 pr-10 rounded-lg bg-white text-gray-700
        border border-[rgba(255,187,191,1)]
        focus:ring-2 focus:ring-[rgba(255,187,191,1)]
        focus:border-[rgba(255,187,191,1)]">

                        <option value="">Toutes les catégories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->nom }}
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

                <button
                    class="px-6 py-3 bg-[rgb(233,29,40)] text-white font-semibold rounded-lg
                shadow-md hover:bg-red-700 hover:shadow-lg active:scale-95
                transition-all duration-200 flex items-center gap-2">

                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707l-6.414 6.414V19a1 1 0 01-.553.894l-4 2A1 1 0 019 21v-7.879L2.293 6.707A1 1 0 012 6V4z" />
                    </svg>

                    Filtrer
                </button>

                {{-- Bouton Réinitialiser --}}
                <a href="{{ route('admin.admin.ads.index') }}"
                    class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg
                shadow-md hover:bg-gray-300 hover:shadow-lg active:scale-95
                transition-all duration-200 flex items-center gap-2">
                    Réinitialiser
                </a>

            </form>
        </div>


        <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">

            <div class="p-6 border-b">
                <h2 class="text-lg font-semibold text-gray-800">Liste des annonces</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Toutes les annonces publiées par les utilisateurs
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Titre</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Utilisateur</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Catégorie</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Statut</th>
                            <th class="px-6 py-3 text-right font-semibold text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($ads as $ad)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $ad->title }}
                                </td>

                                <td class="px-6 py-4 text-gray-600">
                                    {{ $ad->user?->name ?? '—' }}
                                </td>

                                <td class="px-6 py-4 text-gray-600">
                                    {{ $ad->category->nom ?? '—' }}
                                </td>

                                <td class="px-6 py-4">
                                    @if ($ad->is_approved)
                                        <span
                                            class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                            Approuvée
                                        </span>
                                    @elseif ($ad->rejected_at)
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                                            Refusée
                                        </span>
                                    @else
                                        <span
                                            class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">
                                            En attente
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex gap-2 justify-end">

                                        @if (!$ad->is_approved)
                                            <form id="approve-form-{{ $ad->id }}"
                                                action="{{ route('admin.ads.approve', $ad) }}" method="POST"
                                                class="hidden">
                                                @csrf @method('PATCH')
                                            </form>
                                            <button type="button" onclick="confirmAction('approve', {{ $ad->id }})"
                                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white
                       text-xs font-medium rounded-xl hover:bg-green-700 transition">
                                                <i class="bi bi-check-circle mr-1"></i> Approuver
                                            </button>
                                        @endif

                                        @if (!($ad->rejected_at))
                                            <form id="reject-form-{{ $ad->id }}"
                                                action="{{ route('admin.ads.reject', $ad) }}" method="POST"
                                                class="hidden">
                                                @csrf @method('PATCH')
                                            </form>
                                            <button type="button" onclick="confirmAction('reject', {{ $ad->id }})"
                                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white
                       text-xs font-medium rounded-xl hover:bg-red-700 transition">
                                                <i class="bi bi-x-circle mr-1"></i> Refuser
                                            </button>
                                        @endif

                                        @if ($ad->is_approved && $ad->rejected_at)
                                            <span class="text-xs text-gray-400 italic">Aucune action</span>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-gray-500">
                                    Aucune annonce trouvée
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t">
                {{ $ads->links() }}
            </div>

        </div>
    </div>
    {{-- SweetAlert2 CDN (si pas déjà dans le layout) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function confirmAction(type, adId) {
            const config = {
                approve: {
                    title: 'Approuver cette annonce ?',
                    text: 'L\'annonce sera visible par les utilisateurs.',
                    icon: 'question',
                    confirmButtonText: 'Oui, approuver',
                    confirmButtonColor: '#16a34a', // green-600
                    cancelButtonText: 'Annuler',
                },
                reject: {
                    title: 'Refuser cette annonce ?',
                    text: 'L\'annonce sera marquée comme refusée.',
                    icon: 'warning',
                    confirmButtonText: 'Oui, refuser',
                    confirmButtonColor: '#dc2626', // red-600
                    cancelButtonText: 'Annuler',
                }
            };

            const {
                title,
                text,
                icon,
                confirmButtonText,
                confirmButtonColor,
                cancelButtonText
            } = config[type];

            Swal.fire({
                title,
                text,
                icon,
                showCancelButton: true,
                confirmButtonColor,
                cancelButtonColor: '#6b7280', // gray-500
                confirmButtonText,
                cancelButtonText,
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`${type}-form-${adId}`).submit();
                }
            });
        }
    </script>
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
