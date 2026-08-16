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

    <!---- HERO SECTION ------>
    <section class="">
        <div class="map-container w-100">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2786.8347106019554!2d4.542!3d45.497!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47f49b38a2c9f3d3%3A0x40cb7a0e1a85240!2sL'Horme%2C%2042152%20Loire%2C%20France!5e0!3m2!1sfr!2sfr!4v1696876475405!5m2!1sfr!2sfr"
                allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>

        <div class="d-flex">
            <div class="contact-contact-info w-50">
                <h2>Contactez-nous sur</h2>
                <hr class="separator">
                <p>
                    <a href="mailto:olten-location@outlook.fr">
                        olten-location@outlook.fr
                    </a>
                </p>
                <br>
                <p>
                    L’Horme, 42152 Département de la Loire,<br>
                    Région Auvergne-Rhône-Alpes, France
                </p>
            </div>

            <div class="w-50">
                <form action="{{ route('contact.store') }}" method="POST" class="contact-form">
                    @csrf

                    <div class="form-group">
                        <label for="name">Votre nom</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Votre e-mail</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="subject">Sujet</label>
                        <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="message">Votre message (facultatif)</label>
                        <textarea id="message" name="message" rows="6">{{ old('message') }}</textarea>
                    </div>

                    <button type="submit" class="btn-submit">Envoyer</button>
                </form>

            </div>
        </div>
    </section>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toast = document.getElementById('success-toast');
                toast.classList.add('show');

                // auto-hide après 4 secondes
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 4000);

                // fermeture manuelle
                toast.querySelector('.toast-close').addEventListener('click', () => {
                    toast.classList.remove('show');
                });
            });
        </script>
    @endif


@endsection
