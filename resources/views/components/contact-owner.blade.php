{{--
    Popin « Message » d'une fiche produit ou annonce : le membre ecrit au
    proprietaire sans quitter la page.

    Le message part en fetch vers `contact.owner`, qui l'enregistre dans la
    messagerie et previent le proprietaire par e-mail. Le bouton qui l'ouvre
    porte `data-cm-open` ; pour un visiteur, il porte aussi
    `data-auth-required` et la popin de connexion le ramene ici avec
    `?contacter=1`, ce qui rouvre la fenetre (assets/js/contact-owner.js).

    Utilisation :

        <x-contact-owner :owner="$seller" listing-type="product" :listing-id="$product->id"
                         :title="$product->name" price="12,00 €" price-label="l'unité"
                         :image="$sources[0]" kind="vente" />
--}}
@props([
    'owner',
    // « product » ou « ad » : le controleur en deduit le destinataire.
    'listingType',
    'listingId',
    'title',
    'price' => null,
    'priceLabel' => null,
    'image' => null,
    // « vente » ou « location » : choisit les questions rapides proposees.
    'kind' => 'vente',
])

@auth
@if ($owner && auth()->id() !== $owner->id)
@php
    $prenom = $owner->firstname ?: $owner->name;
    $avatar = $owner->profile_photo ? asset('storage/' . $owner->profile_photo) : null;

    // Les questions les plus posees sur une fiche : un clic les ajoute au
    // message, precedees d'une salutation si le champ est encore vide.
    $suggestions = $kind === 'location'
        ? [
            ['label' => 'Disponibilités',   'icon' => 'fa-regular fa-calendar',     'text' => 'Le bien est-il disponible aux dates qui m’intéressent ?'],
            ['label' => 'Caution',          'icon' => 'fa-solid fa-shield-halved',  'text' => 'Une caution est-elle demandée ? Si oui, de quel montant ?'],
            ['label' => 'Remise et retour', 'icon' => 'fa-solid fa-right-left',     'text' => 'Comment se passent la remise et le retour du bien ?'],
            ['label' => 'Livraison',        'icon' => 'fa-solid fa-truck-fast',     'text' => 'Une livraison est-elle possible ? Si oui, à quel tarif ?'],
        ]
        : [
            ['label' => 'Toujours disponible ?', 'icon' => 'fa-regular fa-circle-check', 'text' => 'Est-il toujours disponible ?'],
            ['label' => 'Prix négociable ?',     'icon' => 'fa-solid fa-tag',            'text' => 'Le prix est-il négociable ?'],
            ['label' => 'Livraison',             'icon' => 'fa-solid fa-truck-fast',     'text' => 'Une livraison est-elle possible ? Si oui, à quel tarif ?'],
            ['label' => 'Remise en main propre', 'icon' => 'fa-regular fa-handshake',    'text' => 'Une remise en main propre est-elle possible ? Où et quand seriez-vous disponible ?'],
        ];
@endphp

