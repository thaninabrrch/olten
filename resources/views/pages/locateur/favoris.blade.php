@extends('layouts.connected')
@section('title', 'Favoris - Olten')

@section('content')
<div class="breadcrumb">
    <a href="#">Accueil</a>
    <span>></span>
    <span>Favoris</span>
</div>

<h1 class="page-title">Favoris</h1>

<!-- SECTION ANNONCES ENREGISTRÉES -->
<div class="favoris-container">
    <div class="section-header">
        <h2 class="section-title">Annonces enregistrées</h2>
    </div>

    <div class="favoris-list" id="favorisList">
        @forelse ($favorites as $ad)
            <div class="favori-card" data-id="{{ $ad->id }}">
                <div class="favori-image">
                    <img src="{{ $ad->image ? asset('storage/' . $ad->image) : asset('assets/images/no-image.jpg') }}" alt="{{ $ad->title }}">
                </div>
                <div class="favori-content">
                    <h3 class="favori-title">{{ $ad->title }}</h3>
                </div>
                <button class="btn-delete">
                    <i class="fa-solid fa-heart-circle-minus"></i>
                    Supprimer
                </button>
            </div>
        @empty
            <div class="empty-state" id="emptyState">
                <div class="empty-icon">
                    <i class="fa-solid fa-heart-crack"></i>
                </div>
                <h3>Aucun favori enregistré</h3>
            </div>
        @endforelse
        <div class="empty-state d-none" id="emptyState">
            <div class="empty-icon">
                <i class="fa-solid fa-heart-crack"></i>
            </div>
            <h3>Aucun favori enregistré</h3>
        </div>
    </div>

</div>

<script src="{{ asset('assets/js/favoris.js') }}"></script>
@endsection