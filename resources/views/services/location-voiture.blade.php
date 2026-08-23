@extends('layouts.main')

@section('title', 'Location de voiture - Olten.fr')

@section('content')

<div class="lv-page">

    {{-- Hero --}}
    <section class="lv-hero">
        <div class="lv-hero-bg">
            <img src="{{ asset('assets/images/location-voiture.jpg') }}" alt="Location de véhicules d'exception">
            <div class="lv-hero-overlay"></div>
        </div>

        <div class="lv-hero-content">
            <span class="lv-hero-tag">— Location de véhicules d'exception</span>
            <h1 class="lv-hero-title">Louez votre <span class="lv-hero-title-accent">liberté.</span></h1>
            <p class="lv-hero-subtitle">Découvrez une nouvelle façon de louer. Des véhicules premium, une sécurité totale et une expérience sans compromis.</p>
        </div>
    </section>

    {{-- Search bar — section séparée, chevauche le bas du hero --}}
    <section class="lv-search-section">
        <form class="lv-search-form" action="#" method="GET">
            <div class="lv-search-field">
                <label for="lvDeparture">Lieu de départ</label>
                <div class="lv-search-input">
                    <i class="fa-solid fa-location-dot"></i>
                    <input type="text" id="lvDeparture" name="departure" placeholder="Ville de départ">
                </div>
            </div>

            <div class="lv-search-field">
                <label for="lvReturnCity">Lieu de fin</label>
                <div class="lv-search-input">
                    <i class="fa-solid fa-location-dot"></i>
                    <input type="text" id="lvReturnCity" name="return_city" placeholder="Ville de retour">
                </div>
            </div>

            <div class="lv-search-field">
                <label for="lvStartDate">Date de départ</label>
                <div class="lv-search-input">
                    <input type="date" id="lvStartDate" name="start_date">
                </div>
            </div>

            <div class="lv-search-field">
                <label for="lvEndDate">Date de retour</label>
                <div class="lv-search-input">
                    <input type="date" id="lvEndDate" name="end_date">
                </div>
            </div>

            <div class="lv-search-field">
                <label for="lvVehicleType">Type de véhicule</label>
                <div class="lv-search-input">
                    <i class="fa-solid fa-car"></i>
                    <select id="lvVehicleType" name="vehicle_type">
                        <option value="">Sélectionner un véhicule</option>
                        <option value="voiture">Voiture</option>
                        <option value="suv">SUV</option>
                        <option value="monospace">Monospace</option>
                        <option value="utilitaire">Utilitaire</option>
                        <option value="moto">Moto</option>
                        <option value="camping-car">Camping-car</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="lv-search-btn">Rechercher</button>
        </form>
    </section>

    {{-- Vehicle types --}}
    <section class="lv-types">
        <h2 class="lv-section-title">Parcourez par type de véhicule</h2>

        <div class="lv-types-grid">
            <a href="#" class="lv-type-card">
                <span class="lv-type-icon"><i class="fa-solid fa-car"></i></span>
                <h3 class="lv-type-title">Voiture</h3>
                <p class="lv-type-desc">Véhicules citadins ou berlines, idéaux pour vos trajets quotidiens ou confortables.</p>
                <span class="lv-type-link">Voir les annonces <i class="fa-solid fa-arrow-right"></i></span>
            </a>

            <a href="#" class="lv-type-card">
                <span class="lv-type-icon"><i class="fa-solid fa-car-side"></i></span>
                <h3 class="lv-type-title">SUV</h3>
                <p class="lv-type-desc">Plus d'espace et de robustesse, idéal pour la famille et les longs trajets.</p>
                <span class="lv-type-link">Voir les annonces <i class="fa-solid fa-arrow-right"></i></span>
            </a>

            <a href="#" class="lv-type-card">
                <span class="lv-type-icon"><i class="fa-solid fa-shuttle-van"></i></span>
                <h3 class="lv-type-title">Monospace</h3>
                <p class="lv-type-desc">Espace généreux pour groupes ou familles nombreuses, confort assuré.</p>
                <span class="lv-type-link">Voir les annonces <i class="fa-solid fa-arrow-right"></i></span>
            </a>

            <a href="#" class="lv-type-card">
                <span class="lv-type-icon"><i class="fa-solid fa-truck"></i></span>
                <h3 class="lv-type-title">Utilitaire</h3>
                <p class="lv-type-desc">Pour vos déménagements et transports de marchandises en toute simplicité.</p>
                <span class="lv-type-link">Voir les annonces <i class="fa-solid fa-arrow-right"></i></span>
            </a>

            <a href="#" class="lv-type-card">
                <span class="lv-type-icon"><i class="fa-solid fa-motorcycle"></i></span>
                <h3 class="lv-type-title">Moto</h3>
                <p class="lv-type-desc">Liberté et agilité, parfait pour se déplacer rapidement en ville.</p>
                <span class="lv-type-link">Voir les annonces <i class="fa-solid fa-arrow-right"></i></span>
            </a>

            <a href="#" class="lv-type-card">
                <span class="lv-type-icon"><i class="fa-solid fa-caravan"></i></span>
                <h3 class="lv-type-title">Camping-car</h3>
                <p class="lv-type-desc">Voyagez en toute liberté avec votre maison sur roues, prêt pour l'aventure.</p>
                <span class="lv-type-link">Voir les annonces <i class="fa-solid fa-arrow-right"></i></span>
            </a>
        </div>
    </section>

    {{-- Recent listings --}}
    <section class="lv-recent">
        <div class="lv-recent-header">
            <h2 class="lv-section-title">Annonces récentes</h2>
            <a href="#" class="lv-recent-link">Voir toutes les annonces <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="lv-recent-grid">

            {{-- Carte 1 --}}
            <article class="lv-card">
                <div class="lv-card-media">
                    <img src="{{ asset('assets/images/categories/location-voiture.jpg') }}" alt="Peugeot 308">
                    <button type="button" class="lv-favorite-btn" aria-label="Ajouter aux favoris">
                        <i class="fa-regular fa-heart"></i>
                    </button>
                </div>
                <div class="lv-card-body">
                    <div class="lv-card-top">
                        <h3 class="lv-card-title">Peugeot 308</h3>
                        <span class="lv-card-price">40&nbsp;€<span>/jour</span></span>
                    </div>
                    <div class="lv-card-specs">
                        <span><i class="fa-solid fa-gear"></i> Auto</span>
                        <span><i class="fa-solid fa-gas-pump"></i> Diesel</span>
                        <span><i class="fa-solid fa-user"></i> 5 places</span>
                    </div>
                    <div class="lv-card-footer">
                        <div class="lv-card-agent">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Sofia M.">
                            <span>Sofia M.</span>
                        </div>
                        <a href="#" class="lv-card-arrow" aria-label="Voir détails">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </article>

            {{-- Carte 2 --}}
            <article class="lv-card">
                <div class="lv-card-media">
                    <img src="{{ asset('assets/images/categories/location-voiture.jpg') }}" alt="Peugeot 308">
                    <button type="button" class="lv-favorite-btn" aria-label="Ajouter aux favoris">
                        <i class="fa-regular fa-heart"></i>
                    </button>
                </div>
                <div class="lv-card-body">
                    <div class="lv-card-top">
                        <h3 class="lv-card-title">Peugeot 308</h3>
                        <span class="lv-card-price">40&nbsp;€<span>/jour</span></span>
                    </div>
                    <div class="lv-card-specs">
                        <span><i class="fa-solid fa-gear"></i> Auto</span>
                        <span><i class="fa-solid fa-gas-pump"></i> Diesel</span>
                        <span><i class="fa-solid fa-user"></i> 5 places</span>
                    </div>
                    <div class="lv-card-footer">
                        <div class="lv-card-agent">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Sofia M.">
                            <span>Sofia M.</span>
                        </div>
                        <a href="#" class="lv-card-arrow" aria-label="Voir détails">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </article>

            {{-- Carte 3 --}}
            <article class="lv-card">
                <div class="lv-card-media">
                    <img src="{{ asset('assets/images/categories/location-voiture.jpg') }}" alt="Peugeot 308">
                    <button type="button" class="lv-favorite-btn" aria-label="Ajouter aux favoris">
                        <i class="fa-regular fa-heart"></i>
                    </button>
                </div>
                <div class="lv-card-body">
                    <div class="lv-card-top">
                        <h3 class="lv-card-title">Peugeot 308</h3>
                        <span class="lv-card-price">40&nbsp;€<span>/jour</span></span>
                    </div>
                    <div class="lv-card-specs">
                        <span><i class="fa-solid fa-gear"></i> Auto</span>
                        <span><i class="fa-solid fa-gas-pump"></i> Diesel</span>
                        <span><i class="fa-solid fa-user"></i> 5 places</span>
                    </div>
                    <div class="lv-card-footer">
                        <div class="lv-card-agent">
                            <img src="{{ asset('assets/images/user-profile.webp') }}" alt="Sofia M.">
                            <span>Sofia M.</span>
                        </div>
                        <a href="#" class="lv-card-arrow" aria-label="Voir détails">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </article>

        </div>
    </section>

</div>

@endsection