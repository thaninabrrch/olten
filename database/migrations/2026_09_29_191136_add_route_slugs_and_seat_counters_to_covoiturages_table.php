<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Deux informations jusqu'ici recalculées en PHP à chaque page, désormais
     * stockées sur le trajet pour que les listes filtrent, regroupent et
     * paginent en SQL :
     *
     *   - la ville de départ et d'arrivée, en slug (« lyon ») : les colonnes
     *     depart / destination contiennent l'adresse géocodée complète, un
     *     GROUP BY dessus séparerait « Lyon » de « Lyon, Métropole de Lyon » ;
     *   - les places payées sur chaque sens, tenues à jour par TripBooking à
     *     chaque réservation ou annulation.
     */
    public function up(): void
    {
        Schema::table('covoiturages', function (Blueprint $table) {
            $table->string('depart_slug')->nullable();
            $table->string('destination_slug')->nullable();
            $table->unsignedSmallInteger('places_reservees_aller')->default(0);
            $table->unsignedSmallInteger('places_reservees_retour')->default(0);

            $table->index(['depart_slug', 'destination_slug']);
        });

        // Même règle que Covoiturage::villeCourte() puis Str::slug(). Recopiée
        // ici volontairement : une migration ne doit pas dépendre d'un modèle
        // qui évoluera après elle.
        $slug = function ($adresse): string {
            $adresse = trim((string) $adresse);

            return Str::slug(trim(Str::before($adresse, ',')) ?: $adresse);
        };

        DB::table('covoiturages')
            ->select('covoiturage_id', 'depart', 'destination')
            ->orderBy('covoiturage_id')
            ->chunk(200, function ($trips) use ($slug) {
                foreach ($trips as $trip) {
                    DB::table('covoiturages')
                        ->where('covoiturage_id', $trip->covoiturage_id)
                        ->update([
                            'depart_slug'      => $slug($trip->depart),
                            'destination_slug' => $slug($trip->destination),
                        ]);
                }
            });

        // Places déjà payées : une réservation compte ses places sur chacun
        // des sens qu'elle couvre (« legs » est un tableau JSON).
        if (Schema::hasTable('trip_bookings')) {
            DB::table('trip_bookings')
                ->where('status', 'paid')
                ->get(['trip_id', 'legs', 'seats'])
                ->groupBy('trip_id')
                ->each(function ($bookings, $tripId) {
                    $count = fn (string $leg) => $bookings
                        ->filter(fn ($b) => in_array($leg, (array) json_decode($b->legs, true), true))
                        ->sum('seats');

                    DB::table('covoiturages')
                        ->where('covoiturage_id', $tripId)
                        ->update([
                            'places_reservees_aller'  => $count('aller'),
                            'places_reservees_retour' => $count('retour'),
                        ]);
                });
        }
    }

    public function down(): void
    {
        Schema::table('covoiturages', function (Blueprint $table) {
            $table->dropIndex(['depart_slug', 'destination_slug']);
            $table->dropColumn(['depart_slug', 'destination_slug', 'places_reservees_aller', 'places_reservees_retour']);
        });
    }
};
