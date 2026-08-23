@extends('layouts.main')

@section('title', 'Covoiturage - Olten.fr')

@section('content')

<div class="cv-page">

    {{-- Hero --}}
    <section class="cv-hero">
        <div class="cv-hero-bg">
            <img src="{{ asset('assets/images/location-voiture.jpg') }}" alt="Covoiturage">
            <div class="cv-hero-overlay"></div>
        </div>

        <div class="cv-hero-content">
            <span class="cv-hero-tag">— Axé sur le trajet personnalisé</span>
            <h1 class="cv-hero-title">Réservez votre <span class="cv-hero-title-accent">trajet.</span></h1>
            <p class="cv-hero-subtitle">Profitez d'une expérience de covoiturage unique. Confort premium, sécurité totale et flexibilité à la demande.</p>
        </div>
    </section>

    {{-- Search bar — section séparée, chevauche le bas du hero --}}
    <section class="cv-search-section">
        <form class="cv-search-form" action="#" method="GET">
            <div class="cv-search-field">
                <label for="cvDeparture">Lieu de départ</label>
                <div class="cv-search-input">
                    <i class="fa-solid fa-location-dot"></i>
                    <input type="text" id="cvDeparture" name="departure" placeholder="Ville de départ">
                </div>
            </div>

            <div class="cv-search-field">
                <label for="cvArrival">Lieu de fin</label>
                <div class="cv-search-input">
                    <i class="fa-solid fa-location-dot"></i>
                    <input type="text" id="cvArrival" name="arrival" placeholder="Ville d'arrivée">
                </div>
            </div>

            <div class="cv-search-field">
                <label for="cvStartDate">Date de départ</label>
                <div class="cv-search-input">
                    <input type="date" id="cvStartDate" name="start_date">
                </div>
            </div>

            <div class="cv-search-field">
                <label for="cvEndDate">Date de retour</label>
                <div class="cv-search-input">
                    <input type="date" id="cvEndDate" name="end_date">
                </div>
            </div>

            <div class="cv-search-field">
                <label for="cvPersons">Nombre de personnes</label>
                <div class="cv-search-input">
                    <i class="fa-solid fa-user-group"></i>
                    <select id="cvPersons" name="persons">
                        <option value="1">1 personne</option>
                        <option value="2">2 personnes</option>
                        <option value="3">3 personnes</option>
                        <option value="4">4 personnes</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="cv-search-btn">Rechercher</button>
        </form>
    </section>

    {{-- Destinations disponible --}}
    <section class="cv-destinations">
        <div class="cv-destinations-header">
            <h2 class="cv-section-title">Destinations disponible</h2>
            <a href="#" class="cv-destinations-link">Voir tous les trajets</a>
        </div>

        <div class="cv-destinations-grid">

            {{-- Trajet 1 --}}
            <article class="cv-card">
                <div class="cv-card-media">
                    <img src="{{ asset('assets/images/categories/paris.jpg') }}" alt="Paris - Lyon">
                </div>
                <div class="cv-card-body">
                    <h3 class="cv-card-route">Paris <i class="fa-solid fa-arrow-right"></i> Lyon</h3>
                    <p class="cv-card-date">Lieu de départ</p>
                    <div class="cv-card-top">
                        <span class="cv-card-price">12.00&nbsp;€</span>
                        <a href="#" class="cv-card-arrow" aria-label="Voir le trajet">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="cv-card-footer">
                        <div class="cv-card-avatars">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Passager">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Passager">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Passager">
                        </div>
                        <span class="cv-card-status">Complet</span>
                    </div>
                </div>
            </article>

            {{-- Trajet 2 --}}
            <article class="cv-card">
                <div class="cv-card-media">
                    <img src="{{ asset('assets/images/categories/paris.jpg') }}" alt="Paris - Lyon">
                </div>
                <div class="cv-card-body">
                    <h3 class="cv-card-route">Paris <i class="fa-solid fa-arrow-right"></i> Lyon</h3>
                    <p class="cv-card-date">Lieu de départ</p>
                    <div class="cv-card-top">
                        <span class="cv-card-price">12.00&nbsp;€</span>
                        <a href="#" class="cv-card-arrow" aria-label="Voir le trajet">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="cv-card-footer">
                        <div class="cv-card-avatars">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Passager">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Passager">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Passager">
                        </div>
                        <span class="cv-card-status">Complet</span>
                    </div>
                </div>
            </article>

            {{-- Trajet 3 --}}
            <article class="cv-card">
                <div class="cv-card-media">
                    <img src="{{ asset('assets/images/categories/paris.jpg') }}" alt="Paris - Lyon">
                </div>
                <div class="cv-card-body">
                    <h3 class="cv-card-route">Paris <i class="fa-solid fa-arrow-right"></i> Lyon</h3>
                    <p class="cv-card-date">Lieu de départ</p>
                    <div class="cv-card-top">
                        <span class="cv-card-price">12.00&nbsp;€</span>
                        <a href="#" class="cv-card-arrow" aria-label="Voir le trajet">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="cv-card-footer">
                        <div class="cv-card-avatars">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Passager">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Passager">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Passager">
                        </div>
                        <span class="cv-card-status">Complet</span>
                    </div>
                </div>
            </article>

            {{-- Trajet 4 --}}
            <article class="cv-card">
                <div class="cv-card-media">
                    <img src="{{ asset('assets/images/categories/paris.jpg') }}" alt="Paris - Lyon">
                </div>
                <div class="cv-card-body">
                    <h3 class="cv-card-route">Paris <i class="fa-solid fa-arrow-right"></i> Lyon</h3>
                    <p class="cv-card-date">Lieu de départ</p>
                    <div class="cv-card-top">
                        <span class="cv-card-price">12.00&nbsp;€</span>
                        <a href="#" class="cv-card-arrow" aria-label="Voir le trajet">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="cv-card-footer">
                        <div class="cv-card-avatars">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Passager">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Passager">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Passager">
                        </div>
                        <span class="cv-card-status">Complet</span>
                    </div>
                </div>
            </article>

            {{-- Trajet 5 --}}
            <article class="cv-card">
                <div class="cv-card-media">
                    <img src="{{ asset('assets/images/categories/paris.jpg') }}" alt="Paris - Lyon">
                </div>
                <div class="cv-card-body">
                    <h3 class="cv-card-route">Paris <i class="fa-solid fa-arrow-right"></i> Lyon</h3>
                    <p class="cv-card-date">Lieu de départ</p>
                    <div class="cv-card-top">
                        <span class="cv-card-price">12.00&nbsp;€</span>
                        <a href="#" class="cv-card-arrow" aria-label="Voir le trajet">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="cv-card-footer">
                        <div class="cv-card-avatars">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Passager">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Passager">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Passager">
                        </div>
                        <span class="cv-card-status">Complet</span>
                    </div>
                </div>
            </article>

            {{-- Trajet 6 --}}
            <article class="cv-card">
                <div class="cv-card-media">
                    <img src="{{ asset('assets/images/categories/paris.jpg') }}" alt="Paris - Lyon">
                </div>
                <div class="cv-card-body">
                    <h3 class="cv-card-route">Paris <i class="fa-solid fa-arrow-right"></i> Lyon</h3>
                    <p class="cv-card-date">Lieu de départ</p>
                    <div class="cv-card-top">
                        <span class="cv-card-price">12.00&nbsp;€</span>
                        <a href="#" class="cv-card-arrow" aria-label="Voir le trajet">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="cv-card-footer">
                        <div class="cv-card-avatars">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Passager">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Passager">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Passager">
                        </div>
                        <span class="cv-card-status">Complet</span>
                    </div>
                </div>
            </article>

        </div>
    </section>

    {{-- Bannière conducteur --}}
    <section class="cv-driver-banner">
            <div class="cv-driver-bg">
                <img src="{{ asset('assets/images/conducteur.jpg') }}" alt="Devenir conducteur">
                <div class="cv-driver-overlay"></div>

                <div class="cv-driver-content">
                    <span class="cv-driver-badge">Offre Conducteur</span>
                    <h2 class="cv-driver-title">Récupérez <span class="cv-driver-title-accent">90&nbsp;€</span> par trajet.</h2>
                    <p class="cv-driver-subtitle">Vous avez une voiture ? Faites-la travailler pour vous (et pas l'inverse). Récupérez jusqu'à 90&nbsp;€ en covoiturage sur un trajet de 300&nbsp;km avec 3 passagers.</p>
                    <a href="#" class="cv-driver-btn">Publier un trajet</a>
                </div>
            </div>
    </section>

    {{-- Notre engagement --}}
    <section class="cv-engagement">
        <h2 class="cv-section-title">Notre Engagement</h2>

        <div class="cv-engagement-grid">
            <div class="cv-engagement-card">
                <span class="cv-engagement-icon"><i class="fa-solid fa-hand"></i></span>
                <h3 class="cv-engagement-title">L'autonomie absolue</h3>
                <p class="cv-engagement-desc">Libérez-vous des contraintes horaires. Organisez le trajet selon vos propres règles, sans jamais compromettre votre confort.</p>
            </div>

            <div class="cv-engagement-card">
                <span class="cv-engagement-icon"><i class="fa-solid fa-dollar-sign"></i></span>
                <h3 class="cv-engagement-title">Le luxe de l'épargne</h3>
                <p class="cv-engagement-desc">Ne renoncez plus jamais au confort par souci de budget. Accédez à un partage des dépenses qui préserve votre fin de mois tout en offrant tout le confort recherché.</p>
            </div>

            <div class="cv-engagement-card">
                <span class="cv-engagement-icon"><i class="fa-regular fa-circle-check"></i></span>
                <h3 class="cv-engagement-title">Sérénité certifiée</h3>
                <p class="cv-engagement-desc">Nous sélectionnons rigoureusement nos partenaires et vérifions chaque profil pour garantir votre sécurité à chaque instant, sur chaque trajet.</p>
            </div>
        </div>
    </section>

</div>

@endsection