@extends('layouts.connected')
@section('title', 'Mes réservations reçues - Olten')

@section('content')
    <div class="breadcrumb">
        <a href="#">Accueil</a>
        <span>></span>
        <span>Mes réservations reçues</span>
    </div>

    <h1 class="page-title">Mes réservations reçues</h1>
    @php
        use Carbon\Carbon;
    @endphp
    <div class="annonces-container">
        <div class="section-header">
            <h2 class="section-title">Les réservations</h2>
            <!-- <div class="search-filters">
                <form method="GET" action="{{ route('ads.index') }}">
                    <input type="text" name="search" class="search-input" placeholder="Rechercher une annonce"
                        value="{{ request('search') }}">

                    <select name="category_id" class="filter-select">
                        <option value="all" {{ request('category_id') == 'all' ? 'selected' : '' }}>
                            Toutes les catégories
                        </option>
                        
                    </select>

                    <select name="status" class="filter-select">
                        <option value="">Tous les statuts</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>
                            ✅ Approuvée
                        </option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>
                            ⏳ En attente
                        </option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>
                            ❌ Refusée
                        </option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>
                            🕐 Expirée
                        </option>
                    </select>

                    <button type="submit" class="btn-search">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </form>
            </div> -->
        </div>

        <div class="annonces-list">
            @forelse ($bookings as $booking)
                @php
                    $ad = $booking->ad;
                @endphp

                <div class="annonce-card">
                    <div class="annonce-details">

                        <h3 class="annonce-title">
                            {{ $ad->title }}
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
                                    Acceptée
                                </span>
                            @elseif ($booking->booking_status === 'cancelled')
                                <span class="tag tag-status tag-rejected">
                                    <i class="fa-solid fa-circle-xmark"></i>
                                    Refusée
                                </span>
                            @endif
                        </div>

                        <div class="annonce-location">
                            <i class="fa-solid fa-user"></i>
                            Demandeur :
                            {{ $booking->user->firstname ?? '' }}
                            {{ $booking->user->name ?? '' }}
                        </div>

                        <div class="annonce-stats">
                            <span class="stat-item">
                                <i class="fa-solid fa-calendar-days"></i>
                                Début : {{ $booking->start_date->format('d/m/Y') }}
                            </span>

                            <span class="stat-item">
                                <i class="fa-solid fa-calendar-check"></i>
                                Fin : {{ $booking->end_date->format('d/m/Y') }}
                            </span>

                            <span class="stat-item">
                                <i class="fa-solid fa-money-bill"></i>
                                Total : {{ number_format($booking->total_price, 2, ',', ' ') }} €
                            </span>

                            <span class="stat-item">
                                <i class="fa-solid fa-clock"></i>
                                Reçue le : {{ $booking->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>

                    </div>

                    <div class="annonce-actions">

                        @if ($booking->stabooking_statustus === 'pending')
                            <form action="{{ route('bookings.accept', $booking) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-action btn-edit">
                                    <i class="fa-solid fa-check"></i>
                                    Accepter
                                </button>
                            </form>

                            <form action="{{ route('bookings.reject', $booking) }}"
                                method="POST"
                                style="display:inline-block;">
                                @csrf
                                @method('PATCH')

                                <button type="submit" class="btn-action btn-delete">
                                    <i class="fa-solid fa-xmark"></i>
                                    Refuser
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('ads.show', $ad) }}"
                            class="btn-action btn-view">
                            <i class="fa-solid fa-eye"></i>
                            Voir l'annonce
                        </a>

                        @if($booking->delivery_requested)
                            <a href="{{ route('bookings.show', $booking->id) }}" class="btn-action btn-primary">
                                <i class="fas fa-truck"></i>
                                Suivre la réservation
                            </a>
                        @endif
                    </div>
                </div>

            @empty
                <p>Aucune demande de réservation reçue.</p>
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
