<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('covoiturages', function (Blueprint $table) {

            $table->json('return_trip_data')->nullable()->after('segments');
            $table->date('return_date')->nullable()->after('return_trip_data');
            $table->time('return_time')->nullable()->after('return_date');

        });
    }

    public function down(): void
    {
        Schema::table('covoiturages', function (Blueprint $table) {

            $table->dropColumn([
                'return_trip_data',
                'return_date',
                'return_time',
            ]);

        });
    }
};
