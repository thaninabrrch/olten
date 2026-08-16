@extends('admin.layouts.app')

@section('title', 'Détails de l\'utilisateur')
@section('page_title', 'Détails de l\'utilisateur')

@section('content')

    <div class="page-inner">

        {{-- HEADER / Breadcrumb --}}
        <div class="pb-3 mb-6 border-b flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Utilisateur</h1>
                <ul class="flex items-center text-sm text-gray-500 mt-1 space-x-2">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="text-red-600 hover:underline">
                            <i class="bi bi-house"></i>
                        </a>
                    </li>
                    <li><i class="bi bi-chevron-right text-xs"></i></li>
                    <li>
                        <a href="{{ route('admin.users.index') }}" class="hover:underline">
                            Utilisateurs
                        </a>
                    </li>
                    <li><i class="bi bi-chevron-right text-xs"></i></li>
                    <li class="text-red-600 font-semibold">Détails</li>
                </ul>
            </div>
        </div>

        {{-- Card utilisateur --}}
        <div class="max-w-5xl mx-auto bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100 relative">

            {{-- Bouton Supprimer si ce n'est pas un admin --}}
            @if ($user->role !== 'admin')
                <form id="delete-form" action="{{ route('admin.users.destroy', $user) }}" method="POST"
                    class="delete-form absolute top-5 right-5 z-10">
                    @csrf
                    @method('DELETE')
                    <button type="button"
                        class="w-10 h-10 flex items-center justify-center bg-red-600 text-white rounded-full
                shadow-md hover:bg-red-700 hover:scale-105 active:scale-95 transition-all duration-200 delete-btn"
                        title="Supprimer cet utilisateur">
                        <i class="bi bi-trash-fill text-lg"></i>
                    </button>
                </form>
            @endif

            {{-- Header de la card --}}
            <div class="bg-gradient-to-r from-red-600 to-pink-500 p-6">
                <h2 class="text-2xl font-bold text-white">Détails de l'utilisateur</h2>
                <p class="text-sm text-white/80 mt-1 opacity-90">
                    Informations de l'utilisateur {{ $user->firstname }} {{ $user->lastname }}
                </p>
            </div>

            {{-- Contenu utilisateur --}}
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Nom --}}
                    <div class="flex items-center gap-3">
                        <i class="bi bi-person text-gray-400"></i>
                        <div>
                            <h3 class="text-gray-400 font-semibold text-xs uppercase mb-1 tracking-wider">Nom</h3>
                            <p class="text-gray-900 text-lg font-medium">{{ $user->firstname }} {{ $user->lastname }}</p>
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="flex items-center gap-3">
                        <i class="bi bi-envelope text-gray-400"></i>
                        <div>
                            <h3 class="text-gray-400 font-semibold text-xs uppercase mb-1 tracking-wider">Email</h3>
                            <p class="text-gray-900 text-lg font-medium">
                                <a href="mailto:{{ $user->email }}" class="hover:text-red-600 transition duration-150">
                                    {{ $user->email }}
                                </a>
                            </p>
                        </div>
                    </div>

                    {{-- Téléphone --}}
                    @if ($user->telephone)
                        <div class="flex items-center gap-3">
                            <i class="bi bi-telephone text-gray-400"></i>
                            <div>
                                <h3 class="text-gray-400 font-semibold text-xs uppercase mb-1 tracking-wider">Téléphone</h3>
                                <p class="text-gray-900 text-lg font-medium">{{ $user->telephone }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Genre --}}
                    @if ($user->gender)
                        <div class="flex items-center gap-3">
                            <i class="bi bi-gender-ambiguous text-gray-400"></i>
                            <div>
                                <h3 class="text-gray-400 font-semibold text-xs uppercase mb-1 tracking-wider">Genre</h3>
                                <p class="text-gray-900 text-lg font-medium">{{ ucfirst($user->gender) }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Rôle --}}
                    <div class="flex items-center gap-3">
                        <i class="bi bi-shield-lock text-gray-400"></i>
                        <div>
                            <h3 class="text-gray-400 font-semibold text-xs uppercase mb-1 tracking-wider">Rôle</h3>
                            <p class="text-lg">
                                @php
                                    $roleColors = [
                                        'admin' => ['text' => 'text-red-600', 'bg' => 'bg-red-100'],

                                        'particulier' => ['text' => 'text-gray-800', 'bg' => 'bg-gray-200'],

                                        'livreur' => ['text' => 'text-blue-600', 'bg' => 'bg-blue-100'],

                                        'conducteur' => ['text' => 'text-green-600', 'bg' => 'bg-green-100'],

                                        'locateur' => ['text' => 'text-purple-600', 'bg' => 'bg-purple-100'],

                                        'vendeur' => ['text' => 'text-yellow-600', 'bg' => 'bg-yellow-100'],
                                    ];

                                    $colors = $roleColors[$user->role] ?? [
                                        'text' => 'text-gray-800',
                                        'bg' => 'bg-gray-200',
                                    ];
                                @endphp
                                @foreach ($user->roles as $role)
                                    @php
                                        $colors = $roleColors[$role->name] ?? [
                                            'text' => 'text-gray-800',
                                            'bg' => 'bg-gray-200',
                                        ];
                                    @endphp

                                    <span class="inline-block px-3 py-1 text-xs font-semibold {{ $colors['text'] }} {{ $colors['bg'] }} rounded-full">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            </p>
                        </div>
                    </div>

                    {{-- Réseaux sociaux --}}
                    @php
                        $socials = [
                            'x_com' => ['icon' => 'bi bi-x', 'url' => $user->x_com],
                            'tiktok' => ['icon' => 'bi bi-tiktok', 'url' => $user->tiktok],
                            'whatsapp' => ['icon' => 'bi bi-whatsapp', 'url' => $user->whatsapp],
                        ];
                    @endphp

                    @foreach ($socials as $key => $data)
                        @if ($data['url'])
                            <div class="flex items-center gap-3">
                                <i class="{{ $data['icon'] }} text-gray-400"></i>
                                <div>
                                    <h3 class="text-gray-400 font-semibold text-xs uppercase mb-1 tracking-wider">
                                        {{ ucfirst($key) }}</h3>
                                    <p class="text-gray-900 text-lg font-medium">
                                        <a href="{{ $data['url'] }}" target="_blank"
                                            class="hover:text-red-600 transition duration-150">
                                            {{ $data['url'] }}
                                        </a>
                                    </p>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    {{-- À propos --}}
                    @if ($user->about_me)
                        <div class="md:col-span-2 flex items-start gap-3">
                            <i class="bi bi-info-circle text-gray-400 mt-1"></i>
                            <div>
                                <h3 class="text-gray-400 font-semibold text-xs uppercase mb-1 tracking-wider">À propos</h3>
                                <p class="text-gray-900 text-lg font-medium">{{ $user->about_me }}</p>
                            </div>
                        </div>
                    @endif
                    {{-- Réseaux sociaux en bas à droite --}}


                    {{-- Date de création --}}
                    <div class="md:col-span-2 pt-4 border-t border-gray-100">
                        <div class="absolute bottom-3 right-5 flex items-right gap-4">
                            @php
                                $socials = [
                                    'facebook' => ['icon' => 'bi bi-facebook', 'url' => $user->facebook],
                                    'linkedin' => ['icon' => 'bi bi-linkedin', 'url' => $user->linkedin],
                                    'instagram' => ['icon' => 'bi bi-instagram', 'url' => $user->instagram],
                                    'youtube' => ['icon' => 'bi bi-youtube', 'url' => $user->youtube],
                                    'whatsapp' => ['icon' => 'bi bi-whatsapp', 'url' => $user->whatsapp],
                                ];
                            @endphp

                            @foreach ($socials as $data)
                                @if ($data['url'])
                                    <a href="{{ $data['url'] }}" target="_blank"
                                        class="text-gray-500 hover:text-red-600 transition duration-150 text-xl">
                                        <i class="{{ $data['icon'] }}"></i>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- SweetAlert JS pour suppression --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const form = this.closest('.delete-form');

                    Swal.fire({
                        title: 'Supprimer cet utilisateur ?',
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
