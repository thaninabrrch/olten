@extends('admin.layouts.app')

@section('title', 'Gestion des utilisateurs')
@section('page_title', 'Liste des utilisateurs')

@section('content')

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Liste des utilisateurs</h1>
        <a href="{{ route('admin.users.create') }}" class="btn-red">
            Ajouter un utilisateur
        </a>
    </div>

    {{-- BARRE RECHERCHE --}}
    <div class="card-white p-6 mb-8">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col md:flex-row gap-4 md:items-center">

            <div class="relative w-full md:w-1/3">
                <input name="search" value="{{ request('search') }}" type="text"
                    placeholder="Rechercher par nom ou email..."
                    class="w-full pl-10 pr-4 py-3 rounded-lg bg-white text-gray-700
                   border border-[rgba(255,187,191,1)]
                   focus:ring-2 focus:ring-[rgba(255,187,191,1)]
                   focus:border-[rgba(255,187,191,1)]">
                <span class="absolute left-3 top-1/2 -translate-y-1/2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[rgb(233,29,40)]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z" />
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
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13v6l-4 2 1-5L3.293 6.707A1 1 0 013 6V4z" />
                </svg>

                Rechercher
            </button>
        </form>
    </div>

    {{-- TABLEAU --}}
    <div class="card-white p-4">
        <div class="table-wrapper">
            <table class="min-w-full table-rounded divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left">Nom</th>
                        <th class="px-6 py-3 text-left">Email</th>
                        <th class="px-6 py-3 text-left">Rôle</th>
                        <th class="px-6 py-3 text-left">Etat</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-6 py-4 font-semibold">{{ $user->firstname }} {{ $user->lastname }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $roleColors = [
                                        'admin' => ['text' => 'text-red-600', 'bg' => 'bg-red-100'],

                                        'particulier' => ['text' => 'text-gray-800', 'bg' => 'bg-gray-200'],

                                        'livreur' => ['text' => 'text-blue-600', 'bg' => 'bg-blue-100'],

                                        'conducteur' => ['text' => 'text-green-600', 'bg' => 'bg-green-100'],

                                        'locateur' => ['text' => 'text-purple-600', 'bg' => 'bg-purple-100'],

                                        'vendeur' => ['text' => 'text-yellow-600', 'bg' => 'bg-yellow-100'],
                                    ];
                                @endphp

                                <div class="flex flex-wrap gap-2">
                                    @foreach ($user->roles as $role)
                                        @php
                                            $colors = $roleColors[$role->name] ?? [
                                                'text' => 'text-gray-800',
                                                'bg' => 'bg-gray-200',
                                            ];
                                        @endphp

                                        <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $colors['text'] }} {{ $colors['bg'] }}">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                @if($user->hasRole('admin'))
                                    <span class="text-gray-500">-</span>
                                @elseif ($user->is_approved)
                                    <span
                                        class="inline-block px-3 py-1 text-xs font-semibold text-green-600 bg-green-100 rounded-full">
                                        Approuver
                                    </span>
                                @else
                                    <span
                                        class="inline-block px-3 py-1 text-xs font-semibold text-gray-800 bg-gray-200 rounded-full">
                                        Non Approuver
                                    </span>
                                @endif
                            </td>

                            {{-- ACTIONS --}}
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
                                        @if (!$user->hasRole('admin') && !$user->is_approved)
                                            <form action="{{ route('admin.users.verify', $user) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="w-full flex items-center text-left px-4 py-3 text-green-600 hover:bg-gray-100">
                                                    Approuver
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                            class="flex items-center px-4 py-3 text-sm text-blue-600 hover:bg-gray-100">
                                            Modifier
                                        </a>

                                        <a href="{{ route('admin.users.show', $user) }}"
                                            class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100">
                                            Voir
                                        </a>
                                        @if ($user->role !== 'admin')
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                                class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    class="delete-btn w-full flex items-center text-left px-4 py-3 text-red-600 hover:bg-gray-100">
                                                    Supprimer
                                                </button>
                                            </form>
                                        @endif


                                    </div>
                                </div>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $users->links() }}
    </div>

    {{-- SweetAlert + dropdown JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
