<footer class="footer">
  <div class="footer-container">

    <!-- Logo + description -->
    <div class="footer-section logo-section">
      <img src="{{ asset('assets/images/logo/olten_location.jpg') }}" alt="Olten Location" class="footer-logo">
      <p>
        Avec Olten.fr, trouvez ce qu’il vous faut près de chez vous, 
        ou mettez vos propres affaires en location pour arrondir vos fins de mois.
      </p>
    </div>

    <!-- Nos pages -->
    <div class="footer-section links-section">
      <h3>Nos Pages</h3>
      <ul>
        <li class="d-flex">
          <a class="d-flex gap-2" href="/" >
            <i class="fas fa-home category-icon-small"></i> Accueil
          </a>
        </li>
        @foreach($footerCategories as $category)
          <li class="d-flex">
              <a href="{{ route('categories.show', $category->slug) }}" class="d-flex gap-2">
                <i class="{{ $category->icon }} category-icon-small"></i> {{ $category->nom }}
            </a>
          </li>
        @endforeach
        <li class="d-flex">
          <a class="d-flex gap-2" href="/contact">
            <i class="fas fa-envelope category-icon-small"></i> Contact
          </a>
        </li>
      </ul>
    </div>

    <!-- Contact -->
    <div class="footer-section contact-section">
      <h3>Contactez-nous</h3>
      <p>E-Mail: <a href="mailto:olten-location@outlook.fr">olten-location@outlook.fr</a></p><br>
      <p>
        Adresse: L'Horme, 42152, département de la Loire,<br>
        région Auvergne-Rhône-Alpes, France.
      </p><br>
      <div class="social-icons">
        <a href="#"><i class="fab fa-linkedin"></i></a>
        <a href="#"><i class="fab fa-facebook"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
      </div>
    </div>

  </div>

  <!-- Bas du footer -->
  <div class="footer-bottom">
    © 2025 Tous droits réservés. <a href="#">olten.fr</a>
  </div>

  <!-- Bouton retour haut -->
  <a href="#" class="scroll-top"><i class="fas fa-angle-up"></i></a>
</footer>
