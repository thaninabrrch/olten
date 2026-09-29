@extends('layouts.main')

@php
    /*
     | Gabarit commun des pages legales : bandeau illustre, sommaire de la
     | page, texte (@section('legal')), puis les autres documents et un
     | encart de contact.
     |
     | Les informations de l'editeur viennent de config('olten.legal') et les
     | coordonnees de config('olten.email') : elles se renseignent une fois,
     | a un seul endroit. Ces textes sont une base de travail, a faire
     | valider par un professionnel du droit avant la mise en ligne.
     */
    $site    = config('olten.legal.site');
    $contact = config('olten.email.contact');

    $legalPages = [
        'legal.mentions' => [
            'label' => 'Mentions légales',
            'icon'  => 'fa-building-columns',
            'illus' => 'mentions',
            'lead'  => "Qui édite et héberge {$site}, comment nous signaler un contenu, et à qui appartiennent les contenus du site.",
        ],
        'legal.cgu' => [
            'label' => 'Conditions générales d\'utilisation',
            'icon'  => 'fa-file-contract',
            'illus' => 'cgu',
            'lead'  => 'Les règles communes à tous les membres : compte, annonces, covoiturage, location, vente et livraison.',
        ],
        'legal.cgv' => [
            'label' => 'Conditions générales de vente',
            'icon'  => 'fa-receipt',
            'illus' => 'cgv',
            'lead'  => 'Prix, paiement, annulations et remboursements : ce qui s\'applique à chaque paiement sur la plateforme.',
        ],
        'legal.privacy' => [
            'label' => 'Politique de confidentialité',
            'icon'  => 'fa-shield-halved',
            'illus' => 'confidentialite',
            'lead'  => 'Les données que nous collectons, pourquoi, combien de temps nous les gardons et comment exercer vos droits.',
        ],
    ];

    $currentRoute = request()->route()?->getName();
    $current      = $legalPages[$currentRoute] ?? reset($legalPages);

    /*
     | Sommaire tire des <h2> du texte : chaque titre recoit une ancre, et le
     | numero d'article (« 3. ... ») passe dans une pastille. Le faire ici
     | plutot qu'a la main dans chaque page garde le sommaire fidele au
     | texte quand un article est ajoute ou renomme.
     */
    $legalHtml = $__env->yieldContent('legal');
    $toc = [];
    $legalHtml = preg_replace_callback('#<h2>(.*?)</h2>#s', function ($m) use (&$toc) {
        $num   = null;
        $title = trim($m[1]);
        if (preg_match('/^(\d+)\.\s*(.+)$/s', $title, $n)) {
            [$num, $title] = [str_pad($n[1], 2, '0', STR_PAD_LEFT), $n[2]];
        }

        $text = html_entity_decode(strip_tags($title), ENT_QUOTES, 'UTF-8');
        $id   = $base = \Illuminate\Support\Str::slug($text);
        for ($i = 2; isset($toc[$id]); $i++) {
            $id = $base.'-'.$i;
        }
        $toc[$id] = ['num' => $num, 'title' => $text];

        return '<h2 id="'.$id.'"'.($num ? '' : ' class="is-plain"').'>'
            .($num ? '<span class="legal-h-num">'.$num.'</span>' : '')
            .'<span>'.$title.'</span>'
            .'<a class="legal-anchor" href="#'.$id.'" aria-label="Lien direct vers cette section">#</a>'
            .'</h2>';
    }, $legalHtml);

    // Temps de lecture indicatif, sur une base de 220 mots par minute.
    $words   = preg_match_all('/[\p{L}\p{N}]+/u', html_entity_decode(strip_tags($legalHtml), ENT_QUOTES, 'UTF-8'));
    $minutes = max(1, (int) round($words / 220));
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/legal.css') }}?v={{ @filemtime(public_path('assets/css/legal.css')) ?: 1 }}">
@endpush

