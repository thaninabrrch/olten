<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Olten.fr - créer un site</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <!---------Swiper------------>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <!-- Feuille de style  -->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body>
  <x-header />

  <main>
        <!----HERO SECTION------>
      <section class="hero-section">
          <div class="hero-content">
              <h1>Demander un devis</h1>
          </div>
      </section>
        <!----Form SECTION------>
      <section class="create-site-section">
          <div class="container">

            <form class="create-site-form" id="createSiteForm">
              <div class="form-group">
                <label for="name">Nom complet :</label>
                <input type="text" id="name" name="name" placeholder="Votre nom complet" required>
              </div>

              <div class="form-group">
                <label for="email">Adresse e-mail :</label>
                <input type="email" id="email" name="email" placeholder="votre@email.com" required>
              </div>

              <div class="form-group">
                <label for="phone">Téléphone :</label>
                <input type="tel" id="phone" name="phone" placeholder="+33 6 00 00 00 00">
              </div>

              <div class="form-group">
                <label for="siteType">Type de site souhaité :</label>
                <select id="siteType" name="siteType" required>
                  <option value="">— Veuillez choisir une option —</option>
                  <option value="Blog">Blog</option>
                  <option value="Site vitrine">Site vitrine</option>
                  <option value="E-commerce">E-commerce</option>
                </select>
              </div>

              <div class="form-group">
                <label for="budget">Budget estimé :</label>
                <input type="text" id="budget" name="budget" value="Sélectionnez un type de site" readonly>
              </div>

              <div class="form-group">
                <label for="features">Fonctionnalités souhaitées :</label>
                <textarea id="features" name="features" rows="3" placeholder="Décrivez vos besoins..."></textarea>
              </div>

              <div class="form-group">
                <label for="deadline">Délai souhaité :</label>
                <input type="text" id="deadline" name="deadline" placeholder="Ex : 2 mois">
              </div>

              <div class="form-group">
                <label for="message">Autres précisions :</label>
                <textarea id="message" name="message" rows="3" placeholder="Autres détails utiles..."></textarea>
              </div>

              <button type="submit" class="btn-submit">Envoyer</button>
            </form>
          </div>
        </section>
  </main>
  <x-footer />

  <script src="{{ asset('assets/js/script.js') }}"></script>
</body>
</html>