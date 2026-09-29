<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Places par sens : une même réservation peut prendre 2 places à l'aller
     * et 1 au retour. `seats` reste le plus grand des deux (le nombre de
     * voyageurs), pour les pages qui l'affichent.
     */
    public function up(): void
    {
        Schema::table('trip_bookings', function (Blueprint $table) {
            $table->unsignedSmallInteger('seats_aller')->default(0)->after('seats');
            $table->unsignedSmallInteger('seats_retour')->default(0)->after('seats_aller');
        });

        // Réservations existantes : leurs places valaient pour chacun des
        // sens réservés. Le sens se lit en PHP, les requêtes JSON n'étant
        // pas portables entre MySQL, PostgreSQL et SQLite.
        DB::table('trip_bookings')->select('id', 'legs', 'seats')->orderBy('id')
            ->chunkById(200, function ($bookings) {
                foreach ($bookings as $booking) {
                    $legs  = (array) json_decode((string) $booking->legs, true);
                    $seats = max(1, (int) $booking->seats);

                    DB::table('trip_bookings')->where('id', $booking->id)->update([
                        'seats_aller'  => in_array('aller', $legs, true) ? $seats : 0,
                        'seats_retour' => in_array('retour', $legs, true) ? $seats : 0,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('trip_bookings', function (Blueprint $table) {
            $table->dropColumn(['seats_aller', 'seats_retour']);
        });
    }
};
