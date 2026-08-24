{{--
    Filtres de la page service : recherche, ville, fourchette de prix, tri.

    Le formulaire poste en GET sur l'URL courante du service : la categorie
    selectionnee reste donc dans le chemin, les filtres dans la query string.
--}}
@props([
    'action',
    'resetUrl',
    'cities' => [],
])

@php
    $cities     = collect($cities);
    $filterKeys = ['search', 'location', 'min_price', 'max_price', 'sort'];
    $hasFilters = collect($filterKeys)->contains(fn ($key) => request()->filled($key));
@endphp

<form method="GET" action="{{ $action }}" class="cs-filters">
    <div class="cs-filter-field">
        <label class="cs-filter-label" for="cs-search">Recherche</label>
        <div class="cs-search-input">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="cs-search" name="search" value="{{ request('search') }}"
                   placeholder="Que recherchez-vous ?">
        </div>
    </div>

    <div class="cs-filter-field">
        <label class="cs-filter-label" for="cs-location">Localisation</label>
        <div class="cs-search-input">
            <i class="fa-solid fa-location-dot"></i>
            <input type="text" id="cs-location" name="location" value="{{ request('location') }}"
                   placeholder="Toutes les villes" list="cs-cities">
        </div>

        @if($cities->isNotEmpty())
            <datalist id="cs-cities">
                @foreach($cities as $city)
                    <option value="{{ $city }}"></option>
                @endforeach
            </datalist>
        @endif
    </div>

    <div class="cs-filter-field">
        <label class="cs-filter-label" for="cs-min-price">Prix minimum</label>
        <div class="cs-search-input">
            <input type="number" id="cs-min-price" name="min_price" min="0" step="1"
                   value="{{ request('min_price') }}" placeholder="0">
            <span class="cs-filter-suffix">€</span>
        </div>
    </div>

    <div class="cs-filter-field">
        <label class="cs-filter-label" for="cs-max-price">Prix maximum</label>
        <div class="cs-search-input">
            <input type="number" id="cs-max-price" name="max_price" min="0" step="1"
                   value="{{ request('max_price') }}" placeholder="100000">
            <span class="cs-filter-suffix">€</span>
        </div>
    </div>

    <div class="cs-filter-field">
        <label class="cs-filter-label" for="cs-sort">Trier par</label>
        <select id="cs-sort" name="sort" class="cs-sort-select">
            <option value="">Plus récentes</option>
            <option value="price_asc" @selected(request('sort') === 'price_asc')>Prix croissant</option>
            <option value="price_desc" @selected(request('sort') === 'price_desc')>Prix décroissant</option>
            <option value="popular" @selected(request('sort') === 'popular')>Les plus consultées</option>
        </select>
    </div>

    <div class="cs-filter-actions">
        <button type="submit" class="cs-btn-filter">Appliquer les filtres</button>

        @if($hasFilters)
            <a href="{{ $resetUrl }}" class="cs-btn-reset">Réinitialiser</a>
        @endif
    </div>
</form>
