@extends('layouts.main')

@section('title', 'Accueil - Olten.fr')

@section('content')
<!----HERO SECTION------>
<section class="hero-section">
    <div class="hero-content">

        <span class="hero-badge">
            <i class="fas fa-bolt"></i>
             On loue tout entre nous • Le réflexe collaboratif de proximité.
        </span>

        <h1>
            <span>LOUEZ, VENDEZ, COVOITUREZ</span>
            <span>&amp; FAITES LIVRER</span>
            <span class="highlight">ENTRE NOUS.</span>
        </h1>

        <p>
            Louez, vendez, achetez, faites livrer vos colis, trouvez un trajet
            ou développez votre activité grâce aux services proposés par
            <strong>Olten.fr</strong>.
        </p>

        <!-- Stats -->
        <div class="hero-stats">
            <div class="hero-stat">
                <span class="hero-stat-value">+45 000</span>
                <span class="hero-stat-label">Annonces actives</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-value">100%</span>
                <span class="hero-stat-label">Vérifié &amp; sécurisé</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-value">0€</span>
                <span class="hero-stat-label">Frais cachés</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-value">24/7</span>
                <span class="hero-stat-label">Support client</span>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="hero-actions">
            <a href="{{ route('ads.create') }}" class="hero-btn hero-btn-primary" data-auth-required>
           
                <span>Déposer une annonce</span>
            </a>
            <a href="{{ route('services.index') }}" class="hero-btn hero-btn-ghost">
             
                <span>Explorer nos services</span>
            </a>
        </div>

    </div>
</section>

<!----------Piliers de services-------------->
@if($services->isNotEmpty())
    <section class="categories-section services-section">
        <div class="services-inner">

            <div class="section-header section-header--left">
                <div class="section-heading">
                    <span class="section-eyebrow section-eyebrow--plain">Navigation rapide</span>
                    <h2 class="section-title">
                        Explorez nos services
                    </h2>
                </div>

                <a href="{{ route('services.index') }}" class="section-link">
                    Tous les services
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="services-grid">
                @foreach($services as $service)
                    @php
                        // Teinte de repli, stable pour un service donne, quand
                        // aucune image n'a ete televersee depuis le back-office.
                        $tileHue = ($service->id * 47) % 360;
                    @endphp

                    <a href="{{ route('services.show', $service->slug) }}"
                       class="service-tile"
                       style="--tile-hue: {{ $tileHue }};@if($service->image) --tile-image: url('{{ asset('storage/' . $service->image) }}');@endif">

                        <span class="service-tile-arrow" aria-hidden="true">
                            <i class="fas fa-arrow-right"></i>
                        </span>

                        <span class="service-tile-body">
                            <span class="service-tile-name">{{ $service->display_name }}</span>
                            @if(filled($service->short_description))
                                <span class="service-tile-desc">{{ $service->short_description }}</span>
                            @endif
                        </span>
                    </a>
                @endforeach
            </div>

        </div>
    </section>
@else
    <x-empty-state compact
        title="Aucun service disponible pour le moment"
        text="Les services de la plateforme apparaîtront ici dès qu'ils seront publiés." />
@endif

