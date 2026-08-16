@extends('layouts.connected')
@section('title', 'Mon Profil')

<style>
    /* ── Toggle Switch ── */
    .switch { position: relative; display: inline-block; width: 44px; height: 24px; flex-shrink: 0; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; inset: 0; background: #e2e8f0; transition: .35s; border-radius: 24px; }
    .slider:before { content: ""; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background: white; transition: .35s; border-radius: 50%; box-shadow: 0 1px 4px rgba(0,0,0,0.15); }
    input:checked + .slider { background: #ff3c00; }
    input:checked + .slider:before { transform: translateX(20px); }

    /* ── Pill Tabs ── */
    .tab-rail { background: #f1f5f9; border-radius: 1.25rem; padding: 4px; display: flex; gap: 4px; }
    .tab-pill { padding: 9px 18px; border-radius: 0.875rem; font-size: 0.6rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.12em; color: #94a3b8; cursor: pointer; background: transparent; border: none; transition: all .2s; font-family: inherit; white-space: nowrap; display: flex; align-items: center; gap: 6px; }
    .tab-pill:hover:not(.active) { color: #475569; }
    .tab-pill.active { background: white; color: #ff3c00; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }

    /* ── Fields ── */
    .f-label { display: block; font-size: 0.6rem; font-weight: 900; letter-spacing: 0.1em; text-transform: uppercase; color: #94a3b8; margin-bottom: 6px; }
    .f-wrap { display: flex; align-items: center; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 0.875rem; overflow: hidden; transition: border-color .2s, box-shadow .2s; }
    .f-wrap:focus-within { border-color: #ff3c00; box-shadow: 0 0 0 3px rgba(255,60,0,0.07); }
    .f-wrap .f-ico { width: 42px; display: flex; align-items: center; justify-content: center; color: #cbd5e1; flex-shrink: 0; font-size: 13px; }
    .f-wrap input, .f-wrap select, .f-wrap textarea { flex: 1; padding: 11px 14px 11px 0; background: transparent; border: none; font-size: 0.875rem; font-weight: 500; color: #1e293b; outline: none; font-family: inherit; min-width: 0; }
    .f-wrap select { padding-right: 14px; }
    .f-wrap textarea { padding: 11px 14px 11px 0; resize: none; }

    /* ── Strength bar ── */
    .strength-bar { height: 4px; border-radius: 4px; transition: width .35s, background .35s; }

    /* ── Completion ring (CSS trick) ── */
    .ring-svg { transform: rotate(-90deg); }
    .ring-track { fill: none; stroke: #f1f5f9; stroke-width: 5; }
    .ring-fill  { fill: none; stroke: #ff3c00; stroke-width: 5; stroke-linecap: round; transition: stroke-dashoffset 1s ease; }
</style>

@section('content')
@php
    $user = Auth::user();

    // Calcul complétude profil
    $pts = 0;
    if ($user->firstname)           $pts += 12;
    if ($user->lastname)            $pts += 12;
    if ($user->phone)               $pts += 10;
    if ($user->gender)              $pts += 8;
    if ($user->about_me)            $pts += 13;
    if ($user->profile_photo)       $pts += 15;
    if ($user->x_com || $user->facebook || $user->instagram || $user->linkedin
        || $user->youtube || $user->tiktok || $user->whatsapp) $pts += 15;
    if ($user->identity_verification) $pts += 15;
    $completion = min($pts, 100);

    $circumference = 2 * M_PI * 28; // r=28
    $offset = $circumference - ($completion / 100) * $circumference;

    $socials = [
        ['name'=>'x_com',    'label'=>'X (Twitter)','icon'=>'fa-brands fa-x-twitter','color'=>'#000000','placeholder'=>'https://x.com/username',          'type'=>'url'],
        ['name'=>'facebook', 'label'=>'Facebook',   'icon'=>'fab fa-facebook',       'color'=>'#1877F2','placeholder'=>'https://facebook.com/username',    'type'=>'url'],
        ['name'=>'instagram','label'=>'Instagram',  'icon'=>'fab fa-instagram',      'color'=>'#E1306C','placeholder'=>'https://instagram.com/username',   'type'=>'url'],
        ['name'=>'linkedin', 'label'=>'LinkedIn',   'icon'=>'fab fa-linkedin',       'color'=>'#0A66C2','placeholder'=>'https://linkedin.com/in/username', 'type'=>'url'],
        ['name'=>'youtube',  'label'=>'YouTube',    'icon'=>'fab fa-youtube',        'color'=>'#FF0000','placeholder'=>'https://youtube.com/channel',      'type'=>'url'],
        ['name'=>'tiktok',   'label'=>'TikTok',     'icon'=>'fab fa-tiktok',         'color'=>'#010101','placeholder'=>'https://tiktok.com/@username',     'type'=>'url'],
        ['name'=>'whatsapp', 'label'=>'WhatsApp',   'icon'=>'fab fa-whatsapp',       'color'=>'#25D366','placeholder'=>'+33 6 00 00 00 00',                'type'=>'tel'],
    ];
@endphp

<div class="min-h-screen bg-[#F8FAFC] py-10 px-4 sm:px-6 font-jakarta text-[#0F172A]">
    <div class="max-w-5xl mx-auto">

        <!-- ───── En-tête ───── -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tight">Mon Profil</h1>
                <p class="text-slate-400 text-xs mt-1 font-medium">Gérez vos informations, votre sécurité et vos réseaux</p>
            </div>
            @if ($user->identity_verification)
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs font-black text-emerald-700 uppercase tracking-widest">
                    <i class="fas fa-shield-alt"></i> Vérifié
                </span>
            @endif
        </div>

        <div class="flex flex-col lg:flex-row gap-7 items-start">

            <!-- ═══════════════════════════════════
                 SIDEBAR GAUCHE
            ══════════════════════════════════════ -->
            <div class="w-full lg:w-68 flex-shrink-0 flex flex-col gap-5" style="width:270px">

                <!-- Hero card avatar -->
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">

                    <!-- Bannière sombre -->
                    <div class="relative h-20 bg-[#0F172A] overflow-hidden">
                        <div class="absolute -right-6 -top-6 w-28 h-28 rounded-full bg-white/5"></div>
                        <div class="absolute right-10 top-4 w-16 h-16 rounded-full bg-[#ff3c00]/10"></div>
                        <div class="absolute -left-4 -bottom-8 w-20 h-20 rounded-full bg-white/[0.03]"></div>
                        <!-- Dots pattern -->
                        <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(#fff 1px,transparent 1px);background-size:12px 12px"></div>
                    </div>

                    <!-- Avatar (chevauchement) -->
                    <div class="flex flex-col items-center px-6 pb-6" style="margin-top:-36px">
                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="form-photo">
                            @csrf @method('PATCH')
                            <div class="relative group cursor-pointer mb-3" onclick="document.getElementById('photoInput').click()">
                                <div class="w-[72px] h-[72px] rounded-2xl ring-4 ring-white shadow-xl overflow-hidden">
                                    <img src="{{ $user->profile_photo ? asset('storage/'.$user->profile_photo) : asset('assets/images/user-profile.webp') }}"
                                        class="w-full h-full object-cover" id="avatar-preview">
                                </div>
                                <div class="absolute inset-0 rounded-2xl bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i class="fas fa-camera text-white text-base"></i>
                                </div>
                                <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-[#ff3c00] rounded-lg flex items-center justify-center shadow-md">
                                    <i class="fas fa-pen text-white" style="font-size:9px"></i>
                                </div>
                            </div>
                            <input type="file" name="profile_photo" id="photoInput" accept="image/*" class="hidden"
                                onchange="previewPhoto(this); this.form.submit()">
                            @error('profile_photo')<p class="text-red-500 text-xs mt-1 text-center">{{ $message }}</p>@enderror
                        </form>

                        <p class="font-black text-base text-center leading-tight">{{ $user->firstname }} {{ $user->lastname }}</p>
                        <p class="text-slate-400 text-xs mt-0.5 text-center">{{ $user->email }}</p>

                        <!-- Badges rôles -->
                        <div class="flex flex-wrap justify-center gap-1.5 mt-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-orange-50 border border-orange-100 text-[9px] font-black text-[#ff3c00] uppercase tracking-tight" id="role-status">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#ff3c00] animate-pulse flex-shrink-0"></span>
                                {{ $user->roles->pluck('display_name')->join(', ') ?: 'Aucun rôle actif' }}
                            </span>
                            @if ($user->is_vtc_driver)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-[9px] font-black text-indigo-700 uppercase" id="vtc-stat">
                                    <i class="fas fa-check" style="font-size:8px"></i> VTC
                                </span>
                            @endif
                        </div>

                        <!-- Barre de complétude -->
                        <div class="w-full mt-5 pt-4 border-t border-slate-50">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Profil complété</span>
                                <span class="text-xs font-black text-[#ff3c00]">{{ $completion }}%</span>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700"
                                    style="width:{{ $completion }}%;
                                           background: {{ $completion < 40 ? '#ef4444' : ($completion < 75 ? '#f59e0b' : '#ff3c00') }}">
                                </div>
                            </div>
                            @if ($completion < 100)
                                <p class="text-[9px] text-slate-400 mt-2 font-medium">
                                    @if (!$user->profile_photo) Ajoutez une photo · @endif
                                    @if (!$user->phone) Ajoutez un téléphone · @endif
                                    @if (!$user->about_me) Remplissez la bio @endif
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Opportunités -->
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="relative px-5 py-4 overflow-hidden" style="background:linear-gradient(135deg,#ff3c00 0%,#c92d00 100%)">
                        <div class="relative z-10">
                            <p class="text-white font-black text-sm leading-tight">Opportunités</p>
                            <p class="text-orange-100/80 text-[10px] mt-0.5">Activez vos rôles partenaire</p>
                        </div>
                        <div class="absolute -right-3 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
                        <div class="absolute right-6 -bottom-6 w-14 h-14 rounded-full bg-black/10"></div>
                    </div>

                    <div class="p-4 flex flex-col gap-1">
                        <div class="flex items-center justify-between p-3 rounded-2xl hover:bg-slate-50 transition-colors cursor-default">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-orange-50 flex items-center justify-center text-[#ff3c00] flex-shrink-0">
                                    <i class="fas fa-car text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <label for="vtc-toggle" class="block text-xs font-bold text-slate-800 cursor-pointer truncate">Chauffeur VTC</label>
                                    <p class="text-[10px] text-slate-400">Transport privé</p>
                                </div>
                            </div>
                            <label class="switch ml-3"><input type="checkbox" id="vtc-toggle" {{ $user->is_vtc_driver ? 'checked' : '' }}><span class="slider"></span></label>
                        </div>

                        <div class="h-px bg-slate-50 mx-2"></div>

                        <div class="flex items-center justify-between p-3 rounded-2xl hover:bg-slate-50 transition-colors cursor-default">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-orange-50 flex items-center justify-center text-[#ff3c00] flex-shrink-0">
                                    <i class="fas fa-truck text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <label for="livreur-toggle" class="block text-xs font-bold text-slate-800 cursor-pointer truncate">Livreur</label>
                                    <p class="text-[10px] text-slate-400">Colis & repas</p>
                                </div>
                            </div>
                            <label class="switch ml-3"><input type="checkbox" id="livreur-toggle" {{ $user->hasRole('livreur') ? 'checked' : '' }}><span class="slider"></span></label>
                        </div>
                    </div>
                </div>

            </div><!-- /sidebar -->


            <!-- ═══════════════════════════════════
                 CONTENU PRINCIPAL
            ══════════════════════════════════════ -->
            <div class="flex-1 min-w-0">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">

                    <!-- Pill tab nav -->
                    <div class="px-6 pt-6 pb-0">
                        <div class="tab-rail w-fit">
                            <button onclick="switchTab('informations')" id="tab-btn-informations" class="tab-pill active">
                                <i class="fas fa-user"></i> Informations
                            </button>
                            <button onclick="switchTab('securite')" id="tab-btn-securite" class="tab-pill">
                                <i class="fas fa-lock"></i> Sécurité
                            </button>
                            <button onclick="switchTab('reseaux')" id="tab-btn-reseaux" class="tab-pill">
                                <i class="fas fa-share-alt"></i> Réseaux
                            </button>
                        </div>
                    </div>

                    <!-- ── Tab Informations ── -->
                    <div id="tab-informations" class="p-7">
                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                            @csrf @method('PATCH')

                            <!-- Section : Identité -->
                            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-300 mb-4">Identité</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label class="f-label">Prénom</label>
                                    <div class="f-wrap">
                                        <span class="f-ico"><i class="fas fa-user"></i></span>
                                        <input type="text" name="firstname" value="{{ old('firstname', $user->firstname) }}" placeholder="Votre prénom">
                                    </div>
                                    @error('firstname')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="f-label">Nom de famille</label>
                                    <div class="f-wrap">
                                        <span class="f-ico"><i class="fas fa-user"></i></span>
                                        <input type="text" name="lastname" value="{{ old('lastname', $user->lastname) }}" placeholder="Votre nom">
                                    </div>
                                    @error('lastname')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="f-label">Téléphone</label>
                                    <div class="f-wrap">
                                        <span class="f-ico"><i class="fas fa-phone"></i></span>
                                        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+33 6 00 00 00 00">
                                    </div>
                                    @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="f-label">Genre</label>
                                    <div class="f-wrap">
                                        <span class="f-ico"><i class="fas fa-venus-mars"></i></span>
                                        <select name="gender">
                                            <option value="">-- Sélectionnez --</option>
                                            <option value="Homme" {{ old('gender', $user->gender) == 'Homme' ? 'selected' : '' }}>Homme</option>
                                            <option value="Femme" {{ old('gender', $user->gender) == 'Femme' ? 'selected' : '' }}>Femme</option>
                                        </select>
                                    </div>
                                    @error('gender')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="f-label">Nom d'affichage</label>
                                    <div class="f-wrap">
                                        <span class="f-ico"><i class="fas fa-id-badge"></i></span>
                                        <select name="display_format">
                                            <option value="first_last" {{ old('display_format', $user->display_format ?? 'first_last') == 'first_last' ? 'selected' : '' }}>{{ $user->firstname }} {{ $user->lastname }}</option>
                                            <option value="last_first" {{ old('display_format', $user->display_format ?? 'last_first') == 'last_first' ? 'selected' : '' }}>{{ $user->lastname }} {{ $user->firstname }}</option>
                                        </select>
                                    </div>
                                    @error('display_format')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <!-- Section : Bio -->
                            <div class="h-px bg-slate-50 mb-5"></div>
                            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-300 mb-4">Bio</p>
                            <div class="mb-6">
                                <div class="f-wrap" style="align-items:flex-start">
                                    <span class="f-ico" style="padding-top:11px"><i class="fas fa-align-left"></i></span>
                                    <textarea name="about_me" rows="4" placeholder="Parlez-nous de vous... vos habitudes de trajet, votre style de conduite, vos passions.">{{ old('about_me', $user->about_me) }}</textarea>
                                </div>
                                @error('about_me')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <!-- Notifications -->
                            <label class="flex items-center gap-3 p-3.5 bg-slate-50 hover:bg-slate-100 rounded-2xl cursor-pointer transition-colors">
                                <input type="checkbox" name="disable_email_notifications" id="notif"
                                    {{ old('disable_email_notifications', $user->disable_email_notifications) ? 'checked' : '' }}
                                    class="w-4 h-4 accent-[#ff3c00] cursor-pointer flex-shrink-0">
                                <div>
                                    <span class="text-sm font-semibold text-slate-700 block">Désactiver les notifications email</span>
                                    <span class="text-[10px] text-slate-400">Vous ne recevrez plus d'emails pour les nouvelles réservations</span>
                                </div>
                            </label>

                            <!-- Footer -->
                            <div class="flex items-center justify-between mt-7 pt-5 border-t border-slate-50">
                                @if (session('status') === 'profile-updated')
                                    <span class="text-emerald-600 text-xs font-bold flex items-center gap-2">
                                        <i class="fas fa-check-circle"></i> Profil mis à jour
                                    </span>
                                @else
                                    <span></span>
                                @endif
                                <button type="submit"
                                    class="px-7 py-3 bg-[#ff3c00] hover:bg-[#0F172A] text-white rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 shadow-lg shadow-orange-100 hover:shadow-none">
                                    Sauvegarder
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- ── Tab Sécurité ── -->
                    <div id="tab-securite" class="p-7 hidden">
                        <div class="flex items-start gap-3 p-4 bg-blue-50 border border-blue-100 rounded-2xl mb-7">
                            <i class="fas fa-shield-alt text-blue-400 mt-0.5 flex-shrink-0"></i>
                            <p class="text-xs text-blue-700 font-medium leading-relaxed">
                                Choisissez un mot de passe d'au moins <strong>12 caractères</strong> avec des chiffres et des caractères spéciaux pour plus de sécurité.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf @method('PUT')
                            <div class="flex flex-col gap-5">
                                <div>
                                    <label class="f-label">Mot de passe actuel</label>
                                    <div class="f-wrap">
                                        <span class="f-ico"><i class="fas fa-key"></i></span>
                                        <input type="password" name="current_password" autocomplete="current-password" placeholder="••••••••••••">
                                    </div>
                                    @error('current_password', 'updatePassword')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="f-label">Nouveau mot de passe</label>
                                    <div class="f-wrap">
                                        <span class="f-ico"><i class="fas fa-lock"></i></span>
                                        <input type="password" name="password" id="new-password" autocomplete="new-password" placeholder="••••••••••••" oninput="updateStrength(this.value)">
                                    </div>
                                    <!-- Indicateur de force -->
                                    <div class="mt-2 flex gap-1.5" id="strength-bars">
                                        <div class="h-1 flex-1 rounded-full bg-slate-100" id="sb1"></div>
                                        <div class="h-1 flex-1 rounded-full bg-slate-100" id="sb2"></div>
                                        <div class="h-1 flex-1 rounded-full bg-slate-100" id="sb3"></div>
                                        <div class="h-1 flex-1 rounded-full bg-slate-100" id="sb4"></div>
                                    </div>
                                    <p class="text-[10px] mt-1 font-semibold" id="strength-label" style="color:#94a3b8"></p>
                                    @error('password', 'updatePassword')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="f-label">Confirmer le mot de passe</label>
                                    <div class="f-wrap">
                                        <span class="f-ico"><i class="fas fa-lock"></i></span>
                                        <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="••••••••••••">
                                    </div>
                                    @error('password_confirmation', 'updatePassword')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="flex items-center justify-between mt-7 pt-5 border-t border-slate-50">
                                @if (session('status') === 'password-updated')
                                    <span class="text-emerald-600 text-xs font-bold flex items-center gap-2">
                                        <i class="fas fa-check-circle"></i> Mot de passe mis à jour
                                    </span>
                                @else
                                    <span></span>
                                @endif
                                <button type="submit"
                                    class="px-7 py-3 bg-[#ff3c00] hover:bg-[#0F172A] text-white rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 shadow-lg shadow-orange-100 hover:shadow-none">
                                    Mettre à jour
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- ── Tab Réseaux ── -->
                    <div id="tab-reseaux" class="p-7 hidden">
                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                            @csrf @method('PATCH')

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-7">
                                @foreach ($socials as $s)
                                    <div>
                                        <label class="f-label">{{ $s['label'] }}</label>
                                        <div class="f-wrap">
                                            <span class="f-ico" style="color:{{ $s['color'] }}">
                                                <i class="{{ $s['icon'] }} text-sm"></i>
                                            </span>
                                            <input type="{{ $s['type'] }}" name="{{ $s['name'] }}"
                                                placeholder="{{ $s['placeholder'] }}"
                                                value="{{ old($s['name'], $user->{$s['name']}) }}">
                                        </div>
                                        @error($s['name'])<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    </div>
                                @endforeach
                            </div>

                            <!-- Vérification d'identité -->
                            <div class="p-5 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                <div class="flex items-center gap-2 mb-3">
                                    <i class="fas fa-id-card text-slate-400 text-sm"></i>
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Vérification d'identité</p>
                                </div>
                                <input type="file" name="identity_verification" accept="image/*"
                                    class="block w-full text-xs text-slate-500
                                           file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0
                                           file:text-[10px] file:font-black file:uppercase file:tracking-widest
                                           file:bg-[#ff3c00] file:text-white hover:file:bg-[#0F172A]
                                           file:transition-colors file:cursor-pointer cursor-pointer">
                                @error('identity_verification')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
                                @if ($user->identity_verification)
                                    <a href="{{ asset('storage/'.$user->identity_verification) }}" target="_blank"
                                        class="inline-flex items-center gap-2 mt-3 text-xs font-bold text-[#ff3c00] hover:underline">
                                        <i class="fas fa-file-alt"></i> Voir le document actuel
                                    </a>
                                @endif
                            </div>

                            <div class="flex items-center justify-between mt-7 pt-5 border-t border-slate-50">
                                @if (session('status') === 'profile-updated')
                                    <span class="text-emerald-600 text-xs font-bold flex items-center gap-2">
                                        <i class="fas fa-check-circle"></i> Réseaux mis à jour
                                    </span>
                                @else
                                    <span></span>
                                @endif
                                <button type="submit"
                                    class="px-7 py-3 bg-[#ff3c00] hover:bg-[#0F172A] text-white rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all duration-300 shadow-lg shadow-orange-100 hover:shadow-none">
                                    Sauvegarder
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div><!-- /contenu -->

        </div>
    </div>
</div>

<script>
    // ── Tabs ──
    const TABS = ['informations', 'securite', 'reseaux'];
    function switchTab(name) {
        TABS.forEach(t => {
            document.getElementById('tab-' + t).classList.add('hidden');
            document.getElementById('tab-btn-' + t).classList.remove('active');
        });
        document.getElementById('tab-' + name).classList.remove('hidden');
        document.getElementById('tab-btn-' + name).classList.add('active');
    }

    @if ($errors->updatePassword->isNotEmpty())
        switchTab('securite');
    @elseif ($errors->has('x_com') || $errors->has('facebook') || $errors->has('instagram') ||
             $errors->has('linkedin') || $errors->has('youtube') || $errors->has('tiktok') ||
             $errors->has('whatsapp') || $errors->has('identity_verification'))
        switchTab('reseaux');
    @endif

    // ── Photo preview ──
    function previewPhoto(input) {
        if (input.files && input.files[0]) {
            const r = new FileReader();
            r.onload = e => document.getElementById('avatar-preview').src = e.target.result;
            r.readAsDataURL(input.files[0]);
        }
    }

    // ── Password strength ──
    function updateStrength(val) {
        let score = 0;
        if (val.length >= 8)  score++;
        if (val.length >= 12) score++;
        if (/[0-9]/.test(val) && /[a-zA-Z]/.test(val)) score++;
        if (/[^a-zA-Z0-9]/.test(val)) score++;

        const colors = ['', '#ef4444', '#f59e0b', '#3b82f6', '#22c55e'];
        const labels = ['', 'Trop faible', 'Faible', 'Moyen', 'Fort'];
        const lbl = document.getElementById('strength-label');

        for (let i = 1; i <= 4; i++) {
            const bar = document.getElementById('sb' + i);
            bar.style.background = i <= score ? colors[score] : '#e2e8f0';
        }
        lbl.textContent = val.length ? labels[score] || '' : '';
        lbl.style.color = colors[score] || '#94a3b8';
    }

    // ── VTC toggle ──
    document.getElementById('vtc-toggle').addEventListener('change', function () {
        const isVtc = this.checked ? 1 : 0;
        fetch('{{ route('profile.toggleVtc') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ is_vtc_driver: isVtc })
        })
        .then(r => r.json())
        .then(data => {
            if (data.is_vtc_driver) window.location.href = '{{ route('livreur.carte.vtc') }}';
        })
        .catch(() => {
            alert('Erreur lors de la mise à jour VTC');
            this.checked = !this.checked;
        });
    });

    // ── Livreur toggle ──
    document.getElementById('livreur-toggle').addEventListener('change', function () {
        const isLivreur = this.checked ? 1 : 0;
        fetch('{{ route('profile.toggleLivreur') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ is_livreur: isLivreur })
        })
        .then(r => r.json())
        .then(data => {
            const el = document.getElementById('role-status');
            if (el) el.textContent = data.roles && data.roles.length ? data.roles.join(', ') : 'Aucun rôle actif';
        })
        .catch(() => {
            alert('Erreur lors de la mise à jour Livreur');
            this.checked = !this.checked;
        });
    });
</script>
@endsection
