<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('covoiturages', function (Blueprint $table) {
            $table->index('date_depart', 'idx_covoiturages_date_depart');
        });
    }

    public function down(): void
    {
        Schema::table('covoiturages', function (Blueprint $table) {
            $table->dropIndex('idx_covoiturages_date_depart');
        });
    }
};