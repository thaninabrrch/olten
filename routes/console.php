<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Filet de sécurité des compteurs de places (covoiturages.places_reservees_*) :
// ils sont tenus à jour par TripBooking, mais une réservation modifiée hors
// Eloquent (requête SQL directe, import) les laisserait en retard.
Artisan::command('covoiturage:recompter-places', function () {
    $count = 0;

    \App\Models\Covoiturage::query()->orderBy('covoiturage_id')->chunk(200, function ($trips) use (&$count) {
        foreach ($trips as $trip) {
            $trip->recountSeats();
            $count++;
        }
    });

    $this->info("Places recomptées sur {$count} trajet(s).");
})->purpose('Recompte les places réservées de chaque trajet à partir des réservations payées');

// Demandes de réservation restées sans réponse du conducteur (trajet en
// validation manuelle) : au départ du premier sens réservé, elles sont
// remboursées et le passager prévenu. Planifiée ci-dessous ; en production,
// le cron de Laravel doit tourner (* * * * * php artisan schedule:run).
Artisan::command('covoiturage:expirer-demandes', function () {
    $expired = 0;

    \App\Models\TripBooking::query()->pending()->with(['trip', 'passenger'])
        ->chunkById(100, function ($bookings) use (&$expired) {
            foreach ($bookings as $booking) {
                if (! $booking->departsAt()?->isPast()) {
                    continue;
                }

                try {
                    $booking->refundAndClose('expired');
                } catch (\Throwable $e) {
                    report($e);
                    $this->error("Réservation #{$booking->id} : remboursement impossible ({$e->getMessage()}).");

                    continue;
                }

                try {
                    $booking->passenger?->notify(new \App\Notifications\TripBookingAnswered($booking, 'expired'));
                } catch (\Throwable $e) {
                    report($e);
                }

                $expired++;
            }
        });

    $this->info("{$expired} demande(s) sans réponse remboursée(s).");
})->purpose('Rembourse les demandes de réservation restées sans réponse au départ du trajet');

Schedule::command('covoiturage:expirer-demandes')->everyFifteenMinutes()->withoutOverlapping();
