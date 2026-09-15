@extends('layouts.connected')
@section('title', 'Archives - Olten')

@php
    /*
     | Archives du membre, tous types confondus (voir App\Support\Archive) :
     |   - annonces dont la periode de disponibilite est terminee
     |   - produits epuises ou mis hors ligne
     |   - trajets dont le jour de depart est passe
     |
     | Aucun de ces elements n'est plus visible sur la plateforme. Chaque carte
     | porte le moyen de le remettre en ligne : nouvelles dates pour une
     | annonce ou un trajet, stock ou reactivation pour un produit.
     */
    $tabs = ['' => 'Tout'] + \App\Support\Archive::TYPES;

    $tabCount = fn (string $value) => $value === '' ? $counts['total'] : ($counts[$value] ?? 0);

    // L'onglet ouvert suit la recherche, et inversement
    $keepType = array_filter(['type' => $type]);
@endphp

@section('content')
<div class="sp-page">

    {{-- Fil d'ariane --}}
    <nav class="sp-crumbs" aria-label="Fil d'ariane">
        <a href="{{ url('/') }}">Accueil</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Archives</span>
    </nav>

    {{-- En-tete --}}
    <header class="sp-head">
        <div>
            <h1 class="sp-title">Archives</h1>
            <p class="sp-subtitle">Vos annonces, produits et trajets qui ne sont plus en ligne.</p>
        </div>
    </header>

    <div class="sp-note">
        <i class="fa-solid fa-circle-info"></i>
        Rien de ce qui est archivé n'apparaît sur la plateforme. Une <strong>annonce</strong> est archivée
        le lendemain de sa fin de disponibilité, un <strong>produit</strong> dès que son stock est épuisé ou
        qu'il est mis hors ligne, un <strong>trajet</strong> le lendemain de son départ.
        « Remettre en ligne » vous mène à ce qu'il faut modifier.
    </div>

    {{-- Panneau --}}
    <section class="sp-panel">

        <div class="sp-toolbar">
            <div>
                <h2 class="sp-toolbar-title">{{ $type ? \App\Support\Archive::TYPES[$type] . ' archivés' : 'Tout ce qui est archivé' }}</h2>
                <span class="sp-count">
                    @if($search)
                        {{ $items->total() }} résultat{{ $items->total() > 1 ? 's' : '' }} pour « {{ $search }} »
                    @else
                        {{ $items->total() }} élément{{ $items->total() > 1 ? 's' : '' }} archivé{{ $items->total() > 1 ? 's' : '' }}
                    @endif
                </span>
            </div>

            <div class="sp-toolbar-actions">
                <form method="GET" action="{{ route('archives') }}" class="sp-search" role="search">
                    @if($type)
                        <input type="hidden" name="type" value="{{ $type }}">
                    @endif

                    <div class="sp-search-field">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" class="sp-search-input"
                               placeholder="Titre, produit, ville..."
                               value="{{ $search }}"
                               aria-label="Rechercher dans les archives">

                        @if($search)
                            <a href="{{ route('archives', $keepType) }}"
                               class="sp-search-clear" title="Effacer la recherche" aria-label="Effacer la recherche">&times;</a>
                        @endif
                    </div>

                    <button type="submit" class="sp-search-submit">Rechercher</button>
                </form>
            </div>
        </div>

        {{-- Onglets par type --}}
        <div class="sp-tabs">
            @foreach($tabs as $value => $label)
                <a href="{{ route('archives', array_filter(['type' => $value, 'search' => $search])) }}"
                   class="sp-tab {{ (string) $type === $value ? 'is-active' : '' }}">
                    {{ $label }}
                    <span class="sp-tab-count">{{ $tabCount($value) }}</span>
                </a>
            @endforeach
        </div>

        @if($items->count())
            <div class="sp-grid">
                @foreach ($items as $item)
                    <article class="sp-card is-out">

                        <a href="{{ $item['show_url'] }}" class="sp-media" title="Voir la fiche">
                            <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" loading="lazy">

                            <div class="sp-media-badges">
                                <span class="sp-badge is-type">
                                    <i class="{{ $item['type_icon'] }}"></i> {{ $item['type_label'] }}
                                </span>
                                <span class="sp-badge is-out">
                                    <i class="fa-solid fa-box-archive"></i> {{ $item['reason'] }}
                                </span>
                            </div>
                        </a>

                        <div class="sp-body">
                            <div class="sp-list-main">
                                <span class="sp-chip">
                                    <i class="fa-solid fa-tag"></i>
                                    {{ $item['category'] }}
                                </span>

                                <a href="{{ $item['show_url'] }}" class="sp-name">{{ $item['title'] }}</a>

                                <div class="sp-price">
                                    {{ number_format($item['price'], 2, ',', ' ') }} €
                                    @if ($item['price_suffix'])
                                        <small>{{ $item['price_suffix'] }}</small>
                                    @endif
                                </div>
                            </div>

                            <div class="sp-meta">
                                <span class="sp-tag is-danger">
                                    <i class="{{ $item['detail_icon'] }}"></i>
                                    {{ $item['detail'] }}
                                </span>

                                @if ($item['views'] !== null)
                                    <span class="sp-tag">
                                        <i class="fa-regular fa-eye"></i>
                                        {{ $item['views'] }} vue{{ $item['views'] > 1 ? 's' : '' }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="sp-actions">
                            <a href="{{ $item['restore_url'] }}" class="sp-act is-edit" title="{{ $item['restore_hint'] }}">
                                Remettre en ligne
                            </a>

                            <form action="{{ $item['delete_url'] }}" method="POST"
                                  data-archive-delete data-name="{{ $item['title'] }}" data-type="{{ \Illuminate\Support\Str::lower($item['type_label']) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="sp-act is-delete" aria-label="Supprimer {{ $item['title'] }}">Supprimer</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            @if($items->hasPages())
                <div class="sp-pagination">
                    {{ $items->links() }}
                </div>
            @endif
        @else
            <div class="sp-empty">
                @if($search)
                    <x-empty-state
                        title="Aucun élément archivé ne correspond"
                        text="Modifiez votre recherche ou affichez de nouveau toutes vos archives."
                        :action-url="route('archives', $keepType)"
                        action-label="Effacer la recherche" />
                @else
                    <x-empty-state
                        title="Rien d'archivé pour le moment"
                        text="Vos annonces expirées, produits épuisés ou hors ligne et trajets passés seront rangés ici." />
                @endif
            </div>
        @endif
    </section>
</div>

<script>
    // Confirmation de suppression : meme dialogue que la liste des produits
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-archive-delete]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (form.dataset.confirmed === '1') return;
                e.preventDefault();

                const name = form.dataset.name || 'cet élément';
                const valider = function () {
                    form.dataset.confirmed = '1';
                    form.submit();
                };

                if (typeof Swal === 'undefined') {
                    if (confirm('Supprimer « ' + name + ' » ? Cette action est définitive.')) valider();
                    return;
                }

                Swal.fire({
                    title: form.dataset.type === 'annonce' ? 'Supprimer cette annonce ?' : 'Supprimer ce ' + form.dataset.type + ' ?',
                    html: '« <strong>' + name.replace(/</g, '&lt;') + '</strong> » : cette suppression est définitive.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler',
                    confirmButtonColor: '#c0392b',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                }).then(function (result) {
                    if (result.isConfirmed) valider();
                });
            });
        });
    });
</script>
@endsection
