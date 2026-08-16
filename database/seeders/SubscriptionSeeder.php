<?php

namespace Database\Seeders;

use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subscriptions = [
            [
                'name' => 'Gratuit',
                'slug' => 'free',
                'price' => 0,
                'description' => 'Compte gratuit avec fonctionnalités limitées.',
            ],
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'price' => 4.99,
                'description' => 'Accès aux fonctionnalités essentielles.',
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'price' => 14.99,
                'description' => 'Plus de visibilité et d\'avantages.',
            ],
            [
                'name' => 'VIP',
                'slug' => 'vip',
                'price' => 29.99,
                'description' => 'Accès complet à toutes les fonctionnalités.',
            ],
        ];

        foreach ($subscriptions as $subscription) {
            Subscription::updateOrCreate(
                ['slug' => $subscription['slug']],
                $subscription
            );
        }
    }
}