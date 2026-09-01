{{--
    Les categories (sous-services) d'un service, en pastilles cliquables.

    Cliquer sur une categorie filtre les annonces :
      /vente                        -> toutes les annonces du service
      /vente/telephones-tablettes   -> uniquement cette categorie

    Meme dessin que les pastilles de la page location (.lv-chip) : une
    categorie se choisit de la meme facon partout sur la plateforme.

    Le nombre d'offres n'apparait plus sur la pastille — il tenait mal sur
    une ligne. Une categorie vide se reconnait a son gris (`is-empty`), et le
    volume exact reste lisible dans le compteur de resultats.
--}}
@props([
    'service',
    'categories',
    'selected' => null,
])

@php
    $totalOffers = $categories->sum('ads_count') + $categories->sum('products_count');
@endphp

@if($categories->isNotEmpty())
    <section class="cs-categories-block">
        <div class="cs-section-head">
            <h2 class="cs-section-title">Catégories</h2>
            <span class="cs-section-hint">Choisissez une catégorie pour affiner les annonces et les produits</span>
        </div>

        <nav class="cs-chips" aria-label="Catégories de {{ $service->display_name }}">
            <a href="{{ route('services.show', $service->slug) }}"
               class="cs-chip {{ $selected ? '' : 'is-active' }} {{ $totalOffers ? '' : 'is-empty' }}"
               @unless($selected) aria-current="page" @endunless>
                <i class="fa-solid fa-border-all"></i>
                Toutes
            </a>

            @foreach($categories as $category)
                @php
                    // Annonces et produits partagent la meme grille : c'est
                    // leur somme qui dit si la categorie a quelque chose.
                    $count    = ($category->ads_count ?? 0) + ($category->products_count ?? 0);
                    $isActive = $selected && $selected->id === $category->id;
                @endphp

                <a href="{{ route('services.category', [$service->slug, $category->slug]) }}"
                   class="cs-chip {{ $isActive ? 'is-active' : '' }} {{ $count ? '' : 'is-empty' }}"
                   @if($isActive) aria-current="page" @endif>
                    <i class="{{ $category->icon_class }}"></i>
                    {{ $category->nom }}
                </a>
            @endforeach
        </nav>
    </section>
@endif
