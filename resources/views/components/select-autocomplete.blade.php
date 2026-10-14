{{--
    Charge la recherche dans les listes deroulantes (Tom Select) pour les
    <select data-autocomplete="Texte d'invite…"> de la page.

        <select name="category_id" id="category_id" class="sp-input" data-autocomplete="Rechercher une catégorie…">
            <option value="{{ $category->id }}" data-icon="…" data-service-name="Location">…</option>
        </select>
        <x-select-autocomplete />

    Version de Tom Select figee. Comportement : assets/js/select-autocomplete.js.
--}}
@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
        <link rel="stylesheet" href="{{ asset('assets/css/select-autocomplete.css') }}?v={{ @filemtime(public_path('assets/css/select-autocomplete.css')) ?: 1 }}">
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
        <script src="{{ asset('assets/js/select-autocomplete.js') }}?v={{ @filemtime(public_path('assets/js/select-autocomplete.js')) ?: 1 }}"></script>
    @endpush
@endonce
