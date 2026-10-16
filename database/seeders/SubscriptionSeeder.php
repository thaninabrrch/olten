<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subscription;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        Subscription::query()->delete();

        Subscription::create([
            'name' => 'Standard',
            'slug' => 'standard',
            'price' => 19.90,
            'description' => "Pour les utilisateurs actifs :\n\n"
                . "Répondre aux offres de livraison\n"
                . "Répondre aux offres VTC\n"
                . "Répondre aux appels d'offres\n"
                . "Meilleure visibilité",
        ]);

        Subscription::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'price' => 29.90,
            'description' =>  "Pour les utilisateurs actifs :\n\n"
                . "Répondre aux offres de livraison\n"
                . "Répondre aux offres VTC\n"
                . "Répondre aux appels d'offres\n"
                . "Meilleure visibilité\n"
                . "Notifications e-mail\n"
                . "Notifications en temps réel\n"
                . "Mise en avant des annonces\n"
                . "Support prioritaire",
        ]);
    }
}