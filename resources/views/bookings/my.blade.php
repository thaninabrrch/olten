@extends('layouts.connected')
@section('title', 'Mes réservations - Olten')

@section('content')

<div class="breadcrumb">
    <a href="#">Accueil</a>
    <span>></span>
    <span>Mes réservations</span>
</div>

<h1 class="page-title">Mes réservations</h1>

<div class="annonces-container">

    <div class="section-header">
        <h2 class="section-title">Mes réservations</h2>
    </div>

    <div class="annonces-list">

        @forelse ($bookings as $booking)

            @php
                $ad = $booking->ad;
            @endphp

            <div class="annonce-card">

                <div class="annonce-details">

                    <h3 class="annonce-title">
                        {{ $ad->title ?? 'Annonce supprimée' }}
                    </h3>

                    <div class="annonce-tags">
                        <span class="tag tag-orange">
                            {{ $ad->category->nom ?? 'Catégorie non définie' }}
                        </span>

                        @if ($booking->booking_status === 'pending')
                            <span class="tag tag-status tag-pending">
                                <i class="fa-solid fa-hourglass-half"></i>
                                En attente
                            </span>

                        @elseif ($booking->booking_status === 'confirmed')
                            <span class="tag tag-status tag-approved">
                                <i class="fa-solid fa-circle-check"></i>
                                Confirmée
                            </span>

                        @elseif ($booking->booking_status === 'cancelled')
                            <span class="tag tag-status tag-rejected">
                                <i class="fa-solid fa-circle-xmark"></i>
                                Annulée
                            </span>

                        @elseif ($booking->booking_status === 'completed')
                            <span class="tag tag-status tag-expired">
                                <i class="fa-solid fa-flag-checkered"></i>
                                Terminée
                            </span>
                        @endif
                    </div>

                    <div class="annonce-location">
                        <i class="fa-solid fa-user"></i>
                        Propriétaire annonce :
                        {{ $booking->ad->user->firstname ?? '' }}
                        {{ $booking->ad->user->name ?? '' }}
                    </div>

                    <div class="annonce-stats">

                        <span class="stat-item">
                            <i class="fa-solid fa-calendar-days"></i>
                            Début :
                            {{ \Carbon\Carbon::parse($booking->start_date)->format('d/m/Y') }}
                        </span>

                        <span class="stat-item">
                            <i class="fa-solid fa-calendar-check"></i>
                            Fin :
                            {{ \Carbon\Carbon::parse($booking->end_date)->format('d/m/Y') }}
                        </span>

                        <span class="stat-item">
                            <i class="fa-solid fa-money-bill"></i>
                            Total :
                            {{ number_format($booking->total_price, 2, ',', ' ') }} €
                        </span>

                        <span class="stat-item">
                            <i class="fa-solid fa-clock"></i>
                            Reçue le :
                            {{ $booking->created_at->format('d/m/Y H:i') }}
                        </span>

                    </div>

                </div>

                <div class="annonce-actions">
                    <a href="{{ route('ads.show', $ad) }}" class="btn-action btn-view">
                        <i class="fa-solid fa-eye"></i>
                        Voir l'annonce
                    </a>
                    @if($booking->delivery_requested)
                        <a href="{{ route('bookings.show', $booking->id) }}" class="btn-action btn-primary">
                            <i class="fas fa-truck"></i>
                            Suivre ma réservation
                        </a>
                    @endif
                </div>

            </div>

        @empty
            <p>Aucune réservation effectuée pour le moment.</p>
        @endforelse

    </div>

    <!-- PAGINATION -->
    <div class="pagination">

        @if ($bookings->onFirstPage())
            <button class="page-btn page-prev" disabled>
                <i class="fa-solid fa-chevron-left"></i>
            </button>
        @else
            <a href="{{ $bookings->previousPageUrl() }}" class="page-btn page-prev">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
        @endif

        @foreach ($bookings->getUrlRange(1, $bookings->lastPage()) as $page => $url)
            @if ($page == $bookings->currentPage())
                <button class="page-btn active">{{ $page }}</button>
            @else
                <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
            @endif
        @endforeach

        @if ($bookings->hasMorePages())
            <a href="{{ $bookings->nextPageUrl() }}" class="page-btn page-next">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        @else
            <button class="page-btn page-next" disabled>
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        @endif

    </div>

</div>

@endsection