<!---------- Plus récent annonce / produit -------------->
<section class="annonces-section annonces-section--white">

    <div class="section-header section-header--left">
        <div class="section-heading">
            <span class="section-eyebrow section-eyebrow--plain">Opportunités vérifiées</span>
            <h2 class="section-title">
                Les Annonces Populaires du Moment
            </h2>
        </div>
    </div>

    @if($latestItems->isNotEmpty())

        <div class="annonces-grid">
            @foreach($latestItems as $latest)
                @if($latest->type === 'ad')

                    @php
                        $ad = $latest->item;

                        $isExpired = $ad->expires_at &&
                            \Carbon\Carbon::parse($ad->expires_at)->toDateString() < now()->toDateString();

                        // La description vient d'un editeur riche : on retire
                        // les balises et les entites avant de l'afficher.
                        $excerpt = trim(preg_replace(
                            '/\s+/u',
                            ' ',
                            str_replace("\u{00A0}", ' ', html_entity_decode(
                                strip_tags((string) ($ad->summary ?: $ad->description)),
                                ENT_QUOTES | ENT_HTML5,
                                'UTF-8'
                            ))
                        ));
                    @endphp

                    {{-- ================= ANNONCE ================= --}}

                    <a href="{{ route('ads.show', $ad->id) }}"
                       class="annonce-card {{ $isExpired ? 'expired-card' : '' }}">

                        <div class="card-image-container">

                            <img
                                src="{{ $ad->images->first()
                                    ? asset('storage/' . $ad->images->first()->path)
                                    : asset('assets/images/no-image.jpg') }}"
                                alt="{{ $ad->title }}"
                                class="card-image"
                            >

                            <span class="category-badge">
                                {{ $ad->category->nom ?? 'Catégorie non définie' }}
                            </span>

                            <button
                                type="button"
                                class="favorite-btn"
                                aria-label="Ajouter aux favoris"
                                data-type="ad"
                                data-id="{{ $ad->id }}"
                                data-favorited="{{ auth()->check() && auth()->user()->hasFavorited($ad) ? 'true' : 'false' }}"
                            >
                                <i class="{{ auth()->check() && auth()->user()->hasFavorited($ad) ? 'fas fa-heart' : 'far fa-heart' }}"></i>
                            </button>

                        </div>

                        <div class="card-content">

                            <div class="card-meta">
                                @if(filled($ad->address))
                                    <span class="card-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ $ad->address }}
                                    </span>
                                @endif

                                @if($isExpired)
                                    <span class="expired">Expirée</span>
                                @elseif($ad->delivery_active)
                                    <span class="card-delivery">
                                        <i class="fas fa-truck"></i>
                                        Livraison dispo
                                    </span>
                                @endif
                            </div>

                            <h3 class="card-title">{{ $ad->title }}</h3>

                            @if(filled($excerpt))
                                <p class="card-desc">{{ \Illuminate\Support\Str::limit($excerpt, 160) }}</p>
                            @endif

                            <div class="card-footer">
                                <span class="card-price-block">
                                    <span class="card-price-label">à partir de</span>
                                    <span class="card-price">
                                        {{ number_format($ad->price_per_day, 2) }} €<small>/ jour</small>
                                    </span>
                                </span>

                                <span class="card-cta">Voir détails</span>
                            </div>

                        </div>

                    </a>

                @else

                    @php
                        $product = $latest->item;

                        // La description vient d'un editeur riche : on retire
                        // les balises et les entites avant de l'afficher.
                        $excerpt = trim(preg_replace(
                            '/\s+/u',
                            ' ',
                            str_replace("\u{00A0}", ' ', html_entity_decode(
                                strip_tags((string) $product->description),
                                ENT_QUOTES | ENT_HTML5,
                                'UTF-8'
                            ))
                        ));
                    @endphp

                    {{-- ================= PRODUIT ================= --}}

                    <a href="{{ route('products.show', $product->id) }}" class="annonce-card">

                        <div class="card-image-container">

                            <img
                                src="{{ $product->images->first()
                                    ? asset('storage/' . $product->images->first()->image)
                                    : asset('assets/images/no-image.jpg') }}"
                                alt="{{ $product->name }}"
                                class="card-image"
                            >

                            <span class="category-badge">
                                {{ $product->category->nom ?? 'Catégorie non définie' }}
                            </span>

                            <button
                                type="button"
                                class="favorite-btn"
                                aria-label="Ajouter aux favoris"
                                data-type="product"
                                data-id="{{ $product->id }}"
                                data-favorited="{{ auth()->check() && auth()->user()->hasFavoritedProduct($product) ? 'true' : 'false' }}"
                            >
                                <i class="{{ auth()->check() && auth()->user()->hasFavoritedProduct($product) ? 'fas fa-heart' : 'far fa-heart' }}"></i>
                            </button>

                        </div>

                        <div class="card-content">

                            <div class="card-meta">
                                @if(filled($product->address))
                                    <span class="card-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ $product->address }}
                                    </span>
                                @endif

                                @if($product->delivery_available)
                                    <span class="card-delivery">
                                        <i class="fas fa-truck"></i>
                                        Livraison dispo
                                    </span>
                                @endif
                            </div>

                            <h3 class="card-title">{{ $product->name }}</h3>

                            @if(filled($excerpt))
                                <p class="card-desc">{{ \Illuminate\Support\Str::limit($excerpt, 160) }}</p>
                            @endif

                            <div class="card-footer">
                                <span class="card-price-block">
                                    <span class="card-price-label">à partir de</span>
                                    <span class="card-price">
                                        {{ number_format($product->price, 2) }} €
                                    </span>
                                </span>

                                <span class="card-cta">Voir détails</span>
                            </div>

                        </div>

                    </a>

                @endif
            @endforeach
        </div>

    @else

        <x-empty-state
            :action-url="route('ads.create')"
            action-label="Publier la première annonce"
            action-auth
            text="Soyez le premier à publier un service, une location, un trajet en covoiturage ou un objet à vendre sur la plateforme Olten." />

    @endif

