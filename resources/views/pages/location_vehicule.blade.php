<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Olten-location.fr - Comment voyager ?</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <!-- Feuille de style  -->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body>
  <x-header />

  <main>
    <!-- HERO SECTION -->
    <section class="location-hero-section" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('{{ asset('assets/images/voitures-fdc.png') }}') center/cover no-repeat;">
      <div class="hero-contenue">
        <div class="location-hero-badge">
          <div class="location-hero-trait"></div>
          <p>Location de véhicules d'exception</p>
        </div>
        <h1 class="location-hero-title">Louez votre <span class="orange-dot">liberté.</span></h1>
        <p class="location-hero-desc">Découvrez une nouvelle façon de louer. Des véhicules premium, une sécurité totale et une expérience sans compromis.</p>
      </div>

      <!-- FORMULAIRE DE RECHERCHE -->
      <div class="location-search-form">
        <h3>Rechercher un véhicule</h3>
        
        <div class="location-search-grid">
          
          <!-- LIEU DE DÉPART -->
          <div class="location-form-group">
            <label class="location-form-label">Lieu de départ</label>
            <div class="location-form-input-wrapper">
              <i class="fa-solid fa-location-dot"></i>
              <input type="text" placeholder="Où commencer-vous?">
            </div>
          </div>

          <!-- LIEU DE FIN -->
          <div class="location-form-group">
            <label class="location-form-label">Lieu de fin</label>
            <div class="location-form-input-wrapper">
              <i class="fa-solid fa-location-dot"></i>
              <input type="text" placeholder="Où allez-vous ?">
            </div>
          </div>

          <!-- DATE DE DÉPART -->
          <div class="location-form-group">
            <label class="location-form-label">Date de départ</label>
            <div class="location-form-input-wrapper">
              <i class="fa-solid fa-calendar"></i>
              <input type="text" placeholder="jj / mm / aaaa">
            </div>
          </div>

          <!-- DATE DE RETOUR -->
          <div class="location-form-group">
            <label class="location-form-label">Date de retour</label>
            <div class="location-form-input-wrapper">
              <i class="fa-solid fa-calendar"></i>
              <input type="text" placeholder="jj / mm / aaaa">
            </div>
          </div>

          <!-- TYPE DE VÉHICULE -->
          <div class="location-form-group">
            <label class="location-form-label">Type de véhicule</label>
            <select class="location-form-select">
              <option>Sélectionner un véhicule</option>
              <option>Voiture</option>
              <option>SUV</option>
              <option>Monospace</option>
              <option>Utilitaire</option>
              <option>Moto</option>
              <option>Camping-car</option>
            </select>
          </div>

          <!-- BOUTON RECHERCHER -->
          <div>
            <button class="location-search-btn">Rechercher</button>
          </div>

        </div>
      </div>
    </section>
    <!-- PARCOUREZ PAR TYPE SECTION -->
    <section class="location-types-section">
      <div class="location-types-container">
        <div class="location-types-header">
          <h2 class="location-types-title">Parcourez par type de véhicule</h2>
          <div class="location-types-trait"></div>
        </div>

        <!-- GRILLE DE TYPES -->
        <div class="location-types-grid">
          
          <!-- VOITURE -->
          <div class="location-type-card">
            <img src="{{ asset('assets/images/logo-vehicules/logo-voiture.png') }}" alt="Voiture" class="location-type-logo">
            <h3>Voiture</h3>
            <p>Locations de voitures de tous types, des citadines agiles aux berlines de luxe.</p>
            <a href="#" class="location-type-link">Voir les annonces <i class="fa-solid fa-arrow-right"></i></a>
          </div>

          <!-- SUV -->
          <div class="location-type-card">
            <img src="{{ asset('assets/images/logo-vehicules/logo-suv.png') }}" alt="SUV" class="location-type-logo">
            <h3>SUV</h3>
            <p>Pour vos aventures tout-terrain et un confort optimal pour toute la famille.</p>
            <a href="#" class="location-type-link">Voir les annonces <i class="fa-solid fa-arrow-right"></i></a>
          </div>

          <!-- MONOSPACE -->
          <div class="location-type-card">
            <img src="{{ asset('assets/images/logo-vehicules/logo-monospace.png') }}" alt="Monospace" class="location-type-logo">
            <h3>Monospace</h3>
            <p>L'espace sans compromis pour les familles et les voyages en groupe.</p>
            <a href="#" class="location-type-link">Voir les annonces <i class="fa-solid fa-arrow-right"></i></a>
          </div>

          <!-- UTILITAIRE -->
          <div class="location-type-card">
            <img src="{{ asset('assets/images/logo-vehicules/logo-utilitaire.png') }}" alt="Utilitaire" class="location-type-logo">
            <h3>Utilitaire</h3>
            <p>Idéal pour vos déménagements ou transport de matériel encombrant.</p>
            <a href="#" class="location-type-link">Voir les annonces <i class="fa-solid fa-arrow-right"></i></a>
          </div>

          <!-- MOTO -->
          <div class="location-type-card">
            <img src="{{ asset('assets/images/logo-vehicules/logo-moto.png') }}" alt="Moto" class="location-type-logo">
            <h3>Moto</h3>
            <p>Pour les passionnés de liberté et une mobilité urbaine ultra-rapide.</p>
            <a href="#" class="location-type-link">Voir les annonces <i class="fa-solid fa-arrow-right"></i></a>
          </div>

          <!-- CAMPING-CAR -->
          <div class="location-type-card">
            <img src="{{ asset('assets/images/logo-vehicules/logo-campingcar.png') }}" alt="Camping-car" class="location-type-logo">
            <h3>Camping-car</h3>
            <p>La liberté totale : voyagez et dormez partout où la route vous mène.</p>
            <a href="#" class="location-type-link">Voir les annonces <i class="fa-solid fa-arrow-right"></i></a>
          </div>

        </div>
      </div>
    </section>

    <!-- ANNONCES RÉCENTES SECTION -->
    <section class="location-annonces-section">
      <div class="location-annonces-container">
        <div class="location-annonces-header">
          <div>
            <h2 class="location-annonces-title">Annonces récentes</h2>
            <div class="location-annonces-trait"></div>
          </div>
          <a href="#" class="location-annonces-link">Voir toutes les annonces <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <!-- ANNONCES GRID -->
        <div class="location-annonces-grid">
          
          <!-- ANNONCE 1 -->
          <div class="location-annonce-card">
            <div class="location-annonce-image-wrapper">
              <img src="{{ asset('assets/images/bmw.png') }}" alt="Peugeot 308" class="location-annonce-image">
            </div>
            <div class="location-annonce-content">
              <!-- TITRE ET PRIX -->
              <div class="location-annonce-header">
                <h3 class="location-annonce-title">Peugeot 308</h3>
                <div class="location-annonce-price-box">
                  <p class="location-annonce-price">40€</p>
                  <span class="location-annonce-price-label">par jour</span>
                </div>
              </div>
              <!-- LOCALISATION -->
              <div class="location-annonce-location">
                <i class="fa-solid fa-location-dot"></i>
                <span>Paris, France</span>
              </div>
              <!-- TAGS -->
              <div class="location-annonce-tags">
                <span class="location-annonce-tag">DIESEL</span>
                <span class="location-annonce-tag">AUTO</span>
                <span class="location-annonce-tag">5 PLACES</span>
              </div>
              <!-- INFO PROPRIETAIRE -->
              <div class="location-annonce-footer">
                <div class="location-annonce-owner">
                  <img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="Sofia M." class="location-annonce-owner-avatar">
                  <span class="location-annonce-owner-name">Sofia M.</span>
                </div>
                <button class="location-annonce-btn">></button>
              </div>
            </div>
          </div>

          <!-- ANNONCE 2 -->
          <div class="location-annonce-card">
            <div class="location-annonce-image-wrapper">
              <img src="{{ asset('assets/images/bmw.png') }}" alt="Peugeot 308" class="location-annonce-image">
            </div>
            <div class="location-annonce-content">
              <!-- TITRE ET PRIX -->
              <div class="location-annonce-header">
                <h3 class="location-annonce-title">Peugeot 308</h3>
                <div class="location-annonce-price-box">
                  <p class="location-annonce-price">40€</p>
                  <span class="location-annonce-price-label">par jour</span>
                </div>
              </div>
              <!-- LOCALISATION -->
              <div class="location-annonce-location">
                <i class="fa-solid fa-location-dot"></i>
                <span>Paris, France</span>
              </div>
              <!-- TAGS -->
              <div class="location-annonce-tags">
                <span class="location-annonce-tag">DIESEL</span>
                <span class="location-annonce-tag">AUTO</span>
                <span class="location-annonce-tag">5 PLACES</span>
              </div>
              <!-- INFO PROPRIETAIRE -->
              <div class="location-annonce-footer">
                <div class="location-annonce-owner">
                  <img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="Sofia M." class="location-annonce-owner-avatar">
                  <span class="location-annonce-owner-name">Sofia M.</span>
                </div>
                <button class="location-annonce-btn">></button>
              </div>
            </div>
          </div>

          <!-- ANNONCE 3 -->
          <div class="location-annonce-card">
            <div class="location-annonce-image-wrapper">
              <img src="{{ asset('assets/images/bmw.png') }}" alt="Peugeot 308" class="location-annonce-image">
            </div>
            <div class="location-annonce-content">
              <!-- TITRE ET PRIX -->
              <div class="location-annonce-header">
                <h3 class="location-annonce-title">Peugeot 308</h3>
                <div class="location-annonce-price-box">
                  <p class="location-annonce-price">40€</p>
                  <span class="location-annonce-price-label">par jour</span>
                </div>
              </div>
              <!-- LOCALISATION -->
              <div class="location-annonce-location">
                <i class="fa-solid fa-location-dot"></i>
                <span>Paris, France</span>
              </div>
              <!-- TAGS -->
              <div class="location-annonce-tags">
                <span class="location-annonce-tag">DIESEL</span>
                <span class="location-annonce-tag">AUTO</span>
                <span class="location-annonce-tag">5 PLACES</span>
              </div>
              <!-- INFO PROPRIETAIRE -->
              <div class="location-annonce-footer">
                <div class="location-annonce-owner">
                  <img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="Sofia M." class="location-annonce-owner-avatar">
                  <span class="location-annonce-owner-name">Sofia M.</span>
                </div>
                <button class="location-annonce-btn">></button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>


  </main>

  <x-footer />

    <script src="{{ asset('assets/js/script.js') }}"></script>
  
</body>
</html>
