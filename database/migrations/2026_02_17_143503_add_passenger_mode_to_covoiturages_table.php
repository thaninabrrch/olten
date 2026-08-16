<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('covoiturages', function (Blueprint $table) {
            $table->string('passenger_mode')->default('mixed')->after('nb_places');
            $table->json('selected_route')->nullable()->after('itineraire');
            $table->integer('selected_route_index')->default(0)->after('selected_route');
        });
    }

    public function down(): void
    {
        Schema::table('covoiturages', function (Blueprint $table) {
            $table->dropColumn(['passenger_mode', 'selected_route', 'selected_route_index']);
        });
    }
};