</section>

<!----------À PROPOS D'OLTEN-------------->
<section  id="about" class="about-section">
    <div class="about-container">
        <div class="about-text">
            <span class="section-eyebrow section-eyebrow--plain">À propos d'Olten</span>
            <h2>Qu'est-ce qu'Olten ?<br>L'abréviation de vos échanges de confiance.</h2>
            <p>
                Louez, vendez, achetez, faites livrer vos colis, trouvez un trajet
                ou développez votre activité grâce aux services proposés par Olten.fr.
                Né d'une idée simple, <strong>Olten</strong> (contraction de l'esprit
                collaboratif « On L'out Tout Entre Nous ») est bien plus qu'un site de
                petites annonces classique : c'est un véritable écosystème unifié en France.
            </p>
            <ul class="about-checklist">
                <li>
                    <i class="fas fa-check"></i>
                    <div class="about-checklist-text">
                        <strong>Une seule plateforme polyvalente</strong>
                        <span>Plus besoin de multiplier les applications : louer une voiture, covoiturer ou vendre un objet se fait sur le même espace.</span>
                    </div>
                </li>
                <li>
                    <i class="fas fa-check"></i>
                    <div class="about-checklist-text">
                        <strong>Proximité &amp; Économie Circulaire</strong>
                        <span>Encouragez les circuits courts, donnez une seconde vie aux objets et réalisez des économies avec vos voisins.</span>
                    </div>
                </li>
            </ul>
        </div>
        <div class="about-image">
            <div class="about-image-frame">
                <img src="{{ asset('assets/images/about-us.avif') }}" alt="À propos d'Olten">

                <div class="about-image-caption">
                    <span class="about-caption-icon">
                        <i class="fas fa-handshake"></i>
                    </span>
                    <span class="about-caption-text">
                        <strong>L'esprit Olten</strong>
                        <span>« Connecter les gens pour faciliter chaque moment du quotidien. »</span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<!----------L'ÉCOSYSTÈME OLTEN-------------->
