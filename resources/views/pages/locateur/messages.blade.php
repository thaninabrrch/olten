@extends('layouts.connected')
@section('title', 'Messages - Olten')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="#">Accueil</a> > <span>Tableau de bord</span>
    </div>
    <h1 class="page-title">Messages</h1>
</div>

<div class="messages-layout">
    <!-- Liste des messages -->
    <div class="messages-container">
        <h2 class="section-title">Boîte de réception</h2>
        <div class="messages-list" id="messagesList">
        </div>
    </div>

    <!-- Détail de la conversation -->
    <div class="conversation-detail" id="conversationDetail">
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fa-solid fa-comments"></i>
            </div>
            <h3>Sélectionnez une conversation</h3>
            <p>Choisissez un message dans la liste pour voir les détails</p>
        </div>
    </div>
</div>
<script>
    const AUTH_ID = {{ auth()->id() }};
    const AUTH_NAME = @json(auth()->user()->name);
</script>
<script src="{{ asset('assets/js/messages.js') }}"></script>
@endsection