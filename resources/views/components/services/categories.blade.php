{{--
    Les categories (sous-services) d'un service, sous forme d'icones cliquables.

    Cliquer sur une categorie filtre les annonces :
      /vente                        -> toutes les annonces du service
      /vente/telephones-tablettes   -> uniquement cette categorie
--}}
@props([
    'service',
    'categories',
    'selected' => null,
])

@if($categories->isNotEmpty())
    <section class="cs-categories-block">
        <div class="cs-section-head">
            <h2 class="cs-section-title">Catégories</h2>
            <span class="cs-section-hint">Choisissez une catégorie pour affiner les annonces et les produits</span>
        </div>

        <div class="cs-categories">
            <a href="{{ route('services.show', $service->slug) }}"
               class="cs-cat-item {{ $selected ? '' : 'is-active' }}">
                <span class="cs-cat-icon"><i class="fa-solid fa-border-all"></i></span>
                <span class="cs-cat-text">
                    <span class="cs-cat-label">Toutes les catégories</span>
                    <span class="cs-cat-count">
                        {{ $categories->sum('ads_count') + $categories->sum('products_count') }} offre(s)
                    </span>
                </span>
            </a>

            @foreach($categories as $category)
                <a href="{{ route('services.category', [$service->slug, $category->slug]) }}"
                   class="cs-cat-item {{ $selected && $selected->id === $category->id ? 'is-active' : '' }}"
                   title="{{ $category->description }}">
                    <span class="cs-cat-icon">
                        <i class="{{ $category->icon_class }}"></i>
                    </span>
                    <span class="cs-cat-text">
                        <span class="cs-cat-label">{{ $category->nom }}</span>
                        <span class="cs-cat-count">
                            {{ ($category->ads_count ?? 0) + ($category->products_count ?? 0) }} offre(s)
                        </span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>
@endif
