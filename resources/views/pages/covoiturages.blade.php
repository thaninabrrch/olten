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

  <main class="covoiturage-main">
    <!-- HERO SECTION -->
    <section class="location-hero-section" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('{{ asset('assets/images/voitures-fdc.png') }}') center/cover no-repeat;">
      <div class="hero-contenue">
        <div class="location-hero-badge">
          <div class="location-hero-trait"></div>
          <p>Location de véhicules d'exception</p>
        </div>
        <h1 class="location-hero-title">Réservez votre<span class="orange-dot"> trajet.</span></h1>
        <p class="location-hero-desc">Profitez d’une expérience de covoiturage unique. Confort premium, sécurité totale et flexibilité à la demande.</p>
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
              <input type="text" placeholder="Où commencer votre ?">
            </div>
          </div>

          <!-- LIEU DE FIN -->
          <div class="location-form-group">
            <label class="location-form-label">Lieu de fin</label>
            <div class="location-form-input-wrapper">
              <i class="fa-solid fa-location-dot"></i>
              <input type="text" placeholder="Où aller ?">
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

          <!-- NOMBRES DE PASSAGERS -->
          <div class="location-form-group">
            <label class="location-form-label">Nombre de passagers</label>
            <select class="location-form-select " name="passagers" id="passagers">
              <option value="1">1 passager</option>
              <option value="2">2 passagers</option>
              <option value="3">3 passagers</option>
              <option value="4">4 passagers</option>
            </select>
          </div>

          <!-- BOUTON RECHERCHER -->
          <div>
            <button class="location-search-btn">Rechercher</button>
          </div>

        </div>
      </div>
    </section>

    <!-- DESTINATIONS DISPONIBLES SECTION -->
    <section class="covoiturage-destinations-section">
      <div class="covoiturage-destinations-container">
        <div class="covoiturage-destination-ajustement">
          <div class="covoiturage-destinations-header">
            <h2 class="covoiturage-destinations-title">Destinations disponible</h2>
            <div class="covoiturage-destinations-trait"></div>
          </div>
          <div>
            <a href="#" class="covoiturage-destinations-link">Voir tous les trajets <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>

        <!-- DESTINATIONS GRID -->
        <div class="covoiturage-destinations-grid">
          
          <!-- DESTINATION 1 -->
          <div class="covoiturage-destination-card">
            <div class="covoiturage-destination-image">
              <img src="{{ asset('assets/images/paris-lyon.png') }}" alt="Paris - Lyon">
              <div class="covoiturage-destination-label">Paris - Lyon</div>
            </div>
            <div class="covoiturage-destination-content">
              <div class="covoiturage-destination-price">
                <div class="covoiturage-destination-price2">
                  <span class="covoiturage-price-label">PRIX À PARTIR DE</span>
                  <span class="covoiturage-price">12,00 <span class="euros">€</span></span>
                </div>
                <div>
                  <button class="covoiturage-btn"><i class="fa-solid fa-arrow-right"></i></button>
                </div>
              </div>
              <!-- positionner le trait -->
              <div class="covoiturage-destination-trait"></div>
              <div class="covoiturage-destination-footer">
                <div class="covoiturage-avatars">
                  <img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="User" class="covoiturage-avatar">
                  <img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="User" class="covoiturage-avatar">
                  <span class="covoiturage-count">+4 trajets</span>
                </div>
                <div class="covoiturage-avatars-notes">
                  <span class="covoiturage-note">4.8</span>
                  <i class="fa-regular fa-star"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- DESTINATION 2 -->
          <div class="covoiturage-destination-card">
            <div class="covoiturage-destination-image">
              <img src="{{ asset('assets/images/paris-lyon.png') }}" alt="Paris - Lyon">
              <div class="covoiturage-destination-label">Paris - Lyon</div>
            </div>
            <div class="covoiturage-destination-content">
              <div class="covoiturage-destination-price">
                <div class="covoiturage-destination-price2">
                  <span class="covoiturage-price-label">PRIX À PARTIR DE</span>
                  <span class="covoiturage-price">12,00 <span class="euros">€</span></span>
                </div>
                <div>
                  <button class="covoiturage-btn"><i class="fa-solid fa-arrow-right"></i></button>
                </div>
              </div>
              <!-- positionner le trait -->
              <div class="covoiturage-destination-trait"></div>
              <div class="covoiturage-destination-footer">
                <div class="covoiturage-avatars">
                  <img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="User" class="covoiturage-avatar">
                  <img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="User" class="covoiturage-avatar">
                  <span class="covoiturage-count">+4 trajets</span>
                </div>
                <div class="covoiturage-avatars-notes">
                  <span class="covoiturage-note">4.8</span>
                  <i class="fa-regular fa-star"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- DESTINATION 3 -->
          <div class="covoiturage-destination-card">
            <div class="covoiturage-destination-image">
              <img src="{{ asset('assets/images/paris-lyon.png') }}" alt="Paris - Lyon">
              <div class="covoiturage-destination-label">Paris - Lyon</div>
            </div>
            <div class="covoiturage-destination-content">
              <div class="covoiturage-destination-price">
                <div class="covoiturage-destination-price2">
                  <span class="covoiturage-price-label">PRIX À PARTIR DE</span>
                  <span class="covoiturage-price">12,00 <span class="euros">€</span></span>
                </div>
                <div>
                  <button class="covoiturage-btn"><i class="fa-solid fa-arrow-right"></i></button>
                </div>
              </div>
              <!-- positionner le trait -->
              <div class="covoiturage-destination-trait"></div>
              <div class="covoiturage-destination-footer">
                <div class="covoiturage-avatars">
                  <img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="User" class="covoiturage-avatar">
                  <img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="User" class="covoiturage-avatar">
                  <span class="covoiturage-count">+4 trajets</span>
                </div>
                <div class="covoiturage-avatars-notes">
                  <span class="covoiturage-note">4.8</span>
                  <i class="fa-regular fa-star"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- DESTINATION 4 -->
          <div class="covoiturage-destination-card">
            <div class="covoiturage-destination-image">
              <img src="{{ asset('assets/images/paris-lyon.png') }}" alt="Paris - Lyon">
              <div class="covoiturage-destination-label">Paris - Lyon</div>
            </div>
            <div class="covoiturage-destination-content">
              <div class="covoiturage-destination-price">
                <div class="covoiturage-destination-price2">
                  <span class="covoiturage-price-label">PRIX À PARTIR DE</span>
                  <span class="covoiturage-price">12,00 <span class="euros">€</span></span>
                </div>
                <div>
                  <button class="covoiturage-btn"><i class="fa-solid fa-arrow-right"></i></button>
                </div>
              </div>
              <!-- positionner le trait -->
              <div class="covoiturage-destination-trait"></div>
              <div class="covoiturage-destination-footer">
                <div class="covoiturage-avatars">
                  <img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="User" class="covoiturage-avatar">
                  <img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="User" class="covoiturage-avatar">
                  <span class="covoiturage-count">+4 trajets</span>
                </div>
                <div class="covoiturage-avatars-notes">
                  <span class="covoiturage-note">4.8</span>
                  <i class="fa-regular fa-star"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- DESTINATION 5 -->
          <div class="covoiturage-destination-card">
            <div class="covoiturage-destination-image">
              <img src="{{ asset('assets/images/paris-lyon.png') }}" alt="Paris - Lyon">
              <div class="covoiturage-destination-label">Paris - Lyon</div>
            </div>
            <div class="covoiturage-destination-content">
              <div class="covoiturage-destination-price">
                <div class="covoiturage-destination-price2">
                  <span class="covoiturage-price-label">PRIX À PARTIR DE</span>
                  <span class="covoiturage-price">12,00 <span class="euros">€</span></span>
                </div>
                <div>
                  <button class="covoiturage-btn"><i class="fa-solid fa-arrow-right"></i></button>
                </div>
              </div>
              <!-- positionner le trait -->
              <div class="covoiturage-destination-trait"></div>
              <div class="covoiturage-destination-footer">
                <div class="covoiturage-avatars">
                  <img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="User" class="covoiturage-avatar">
                  <img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="User" class="covoiturage-avatar">
                  <span class="covoiturage-count">+4 trajets</span>
                </div>
                <div class="covoiturage-avatars-notes">
                  <span class="covoiturage-note">4.8</span>
                  <i class="fa-regular fa-star"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- DESTINATION 6 -->
          <div class="covoiturage-destination-card">
            <div class="covoiturage-destination-image">
              <img src="{{ asset('assets/images/paris-lyon.png') }}" alt="Paris - Lyon">
              <div class="covoiturage-destination-label">Paris - Lyon</div>
            </div>
            <div class="covoiturage-destination-content">
              <div class="covoiturage-destination-price">
                <div class="covoiturage-destination-price2">
                  <span class="covoiturage-price-label">PRIX À PARTIR DE</span>
                  <span class="covoiturage-price">12,00 <span class="euros">€</span></span>
                </div>
                <div>
                  <button class="covoiturage-btn"><i class="fa-solid fa-arrow-right"></i></button>
                </div>
              </div>
              <!-- positionner le trait -->
              <div class="covoiturage-destination-trait"></div>
              <div class="covoiturage-destination-footer">
                <div class="covoiturage-avatars">
                  <img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="User" class="covoiturage-avatar">
                  <img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="User" class="covoiturage-avatar">
                  <span class="covoiturage-count">+4 trajets</span>
                </div>
                <div class="covoiturage-avatars-notes">
                  <span class="covoiturage-note">4.8</span>
                  <i class="fa-regular fa-star"></i>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

        <!-- OFFRE CONDUCTEUR SECTION -->
    <section class="offre-conducteur-section" style="background-image: url('{{ asset('assets/images/sec2.png') }}'); ">
      <div class="offre-conducteur-container">
        <div class="offre-conducteur-badge">
          <span class="offre-badge-text">Offre Conducteur</span>
        </div>
        <h2 class="offre-conducteur-title">Récupérez <span class="offre-price">90 €</span> par trajet.</h2>
        <p class="offre-conducteur-desc">Vous avez une voiture ? Faites-la travailler pour vous (et pas l'inverse). Récupérez jusqu'à 90 € en covoiturant sur un trajet de 300 km avec 3 passagers.</p>
        <button class="offre-conducteur-btn">Publier un trajet</button>
      </div>
    </section>
    <section class="engagement-section">
      <div class="engagement-titre">
        <h2>Notre Engagement</h2>
        <div class="covoiturage-destinations-trait"></div>
      </div>
      <div class="engagement-block">
        <div class="engagement-block-card">
          <div class="engagement-contenue">
            <img src="{{ asset('assets/images/logo-engagement/logo1.png') }}" alt="">
            <h3>L'autonomie absolue</h3>
            <p>Libérez-vous des contraintes horaires. Explorez le pays selon vos propres règles grâce à notre écosystème combinant bus, covoiturage et rail.</p>
            <div class="covoiturage-destination-trait"></div>
            <p>Flexibilité illimitée</p>
          </div>
        </div>
        <div class="engagement-block-card">
          <div class="engagement-contenue">
            <img src="{{ asset('assets/images/logo-engagement/logo2.png') }}" alt="">
            <h3>Le luxe de l'épargne</h3>
            <p>Ne choisissez plus entre confort et budget. Accédez à un catalogue de destinations premium au tarif le plus juste du marché, sans frais cachés.</p>
            <div class="covoiturage-destination-trait"></div>
            <p>Meilleurs tarifs garantis</p>
          </div>
        </div>
        <div class="engagement-block-card">
          <div class="engagement-contenue">
            <img src="{{ asset('assets/images/logo-engagement/logo3.png') }}" alt="">
            <h3>Sérénité certifiée</h3>
            <p>Nous sélectionnons rigoureusement nos partenaires et vérifions manuellement chaque profil. Identité, avis et véhicules : tout est passé au crible.</p>
            <div class="covoiturage-destination-trait"></div>
            <p>Réseau 100% vérifié</p>
          </div>
        </div>
      </div>
    </section>
  </main>

    <x-footer />
    <script src="{{ asset('assets/js/script.js') }}"></script>
</body>