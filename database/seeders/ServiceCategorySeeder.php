<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Structure standard de la plateforme :
 *
 *   SERVICE
 *      |
 *      +-- Categories (sous-services)
 *      |        +-- Vehicules
 *      |        +-- Immobilier
 *      |        +-- ...
 *      |
 *      +-- Annonces (rattachees a une categorie)
 *
 * "Vehicules", "Immobilier", "Informatique" ne sont pas des services :
 * ce sont les categories du service Vente. Ce seeder construit cette
 * arborescence pour chaque service, en s'appuyant sur son slug.
 *
 * Il est idempotent : on peut le rejouer sans creer de doublon.
 */
class ServiceCategorySeeder extends Seeder
{
    /**
     * slug du service => [nom, icone, description] de chacune de ses categories.
     */
    private const TREE = [
        'vente' => [
            ['Véhicules',              'fa-solid fa-car',                'Voitures, motos, pièces détachées et accessoires.'],
            ['Immobilier',             'fa-solid fa-house',              'Appartements, maisons, terrains et locaux.'],
            ['Téléphones & Tablettes', 'fa-solid fa-mobile-screen',      'Smartphones, tablettes et accessoires.'],
            ['Informatique',           'fa-solid fa-laptop',             'Ordinateurs, composants et périphériques.'],
            ['Électronique',           'fa-solid fa-tv',                 'TV, son, photo et objets connectés.'],
            ['Maison & Jardin',        'fa-solid fa-couch',              'Meubles, décoration, électroménager et jardin.'],
            ['Mode & Accessoires',     'fa-solid fa-shirt',              'Vêtements, chaussures, montres et bijoux.'],
            ['Sports & Loisirs',       'fa-solid fa-futbol',             'Équipements sportifs, vélos et instruments.'],
            ['Enfants & Bébé',         'fa-solid fa-baby-carriage',      'Puériculture, jouets et vêtements enfants.'],
            ['Beauté & Bien-être',     'fa-solid fa-spa',                'Cosmétiques, parfums et matériel de soin.'],
            ['Livres & Culture',       'fa-solid fa-book',               'Livres, jeux, films et instruments de musique.'],
            ['Animaux',                'fa-solid fa-paw',                'Animaux, accessoires et alimentation.'],
            ['Bricolage & Outils',     'fa-solid fa-screwdriver-wrench', 'Outillage, matériaux et équipement de chantier.'],
        ],

        'location' => [
            ['Voitures',             'fa-solid fa-car-side',          'Citadines, berlines, SUV et véhicules premium.'],
            ['Utilitaires',          'fa-solid fa-truck-pickup',      'Camionnettes et fourgons pour vos transports.'],
            ['Motos & Scooters',     'fa-solid fa-motorcycle',        'Deux-roues à la journée ou à la semaine.'],
            ['Logements',            'fa-solid fa-house-chimney',     'Appartements, maisons et locations saisonnières.'],
            ['Salles & Événements',  'fa-solid fa-champagne-glasses', 'Salles de fête, séminaires et réceptions.'],
            ['Matériel de chantier', 'fa-solid fa-helmet-safety',     'Engins, échafaudages et outillage professionnel.'],
            ['Matériel audiovisuel', 'fa-solid fa-camera',            'Photo, vidéo, sonorisation et éclairage.'],
            ['Sport & Plein air',    'fa-solid fa-person-hiking',     'Vélos, skis, camping et équipements outdoor.'],
            ['Bateaux',              'fa-solid fa-sailboat',          'Bateaux, jet-skis et matériel nautique.'],
        ],

        'covoiturage' => [
            ['Trajets quotidiens', 'fa-solid fa-route',           'Domicile-travail et trajets réguliers.'],
            ['Longue distance',    'fa-solid fa-road',            'Trajets entre villes et inter-régions.'],
            ['Aéroport & Gare',    'fa-solid fa-plane-departure', 'Navettes vers les aéroports et les gares.'],
            ['Événements',         'fa-solid fa-calendar-day',    'Concerts, matchs, mariages et festivals.'],
            ['Covoiturage colis',  'fa-solid fa-box',             'Transport de colis sur un trajet existant.'],
        ],

        'livraison' => [
            ['Colis & Paquets',      'fa-solid fa-box',             'Petits colis et plis à livrer rapidement.'],
            ['Repas',                'fa-solid fa-utensils',        'Livraison de repas et de plats préparés.'],
            ['Courses',              'fa-solid fa-basket-shopping', 'Courses alimentaires et achats du quotidien.'],
            ['Déménagement',         'fa-solid fa-truck-ramp-box',  'Transport de cartons et petit déménagement.'],
            ['Meubles & Volumineux', 'fa-solid fa-couch',           'Objets lourds, meubles et électroménager.'],
            ['Express / Coursier',   'fa-solid fa-bolt',            'Livraison urgente en moins de deux heures.'],
        ],

        'prestations-de-services' => [
            ['Bricolage & Réparation',    'fa-solid fa-screwdriver-wrench', 'Plomberie, électricité, peinture et montage.'],
            ['Ménage & Repassage',        'fa-solid fa-broom',              'Entretien du domicile et des locaux.'],
            ['Jardinage',                 'fa-solid fa-seedling',           'Tonte, taille, élagage et entretien des espaces verts.'],
            ['Cours & Soutien scolaire',  'fa-solid fa-graduation-cap',     'Cours particuliers et accompagnement scolaire.'],
            ['Informatique & Web',        'fa-solid fa-code',               'Dépannage, développement et création de sites.'],
            ['Beauté & Bien-être',        'fa-solid fa-spa',                'Coiffure, esthétique et massages à domicile.'],
            ['Événementiel',              'fa-solid fa-music',              'DJ, traiteur, photographe et animation.'],
            ['Santé & Aide à domicile',   'fa-solid fa-hand-holding-heart', 'Aide aux personnes, garde d enfants et soins.'],
            ['Administratif & Juridique', 'fa-solid fa-file-signature',     'Comptabilité, traduction et démarches.'],
        ],

        'appels-doffres' => [
            ['Bâtiment & Travaux publics', 'fa-solid fa-helmet-safety', 'Construction, rénovation et voirie.'],
            ['Fournitures & Équipements',  'fa-solid fa-boxes-stacked', 'Achat de matériel et de consommables.'],
            ['Informatique & Télécoms',    'fa-solid fa-network-wired', 'Logiciels, infrastructure et réseaux.'],
            ['Transport & Logistique',     'fa-solid fa-truck-fast',    'Acheminement, stockage et distribution.'],
            ['Études & Conseil',           'fa-solid fa-chart-line',    'Audit, ingénierie et assistance à maîtrise ouvrage.'],
            ['Énergie & Environnement',    'fa-solid fa-leaf',          'Énergies renouvelables, déchets et assainissement.'],
        ],
    ];

