<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Olten - Accès à la Plateforme</title>

    <!-- 1. Lien CSS de Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- 2. Police Moderne -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon/olten_location.ico') }}">
    <!-- 3. Styles Personnalisés Split Screen + Background Image -->
    <style>
        :root {
            /* Palette Olten */
            --olten-primary: #ff3b00;
            --olten-primary-hover: #e63400;
            /* Arrière-plan général */
            --bg-pro: #f5f8fb;
            --font-family-pro: 'Inter', sans-serif;
            --text-color: #333d47;
        }

        body {
            background-color: var(--bg-pro);
            font-family: var(--font-family-pro);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            align-items: stretch;
            /* Permet aux colonnes de prendre toute la hauteur */
            justify-content: center;
        }

        /* Conteneur principal qui assure que les deux colonnes ont la même hauteur */
        .login-split-container {
            width: 100%;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        /* ================================================= */
        /* PARTIE GAUCHE : IMAGE DE FOND (BACKGROUND IMAGE) */
        /* ================================================= */
        .illustration-panel {
            /* L'IMAGE DOIT ÊTRE ICI. Remplacer l'URL ci-dessous par votre image HD. */
            background-image: url('{{ asset('assets/images/illus/index.jpg') }}');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            position: relative;
            color: white;
            display: none;
            /* Masqué par défaut sur mobile */
        }

        /* Filtre Dark Overlay (indispensable pour la lisibilité du texte blanc) */
        .illustration-panel::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            /* Noir semi-transparent pour assombrir l'image. Plus sombre sur les bords. */
            background: linear-gradient(135deg, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0) 100%);
            z-index: 1;
        }

        /* Contenu de la colonne de gauche (texte et logo) */
        .illustration-content {
            position: relative;
            z-index: 2;
            /* S'assurer que le contenu est au-dessus de l'overlay */
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            /* Pour placer le branding en bas */
            height: 100%;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        }

        .illustration-content h3 {
            font-weight: 700;
            font-size: 2.2rem;
            line-height: 1.3;
            margin-bottom: 20px;
        }

        .illustration-content p {
            font-weight: 300;
            font-size: 1.1rem;
            opacity: 0.8;
        }

        /* Branding Olten en bas à gauche */
        .branding-bottom {
            font-size: 1.5rem;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--olten-primary);
            /* Utilisation du orange Olten pour le branding */
        }


        /* ================================================= */
        /* PARTIE DROITE : FORMULAIRE */
        /* ================================================= */
        .form-panel {
            background-color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .login-card-content {
            width: 100%;
            max-width: 420px;
        }

        .logo-section-top {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-section-top img {
            max-width: 150px;
            height: auto;
        }

        .card-title {
            font-weight: 700;
            font-size: 1.75rem;
            color: #1e293b;
        }

        .btn-olten-2026 {
            background-color: #e91d28;
            /* Même rouge que le sidebar actif */
            border-color: #e91d28;
            color: white;
            font-weight: 700;
            /* Plus gras comme dans le sidebar */
            padding: 14px;
            border-radius: 10px;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 4px 10px rgba(233, 29, 40, 0.25);
        }

        .btn-olten-2026:hover {
            background-color: #d11a24;
            /* Hover légèrement plus foncé */
            border-color: #d11a24;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(233, 29, 40, 0.4);
        }


        .form-control-lg {
            padding: 14px 18px;
            border-radius: 10px;
            border: 1px solid #e0e6ec;
        }

        .form-control:focus {
            border-color: var(--olten-primary);
            box-shadow: 0 0 0 3px rgba(255, 59, 0, 0.12);
            background-color: #fff;
        }

        /* Affichage de la colonne d'illustration sur les grands écrans */
        @media (min-width: 768px) {
            .illustration-panel {
                display: block !important;
            }
        }
    </style>
</head>

<body>

    <div class="login-split-container">
        <div class="row g-0 h-100">

            <div class="col-md-6 illustration-panel">
                <div class="illustration-content">


                </div>
            </div>

            <div class="col-12 col-md-6 form-panel d-flex align-items-center justify-content-center">
                <div class="login-card-content" style="max-width: 420px; width: 100%;">

                    <!-- Logo centré -->
                    <div class="logo-section-top text-center mb-4">
                        <img src="{{ asset('assets/images/logo/olten_location.png') }}" alt="Logo Olten"
                            class="img-fluid" style="max-width: 140px;">
                    </div>



                    <!-- Sous-titre -->
                    <p class="text-center text-muted mb-4" style="font-size: 0.95rem;">
                        Connectez-vous pour accéder à votre tableau de bord et gérer vos opérations.
                    </p>

                    <!-- Formulaire -->
                    <form action="{{ route('admin.login.submit') }}" method="POST">
                        @csrf

                        @error('email')
                            <div class="alert alert-danger py-2 px-3 rounded" role="alert">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="mb-3">
                            <label for="email" class="form-label fw-medium">Adresse e-mail</label>
                            <input type="email" name="email" id="email" class="form-control form-control-lg"
                                placeholder="votre.email@exemple.com" required value="{{ old('email') }}">
                        </div>

                        <div class="mb-4">
                            <label for="mot_de_passe" class="form-label fw-medium">Mot de passe</label>
                            <input type="password" name="mot_de_passe" id="mot_de_passe"
                                class="form-control form-control-lg" placeholder="••••••••" required>
                        </div>

                        <button type="submit" class="btn btn-olten-2026 w-100 mb-3 fw-semibold">
                            Se connecter
                        </button>


                    </form>

                    <p class="text-center text-muted mt-5 mb-0" style="font-size: 0.85rem;">
                        Olten &copy; {{ date('Y') }} - Gérer votre éspace
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- Script JS de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>
