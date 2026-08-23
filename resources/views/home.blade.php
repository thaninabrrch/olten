@extends('layouts.main')

@section('title', 'Accueil - Olten.fr')

@section('content')
<!----HERO SECTION------>
<section class="hero-section">
    <div class="hero-content">

        <span class="hero-badge">
            <i class="fas fa-bolt"></i>
            On l'out tout entre nous • Le réflexe collaboratif de proximité
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

    </div>
</section>

<!----------Catégories-------------->
@if($categories->isNotEmpty())
    <section class="categories-section">
        <div class="section-header">
            <h2 class="section-title">
                Explorez nos <span class="site-name">catégories</span>
            </h2>
            <div class="carousel-nav">
                <button class="carousel-btn prev-btn" aria-label="Précédent">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="carousel-btn next-btn" aria-label="Suivant">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="carousel-wrapper">
            <div class="carousel-track">
                @foreach($categories as $category)
                    <a href="{{ route('categories.show', $category->slug) }}" class="category-card">
                        <div class="category-overlay">
                            <span>Parcourir</span>
                        </div>
                        <i class="{{ $category->icon }} category-icon"></i>
                        <h5>{{ $category->nom }}</h5>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="carousel-dots"></div>
    </section>
@else
    <p class="text-center">
        Aucune catégorie disponible pour le moment.
    </p>
@endif

<!---------- Plus récent annonce / produit -------------->
@if($latestItems->isNotEmpty())

    <section class="annonces-section">

        <div class="section-header">

            <h2 class="section-title">
                Les Annonces qui Font Parler d'elles sur
                <span class="site-name">Olten.fr</span>
            </h2>

            <div class="carousel-nav">

                <button class="carousel-btn prev-btn" aria-label="Précédent">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <button class="carousel-btn next-btn" aria-label="Suivant">
                    <i class="fas fa-chevron-right"></i>
                </button>

            </div>
        </div>

        <div class="carousel-wrapper">

            <div class="carousel-track">
                @foreach($latestItems as $latest)
                    @if($latest->type === 'ad')

                        @php
                            $ad = $latest->item;

                            $isExpired = $ad->expires_at &&
                                \Carbon\Carbon::parse($ad->expires_at)->toDateString() < now()->toDateString();
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

                                <div class="d-flex justify-content-between">

                                    <h3 class="card-title">
                                        {{ $ad->title }}
                                    </h3>

                                    @if($isExpired)
                                        <span class="expired">
                                            Expirée
                                        </span>
                                    @endif

                                    @if($ad->delivery_active)
                                        <span class="mt-auto mb-auto bg-success text-white fs-6 p-1 radius-2">
                                            Livraison disponible
                                        </span>
                                    @endif

                                </div>

                                <p class="card-price">
                                    Commence à partir de
                                    {{ number_format($ad->price_per_day, 2) }} € / jour
                                </p>

                            </div>

                        </a>

                    @else

                        @php
                            $product = $latest->item;
                        @endphp

                        {{-- ================= PRODUIT ================= --}}

                        <a href="{{ route('products.show', $product->id) }}"
                        class="annonce-card">

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

                                @if($product->delivery_available)
                                    <span class="mt-auto mb-auto bg-success text-white fs-6 p-1 radius-2">
                                        Livraison disponible
                                    </span>
                                @endif

                            </div>

                            <div class="card-content">

                                <h3 class="card-title">
                                    {{ $product->name }}
                                </h3>

                                <p class="card-price">
                                    {{ number_format($product->price, 2) }} €
                                </p>

                            </div>

                        </a>

                    @endif
                @endforeach
            </div>

        </div>

        <div class="carousel-dots"></div>

    </section>

@else

    <p class="text-center">
        Aucune annonce ou produit disponible pour le moment.
    </p>

@endif

<!----------À PROPOS D'OLTEN-------------->
<section  id="about" class="about-section">
    <div class="about-container">
        <div class="about-text">
            <span class="section-eyebrow">À propos d'Olten</span>
            <h2>Qu'est-ce qu'Olten ?<br>L'abréviation de vos échanges de confiance.</h2>
            <p>
                Louez, vendez, achetez, faites livrer vos colis, trouvez un trajet
                ou développez votre activité grâce aux services proposés par Olten.fr.
                Né d'une idée simple, <strong>Olten</strong> (contraction de l'esprit
                collaboratif « On L'out Tout Entre Nous ») est bien plus qu'un site de
                petites annonces classique : c'est un véritable écosystème unifié en France.
            </p>
            <ul class="about-checklist">
                <li><i class="fas fa-check"></i> Une plateforme 100% française et sécurisée</li>
                <li><i class="fas fa-check"></i> Des milliers de membres actifs chaque jour</li>
            </ul>
        </div>
        <div class="about-image">
            <img src="{{ asset('assets/images/about-us.avif') }}" alt="À propos d'Olten">
        </div>
    </div>
</section>

<!----------L'ÉCOSYSTÈME OLTEN-------------->
<section class="ecosystem-section">
    <div class="ecosystem-header">
        <span class="section-eyebrow">L'écosystème Olten</span>
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
    <a href="#" class="cta-btn">
        <i class="fas fa-plus"></i>
        Déposer une annonce gratuite
    </a>
</section>
@endsection