@section('content')
<div class="legal-page">

    <section class="legal-hero">
        <div class="legal-hero-inner">
            <div class="legal-hero-text">
                <nav class="legal-crumbs" aria-label="Fil d'Ariane">
                    <a href="{{ route('home') }}">Accueil</a>
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    <span>Informations légales</span>
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    <span aria-current="page">{{ $current['label'] }}</span>
                </nav>

                <span class="legal-tag">
                    <i class="fa-solid {{ $current['icon'] }}" aria-hidden="true"></i>
                    Informations légales
                </span>
                <h1>@yield('legal_title')</h1>
                <p class="legal-lead">{{ $current['lead'] }}</p>

                <div class="legal-meta">
                    <span class="legal-chip">
                        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                        Mis à jour le
                        {{ \Illuminate\Support\Carbon::parse(config('olten.legal.updated_at'))->translatedFormat('d F Y') }}
                    </span>
                    <span class="legal-chip">
                        <i class="fa-regular fa-clock" aria-hidden="true"></i>
                        {{ $minutes }} min de lecture
                    </span>
                    <button type="button" class="legal-chip legal-chip--btn" data-legal-print>
                        <i class="fa-solid fa-print" aria-hidden="true"></i>
                        Imprimer
                    </button>
                </div>
            </div>

            <div class="legal-hero-art">
                @include('pages.legal.illustrations.'.$current['illus'])
            </div>
        </div>
    </section>

    <div class="legal-shell">

        <aside class="legal-aside">
            <nav class="legal-panel legal-docs" aria-label="Documents légaux">
                <p class="legal-aside-title">Documents</p>
                <ul>
                    @foreach ($legalPages as $routeName => $page)
                        <li>
                            <a href="{{ route($routeName) }}" class="{{ $routeName === $currentRoute ? 'is-active' : '' }}"
                               @if ($routeName === $currentRoute) aria-current="page" @endif>
                                <span class="legal-doc-icon"><i class="fa-solid {{ $page['icon'] }}" aria-hidden="true"></i></span>
                                <span>{{ $page['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            @if (count($toc) > 1)
                <details class="legal-panel legal-toc" open data-legal-toc>
                    <summary>
                        <span class="legal-aside-title">Sur cette page</span>
                        <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                    </summary>
                    <ol>
                        @foreach ($toc as $id => $item)
                            <li>
                                <a href="#{{ $id }}">
                                    <span class="legal-toc-num">{{ $item['num'] ?? str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span>{{ $item['title'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </details>
            @endif
        </aside>

        <div class="legal-main">
            <article class="legal-content">
                {!! $legalHtml !!}
            </article>

            <section class="legal-more" aria-labelledby="legal-more-title">
                <h2 id="legal-more-title" class="legal-section-title">Les autres documents</h2>
                <div class="legal-more-grid">
                    @foreach ($legalPages as $routeName => $page)
                        @continue($routeName === $currentRoute)
                        <a href="{{ route($routeName) }}" class="legal-more-card">
                            <span class="legal-doc-icon"><i class="fa-solid {{ $page['icon'] }}" aria-hidden="true"></i></span>
                            <strong>{{ $page['label'] }}</strong>
                            <p>{{ $page['lead'] }}</p>
                            <span class="legal-more-go">Lire le document <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="legal-help">
                <div class="legal-help-art">
                    @include('pages.legal.illustrations.aide')
                </div>
                <div class="legal-help-text">
                    <h2>Une question sur ces textes ?</h2>
                    <p>Un point vous semble flou ou vous souhaitez exercer un droit : écrivez-nous, notre équipe vous répond dans les meilleurs délais.</p>
                </div>
                <div class="legal-help-actions">
                    <a href="{{ route('contact') }}" class="legal-btn legal-btn--primary">
                        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Nous contacter
                    </a>
                    <a href="mailto:{{ $contact }}" class="legal-btn legal-btn--ghost">
                        <i class="fa-regular fa-envelope" aria-hidden="true"></i> {{ $contact }}
                    </a>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        document.querySelectorAll('[data-legal-print]').forEach(function (btn) {
            btn.addEventListener('click', function () { window.print(); });
        });

        // Sur mobile les documents defilent en onglets : on amene l'onglet
        // courant au centre, sinon il peut rester coupe hors de l'ecran.
        var tabs = document.querySelector('.legal-docs ul');
        var activeTab = tabs && tabs.querySelector('a.is-active');
        if (activeTab && tabs.scrollWidth > tabs.clientWidth) {
            tabs.scrollLeft += activeTab.getBoundingClientRect().left - tabs.getBoundingClientRect().left
                - (tabs.clientWidth - activeTab.offsetWidth) / 2;
        }

        var toc = document.querySelector('[data-legal-toc]');
        if (!toc) return;

        // Sur mobile le sommaire passe au-dessus du texte : replie, il ne
        // repousse pas la lecture, et il se referme apres un choix.
        var mobile = window.matchMedia('(max-width: 900px)');
        if (mobile.matches) toc.removeAttribute('open');
        toc.addEventListener('click', function (e) {
            if (mobile.matches && e.target.closest('a')) toc.removeAttribute('open');
        });

        // Surligne dans le sommaire la section en cours de lecture : la
        // derniere dont le titre est passe sous le header fixe.
        var links = {};
        toc.querySelectorAll('a[href^="#"]').forEach(function (a) {
            links[a.getAttribute('href').slice(1)] = a;
        });
        var headings = Array.prototype.slice.call(document.querySelectorAll('.legal-content h2[id]'));
        if (!headings.length) return;

        var ticking = false;
        function update() {
            ticking = false;
            var active = headings[0].id;
            for (var i = 0; i < headings.length; i++) {
                if (headings[i].getBoundingClientRect().top > 140) break;
                active = headings[i].id;
            }
            Object.keys(links).forEach(function (id) {
                links[id].classList.toggle('is-active', id === active);
            });
        }
        window.addEventListener('scroll', function () {
            if (!ticking) { ticking = true; requestAnimationFrame(update); }
        }, { passive: true });
        update();
    })();
</script>
@endpush