<section class="ecosystem-section">
    <div class="ecosystem-header">
        <span class="section-eyebrow section-eyebrow--plain">L'écosystème Olten</span>
        <h2>Pourquoi notre plateforme transforme vos habitudes</h2>
        <p class="ecosystem-subtitle">Un modèle pensé pour la liberté, la sécurité et la synergie entre les citoyens et les professionnels.</p>
    </div>

    <div class="ecosystem-grid">
        <div class="ecosystem-card">
            <div class="ecosystem-icon">
                <i class="fas fa-leaf"></i>
            </div>
            <h3>Économie Circulaire &amp; Durable</h3>
            <p>Réduisez le gaspillage en louant ou achetant du matériel déjà présent près de chez vous au lieu d'acheter du neuf inutilement.</p>
        </div>

        <div class="ecosystem-card">
            <div class="ecosystem-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3>Sécurité &amp; Profils Vérifiés</h3>
            <p>Chaque membre dispose d'un profil noté et d'identités vérifiées. Échangez et réservez l'esprit totalement tranquille.</p>
        </div>

        <div class="ecosystem-card">
            <div class="ecosystem-icon">
                <i class="fas fa-globe"></i>
            </div>
            <h3>Synergie Particuliers &amp; Pros</h3>
            <p>Que vous soyez un citoyen cherchant un covoiturage ou un artisan répondant à un appel d'offres local, Olten réunit tout le monde.</p>
        </div>
    </div>
</section>

<!----------CENTRE D'ASSISTANCE - FAQ-------------->
<section id="faq" class="faq-section">
    <div class="faq-header">
        <span class="faq-badge">
            <i class="fas fa-headset"></i>
            Centre d'assistance
        </span>
        <h2>Foire Aux Questions (FAQ)</h2>
        <p>Tout ce que vous devez savoir pour louer, covoiturer, vendre et échanger en toute confiance sur Olten.fr.</p>
    </div>

    <div class="faq-list">
        <div class="faq-item active">
            <button class="faq-question" type="button">
                <span class="faq-number">01</span>
                <span class="faq-text">Est-ce que le dépôt d'annonce est gratuit sur Olten.fr ?</span>
                <i class="fas fa-chevron-down faq-toggle-icon"></i>
            </button>
            <div class="faq-answer">
                <p>Oui, le dépôt d'annonce de base est 100% gratuit pour tous les particuliers sur l'ensemble de nos catégories (Vente, Location, Covoiturage, Livraison, Prestations et Appels d'offres). Vous profitez d'une visibilité optimale immédiate dès la validation de votre annonce.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" type="button">
                <span class="faq-number">02</span>
                <span class="faq-text">Comment sont garantis les paiements et les transactions ?</span>
                <i class="fas fa-chevron-down faq-toggle-icon"></i>
            </button>
            <div class="faq-answer">
                <p>Toutes les transactions passent par notre système de paiement sécurisé. Les fonds sont bloqués jusqu'à la confirmation de la bonne réalisation de la prestation ou de la livraison, garantissant une protection totale pour l'acheteur comme pour le vendeur.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" type="button">
                <span class="faq-number">03</span>
                <span class="faq-text">Quelle est l'origine exacte du nom "Olten" ?</span>
                <i class="fas fa-chevron-down faq-toggle-icon"></i>
            </button>
            <div class="faq-answer">
                <p>Olten est né d'une contraction de l'esprit collaboratif "On L'out Tout Entre Nous", reflétant notre volonté de créer un véritable écosystème d'entraide et d'échange entre les membres de la plateforme.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" type="button">
                <span class="faq-number">04</span>
                <span class="faq-text">Puis-je utiliser Olten en tant que professionnel ?</span>
                <i class="fas fa-chevron-down faq-toggle-icon"></i>
            </button>
            <div class="faq-answer">
                <p>Absolument. Olten accueille aussi bien les particuliers que les professionnels, notamment via nos catégories Prestations de services et Appels d'offres, pensées pour connecter artisans et clients locaux.</p>
            </div>
        </div>
    </div>
</section>

<!----------CTA - Déposer une annonce-------------->
<section class="cta-section">
    <div class="cta-content">
        <h2>Prêt à publier votre première annonce&nbsp;?</h2>
        <p>
            Rejoignez des milliers de membres actifs dès aujourd'hui sur Olten.fr.
            C'est simple, rapide et sans engagement.
        </p>
    </div>
    <a href="{{ route('ads.create') }}" class="cta-btn" data-auth-required>
        <i class="fas fa-plus"></i>
        Déposer une annonce gratuite
    </a>
</section>
@endsection