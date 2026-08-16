@extends('layouts.connected')
@section('title', 'Mes annonces - Olten')

@section('content')
    <div class="breadcrumb">
        <a href="#">Accueil</a>
        <span>></span>
        <span>Mes annonces</span>
    </div>

    <h1 class="page-title">Mes annonces</h1>
    @php
        use Carbon\Carbon;
    @endphp
    <!-- SECTION MES ANNONCES -->
    <div class="annonces-container">
        <div class="section-header">
            <h2 class="section-title">Annonces actives</h2>
            <div class="search-filters">
                <form method="GET" action="{{ route('ads.index') }}">
                    <input type="text" name="search" class="search-input" placeholder="Rechercher une annonce"
                        value="{{ request('search') }}">

                    <select name="category_id" class="filter-select">
                        <option value="all" {{ request('category_id') == 'all' ? 'selected' : '' }}>
                            Toutes les catégories
                        </option>
                        @forelse($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->nom }}
                            </option>
                        @empty
                            <option value="">Aucune catégorie disponible</option>
                        @endforelse
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
            </div>
        </div>

        <!-- LISTE DES ANNONCES -->
        <div class="annonces-list">
            @forelse ($ads as $ad)
                <div class="annonce-card">
                    <div class="annonce-image">
                        <img src="{{ $ad->images->first() ? asset('storage/' . $ad->images->first()->path) : asset('assets/images/no-image.jpg') }}"
                            alt="{{ $ad->title }}">
                    </div>
                    <div class="annonce-details">
                        <h3 class="annonce-title">{{ $ad->title }}</h3>
                        <div class="annonce-tags">
                            <span class="tag tag-orange">
                                {{ $ad->category->nom ?? 'Catégorie non définie' }}
                            </span>
                            @if ($ad->is_approved)
                                <span class="tag tag-status tag-approved">
                                    <i class="fa-solid fa-circle-check"></i> Approuvée
                                </span>
                            @elseif($ad->rejected_at)
                                <span class="tag tag-status tag-rejected">
                                    <i class="fa-solid fa-circle-xmark"></i> Refusée
                                </span>
                            @elseif($ad->expires_at && $ad->expires_at->isPast())
                                <span class="tag tag-status tag-expired">
                                    <i class="fa-solid fa-clock"></i> Expirée
                                </span>
                            @else
                                <span class="tag tag-status tag-pending">
                                    <i class="fa-solid fa-hourglass-half"></i> En attente de validation
                                </span>
                            @endif
                        </div>

                        @if ($ad->address)
                            <div class="annonce-location">
                                <i class="fa-solid fa-location-dot"></i>
                                {{ $ad->address }}
                            </div>
                        @endif
                        <div class="annonce-stats">
                            <span class="stat-item">
                                <i class="fa-solid fa-eye"></i>
                                Vues : {{ $ad->views ?? 0 }}
                            </span>
                            <span class="stat-item">
                                <i class="fa-solid fa-calendar"></i>
                                Expirant : {{ $ad->expires_at ? $ad->expires_at->format('d/m/Y') : 'Jamais/non défini' }}
                            </span>
                            <span class="stat-item">
                                <i class="fa-solid fa-truck"></i>
                                Livraison : {{ $ad->delivery_active ? 'disponible' : 'Non disponible' }}
                            </span>
                        </div>
                    </div>
                    @if ($ad->expires_at && Carbon::parse($ad->expires_at)->toDateString() < now()->toDateString())
                        <span class="expired">Expirée</span>
                    @endif
                    <div class="annonce-actions">
                        <a href="{{ route('ads.ical', $ad) }}" class="btn-action btn-ical" title="Exporter en iCal">
                            <i class="fa-solid fa-calendar-plus"></i> iCal
                        </a>

                        <a href="{{ route('ads.edit', $ad) }}" class="btn-action btn-edit" title="Modifier l'annonce">
                            <i class="fa-solid fa-pen"></i> Modifier
                        </a>

                        <button type="button" class="btn-action btn-delete" data-bs-toggle="modal"
                            data-bs-target="#deleteAdModal" data-title="{{ $ad->title }}"
                            data-url="{{ route('ads.destroy', $ad) }}" title="Supprimer l'annonce">
                            <i class="fa-solid fa-trash"></i> Supprimer
                        </button>
                    </div>
                </div>
            @empty
                <p>Aucune annonce disponible.</p>
            @endforelse
        </div>

        <!-- PAGINATION -->
        <div class="pagination">
            {{-- Bouton précédent --}}
            @if ($ads->onFirstPage())
                <button class="page-btn page-prev" disabled>
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
            @else
                <a href="{{ $ads->previousPageUrl() }}" class="page-btn page-prev">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            @endif

            @foreach ($ads->getUrlRange(1, $ads->lastPage()) as $page => $url)
                @if ($page == $ads->currentPage())
                    <button class="page-btn active">{{ $page }}</button>
                @else
                    <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                @endif
            @endforeach

            @if ($ads->hasMorePages())
                <a href="{{ $ads->nextPageUrl() }}" class="page-btn page-next">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            @else
                <button class="page-btn page-next" disabled>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            @endif
        </div>
        @include('pages.modals.ad_confim_delete')
        <!-- BOUTON NOUVELLE ANNONCE -->
        <div class="create-annonce-section">
            <a href="{{ route('ads.create') }}" class="btn-create-annonce">
                <i class="fa-solid fa-plus"></i>
                Soumettre une nouvelle annonce
            </a>
        </div>
    </div>
    <script src="{{ asset('assets/js/confirm_delete_ad.js') }}"></script>
@endsection
