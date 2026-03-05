@extends('layouts.connected')
@section('title', 'Mon Profil')
<style>
    :root {
        --brand-orange: #ff3c00;
        --brand-orange-soft: rgba(255, 60, 0, 0.1);
    }

    .bg-brand {
        background-color: var(--brand-orange);
    }

    .text-brand {
        color: var(--brand-orange);
    }

    .border-brand {
        border-color: var(--brand-orange);
    }

    .focus-ring-brand:focus {
        --tw-ring-color: var(--brand-orange);
    }

    /* Custom Toggle Switch */
    .switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e5e7eb;
        transition: .4s;
        border-radius: 24px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    input:checked+.slider {
        background-color: var(--brand-orange);
    }

    input:checked+.slider:before {
        transform: translateX(20px);
    }
</style>
@section('content')
    @php
        $fullName = Auth::user()->name;
        $parts = explode(' ', $fullName, 2);

        $prenom = $parts[0];
        $nom = $parts[1] ?? '';
    @endphp
    <div class="breadcrumb">
        <a href="#">Accueil</a>
        <span>></span>
        <span>Mon Profil</span>
    </div>

    <h1 class="page-title">Mon Profil</h1>

    <style>
        /* Animation fluide pour le cercle du toggle */
        #vtc-toggle:checked~.dot {
            transform: translateX(100%);
            background-color: #ffffff;
        }

        /* Changement de couleur de fond quand coché */
        #vtc-toggle:checked~.block-bg {
            background-color: #10b981;
            /* vert emerald-500 */
        }
    </style>
    <div class="profile-container">

        {{-- DÉTAILS DU PROFIL --}}
        <div class="profile-section">

            <h2 class="section-title">Détails du profil</h2>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="profile-photo-wrapper">
                    <img src="{{ $user->profile_photo ? asset('storage/' . $user->profile_photo) : asset('assets/images/user-profile.webp') }}"
                        class="profile-photo">
                    {{-- Upload photo --}}
                    <input type="file" name="profile_photo" id="photoInput" accept="image/*">

                    @error('profile_photo')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Prénom --}}
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="firstname" value="{{ old('firstname', $user->firstname) }}">

                    @error('firstname')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Nom de famille --}}
                <div class="form-group">
                    <label>Nom de famille</label>
                    <input type="text" name="lastname" value="{{ old('lastname', $user->lastname) }}">

                    @error('lastname')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Nom d'affichage --}}
                <div class="form-group">
                    <label>Nom d'affichage</label>
                    <select name="display_format">
                        <option value="first_last"
                            {{ old('display_format', $user->display_format ?? 'first_last') == 'first_last' ? 'selected' : '' }}>
                            {{ $user->firstname }} {{ $user->lastname }}
                        </option>
                        <option value="last_first"
                            {{ old('display_format', $user->display_format ?? 'last_first') == 'last_first' ? 'selected' : '' }}>
                            {{ $user->lastname }} {{ $user->firstname }}
                        </option>
                    </select>

                    @error('display_format')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <button class="btn-save">Sauvegarder</button>

                @if (session('status') === 'profile-updated')
                    <p class="saved-message">✔ Profil mis à jour</p>
                @endif
            </form>

        </div>

        {{-- MOT DE PASSE --}}
        <div class="profile-section">

            <h2 class="section-title">Changer de mot de passe</h2>

            <div class="password-info">
                Votre mot de passe doit comporter au moins 12 caractères.
            </div>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Mot de passe actuel</label>
                    <input type="password" name="current_password">
                    @error('current_password', 'updatePassword')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="password">
                    @error('password', 'updatePassword')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation">
                    @error('password_confirmation', 'updatePassword')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <button class="btn-save">Sauvegarder</button>

                @if (session('status') === 'password-updated')
                    <p class="saved-message">✔ Mot de passe mis à jour</p>
                @endif
            </form>

        </div>

        <!-- Premium Card Container -->
        <div
            class="w-full max-w-md bg-white rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-gray-100 overflow-hidden transition-all hover:shadow-[0_20px_60px_rgba(255,60,0,0.08)]">

            <!-- Header with Illustration Pattern -->
            <div class="relative bg-brand h-24 flex items-center px-8 overflow-hidden">
                <div class="relative z-10">
                    <h3 class="text-white font-bold text-xl">Opportunités</h3>
                    <p class="text-orange-100 text-xs">Gérez votre activité de partenaire</p>
                </div>
                <!-- Abstract Illustration Circles -->
                <div class="absolute -right-4 -top-8 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="absolute right-12 -bottom-12 w-24 h-24 bg-black/5 rounded-full"></div>
            </div>

            <div class="p-8">
                <!-- VTC Section -->
                <div class="group flex items-center justify-between p-4 -mx-4 rounded transition-colors hover:bg-gray-50">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center text-brand">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path
                                    d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2" />
                                <circle cx="7" cy="17" r="2" />
                                <path d="M9 17h6" />
                                <circle cx="17" cy="17" r="2" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <label for="vtc-toggle" class="text-sm font-bold text-gray-800 cursor-pointer">
                                Chauffeur VTC
                            </label>
                            <p class="text-[11px] text-gray-400 leading-tight mt-0.5">
                                Transport de personnes & courses privées
                            </p>
                        </div>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="vtc-toggle" {{ $user->is_vtc_driver ? 'checked' : '' }}>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="h-px bg-gradient-to-r from-transparent via-gray-100 to-transparent my-2"></div>

                <!-- Delivery Section -->
                <div
                    class="group flex items-center justify-between p-4 -mx-4 rounded-2xl transition-colors hover:bg-gray-50">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center text-brand">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2" />
                                <path d="M15 18H9" />
                                <path
                                    d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-2.048-2.56a1 1 0 0 0-.78-.366H15" />
                                <circle cx="7" cy="18" r="2" />
                                <circle cx="17" cy="18" r="2" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <label for="livreur-toggle" class="text-sm font-bold text-gray-800 cursor-pointer">
                                Livreur
                            </label>
                            <p class="text-[11px] text-gray-400 leading-tight mt-0.5">
                                Livraison de colis, repas et courses
                            </p>
                        </div>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="livreur-toggle" {{ $user->hasRole('livreur') ? 'checked' : '' }}>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="mt-6 pt-5 border-t border-gray-100">
                    <div class="flex flex-col gap-3">
                        <span class="text-[10px] uppercase tracking-[0.1em] text-gray-400 font-black">Mon Statut
                            Actuel</span>

                        <div class="flex flex-wrap gap-2">
                            <!-- Badge Rôle Principal -->
                            <div
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand-soft border border-orange-100 transition-all hover:border-brand">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand animate-pulse"></span>
                                <span class="text-[11px] font-bold text-brand uppercase tracking-tight" id="role-status">
                                    {{ $user->roles->pluck('display_name')->join(', ') ?: 'Aucun rôle actif' }}
                                </span>
                            </div>

                            <!-- Badge Conditionnel Chauffeur -->
                            @if ($user->is_vtc_driver)
                                <div
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-indigo-50 border border-indigo-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-indigo-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-[10px] text-indigo-700 font-black uppercase"
                                        id="vtc-stat">Chauffeur VTC</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- A PROPOS DE MOI --}}
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="profile-section">

                <h2 class="section-title">À propos de moi</h2>

                {{-- À propos de moi --}}
                <div class="form-group">
                    <label>À propos de moi</label>
                    <textarea name="about_me" placeholder="Parlez-nous de vous...">{{ old('about_me', $user->about_me) }}</textarea>

                    @error('about_me')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Désactiver notifications email --}}
                <div class="checkbox-group">
                    <input type="checkbox" name="disable_email_notifications" id="notif"
                        {{ old('disable_email_notifications', $user->disable_email_notifications) ? 'checked' : '' }}>

                    <label for="notif">Désactiver les notifications email</label>

                    @error('disable_email_notifications')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Téléphone --}}
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}">

                    @error('phone')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Sexe --}}
                <div class="form-group">
                    <label>Sexe</label>
                    <select name="gender">
                        <option value="">-- Sélectionnez --</option>
                        <option value="Homme" {{ old('gender', $user->gender) == 'Homme' ? 'selected' : '' }}>Homme
                        </option>
                        <option value="Femme" {{ old('gender', $user->gender) == 'Femme' ? 'selected' : '' }}>Femme
                        </option>
                    </select>

                    @error('gender')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <button class="btn-save" type="submit">Sauvegarder</button>

                @if (session('status') === 'profile-updated')
                    <p class="saved-message">✔ Profil mis à jour</p>
                @endif
            </div>
        </form>

        {{-- RÉSEAUX SOCIAUX (statique pour l’instant) --}}
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="profile-section">
                <h2 class="section-title">Réseaux sociaux</h2>

                {{-- X.com --}}
                <div class="form-group">
                    <label>x.com</label>
                    <input type="url" name="x_com" placeholder="https://x.com/username"
                        value="{{ old('x_com', $user->x_com) }}">

                    @error('x_com')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Facebook --}}
                <div class="form-group">
                    <label>Facebook</label>
                    <input type="url" name="facebook" placeholder="https://facebook.com/username"
                        value="{{ old('facebook', $user->facebook) }}">

                    @error('facebook')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- LinkedIn --}}
                <div class="form-group">
                    <label>LinkedIn</label>
                    <input type="url" name="linkedin" placeholder="https://linkedin.com/in/username"
                        value="{{ old('linkedin', $user->linkedin) }}">

                    @error('linkedin')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Instagram --}}
                <div class="form-group">
                    <label>Instagram</label>
                    <input type="url" name="instagram" placeholder="https://instagram.com/username"
                        value="{{ old('instagram', $user->instagram) }}">

                    @error('instagram')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- YouTube --}}
                <div class="form-group">
                    <label>YouTube</label>
                    <input type="url" name="youtube" placeholder="https://youtube.com/channel"
                        value="{{ old('youtube', $user->youtube) }}">

                    @error('youtube')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- TikTok --}}
                <div class="form-group">
                    <label>TikTok</label>
                    <input type="url" name="tiktok" placeholder="https://tiktok.com/@username"
                        value="{{ old('tiktok', $user->tiktok) }}">

                    @error('tiktok')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- WhatsApp --}}
                <div class="form-group">
                    <label>WhatsApp</label>
                    <input type="tel" name="whatsapp" placeholder="+213xxxxxxxx"
                        value="{{ old('whatsapp', $user->whatsapp) }}">

                    @error('whatsapp')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Vérification d’identité --}}
                <div class="form-group">
                    <label>Vérification d’identité</label>
                    <input type="file" name="identity_verification" accept="image/*">

                    @error('identity_verification')
                        <small class="error">{{ $message }}</small>
                    @enderror

                    @if ($user->identity_verification)
                        <p class="mt-2">
                            <a href="{{ asset('storage/' . $user->identity_verification) }}" target="_blank">
                                Voir le document actuel
                            </a>
                        </p>
                    @endif
                </div>

                <button class="btn-save">Sauvegarder</button>

                @if (session('status') === 'profile-updated')
                    <p class="saved-message">✔ Informations mises à jour</p>
                @endif
            </div>
        </form>

    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#vtc-toggle').change(function() {

                let isVtc = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: '{{ route('profile.toggleVtc') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        is_vtc_driver: isVtc
                    },
                    success: function(response) {

                        if (response.is_vtc_driver) {
                            window.location.href = '{{ route('livreur.carte.vtc') }}';
                        } else {
                            $('#vtc-stat').text('Statut : Chauffeur VTC désactivé');
                        }
                    },
                    error: function() {
                        alert('Erreur lors de la mise à jour VTC');
                        $('#vtc-toggle').prop('checked', !isVtc);
                    }
                });

            });

            $('#livreur-toggle').change(function() {
                let isLivreur = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: '{{ route('profile.toggleLivreur') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        is_livreur: isLivreur
                    },
                    success: function(response) {
                        $('#role-status').text('Statut : ' + (response.roles.length ? response
                            .roles.join(', ') : 'Aucun rôle actif'));
                    },
                    error: function() {
                        alert('Erreur lors de la mise à jour Livreur');
                        $('#livreur-toggle').prop('checked', !isLivreur);
                    }
                });
            });
        });
    </script>
@endsection
