@extends('layouts.connected')
@section('title', 'Mon compte - Olten')

@section('content')
@php
    $user = Auth::user();

    /*
     | Completude du profil : chaque information renseignee rapporte des
     | points, le total est plafonne a 100.
     */
    $pts = 0;
    if ($user->firstname)             $pts += 12;
    if ($user->lastname)              $pts += 12;
    if ($user->phone)                 $pts += 10;
    if ($user->gender)                $pts += 8;
    if ($user->about_me)              $pts += 13;
    if ($user->profile_photo)         $pts += 15;
    if ($user->x_com || $user->facebook || $user->instagram || $user->linkedin
        || $user->youtube || $user->tiktok || $user->whatsapp) $pts += 15;
    if ($user->identity_verification) $pts += 15;

    $completion = min($pts, 100);

    $manque = collect([
        ! $user->profile_photo         ? 'une photo' : null,
        ! $user->phone                 ? 'un téléphone' : null,
        ! $user->about_me              ? 'une bio' : null,
        ! $user->identity_verification ? 'une pièce d\'identité' : null,
    ])->filter()->values();

    $socials = [
        ['name'=>'x_com',    'label'=>'X (Twitter)','icon'=>'fa-brands fa-x-twitter','placeholder'=>'https://x.com/username',          'type'=>'url'],
        ['name'=>'facebook', 'label'=>'Facebook',   'icon'=>'fab fa-facebook',       'placeholder'=>'https://facebook.com/username',   'type'=>'url'],
        ['name'=>'instagram','label'=>'Instagram',  'icon'=>'fab fa-instagram',      'placeholder'=>'https://instagram.com/username',  'type'=>'url'],
        ['name'=>'linkedin', 'label'=>'LinkedIn',   'icon'=>'fab fa-linkedin',       'placeholder'=>'https://linkedin.com/in/username','type'=>'url'],
        ['name'=>'youtube',  'label'=>'YouTube',    'icon'=>'fab fa-youtube',        'placeholder'=>'https://youtube.com/channel',     'type'=>'url'],
        ['name'=>'tiktok',   'label'=>'TikTok',     'icon'=>'fab fa-tiktok',         'placeholder'=>'https://tiktok.com/@username',    'type'=>'url'],
        ['name'=>'whatsapp', 'label'=>'WhatsApp',   'icon'=>'fab fa-whatsapp',       'placeholder'=>'+33 6 00 00 00 00',              'type'=>'tel'],
    ];

    $socialErreur = collect($socials)->pluck('name')->push('identity_verification')
                        ->contains(fn ($f) => $errors->has($f));
@endphp

