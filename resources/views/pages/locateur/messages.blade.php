@extends('layouts.connected')
@section('title', 'Messages - Olten')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/messages.css') }}?v={{ @filemtime(public_path('assets/css/messages.css')) ?: 1 }}">
@endpush

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Messages</span>
    </nav>

    {{--
        Messagerie : conversations a gauche, fil a droite. Sous 900px, un seul
        panneau a la fois : la liste, puis le fil en plein ecran.
        Tout le contenu est rempli par assets/js/messages.js ; les adresses lui
        sont passees ici pour que les noms de route restent la seule source.
    --}}
    <div class="ib" data-ib
         data-list-url="{{ route('messages.index') }}"
         data-thread-url="{{ route('messages.show', ['user' => '__ID__']) }}"
         data-me="{{ auth()->id() }}">

        {{-- ---------- Conversations ---------- --}}
        <aside class="ib-side" aria-label="Conversations">
            <header class="ib-side-head">
                <div class="ib-side-title">
                    <h1>Messages</h1>
                    <span class="ib-pill" data-ib-total hidden></span>
                </div>

                <label class="ib-search">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input type="search" data-ib-search placeholder="Rechercher une conversation"
                           aria-label="Rechercher une conversation" autocomplete="off">
                </label>

                <div class="ib-filters" role="tablist" aria-label="Filtrer les conversations">
                    <button type="button" class="ib-filter is-active" role="tab" aria-selected="true" data-ib-filter="all">
                        Toutes
                    </button>
                    <button type="button" class="ib-filter" role="tab" aria-selected="false" data-ib-filter="unread">
                        Non lues <span class="ib-filter-count" data-ib-unread-count></span>
                    </button>
                </div>
            </header>

            <div class="ib-list" data-ib-list>
                {{-- Squelette de chargement, remplace par la liste --}}
                @for ($i = 0; $i < 6; $i++)
                    <div class="ib-skel" aria-hidden="true">
                        <span class="ib-skel-avatar"></span>
                        <span class="ib-skel-lines"><span></span><span></span></span>
                    </div>
                @endfor
            </div>
        </aside>

        {{-- ---------- Fil de discussion ---------- --}}
        <section class="ib-thread" data-ib-thread aria-label="Conversation">

            {{-- Aucun fil ouvert --}}
            <div class="ib-welcome" data-ib-welcome>
                <div class="ib-welcome-art" aria-hidden="true">
                    <span class="ib-art-bubble is-in"></span>
                    <span class="ib-art-bubble is-out"></span>
                    <span class="ib-art-bubble is-in is-short"></span>
                    <span class="ib-art-icon"><i class="fa-regular fa-comments"></i></span>
                </div>
                <h2>Vos conversations</h2>
                <p>Sélectionnez une conversation pour lire vos messages et répondre aux acheteurs, vendeurs et loueurs.</p>
                <p class="ib-welcome-tip">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    Restez sur Olten pour échanger et payer : ne communiquez jamais vos coordonnées bancaires.
                </p>
            </div>

            <header class="ib-head" data-ib-head hidden>
                <button type="button" class="ib-icon-btn ib-back" data-ib-back aria-label="Retour aux conversations">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>

                <span class="ib-head-avatar" data-ib-head-avatar></span>

                <div class="ib-head-text">
                    <h2 data-ib-head-name></h2>
                    <p data-ib-head-meta></p>
                </div>
            </header>

            <div class="ib-scroll" data-ib-scroll hidden>
                <div class="ib-messages" data-ib-messages></div>
            </div>

            <button type="button" class="ib-jump" data-ib-jump hidden>
                <i class="fa-solid fa-arrow-down"></i> Nouveaux messages
            </button>

            <form class="ib-composer" data-ib-composer hidden novalidate>
                <p class="ib-composer-error" data-ib-error role="alert" hidden></p>

                <div class="ib-file" data-ib-file hidden>
                    <span class="ib-file-icon"><i class="fa-solid fa-paperclip"></i></span>
                    <span class="ib-file-text">
                        <strong data-ib-file-name></strong>
                        <small data-ib-file-size></small>
                    </span>
                    <button type="button" class="ib-icon-btn" data-ib-file-remove aria-label="Retirer la pièce jointe">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="ib-composer-row">
                    <button type="button" class="ib-icon-btn" data-ib-attach
                            aria-label="Joindre un fichier" title="Joindre un fichier (10 Mo max.)">
                        <i class="fa-solid fa-paperclip"></i>
                    </button>
                    <input type="file" data-ib-file-input hidden
                           accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt">

                    <textarea rows="1" maxlength="2000" data-ib-input
                              placeholder="Écrire un message…" aria-label="Votre message"></textarea>

                    <button type="submit" class="ib-send" data-ib-send disabled aria-label="Envoyer">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>

                <p class="ib-composer-hint" aria-hidden="true">
                    <kbd>Entrée</kbd> pour envoyer · <kbd>Maj</kbd> + <kbd>Entrée</kbd> pour aller à la ligne
                </p>
            </form>

            {{-- Annonce des nouveaux messages aux lecteurs d'ecran --}}
            <p class="ib-sr" data-ib-live aria-live="polite"></p>
        </section>
    </div>
</div>

{{-- Etats vides, clones par le script --}}
<template id="ib-empty-all">
    <div class="ib-list-empty">
        <span class="ib-list-empty-icon"><i class="fa-regular fa-comment-dots"></i></span>
        <strong>Aucune conversation</strong>
        <p>Contactez un vendeur ou un loueur depuis une annonce : vos échanges apparaîtront ici.</p>
        <a class="ib-btn" href="{{ route('search') }}">
            <i class="fa-solid fa-magnifying-glass"></i> Parcourir les offres
        </a>
    </div>
</template>

<template id="ib-empty-unread">
    <div class="ib-list-empty">
        <span class="ib-list-empty-icon is-green"><i class="fa-solid fa-check"></i></span>
        <strong>Vous êtes à jour</strong>
        <p>Aucun message non lu.</p>
    </div>
</template>

<template id="ib-empty-search">
    <div class="ib-list-empty">
        <span class="ib-list-empty-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
        <strong>Aucun résultat</strong>
        <p>Aucune conversation ne correspond à cette recherche.</p>
    </div>
</template>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/messages.js') }}?v={{ @filemtime(public_path('assets/js/messages.js')) ?: 1 }}"></script>
@endpush
