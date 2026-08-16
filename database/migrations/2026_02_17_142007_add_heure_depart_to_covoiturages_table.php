<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('covoiturages', function (Blueprint $table) {
            $table->time('heure_depart')->nullable()->after('date_depart');
        });
    }

    public function down(): void
    {
        Schema::table('covoiturages', function (Blueprint $table) {
            $table->dropColumn('heure_depart');
        });
    }
};
