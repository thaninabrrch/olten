@extends('layouts.connected')

@section('title', 'Publier un trajet | ' . config('app.name'))

<!-- Leaflet & Tailwind (Note: En production, mettez-les dans votre layout ou via Vite) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

{{-- Ici l'assistant occupe toute la zone de contenu, sous l'en-tête du
     layout (70px) : à 100vh, ses boutons d'action passaient sous la ligne
     de flottaison. --}}
<style>
    .dashboard-content {
        padding: 0px !important;
    }

    .dashboard-content .app-content-area {
        height: calc(100vh - 70px);
    }
</style>

{{-- L'assistant est partagé avec « Déposer une annonce » (service covoiturage) --}}
@include('livreur.covoiturage.partials.wizard-styles')

@section('content')
    @include('livreur.covoiturage.partials.wizard')

    @include('livreur.covoiturage.partials.wizard-scripts')
@endsection
