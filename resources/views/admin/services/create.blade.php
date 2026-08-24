@extends('admin.layouts.app')

@section('title', 'Ajouter un Service')

@section('content')

<div class="page-inner">

    {{-- HEADER --}}
    <div class="pb-3 mb-6 border-b flex flex-col md:flex-row md:items-center md:justify-between">

        <div>
            <h1 class="text-xl font-bold text-gray-800">Services</h1>

            <ul class="flex items-center text-sm text-gray-500 mt-1 space-x-2">
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="text-red-600 hover:underline">
                        <i class="bi bi-house"></i>
                    </a>
                </li>

                <li><i class="bi bi-chevron-right text-xs"></i></li>
                <li>Gestion</li>

                <li><i class="bi bi-chevron-right text-xs"></i></li>
                <li>Services</li>

                <li><i class="bi bi-chevron-right text-xs"></i></li>
                <li class="text-red-600 font-semibold">Ajouter</li>
            </ul>
        </div>

    </div>

    {{-- FORMULAIRE --}}
    <form action="{{ route('admin.services.store') }}" method="POST" enctype="multipart/form-data"
          class="mb-8 bg-white p-6 rounded-lg shadow">

        @csrf

        {{-- Nom --}}
        <div class="mb-4">
            <label for="nom" class="block text-gray-700 font-medium mb-1">Nom du service *</label>

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
            <label for="slug" class="block text-gray-700 font-medium mb-1">Slug du service *</label>

            <input type="text" name="slug" id="slug"
                class="w-full border @error('slug') border-red-500 @else border-gray-300 @enderror
                       rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500"
                value="{{ old('slug', '') }}" placeholder="ex : vente">

            <p class="text-gray-500 text-sm mt-1">
                Identifiant unique du service dans l'URL : <code>/mon-slug</code>.
                C'est lui qui détermine le design affiché côté front.
                Laisser vide pour le générer automatiquement depuis le nom.
            </p>

            @error('slug')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Accroche courte --}}
        <div class="mb-4">
            <label for="short_description" class="block text-gray-700 font-medium mb-1">Accroche courte</label>

            <input type="text" name="short_description" id="short_description" maxlength="120"
                class="w-full border @error('short_description') border-red-500 @else border-gray-300 @enderror
                       rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500"
                value="{{ old('short_description') }}" placeholder="ex : Objets & matériel">

            <p class="text-gray-500 text-sm mt-1">
                Affichée sous le nom du service sur les tuiles de l'accueil. 120 caractères maximum.
            </p>

            @error('short_description')
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

        {{-- Image --}}
        <div class="mb-6">
            <label for="image" class="block text-gray-700 font-medium mb-1">Image</label>

            <input type="file" name="image" id="image"
                class="w-full border @error('image') border-red-500 @else border-gray-300 @enderror
                       rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-red-500">

            @error('image')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Boutons --}}
        <div class="flex items-center justify-between">

            {{-- Annuler --}}
            <a href="{{ route('admin.services.index') }}" class="text-gray-600 hover:text-gray-800 underline">
                Annuler
            </a>

            {{-- Enregistrer --}}
            <button type="submit" class="ml-auto px-4 py-2 text-white rounded-2xl border transition"
                style="background-color: #2c2c2c; border: 1px solid #2c2c2c;">
                Ajouter
            </button>

        </div>

    </form>

</div>

{{-- Génération automatique du slug depuis le nom --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const nom  = document.getElementById('nom');
        const slug = document.getElementById('slug');

        if (!nom || !slug) return;

        const slugify = (value) => value
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');

        // On ne remplace jamais un slug saisi manuellement
        let touched = slug.value.trim() !== '';

        slug.addEventListener('input', () => { touched = true; });

        nom.addEventListener('input', () => {
            if (!touched) slug.value = slugify(nom.value);
        });
    });
</script>

@endsection
