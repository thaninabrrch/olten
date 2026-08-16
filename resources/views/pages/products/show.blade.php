@extends('layouts.main')

@section('title', 'Détail du produit - Olten.fr')

@section('content')

<!-- Galerie d'images -->
<div class="image-gallery">
    <div class="gallery-container">
        <div class="gallery-slides" id="gallerySlides">
            @forelse ($product->images as $image)
                <div class="gallery-slide">
                    <img 
                        src="{{ asset('storage/' . $image->image) }}" 
                        alt="{{ $product->name }}"
                    >
                </div>
            @empty
                <div class="gallery-slide">
                    <img src="{{ asset('assets/images/no-image.jpg') }}" alt="Aucune image">
                </div>
            @endforelse
        </div>

        <button class="gallery-nav prev" onclick="changeSlide(-1)">‹</button>
        <button class="gallery-nav next" onclick="changeSlide(1)">›</button>

        <div class="gallery-indicators" id="indicators"></div>
    </div>
</div>

<!-- Contenu principal -->
<div class="container-product-details">
    <div class="main-content-product">

        <!-- SECTION GAUCHE -->
        <div class="left-section-product">
            <h1>{{ $product->name }}</h1>

            <div class="tags-container">
                <div class="category-tag">
                    <i class="fas fa-tags"></i>
                    {{ $product->category->nom ?? 'Catégorie non définie' }}
                </div>
            </div>
            
            <section id="apercu" class="content-section active">
                <h2 class="section-title">Description</h2>
                <p class="description">{!! $product->description ?? 'Pas de description disponible.' !!}</p>
            </section>
        </div>

        <!-- SECTION DROITE -->
        <div class="right-section">
            @if(!auth()->check() || (auth()->check() && auth()->id() != $product->user_id))
                <div class="purchase-box">
                    <p class="price">Prix : {{ number_format($product->price, 2) }} €</p>
                    <p class="stock {{ $product->stock <= 0 ? 'text-danger' : '' }}">
                        {{ $product->stock <= 0 ? 'Rupture de stock' : 'Stock : ' . $product->stock }}
                    </p>
                    @if($product->stock > 0)
                        <form action="{{ route('products.purchase', $product) }}" method="POST">
                            @csrf
                            <div class="form-group w-50">
                                <label for="quantity">Quantité</label>
                                <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" max="{{ $product->stock }}">
                            </div>
                            <button type="submit" class="btn btn-buy mt-2 w-100">Acheter</button>
                        </form>
                    @endif

                    <div class="action-buttons mt-3">
                        <button class="action-btn" onclick="window.location.href='mailto:{{ $product->user->email }}'">
                            <i class="far fa-comment"></i> Message
                        </button>
                        <button class="action-btn" onclick="window.location.href='tel:{{ $product->user->phone }}'">
                            <i class="fas fa-phone"></i> Appeler
                        </button>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>

<script>
    function changeSlide(direction) {
        const slides = document.querySelectorAll('.gallery-slide');
        let current = Array.from(slides).findIndex(slide => slide.style.display !== 'none');
        slides[current].style.display = 'none';

        let next = current + direction;
        if(next < 0) next = slides.length - 1;
        if(next >= slides.length) next = 0;

        slides[next].style.display = 'flex';
    }

    document.addEventListener('DOMContentLoaded', () => {
        const slides = document.querySelectorAll('.gallery-slide');
        slides.forEach(slide => slide.style.display = 'none');
        if(slides[0]) slides[0].style.display = 'flex';
    });
</script>

@endsection