<div class="cm" id="contact-owner" role="dialog" aria-modal="true"
     aria-labelledby="cm-title" aria-describedby="cm-meta" data-cm hidden>

    <div class="cm-backdrop" data-cm-close></div>

    <div class="cm-card" tabindex="-1" data-cm-card>
        <span class="cm-grip" aria-hidden="true"></span>

        <form class="cm-form" action="{{ route('contact.owner') }}" method="POST" novalidate data-cm-form>
            @csrf
            <input type="hidden" name="listing_type" value="{{ $listingType }}">
            <input type="hidden" name="listing_id" value="{{ $listingId }}">

            <header class="cm-head">
                <span class="cm-avatar">
                    @if ($avatar)
                        <img src="{{ $avatar }}" alt="">
                    @else
                        <span class="cm-avatar-initial">{{ strtoupper(mb_substr($owner->name, 0, 1)) }}</span>
                    @endif

                    @if ($owner->is_approved)
                        <span class="cm-avatar-badge" title="Profil vérifié"><i class="fa-solid fa-check"></i></span>
                    @endif
                </span>

                <div class="cm-head-text">
                    <p class="cm-eyebrow">Nouveau message</p>
                    <h2 class="cm-title" id="cm-title">Écrire à {{ $prenom }}</h2>
                    <p class="cm-meta" id="cm-meta">
                        @if ($owner->is_approved)
                            <span class="cm-verified"><i class="fa-solid fa-circle-check"></i> Profil vérifié</span>
                        @endif
                        @if ($owner->is_approved && $owner->created_at)
                            <span class="cm-dot" aria-hidden="true"></span>
                        @endif
                        @if ($owner->created_at)
                            <span>Membre depuis {{ $owner->created_at->translatedFormat('F Y') }}</span>
                        @endif
                    </p>
                </div>

                <button type="button" class="cm-close" data-cm-close aria-label="Fermer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </header>

            <div class="cm-body">
                {{-- Rappel de l'offre : on sait toujours de quoi on parle --}}
                <div class="cm-listing">
                    @if ($image)
                        <img class="cm-listing-thumb" src="{{ $image }}" alt="">
                    @endif

                    <span class="cm-listing-text">
                        <small>À propos de</small>
                        <strong>{{ $title }}</strong>
                    </span>

                    @if ($price)
                        <span class="cm-listing-price">
                            {{ $price }}
                            @if ($priceLabel)
                                <small>{{ $priceLabel }}</small>
                            @endif
                        </span>
                    @endif
                </div>

                <div class="cm-quick">
                    <p class="cm-label" id="cm-quick-label">Questions rapides</p>

                    <div class="cm-chips" role="group" aria-labelledby="cm-quick-label">
                        @foreach ($suggestions as $s)
                            <button type="button" class="cm-chip" data-cm-chip="{{ $s['text'] }}" aria-pressed="false">
                                <i class="{{ $s['icon'] }}" aria-hidden="true"></i>
                                {{ $s['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="cm-field">
                    <label class="cm-label" for="cm-message">
                        Votre message
                        <span class="cm-count" data-cm-count>0 / 2000</span>
                    </label>

                    <textarea id="cm-message" name="message" class="cm-textarea" rows="4" maxlength="2000"
                              data-cm-text data-cm-greeting="Bonjour {{ $prenom }},"
                              placeholder="Bonjour {{ $prenom }}, votre offre m’intéresse…"></textarea>
                </div>

                <p class="cm-alert" data-cm-alert role="alert" hidden>
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span></span>
                </p>

                <div class="cm-safety">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    <span>
                        <strong>Restez sur Olten pour échanger et payer.</strong>
                        Ne communiquez jamais vos coordonnées bancaires ni un code reçu par SMS.
                    </span>
                </div>
            </div>

            <footer class="cm-foot">
                <span class="cm-hint" aria-hidden="true">
                    <kbd data-cm-mod>Ctrl</kbd> + <kbd>Entrée</kbd> pour envoyer
                </span>

                <div class="cm-foot-actions">
                    <button type="button" class="cm-btn is-ghost" data-cm-close>Annuler</button>
                    <button type="submit" class="cm-btn is-primary" data-cm-submit disabled>
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Envoyer</span>
                    </button>
                </div>
            </footer>
        </form>

        {{-- Confirmation, une fois le message enregistre --}}
        <div class="cm-done" data-cm-done tabindex="-1" hidden>
            <div class="cm-done-icon" aria-hidden="true">
                <span class="cm-done-ring"></span>
                <i class="fa-solid fa-paper-plane"></i>
            </div>

            <h3>Message envoyé</h3>
            <p data-cm-done-text>Votre message a bien été envoyé à {{ $prenom }}.</p>
            <p class="cm-done-sub">
                Un e-mail lui signale votre message. Sa réponse arrivera dans votre messagerie Olten.
            </p>

            <div class="cm-done-actions">
                <a class="cm-btn is-primary" href="{{ route('messages', ['avec' => $owner->id]) }}" data-cm-conversation>
                    <i class="fa-regular fa-comments"></i>
                    <span>Voir la conversation</span>
                </a>
                <button type="button" class="cm-btn is-ghost" data-cm-close>Continuer ma visite</button>
            </div>
        </div>
    </div>
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/contact-owner.css') }}?v={{ @filemtime(public_path('assets/css/contact-owner.css')) ?: 1 }}">
    @endpush
    @push('scripts')
        <script src="{{ asset('assets/js/contact-owner.js') }}?v={{ @filemtime(public_path('assets/js/contact-owner.js')) ?: 1 }}"></script>
    @endpush
@endonce
@endif
@endauth
