<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {

            $table->decimal('delivery_distance_km', 8, 2)->nullable()->after('delivery_cost');
            $table->string('delivery_address')->nullable()->after('delivery_distance_km');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            //
            $table->dropColumn('delivery_distance_km');
            $table->dropColumn('delivery_address');
        });
    }
};
