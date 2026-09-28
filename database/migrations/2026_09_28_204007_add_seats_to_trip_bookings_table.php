<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Une réservation peut désormais porter sur plusieurs places. Les
     * réservations déjà enregistrées en comptaient une : c'est le défaut.
     */
    public function up(): void
    {
        Schema::table('trip_bookings', function (Blueprint $table) {
            $table->unsignedSmallInteger('seats')->default(1)->after('legs');
        });
    }

    public function down(): void
    {
        Schema::table('trip_bookings', function (Blueprint $table) {
            $table->dropColumn('seats');
        });
    }
};
