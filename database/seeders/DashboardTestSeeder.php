<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Service;
use App\Models\TypeService;
use App\Models\Covoiturage;
use App\Models\ContactMessage;
use App\Models\ProductSale;
use Carbon\Carbon;

class DashboardTestSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. Utilisateurs par rôle avec created_at répartis sur 6 mois ---
        $roles = [
            'particulier' => 8,
            'livreur'     => 6,
            'conducteur'  => 5,
            'locataire'   => 3,
        ];

        $firstNames = ['Karim','Amina','Youcef','Fatima','Mehdi','Nadia','Amine','Sara','Omar','Leila','Bilal','Rania','Sofiane','Dounia','Tarek'];
        $lastNames  = ['Benali','Meziane','Hamdi','Khelil','Zaoui','Djebbar','Mansouri','Bouali','Ferhat','Lahlou'];
        $userIndex  = 0;

        foreach ($roles as $role => $count) {
            for ($i = 0; $i < $count; $i++) {
                $fn = $firstNames[$userIndex % count($firstNames)];
                $ln = $lastNames[$userIndex % count($lastNames)];
                $monthsAgo = $userIndex % 6;
                $createdAt = Carbon::now()->subMonths($monthsAgo)->subDays(rand(0, 25));

                User::updateOrCreate(
                    ['email' => strtolower("{$fn}.{$ln}.{$role}{$i}@test.com")],
                    [
                        'name'       => "{$fn} {$ln}",
                        'firstname'  => $fn,
                        'lastname'   => $ln,
                        'password'   => Hash::make('password'),
                        'role'       => $role,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]
                );
                $userIndex++;
            }
        }

        // --- 2. Types de service + Services ---
        $types = ['Transport', 'Livraison', 'Location', 'Restauration'];
        foreach ($types as $typeName) {
            TypeService::firstOrCreate(['nom' => $typeName]);
        }

        $servicesList = [
            ['nom' => 'Livraison Express Urbaine',  'type' => 'Livraison'],
            ['nom' => 'Covoiturage Longue Distance', 'type' => 'Transport'],
            ['nom' => 'Location Véhicule Utilitaire','type' => 'Location'],
            ['nom' => 'Livraison Repas Domicile',    'type' => 'Restauration'],
            ['nom' => 'VTC Premium',                 'type' => 'Transport'],
            ['nom' => 'Livraison Colis Inter-Ville', 'type' => 'Livraison'],
        ];

        foreach ($servicesList as $s) {
            $type = TypeService::where('nom', $s['type'])->first();
            if ($type) {
                Service::firstOrCreate(
                    ['nom' => $s['nom']],
                    ['description' => 'Service de test — ' . $s['nom'], 'type_service_id' => $type->id]
                );
            }
        }

        // --- 3. Covoiturages avec statuts variés ---
        $conducteurs = User::where('role', 'conducteur')->pluck('id')->toArray();
        if (empty($conducteurs)) {
            $conducteurs = User::pluck('id')->toArray();
        }

        $trajets = [
            ['Alger', 'Oran'], ['Alger', 'Constantine'], ['Annaba', 'Sétif'],
            ['Blida', 'Médéa'], ['Tizi Ouzou', 'Alger'], ['Béjaïa', 'Jijel'],
            ['Oran', 'Tlemcen'], ['Constantine', 'Skikda'],
        ];
        $statuts = ['actif', 'actif', 'actif', 'pending', 'pending', 'inactif'];

        for ($i = 0; $i < 12; $i++) {
            $trajet = $trajets[$i % count($trajets)];
            Covoiturage::create([
                'conducteur_id'          => $conducteurs[$i % count($conducteurs)],
                'depart'                 => $trajet[0],
                'destination'            => $trajet[1],
                'date_depart'            => Carbon::now()->addDays(rand(1, 30)),
                'heure_depart'           => sprintf('%02d:%02d', rand(6, 20), rand(0, 1) * 30),
                'nb_places'              => rand(1, 4),
                'prix_place'             => rand(500, 2500),
                'commission_plateforme'  => 10,
                'prix_total_affiche'     => rand(500, 2500),
                'statut'                 => $statuts[$i % count($statuts)],
                'reglementation_applicable' => 'Standard',
                'passenger_mode'         => 'normal',
                'booking_mode'           => 'instant',
            ]);
        }

        // --- 4. Messages de contact ---
        $sujets = ['Problème de paiement', 'Question sur la livraison', 'Signalement utilisateur', 'Demande de partenariat', 'Bug sur l\'application'];
        $noms   = ['Ali Bensalem', 'Meriem Hadj', 'Rachid Ouali', 'Houda Ferdi', 'Samir Tebib'];

        for ($i = 0; $i < 8; $i++) {
            ContactMessage::create([
                'name'    => $noms[$i % count($noms)],
                'email'   => 'contact' . $i . '@test.com',
                'subject' => $sujets[$i % count($sujets)],
                'message' => 'Ceci est un message de test numéro ' . ($i + 1) . '. Merci de bien vouloir traiter cette demande dans les meilleurs délais.',
            ]);
        }

        $this->command->info('✓ Données de test dashboard insérées avec succès.');
    }
}
