{{--
    Sélecteur de catégorie de « Déposer une annonce », groupé par service.

    Le covoiturage n'a pas de catégorie d'annonce (Category::publishable) :
    son option ouvre l'assistant de trajet dans la même page. Une option qui
    porte data-href mène à cette adresse au lieu de changer le mode du
    formulaire (assets/js/ad-form.js). Depuis l'assistant, choisir une
    catégorie ramène au formulaire d'annonce, catégorie présélectionnée.
--}}
<select name="category_id" id="category_id" class="sp-input" required
        data-autocomplete="Rechercher une catégorie…">
    <option value="">Choisir une catégorie</option>
    {{-- Les categories sont des sous-parties d'un service --}}
    @forelse($categories->groupBy(fn ($c) => $c->service->display_name ?? 'Autres') as $serviceName => $group)
        <optgroup label="{{ $serviceName }}">
            @foreach($group as $category)
                <option value="{{ $category->id }}"
                        data-service="{{ $category->service->slug ?? '' }}"
                        data-service-name="{{ $serviceName }}"
                        data-icon="{{ $category->icon_class }}"
                        @if($tripMode) data-href="{{ route('ads.create', ['category' => $category->id]) }}" @endif
                        @selected(! $tripMode && old('category_id', request('category')) == $category->id)>
                    {{ $category->nom }}
                </option>
            @endforeach
        </optgroup>
    @empty
        <option value="" disabled>Aucune catégorie disponible</option>
    @endforelse

    <optgroup label="Covoiturage">
        <option value="covoiturage"
                data-service="covoiturage"
                data-service-name="Covoiturage"
                data-icon="fas fa-car-side"
                data-href="{{ route('ads.create.trip') }}"
                @selected($tripMode)>
            Publier un trajet
        </option>
    </optgroup>
</select>
<x-select-autocomplete />