    public function run(): void
    {
        foreach (self::TREE as $serviceSlug => $categories) {
            $service = Service::where('slug', $serviceSlug)->first();

            if (! $service) {
                $this->command?->warn("Service « {$serviceSlug} » introuvable : categories ignorees.");
                continue;
            }

            // Les anciennes categories qui ne faisaient que dupliquer le nom du
            // service sont converties en vraie premiere categorie : leur id est
            // conserve, donc les annonces et produits deja rattaches restent valides.
            $this->convertMirrorCategory($service, $categories[0]);

            foreach ($categories as [$nom, $icon, $description]) {
                Category::updateOrCreate(
                    [
                        'service_id' => $service->id,
                        'slug'       => Str::slug($nom),
                    ],
                    [
                        'nom'         => $nom,
                        'icon'        => $icon,
                        'description' => $description,
                    ]
                );
            }

            $this->command?->info("Service « {$service->nom} » : " . count($categories) . ' categories.');
        }
    }

    /**
     * Reprend la categorie « miroir » (celle qui portait le nom du service)
     * et la transforme en premiere categorie reelle du service.
     */
    private function convertMirrorCategory(Service $service, array $first): void
    {
        $mirror = Category::where('service_id', $service->id)
            ->get()
            ->first(function (Category $category) use ($service) {
                return Str::slug($category->nom) === Str::slug($service->nom)
                    || $category->slug === $service->slug;
            });

        if (! $mirror) {
            return;
        }

        [$nom, $icon, $description] = $first;

        // Deja convertie lors d'un precedent passage du seeder.
        if (Str::slug($nom) === $mirror->slug) {
            return;
        }

        $mirror->forceFill([
            'nom'         => $nom,
            'slug'        => Str::slug($nom),
            'icon'        => $icon,
            'description' => $description,
        ])->save();
    }
}