<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Mon compte</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Mon compte</h1>
            <p class="sp-subtitle">Vos informations, votre sécurité et vos rôles sur la plateforme.</p>
        </div>
    </header>

    <div class="sp-profile">

        {{-- ══ Colonne de gauche ══ --}}
        <aside class="sp-profile-aside">

            {{-- Carte identite --}}
            <div class="sp-panel sp-profile-card">
                <div class="sp-profile-cover"></div>

                {{-- Le formulaire ne porte que la photo : profile.update valide
                     firstname/lastname et recalcule le nom affiche, on les
                     renvoie donc tels quels. --}}
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="form-photo">
                    @csrf @method('PATCH')

                    <input type="hidden" name="firstname" value="{{ $user->firstname }}">
                    <input type="hidden" name="lastname" value="{{ $user->lastname }}">
                    <input type="hidden" name="display_format" value="{{ $user->display_format ?? 'first_last' }}">
                    @if ($user->disable_email_notifications)
                        <input type="hidden" name="disable_email_notifications" value="1">
                    @endif

                    <button type="button" class="sp-avatar-edit"
                            onclick="document.getElementById('photoInput').click()"
                            aria-label="Changer la photo de profil">
                        <img id="avatar-preview"
                             src="{{ $user->profile_photo ? asset('storage/'.$user->profile_photo) : asset('assets/images/user-profile.webp') }}"
                             alt="Photo de profil">
                        <span class="sp-avatar-overlay">Changer</span>
                    </button>

                    <input type="file" name="profile_photo" id="photoInput" accept="image/*" hidden
                           onchange="previewPhoto(this); this.form.submit()">

                    @error('profile_photo')<p class="sp-error is-center">{{ $message }}</p>@enderror
                </form>

                <div class="sp-profile-identity">
                    <h2>{{ trim($user->firstname . ' ' . $user->lastname) ?: $user->name }}</h2>
                    <p>{{ $user->email }}</p>

                    <div class="sp-role-badges">
                        <span class="sp-status is-pending" id="role-status">
                            {{ $user->roles->pluck('display_name')->join(', ') ?: 'Aucun rôle actif' }}
                        </span>

                        @if ($user->hasRole('chauffeur_vtc'))
                            <span class="sp-status is-confirmed" id="vtc-stat">Chauffeur VTC</span>
                        @endif
                    </div>
                </div>

                {{-- Completude --}}
                <div class="sp-completion">
                    <div class="sp-completion-head">
                        <span>Profil complété</span>
                        <strong>{{ $completion }} %</strong>
                    </div>

                    <div class="sp-progress">
                        <span class="sp-progress-fill {{ $completion < 40 ? 'is-low' : ($completion < 75 ? 'is-mid' : '') }}"
                              style="width: {{ $completion }}%"></span>
                    </div>

                    @if ($manque->count())
                        <p class="sp-completion-hint">Il manque {{ $manque->join(', ', ' et ') }}.</p>
                    @endif
                </div>
            </div>

            {{-- Roles partenaires --}}
            <div class="sp-panel">
                <div class="sp-toolbar">
                    <div>
                        <h2 class="sp-toolbar-title">Opportunités</h2>
                        <span class="sp-count">Activez vos rôles partenaire</span>
                    </div>
                </div>

                <div class="sp-opt-list">
                    <div class="sp-opt-row">
                        <div>
                            <label for="vtc-toggle" class="sp-label">Chauffeur VTC</label>
                            <span class="sp-help">Transport de personnes et covoiturage</span>
                        </div>

                        <div class="toggle-switch">
                            <input type="checkbox" id="vtc-toggle" @checked($user->hasRole('chauffeur_vtc'))>
                            <label for="vtc-toggle" class="toggle-label"></label>
                        </div>
                    </div>

                    <div class="sp-opt-row">
                        <div>
                            <label for="livreur-toggle" class="sp-label">Livreur</label>
                            <span class="sp-help">Livraison de colis et de commandes</span>
                        </div>

                        <div class="toggle-switch">
                            <input type="checkbox" id="livreur-toggle" @checked($user->hasRole('livreur'))>
                            <label for="livreur-toggle" class="toggle-label"></label>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        {{-- ══ Colonne de droite ══ --}}
        <section class="sp-panel sp-profile-main">

            <div class="sp-tabs">
                <button type="button" class="sp-tab is-active" id="tab-btn-informations" onclick="switchTab('informations')">Informations</button>
                <button type="button" class="sp-tab" id="tab-btn-securite" onclick="switchTab('securite')">Sécurité</button>
                <button type="button" class="sp-tab" id="tab-btn-reseaux" onclick="switchTab('reseaux')">Réseaux &amp; identité</button>
            </div>

            {{-- ── Informations ── --}}
            <div id="tab-informations" class="sp-tab-pane">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf @method('PATCH')

                    <p class="sp-section-label">Identité</p>

                    <div class="sp-form-grid">
                        <div class="sp-field">
                            <label class="sp-label" for="firstname">Prénom <span class="sp-req">*</span></label>
                            <input type="text" name="firstname" id="firstname" class="sp-input"
                                   value="{{ old('firstname', $user->firstname) }}" placeholder="Votre prénom" required>
                            @error('firstname')<p class="sp-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="sp-field">
                            <label class="sp-label" for="lastname">Nom <span class="sp-req">*</span></label>
                            <input type="text" name="lastname" id="lastname" class="sp-input"
                                   value="{{ old('lastname', $user->lastname) }}" placeholder="Votre nom" required>
                            @error('lastname')<p class="sp-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="sp-field">
                            <label class="sp-label" for="phone">Téléphone</label>
                            <input type="tel" name="phone" id="phone" class="sp-input"
                                   value="{{ old('phone', $user->phone) }}" placeholder="+33 6 00 00 00 00">
                            @error('phone')<p class="sp-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="sp-field">
                            <label class="sp-label" for="gender">Genre</label>
                            <select name="gender" id="gender" class="sp-input">
                                <option value="">Non précisé</option>
                                <option value="Homme" @selected(old('gender', $user->gender) == 'Homme')>Homme</option>
                                <option value="Femme" @selected(old('gender', $user->gender) == 'Femme')>Femme</option>
                            </select>
                            @error('gender')<p class="sp-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="sp-field">
                        <label class="sp-label" for="display_format">Nom affiché</label>
                        <select name="display_format" id="display_format" class="sp-input">
                            <option value="first_last" @selected(old('display_format', $user->display_format ?? 'first_last') == 'first_last')>
                                {{ $user->firstname }} {{ $user->lastname }}
                            </option>
                            <option value="last_first" @selected(old('display_format', $user->display_format ?? 'first_last') == 'last_first')>
                                {{ $user->lastname }} {{ $user->firstname }}
                            </option>
                        </select>
                        @error('display_format')<p class="sp-error">{{ $message }}</p>@enderror
                    </div>

                    <p class="sp-section-label">À propos</p>

                    <div class="sp-field">
                        <label class="sp-label" for="about_me">Bio</label>
                        <textarea name="about_me" id="about_me" rows="4"
                                  placeholder="Présentez-vous en quelques lignes...">{{ old('about_me', $user->about_me) }}</textarea>
                        @error('about_me')<p class="sp-error">{{ $message }}</p>@enderror
                    </div>

                    <label class="sp-check-row" for="notif">
                        <input type="checkbox" name="disable_email_notifications" id="notif"
                               @checked(old('disable_email_notifications', $user->disable_email_notifications))>
                        <span>
                            <strong>Désactiver les notifications par e-mail</strong>
                            <small>Vous ne recevrez plus d'e-mail lors des nouvelles réservations.</small>
                        </span>
                    </label>

                    <div class="sp-pane-actions">
                        @if (session('status') === 'profile-updated' || session('status'))
                            <span class="sp-saved">{{ is_string(session('status')) && session('status') !== 'profile-updated' ? session('status') : 'Profil mis à jour' }}</span>
                        @else
                            <span></span>
                        @endif

                        <button type="submit" class="sp-btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>

            {{-- ── Sécurité ── --}}
            <div id="tab-securite" class="sp-tab-pane" hidden>
                <div class="sp-note">
                    Choisissez un mot de passe d'au moins <strong>12 caractères</strong>, mêlant chiffres et caractères spéciaux.
                </div>

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf @method('PUT')

                    <div class="sp-field">
                        <label class="sp-label" for="current_password">Mot de passe actuel</label>
                        <input type="password" name="current_password" id="current_password" class="sp-input"
                               autocomplete="current-password" placeholder="••••••••••••">
                        @error('current_password', 'updatePassword')<p class="sp-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="sp-field">
                        <label class="sp-label" for="new-password">Nouveau mot de passe</label>
                        <input type="password" name="password" id="new-password" class="sp-input"
                               autocomplete="new-password" placeholder="••••••••••••" oninput="updateStrength(this.value)">

                        <div class="sp-strength" id="strength-bars">
                            <span id="sb1"></span><span id="sb2"></span><span id="sb3"></span><span id="sb4"></span>
                        </div>
                        <p class="sp-strength-label" id="strength-label"></p>

                        @error('password', 'updatePassword')<p class="sp-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="sp-field">
                        <label class="sp-label" for="password_confirmation">Confirmer le mot de passe</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="sp-input"
                               autocomplete="new-password" placeholder="••••••••••••">
                        @error('password_confirmation', 'updatePassword')<p class="sp-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="sp-pane-actions">
                        @if (session('status') === 'password-updated')
                            <span class="sp-saved">Mot de passe mis à jour</span>
                        @else
                            <span></span>
                        @endif

                        <button type="submit" class="sp-btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>

            {{-- ── Réseaux & identité ── --}}
            <div id="tab-reseaux" class="sp-tab-pane" hidden>
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf @method('PATCH')

                    {{-- Memes champs caches que le formulaire photo : ce
                         formulaire partiel ne doit pas perdre l'identite. --}}
                    <input type="hidden" name="firstname" value="{{ $user->firstname }}">
                    <input type="hidden" name="lastname" value="{{ $user->lastname }}">
                    <input type="hidden" name="display_format" value="{{ $user->display_format ?? 'first_last' }}">
                    @if ($user->disable_email_notifications)
                        <input type="hidden" name="disable_email_notifications" value="1">
                    @endif

                    <p class="sp-section-label">Réseaux sociaux</p>

                    <div class="sp-form-grid">
                        @foreach ($socials as $s)
                            <div class="sp-field">
                                <label class="sp-label" for="{{ $s['name'] }}">
                                    <i class="{{ $s['icon'] }}"></i> {{ $s['label'] }}
                                </label>
                                <input type="{{ $s['type'] }}" name="{{ $s['name'] }}" id="{{ $s['name'] }}" class="sp-input"
                                       placeholder="{{ $s['placeholder'] }}"
                                       value="{{ old($s['name'], $user->{$s['name']}) }}">
                                @error($s['name'])<p class="sp-error">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>

                    <p class="sp-section-label">Vérification d'identité</p>

                    <div class="sp-field">
                        <label class="sp-label" for="identity_verification">Pièce d'identité</label>
                        <input type="file" name="identity_verification" id="identity_verification"
                               class="sp-input sp-file" accept="image/*">
                        <span class="sp-help">Carte d'identité, passeport ou permis. Ce document reste confidentiel.</span>
                        @error('identity_verification')<p class="sp-error">{{ $message }}</p>@enderror

                        @if ($user->identity_verification)
                            <a href="{{ asset('storage/'.$user->identity_verification) }}" target="_blank"
                               rel="noopener" class="sp-doc-link">Voir le document actuel</a>
                        @endif
                    </div>

                    <div class="sp-pane-actions">
                        <span></span>
                        <button type="submit" class="sp-btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<script>
    // ── Onglets ──
    const TABS = ['informations', 'securite', 'reseaux'];

    function switchTab(name) {
        TABS.forEach(function (t) {
            document.getElementById('tab-' + t).hidden = true;
            document.getElementById('tab-btn-' + t).classList.remove('is-active');
        });

        document.getElementById('tab-' + name).hidden = false;
        document.getElementById('tab-btn-' + name).classList.add('is-active');
    }

    // On ouvre directement l'onglet qui porte une erreur de validation
    @if ($errors->updatePassword->isNotEmpty())
        switchTab('securite');
    @elseif ($socialErreur)
        switchTab('reseaux');
    @endif

    // ── Aperçu de la photo ──
    function previewPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => document.getElementById('avatar-preview').src = e.target.result;
            reader.readAsDataURL(input.files[0]);
        }
    }

    // ── Force du mot de passe ──
    function updateStrength(value) {
        let score = 0;
        if (value.length >= 8) score++;
        if (value.length >= 12) score++;
        if (/[0-9]/.test(value) && /[a-zA-Z]/.test(value)) score++;
        if (/[^a-zA-Z0-9]/.test(value)) score++;

        const colors = ['', '#c0392b', '#b47500', '#1d6ad4', '#1a8245'];
        const labels = ['', 'Trop faible', 'Faible', 'Moyen', 'Fort'];
        const label = document.getElementById('strength-label');

        for (let i = 1; i <= 4; i++) {
            document.getElementById('sb' + i).style.background = i <= score ? colors[score] : '#e9ecef';
        }

        label.textContent = value.length ? (labels[score] || '') : '';
        label.style.color = colors[score] || '#6c757d';
    }

    // ── Rôles partenaire ──
    function bindToggle(id, url, payload, onSuccess) {
        const input = document.getElementById(id);
        if (!input) return;

        input.addEventListener('change', function () {
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(payload(this.checked ? 1 : 0)),
            })
            .then(r => r.json())
            .then(onSuccess)
            .catch(() => {
                input.checked = !input.checked;
                alert('La mise à jour du rôle a échoué. Réessayez.');
            });
        });
    }

    bindToggle('vtc-toggle', '{{ route('profile.toggleVtc') }}',
        v => ({ is_vtc_driver: v }),
        data => { if (data.is_vtc_driver) window.location.href = '{{ route('livreur.carte.vtc') }}'; });

    bindToggle('livreur-toggle', '{{ route('profile.toggleLivreur') }}',
        v => ({ is_livreur: v }),
        data => {
            // Activation : meme parcours que le chauffeur VTC, on envoie
            // directement sur les pieces a fournir. La page n'affiche que
            // celles qu'exige le profil livreur.
            if (data.is_livreur) {
                window.location.href = '{{ route('livreur.carte.vtc') }}';
                return;
            }

            const el = document.getElementById('role-status');
            if (el) el.textContent = data.roles && data.roles.length ? data.roles.join(', ') : 'Aucun rôle actif';
        });
</script>
@endsection
