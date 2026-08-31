{{--
    Pagination de la plateforme, utilisee par tous les `->links()`.

    Elle remplace la vue livree par Laravel, ecrite pour Tailwind : hors de
    l'admin, ou Tailwind n'est pas charge, ses classes utilitaires ne
    s'appliquent pas et ses deux variantes (mobile et bureau) s'empilent.

    Le style vit dans public/assets/css/pagination.css.
--}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination">
        <ul class="olten-pager">

            {{-- Page precedente --}}
            <li>
                @if ($paginator->onFirstPage())
                    <span class="olten-pager-link is-disabled" aria-disabled="true" aria-label="Page précédente">
                        <i class="fa-solid fa-chevron-left"></i>
                    </span>
                @else
                    <a class="olten-pager-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Page précédente">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                @endif
            </li>

            {{-- Numeros de page --}}
            @foreach ($elements as $element)
                {{-- Rupture dans la numerotation : « ... » --}}
                @if (is_string($element))
                    <li><span class="olten-pager-gap">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span class="olten-pager-link olten-pager-number is-current" aria-current="page">{{ $page }}</span>
                            @else
                                <a class="olten-pager-link olten-pager-number" href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            {{-- Page suivante --}}
            <li>
                @if ($paginator->hasMorePages())
                    <a class="olten-pager-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Page suivante">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                @else
                    <span class="olten-pager-link is-disabled" aria-disabled="true" aria-label="Page suivante">
                        <i class="fa-solid fa-chevron-right"></i>
                    </span>
                @endif
            </li>

            <li class="olten-pager-info">
                {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }} résultat{{ $paginator->total() > 1 ? 's' : '' }}
            </li>
        </ul>
    </nav>
@endif
