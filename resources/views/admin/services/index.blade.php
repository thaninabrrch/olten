@extends('admin.layouts.app')

@section('title', 'Gestion des Services')
@section('page_title', 'Gestion des services')

@section('content')

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">

            Services
        </h1>

        <a href="{{ route('admin.services.create') }}" class="btn-red">
            Nouveau service
        </a>
    </div>


    {{-- BARRE RECHERCHE & FILTRES --}}
    <div class="card-white p-6 mb-8">
        <form method="GET" action="{{ route('admin.services.index') }}"
              class="flex flex-col md:flex-row gap-4 md:items-center">

            {{-- Recherche (nom ou slug) --}}
            <div class="relative w-full md:w-1/3">
                <input name="search" value="{{ request('search') }}" type="text"
                       placeholder="Rechercher un nom ou un slug..."
                       class="w-full pl-10 pr-4 py-3 rounded-lg bg-white text-gray-700
                       border border-[rgba(255,187,191,1)]
                       focus:ring-2 focus:ring-[rgba(255,187,191,1)]
                       focus:border-[rgba(255,187,191,1)]">

                <span class="absolute left-3 top-1/2 -translate-y-1/2">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-5 h-5 text-[rgb(233,29,40)]"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
            </div>

            {{-- Bouton Filtrer --}}
            <button
                class="px-6 py-3 bg-[rgb(233,29,40)] text-white font-semibold rounded-lg
                shadow-md hover:bg-red-700 hover:shadow-lg active:scale-95
                transition-all duration-200 flex items-center gap-2">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5 text-white"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13v6l-4 2 1-5L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>

                Filtrer
            </button>

            @if(request('search'))
                <a href="{{ route('admin.services.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 underline">
                    Réinitialiser
                </a>
            @endif

        </form>
    </div>


    {{-- TABLEAU --}}
    <div class="card-white p-4">
        <div class="table-wrapper">
            <table class="min-w-full table-rounded divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left">Nom</th>
                        <th class="px-6 py-3 text-left">Slug</th>
                        <th class="px-6 py-3 text-left">Catégories</th>
                        <th class="px-6 py-3 text-left">Image</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse($services as $service)
                        <tr>
                            <td class="px-6 py-4 font-semibold">{{ $service->nom }}</td>

                            {{-- Le slug identifie le service côté front --}}
                            <td class="px-6 py-4">
                                <code class="px-2 py-1 rounded bg-gray-100 text-gray-700 text-sm">
                                    /{{ $service->slug }}
                                </code>
                            </td>

                            <td class="px-6 py-4 text-gray-600">
                                <a href="{{ route('admin.categories.index', ['service_id' => $service->id]) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-full
                                          bg-red-50 text-red-600 text-sm font-medium hover:bg-red-100">
                                    {{ $service->categories_count }}
                                    {{ \Illuminate\Support\Str::plural('catégorie', $service->categories_count) }}
                                </a>
                            </td>

                            <td class="px-6 py-4">
                                @if($service->image)
                                    <img src="{{ asset('storage/' . $service->image) }}"
                                         class="w-16 h-16 rounded object-cover">
                                @else
                                    <span class="text-gray-400 italic">Aucune image</span>
                                @endif
                            </td>

                            {{-- ACTIONS --}}
                            <td class="px-6 py-4 text-right">
                                <div class="relative inline-block">

                                    {{-- bouton --}}
                                    <button
                                        class="action-btn px-2 py-2 rounded-full border bg-white hover:bg-gray-100 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="w-5 h-5 text-gray-700"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 6h.01M12 12h.01M12 18h.01"/>
                                        </svg>
                                    </button>

                                    {{-- menu --}}
                                    <div class="dropdown-menu-white absolute right-9 w-44 divide-y divide-gray-200">

                                        <a href="{{ route('admin.services.edit', $service) }}"
                                           class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 class="w-4 h-4 mr-2 text-red-500"
                                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 4H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2v-4M18.5 2.5a2.121 2.121 0 113 3L13 14l-4 1 1-4 8.5-8.5z"/>
                                            </svg>
                                            Modifier
                                        </a>

                                        <form action="{{ route('admin.services.destroy', $service) }}"
                                              method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')

                                            <button type="button"
                                                    class="delete-btn w-full flex items-center text-left px-4 py-3 text-sm text-red-600 hover:bg-gray-100">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="w-4 h-4 mr-2 text-red-600"
                                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-9 0h10"/>
                                                </svg>
                                                Supprimer
                                            </button>
                                        </form>

                                    </div>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400 italic">
                                Aucun service trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $services->links() }}
    </div>


    {{-- SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {

            // Dropdown
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

            // SweetAlert suppression
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const form = this.closest('.delete-form');

                    Swal.fire({
                        title: 'Supprimer ?',
                        text: "Cette action est irréversible !",
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Oui, supprimer',
                        cancelButtonText: 'Annuler'
                    }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });

        });
    </script>

@endsection
