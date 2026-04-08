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
    <section class="intermediaire-hero-section" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('{{ asset('assets/images/voitures-fdc.png') }}') center/cover no-repeat;">
      <div class="hero-contenue">
        <div class="intermediaire-hero-badge">
          <div class="intermediaire-hero-trait"></div>
          <p>Mobilité Nouvelle Génération</p>
        </div>
        <h1 class="intermediaire-hero-title">Redéfinir vos <br> <span class="orange-accent">trajets.</span></h1>
        <p class="intermediaire-hero-desc">Une plateforme unique pour le covoiturage intelligent et la location de véhicules d'exception. Simple, sûr, premium.</p>
      </div>


      <!-- container location/covoiturage -->
  <div class="container-transport">
    
    <!-- TABS NAVIGATION (Liste) -->
    <ul class="tabs-list">
      <li class="tab-item " data-tab="covoiturage">
        <a href="#covoiturage" class="tab-link">
          <i class="fa-solid fa-car"></i> Covoiturage
        </a>
      </li>
      <li class="tab-item" data-tab="location">
        <a href="#location" class="tab-link">
          <i class="fa-solid fa-car"></i> Location
        </a>
      </li>
    </ul>

    <!-- TAB CONTENT - COVOITURAGE -->
    <div class="tab-content tab-1" id="covoiturage">
      <div class="tab-content-inner">
        <div class="tab-header">
          <div class="tab-left">
            <h3>Covoiturage Intelligent</h3>
            <p>Partagez vos frais et voyagez sereinement avec des membres certifiés de la communauté Olten.</p>
          </div>
          <div class="tab-avatars">
            <div class="avatar"><img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="User 1"></div>
            <div class="avatar"><img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="User 2"></div>
            <div class="avatar"><img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="User 3"></div>
            <div class="avatar plus">+1k</div>
          </div>
        </div>
        
        <div class="tab-footer">
          <div class="info-fields">
            <div class="info-item">
              <span class="label">DÉPART</span>
              <span class="value">Paris, France</span>
            </div>
            <div class="info-divider"></div>
            <div class="info-item">
              <span class="label">DESTINATION</span>
              <span class="value">Lyon, France</span>
            </div>
            <button class="btn-primary" onclick="window.location.href='{{ route('covoiturages') }}';">Trouver un trajet</button>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB CONTENT - LOCATION -->
    <div class="tab-content" id="location">
      <div class="tab-content-inner">
        <div class="tab-header">
          <div class="tab-left">
            <h3>Location de Véhicule</h3>
            <p>Accédez à notre catalogue exclusif : des citadines électriques aux berlines de luxe pour vos besoins spécifiques.</p>
          </div>
          <div class="tab-avatars">
            <div class="avatar"><img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="Vehicle 1"></div>
            <div class="avatar"><img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="Vehicle 2"></div>
            <div class="avatar"><img src="{{ asset('assets/images/photo-profil/provi-profil.jpg') }}" alt="Vehicle 3"></div>
            <div class="avatar plus">+2k</div>
          </div>
        </div>
        
        <div class="tab-footer">
          <div class="info-fields">
            <div class="info-item">
              <span class="label">RÉCUPÉRATION</span>
              <span class="value">Aéroport CDG</span>
            </div>
            <div class="info-divider"></div>
            <div class="info-item">
              <span class="label">PÉRIODE</span>
              <span class="value">3 Jours</span>
            </div>
            <button class="btn-primary" onclick="window.location.href='{{ route('location.vehicule') }}';">Voir les véhicules</button>
          </div>
        </div>
      </div>
    </div>

  </div>
    </section>
    <section class="presentation-olten">
      <div class="texte-present">
        <h2>Pourquoi faire confiance à <span class="orange-accent">Olten Location</span> ?</h2>
        
        <div>
          <div class="info-item-container">
            <i class="fa-solid fa-shield info-item-icon"></i>
            <div>
              <h3>Sécurité & Vérification</h3>
              <p>Chaque conducteur et chaque véhicule passe par un processus de certification rigoureux.</p>
            </div>
          </div>
          
          <div class="info-item-container">
            <i class="fa-solid fa-lock info-item-icon"></i>
            <div>
              <h3>Paiement Sécurisé</h3>
              <p>Transactions protégées et remboursement garanti en cas d'annulation justifiée.</p>
            </div>
          </div>
          
          <div class="info-item-container">
            <i class="fa-solid fa-leaf info-item-icon"></i>
            <div>
              <h3>Engagement Éco-responsable</h3>
              <p>Nous favorisons les trajets partagés et les véhicules à faible émission.</p>
            </div>
          </div>
        </div>
      </div>

        <img src="{{ asset('assets/images/sec1.png') }}" alt="Olten Location - Confiance">
    </section>
    <!-- INFO SECTION -->


  </main>

  <x-footer />

    <script src="{{ asset('assets/js/script.js') }}"></script>
  
</body>
</html>
