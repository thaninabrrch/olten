@extends('layouts.connected')
@section('title', 'Messages - Olten')

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Messages</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Messages</h1>
            <p class="sp-subtitle">Vos échanges avec les acheteurs, vendeurs et loueurs de la plateforme.</p>
        </div>
    </header>

    {{-- Messagerie : liste des conversations + fil de discussion --}}
    <div class="sp-chat">

        <aside class="sp-chat-aside">
            <div class="sp-chat-aside-head">
                <div class="sp-chat-aside-title">
                    <h2>Conversations</h2>
                    <span class="sp-chat-counter" id="convCounter"></span>
                </div>

                <input type="text" class="sp-chat-search" id="convSearch"
                       placeholder="Rechercher un contact..."
                       aria-label="Rechercher une conversation">
            </div>

            {{-- Rempli par assets/js/messages.js --}}
            <div class="sp-conv-list" id="messagesList"></div>
        </aside>

        <div class="sp-thread" id="conversationDetail">
            <div class="sp-thread-empty">
                {{-- Apercu decoratif : suggere un fil de discussion, sans image --}}
                <div class="sp-thread-ghost" aria-hidden="true">
                    <span class="sp-ghost-line is-in"></span>
                    <span class="sp-ghost-line is-out"></span>
                    <span class="sp-ghost-line is-in is-short"></span>
                </div>

                <h3>Sélectionnez une conversation</h3>
                <p>Choisissez un contact dans la liste pour afficher vos échanges.</p>
            </div>
        </div>
    </div>
</div>

<script>
    const AUTH_ID = {{ auth()->id() }};
    const AUTH_NAME = @json(auth()->user()->name);
</script>
<script src="{{ asset('assets/js/messages.js') }}?v={{ @filemtime(public_path('assets/js/messages.js')) ?: 1 }}"></script>
@endsection
