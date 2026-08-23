@extends('layouts.main')

@section('title', 'Contact - Olten.fr')

@section('content')
    <div id="success-toast" class="toast-success">
        <div class="toast-icon">✅</div>
        <div class="toast-message">
            <h4>Super !</h4>
            <p>Votre message a bien été envoyé. Nous vous répondrons très vite.</p>
        </div>
        <button class="toast-close">&times;</button>
    </div>

    <!---- HERO / MAP (full width) ------>
    <section class="hero-map-section">
        <div class="contact-map-container">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2786.8347106019554!2d4.542!3d45.497!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47f49b38a2c9f3d3%3A0x40cb7a0e1a85240!2sL'Horme%2C%2042152%20Loire%2C%20France!5e0!3m2!1sfr!2sfr!4v1696876475405!5m2!1sfr!2sfr"
                allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </section>

    <!---- INTRO ------>
    <section class="contact-intro">
        <span class="contact-badge">Besoin d'assistance ?</span>
        <h2>Contactez-nous</h2>
        <p>Notre équipe est à votre écoute pour toute question concernant vos locations, covoiturages ou livraisons sur Olten.fr.</p>
    </section>

    <!---- FORM + INFO ------>
    <section class="contact-contact-section">
        <div class="contact-form-wrapper">

            <div class="info-card">
                <h3>Coordonnées</h3>
                <p class="info-desc">Retrouvez toutes nos informations directes pour échanger avec notre équipe à tout moment.</p>

                <div class="info-row">
                    <span class="icon"><i class="fa-solid fa-envelope"></i></span>
                    <div class="info-content">
                        <span class="info-label">Email</span>
                        <a href="mailto:olten-location@outlook.fr">olten-location@outlook.fr</a>
                    </div>
                </div>

                <div class="info-row">
                    <span class="icon"><i class="fa-solid fa-location-dot"></i></span>
                    <div class="info-content">
                        <span class="info-label">Adresse</span>
                        <p>L'Horme, 42152, Département de la Loire, Région Auvergne-Rhône-Alpes, France.</p>
                    </div>
                </div>

                <a href="#" class="report-link">+ Rapport erreur</a>
            </div>

            <form action="{{ route('contact.store') }}" method="POST" class="contact-form">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Votre nom</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="firstname">Votre prénom</label>
                        <input type="text" id="firstname" name="firstname" value="{{ old('firstname') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="subject">Sujet</label>
                    <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required>
                </div>

                <div class="form-group">
                    <label for="message">Votre message (facultatif)</label>
                    <textarea id="message" name="message" rows="6">{{ old('message') }}</textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Envoyer</button>
                </div>
            </form>

        </div>
    </section>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toast = document.getElementById('success-toast');
                toast.classList.add('show');
                setTimeout(() => { toast.classList.remove('show'); }, 4000);
                toast.querySelector('.toast-close').addEventListener('click', () => {
                    toast.classList.remove('show');
                });
            });
        </script>
    @endif
@endsection