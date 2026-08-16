<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Olten.fr - Annonce detail</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <!---------Swiper------------>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <!-- Leaflet Map -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
  <!-- Feuille de style  -->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body>
  <x-header />

<div class="main-container">
    <!-- SIDEBAR FILTERS -->
    <aside class="filters-sidebar" id="filtersSidebar">
      <h2 class="filters-title">Filters</h2>

      <div class="filter-group">
        <label class="filter-label">Que recherchez-vous ?</label>
        <input type="text" class="search-input" placeholder="Rechercher...">
      </div>

      <div class="filter-group">
        <label class="filter-label">Toutes les catégories</label>
        <select class="select-input">
          <option>Toutes les catégories</option>
          <option>Location électronique</option>
          <option>Location événementiel</option>
          <option>Location immobilier</option>
          <option>Location maison & bricolage</option>
        </select>
      </div>

      <div class="filter-group">
        <label class="filter-label">Emplacement</label>
        <div class="location-input">
          <i class="fas fa-map-marker-alt location-icon"></i>
          <input type="text" class="search-input" placeholder="Ville, code postal...">
        </div>
      </div>

      <div class="filter-group">
        <label class="filter-label">Filtrer les prix</label>
        <p style="font-size: 12px; color: var(--color-gray); margin-bottom: 10px;">
          Sélectionnez la plage de prix min et max
        </p>
        <div class="price-range">
          <input type="range" min="0" max="1000" value="500" class="range-slider">
        </div>
      </div>

      <button class="filter-btn">Activer Filtrer les prix</button>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
      <!-- HEADER -->
      <header class="content-header">
        <div class="header-top">
          <button class="filter-pill" id="toggleFilters">
            <i class="fas fa-sliders-h"></i>
            <span id="filterText">Afficher les filtres</span>
          </button>

          <div class="categories-scroll">
            <div class="category-item">
              <div class="category-icon"><i class="fas fa-headphones"></i></div>
              <span class="category-name">Location électronique</span>
            </div>
            <div class="category-item">
              <div class="category-icon"><i class="fas fa-calendar-alt"></i></div>
              <span class="category-name">Location événementiel</span>
            </div>
            <div class="category-item">
              <div class="category-icon"><i class="fas fa-home"></i></div>
              <span class="category-name">Location immobilier</span>
            </div>
            <div class="category-item">
              <div class="category-icon"><i class="fas fa-building"></i></div>
              <span class="category-name">Location maison & bricolage</span>
            </div>
            <div class="category-item">
              <div class="category-icon"><i class="fas fa-user-md"></i></div>
              <span class="category-name">Location médical</span>
            </div>
            <div class="category-item">
              <div class="category-icon"><i class="fas fa-car"></i></div>
              <span class="category-name">Location mode & famille</span>
            </div>
            <div class="category-item">
              <div class="category-icon"><i class="fas fa-anchor"></i></div>
              <span class="category-name">Location nautisme</span>
            </div>
          </div>
        </div>
      </header>

      <!-- VIEW OPTIONS -->
      <div class="view-options">
        <div class="view-toggle">
          <button class="view-btn active" data-view="grid"><i class="fas fa-th"></i></button>
          <button class="view-btn" data-view="list"><i class="fas fa-list"></i></button>
        </div>

        <div class="sort-dropdown">
          <span>En vedette</span>
          <i class="fas fa-chevron-down"></i>
        </div>
      </div>

      <!-- CONTENT AREA -->
      <div class="content-area">
        <div class="listings-container">
          <!-- GRID VIEW -->
          <div class="listings-grid" id="listingsGrid">
            <div class="annonce-card">
              <div class="card-image-container">
                <img src="https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?w=600&h=450&fit=crop" alt="Clio" class="card-image">
                <span class="watermark">leboncoin</span>
                <span class="category-badge">Location véhicule</span>
                <button class="favorite-btn" aria-label="Ajouter aux favoris">
                  <i class="far fa-heart"></i>
                </button>
              </div>
              <div class="card-content">
                <h3 class="card-title">
                  <span class="card-title-text">Clio</span>
                  <span class="info-badge">?</span>
                </h3>
                <p class="card-location">
                  <i class="fas fa-map-marker-alt"></i>
                  Paris, Île-de-France, France métropolitaine, France
                </p>
                <p class="card-price">Commence à partir de €60,00</p>
              </div>
            </div>

            <div class="annonce-card">
              <div class="card-image-container">
                <img src="https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?w=600&h=450&fit=crop" alt="Renault clio 2" class="card-image">
                <span class="watermark">leboncoin</span>
                <span class="category-badge">location voiture</span>
                <button class="favorite-btn" aria-label="Ajouter aux favoris">
                  <i class="far fa-heart"></i>
                </button>
              </div>
              <div class="card-content">
                <h3 class="card-title">
                  <span class="card-title-text">Renault clio 2</span>
                  <span class="info-badge">?</span>
                </h3>
                <p class="card-location">
                  <i class="fas fa-map-marker-alt"></i>
                  Lyon
                </p>
                <p class="card-price">Commence à partir de €40,00</p>
              </div>
            </div>

            <div class="annonce-card">
              <div class="card-image-container">
                <img src="https://images.unsplash.com/photo-1583121274602-3e2820c69888?w=600&h=450&fit=crop" alt="Voiture Rouge" class="card-image">
                <span class="watermark">leboncoin</span>
                <span class="category-badge">Location véhicule</span>
                <button class="favorite-btn" aria-label="Ajouter aux favoris">
                  <i class="far fa-heart"></i>
                </button>
              </div>
              <div class="card-content">
                <h3 class="card-title">
                  <span class="card-title-text">Voiture Rouge</span>
                  <span class="info-badge">?</span>
                </h3>
                <p class="card-location">
                  <i class="fas fa-map-marker-alt"></i>
                  Lyon
                </p>
                <p class="card-price">Commence à partir de €1 500,00</p>
              </div>
            </div>

            <div class="annonce-card">
              <div class="card-image-container">
                <img src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=600&h=450&fit=crop" alt="Karcher vapeur" class="card-image">
                <span class="watermark">leboncoin</span>
                <span class="category-badge">Outils et matériel de bricolage</span>
                <button class="favorite-btn" aria-label="Ajouter aux favoris">
                  <i class="far fa-heart"></i>
                </button>
              </div>
              <div class="card-content">
                <h3 class="card-title">
                  <span class="card-title-text">Karcher vapeur eau chaude</span>
                  <span class="info-badge">?</span>
                </h3>
                <p class="card-location">
                  <i class="fas fa-map-marker-alt"></i>
                  Paris, France
                </p>
                <p class="card-price">Commence à partir de €30,00</p>
              </div>
            </div>

            <div class="annonce-card">
              <div class="card-image-container">
                <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&h=450&fit=crop" alt="Aspirateur" class="card-image">
                <span class="watermark">leboncoin</span>
                <span class="category-badge">Location maison & bricolage</span>
                <button class="favorite-btn" aria-label="Ajouter aux favoris">
                  <i class="far fa-heart"></i>
                </button>
              </div>
              <div class="card-content">
                <h3 class="card-title">
                  <span class="card-title-text">Aspirateur feuille Pro</span>
                  <span class="info-badge">?</span>
                </h3>
                <p class="card-location">
                  <i class="fas fa-map-marker-alt"></i>
                  Marseille, France
                </p>
                <p class="card-price">Commence à partir de €40,00</p>
              </div>
            </div>

            <div class="annonce-card">
              <div class="card-image-container">
                <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=600&h=450&fit=crop" alt="Scène professionnelle" class="card-image">
                <span class="watermark">leboncoin</span>
                <span class="category-badge">Location événementiel</span>
                <button class="favorite-btn" aria-label="Ajouter aux favoris">
                  <i class="far fa-heart"></i>
                </button>
              </div>
              <div class="card-content">
                <h3 class="card-title">
                  <span class="card-title-text">Scène professionnelle 7m x 5m</span>
                  <span class="info-badge">?</span>
                </h3>
                <p class="card-location">
                  <i class="fas fa-map-marker-alt"></i>
                  Nice, France
                </p>
                <p class="card-price">Commence à partir de €300,00</p>
              </div>
            </div>
          </div>

          <!-- LIST VIEW (hidden by default) -->
          <div class="listings-list" id="listingsList" style="display: none;">
            <!-- Cards will be dynamically added here -->
          </div>

          <!-- PAGINATION -->
          <div class="pagination" id="pagination">
            <button class="pagination-btn prev-page" id="prevPage">
              <i class="fas fa-chevron-left"></i>
              <span>Précédent</span>
            </button>
            
            <div id="pageNumbers"></div>
            
            <button class="pagination-btn next-page" id="nextPage">
              <span>Suivant</span>
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>

        <!-- MAP -->
        <div class="map-container">
          <div id="map"></div>
          <div class="map-zoom-control">
            <button class="zoom-btn" id="zoom-in"><i class="fas fa-plus"></i></button>
            <button class="zoom-btn" id="zoom-out"><i class="fas fa-minus"></i></button>
          </div>
          <button class="locate-btn" id="locate"><i class="fas fa-location-arrow"></i></button>
        </div>
      </div>
    </main>
  </div>
   <x-footer />

  <script src="{{ asset('assets/js/script.js') }}"></script>
</body>
</html>