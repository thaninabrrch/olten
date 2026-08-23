<footer class="site-footer">
    <div class="footer-container">

        <!-- Logo + description -->
        <div class="footer-section logo-section">
            <img src="{{ asset('assets/images/logo/olten_location.png') }}" alt="Olten Location" class="footer-logo">
            <p>
                Avec Olten.fr, trouvez ce qu’il vous faut près de chez vous,
                ou mettez vos propres affaires en location pour arrondir vos fins de mois.
            </p>
            <div class="social-icons">
                <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            </div>
        </div>

        <!-- Nos pages -->
        <div class="footer-section links-section">
            <h3>Nos Pages</h3>
            <ul>
                <li><a href="/">Accueil</a></li>
                <li><a href="/contact">Contact</a></li>
                <li><a href="#about">À propos d'Olten</a></li>
                <li><a href="#faq">FAQ</a></li>
            </ul>
        </div>

        <!-- Catégories -->
        <div class="footer-section categories-section-footer">
            <h3>Catégories</h3>
            <ul>
                @foreach($footerCategories as $category)
                    <li>
                        <a href="{{ route('categories.show', $category->slug) }}">
                            {{ $category->nom }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Contact -->
        <div class="footer-section contact-section">
            <h3>Contactez-nous</h3>
            <p>E-Mail: <a href="mailto:olten-location@outlook.fr">olten-location@outlook.fr</a></p>
            <p>
                Adresse: L'Horme, 42152, département de la Loire,<br>
                région Auvergne-Rhône-Alpes, France.
            </p>
        </div>

    </div>

    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} Olten.fr — Tous droits réservés.</p>
    </div>
</footer>