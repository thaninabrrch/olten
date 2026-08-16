@extends('admin.layouts.app')

@section('title', 'Ajouter une Catégorie')

@section('content')

    <div class="page-inner">

        {{-- HEADER --}}
        <div class="pb-3 mb-6 border-b flex flex-col md:flex-row md:items-center md:justify-between">

            <div>
                <h1 class="text-xl font-bold text-gray-800">Catégories</h1>

                <ul class="flex items-center text-sm text-gray-500 mt-1 space-x-2">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="text-red-600 hover:underline">
                            <i class="bi bi-house"></i>
                        </a>
                    </li>

                    <li><i class="bi bi-chevron-right text-xs"></i></li>
                    <li>Gestion</li>

                    <li><i class="bi bi-chevron-right text-xs"></i></li>
                    <li>Catégories</li>

                    <li><i class="bi bi-chevron-right text-xs"></i></li>
                    <li class="text-red-600 font-semibold">Ajouter</li>
                </ul>
            </div>

        </div>
        {{-- Formulaire --}}
        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data"
            class="mb-8 bg-white p-6 rounded-lg shadow">

            @csrf

            {{-- Nom --}}
            <div class="mb-4">
                <label for="nom" class="block text-gray-700 font-medium mb-1">Nom de la catégorie *</label>

                <input type="text" name="nom" id="nom"
                    class="w-full border @error('nom') border-red-500 @else border-gray-300 @enderror
                       rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500"
                    value="{{ old('nom') }}">

                @error('nom')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Slug --}}
            <div class="mb-4">
                <label for="nom" class="block text-gray-700 font-medium mb-1">Slug de la catégorie *</label>

                <input type="text" name="slug" id="slug"
                    class="w-full border @error('slug') border-red-500 @else border-gray-300 @enderror
                       rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500"
                    value="{{ old('slug') }}">

                @error('slug')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            {{-- Description --}}
            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-medium mb-1">Description</label>

                <textarea name="description" id="description" rows="3"
                    class="w-full border @error('description') border-red-500 @else border-gray-300 @enderror
                       rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('description') }}</textarea>

                @error('description')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Icone --}}
            <div class="mb-6">
                @php
                    $icons = [
                        'bi bi-key-fill'            => 'Location',
                        'bi bi-house-door-fill'      => 'Immobilier',
                        'bi bi-car-front-fill'       => 'Véhicules',
                        'bi bi-bicycle'              => 'Vélos',
                        'bi bi-phone-fill'           => 'Téléphones',
                        'bi bi-laptop-fill'          => 'Informatique',
                        'bi bi-tv-fill'              => 'Électronique',
                        'bi bi-controller'           => 'Jeux vidéo',
                        'bi bi-camera-fill'          => 'Photo & Vidéo',
                        'bi bi-music-note-beamed'    => 'Musique',
                        'bi bi-book-fill'            => 'Livres',
                        'bi bi-tools'                => 'Bricolage',
                        'bi bi-hammer'               => 'Matériel professionnel',
                        'bi bi-flower1'              => 'Jardin',
                        'bi bi-basket-fill'          => 'Sport',
                        'bi bi-heart-pulse-fill'     => 'Santé & Bien-être',
                        'bi bi-scissors'             => 'Beauté',
                        'bi bi-bag-fill'             => 'Accessoires',
                        'bi bi-cup-hot-fill'         => 'Restauration',
                        'bi bi-egg-fried'            => 'Alimentation',
                        'bi bi-box-seam-fill'        => 'Colis',
                        'bi bi-truck'                => 'Livraison',
                        'bi bi-geo-alt-fill'         => 'Livraison locale',
                        'bi bi-person-walking'       => 'Services à domicile',
                        'bi bi-people-fill'          => 'Services',
                        'bi bi-briefcase-fill'       => 'Prestations professionnelles',
                        'bi bi-person-workspace'     => 'Freelance',
                        'bi bi-globe2'               => 'Création de site web',
                        'bi bi-code-slash'           => 'Développement',
                        'bi bi-palette-fill'         => 'Design',
                        'bi bi-megaphone-fill'       => 'Marketing',
                        'bi bi-taxi-front-fill'      => 'VTC',
                        'bi bi-sign-turn-right-fill' => 'Covoiturage',
                        'bi bi-airplane-fill'        => 'Voyages',
                        'bi bi-building-fill'        => 'Entreprises',
                        'bi bi-shop'                 => 'Commerces',
                        'bi bi-cart-fill'            => 'Vente',
                        'bi bi-calendar-event-fill'  => 'Événements',
                    ];
                @endphp
                <label for="description" class="block text-gray-700 font-medium mb-1">Choisir une icone</label>
                <div class="grid grid-cols-5 gap-4">
                    @foreach($icons as $icon => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="icon" value="{{ $icon }}" class="hidden peer" {{ old('icon') == $icon ? 'checked' : '' }}>
                            <div class="border rounded-xl p-4 text-center transition hover:border-orange-500 hover:bg-orange-50 peer-checked:border-orange-500 peer-checked:bg-orange-100">
                                <i class="{{ $icon }} text-3xl text-orange-500"></i>
                                <div class="mt-2 text-sm text-gray-700">
                                    {{ $label }}
                                </div>
                            </div>
                        </label>
                    @endforeach
                    
                </div>
            </div>

            <div class="mb-4">
                <label for="image" class="block text-gray-700 font-medium mb-1">Image </label>

                <input type="file" name="image" id="image"
                    class="w-full border @error('image') border-red-500 @else border-gray-300 @enderror
                       rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-red-500">

                @error('image')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                @error('icon')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="service_id" class="block text-gray-700 font-medium mb-2">Service</label>
                <select name="service_id" id="service_id" class="w-full border px-3 py-2 rounded">
                    <option value="">-- Choisir un service --</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}"
                            {{ old('service_id', $category->service_id ?? '') == $service->id ? 'selected' : '' }}>
                            {{ $service->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Boutons --}}
            <div class="flex items-center justify-between">

                {{-- Annuler (à gauche) --}}
                <a href="{{ route('admin.categories.index') }}" class="text-gray-600 hover:text-gray-800 underline">
                    Annuler
                </a>

                {{-- Ajouter (à droite) --}}
                <button type="submit" class="ml-auto px-4 py-2 text-white rounded-2xl border transition"
                    style="background-color: #2c2c2c; border: 1px solid #2c2c2c;">
                    Ajouter
                </button>

            </div>

        </form>
    </div>

@endsection
