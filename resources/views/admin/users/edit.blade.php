@extends('admin.layouts.app')

@section('title', 'Modifier un Utilisateur')

@section('content')
    <div class="page-inner">

        {{-- Header --}}
        <div class="pb-3 mb-6 border-b flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Utilisateurs</h1>
                <ul class="flex items-center text-sm text-gray-500 mt-1 space-x-2">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="text-red-600 hover:underline">
                            <i class="bi bi-house"></i>
                        </a>
                    </li>
                    <li><i class="bi bi-chevron-right text-xs"></i></li>
                    <li>Gestion</li>
                    <li><i class="bi bi-chevron-right text-xs"></i></li>
                    <li>Utilisateurs</li>
                    <li><i class="bi bi-chevron-right text-xs"></i></li>
                    <li class="text-red-600 font-semibold">Modifier</li>
                </ul>
            </div>
        </div>

        {{-- Formulaire --}}
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data"
            class="mb-8 bg-white p-6 rounded-lg shadow">
            @csrf
            @method('PATCH')

            {{-- Nom / Prénom / Email --}}
            <div class="flex flex-col md:flex-row gap-4 mb-4">
                <div class="flex-1">
                    <label for="firstname" class="block text-gray-700 font-medium mb-1">Prénom *</label>
                    <input type="text" name="firstname" id="firstname"
                        class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500"
                        value="{{ old('firstname', $user->firstname) }}">
                </div>
                <div class="flex-1">
                    <label for="lastname" class="block text-gray-700 font-medium mb-1">Nom *</label>
                    <input type="text" name="lastname" id="lastname"
                        class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500"
                        value="{{ old('lastname', $user->lastname) }}">
                </div>
                <div class="flex-1">
                    <label for="email" class="block text-gray-700 font-medium mb-1">Email *</label>
                    <input type="email" name="email" id="email"
                        class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500"
                        value="{{ old('email', $user->email) }}">
                </div>
            </div>

            {{-- Mot de passe / Confirmation --}}
            <div class="flex flex-col md:flex-row gap-4 mb-4">
                <div class="flex-1">
                    <label for="password" class="block text-gray-700 font-medium mb-1">Mot de passe</label>
                    <input type="password" name="password" id="password"
                        class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <small class="text-gray-500">Laisser vide si vous ne souhaitez pas changer le mot de passe</small>
                </div>
                <div class="flex-1">
                    <label for="password_confirmation" class="block text-gray-700 font-medium mb-1">Confirmer le mot de
                        passe</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
            </div>

            {{-- Rôle --}}
            <div class="mb-4">
                <label for="role" class="block text-gray-700 font-medium mb-1">Rôle</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">

                    @foreach ($roles as $role)
                        <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">

                            <input type="checkbox"
                                name="roles[]"
                                value="{{ $role->name }}"
                                class="accent-red-500"
                                {{ $user->roles->pluck('name')->contains($role->name) ? 'checked' : '' }}>

                            <span class="text-sm font-medium">
                                {{ ucfirst($role->name) }}
                            </span>

                        </label>
                    @endforeach

                </div>
            </div>

            {{-- Téléphone / Genre / Photo --}}
            <div class="flex flex-col md:flex-row gap-4 mb-4">
                <div class="flex-1">
                    <label for="phone" class="block text-gray-700 font-medium mb-1">Téléphone</label>
                    <input type="text" name="phone" id="phone"
                        class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500"
                        value="{{ old('phone', $user->telephone) }}">
                </div>
                <div class="flex-1">
                    <label for="gender" class="block text-gray-700 font-medium mb-1">Genre</label>
                    <select name="gender" id="gender"
                        class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Sélectionner --</option>
                        <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Homme</option>
                        <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Femme
                        </option>
                        <option value="other" {{ old('gender', $user->gender) == 'other' ? 'selected' : '' }}>Autre
                        </option>
                    </select>
                </div>
                <div class="flex-1">
                    <label for="profile_photo" class="block text-gray-700 font-medium mb-1">Photo de profil</label>
                    <input type="file" name="profile_photo" id="profile_photo"
                        class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                    @if ($user->profile_photo)
                        <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Photo profil"
                            class="mt-2 w-24 h-24 rounded-full">
                    @endif
                </div>
            </div>

            {{-- À propos --}}
            <div class="mb-4">
                <label for="about_me" class="block text-gray-700 font-medium mb-1">À propos</label>
                <textarea name="about_me" id="about_me" rows="3"
                    class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('about_me', $user->about_me) }}</textarea>
            </div>

            {{-- Réseaux sociaux --}}
            <div class="flex flex-col md:flex-row gap-4 mb-4">
                {{-- X_COM / Facebook / LinkedIn --}}
                <div class="flex-1">
                    <label for="x_com" class="block text-gray-700 font-medium mb-1">X_COM</label>
                    <input type="text" name="x_com" id="x_com"
                        class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500"
                        value="{{ old('x_com', $user->x_com) }}">
                </div>
                <div class="flex-1">
                    <label for="facebook" class="block text-gray-700 font-medium mb-1">Facebook</label>
                    <input type="text" name="facebook" id="facebook"
                        class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500"
                        value="{{ old('facebook', $user->facebook) }}">
                </div>
                <div class="flex-1">
                    <label for="linkedin" class="block text-gray-700 font-medium mb-1">LinkedIn</label>
                    <input type="text" name="linkedin" id="linkedin"
                        class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500"
                        value="{{ old('linkedin', $user->linkedin) }}">
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-4 mb-4">
                {{-- Instagram / YouTube / TikTok --}}
                <div class="flex-1">
                    <label for="instagram" class="block text-gray-700 font-medium mb-1">Instagram</label>
                    <input type="text" name="instagram" id="instagram"
                        class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500"
                        value="{{ old('instagram', $user->instagram) }}">
                </div>
                <div class="flex-1">
                    <label for="youtube" class="block text-gray-700 font-medium mb-1">YouTube</label>
                    <input type="text" name="youtube" id="youtube"
                        class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500"
                        value="{{ old('youtube', $user->youtube) }}">
                </div>
                <div class="flex-1">
                    <label for="tiktok" class="block text-gray-700 font-medium mb-1">TikTok</label>
                    <input type="text" name="tiktok" id="tiktok"
                        class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500"
                        value="{{ old('tiktok', $user->tiktok) }}">
                </div>
            </div>

            <div class="mb-4 flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label for="whatsapp" class="block text-gray-700 font-medium mb-1">WhatsApp</label>
                    <input type="text" name="whatsapp" id="whatsapp"
                        class="w-full border @error('whatsapp') border-red-500 @else border-gray-300 @enderror
                      rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500"
                        value="{{ old('whatsapp', $user->whatsapp) }}">
                    @error('whatsapp')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Boutons --}}
            <div class="flex items-center justify-between mt-8">
                <a href="{{ route('admin.users.index') }}"
                    class="text-gray-600 hover:text-gray-800 underline">Annuler</a>
                <button type="submit" class="ml-auto px-4 py-2 text-white rounded-2xl border transition"
                    style="background-color: #2c2c2c; border: 1px solid #2c2c2c;">
                    Modifier
                </button>
            </div>

        </form>
    </div>
@endsection
