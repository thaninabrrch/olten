<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('covoiturages', function (Blueprint $table) {
            $table->decimal('prix_total_affiche', 10, 2)->nullable()->after('commission_plateforme');
        });

        // Initialisation des valeurs existantes
        DB::table('covoiturages')->update([
            'prix_total_affiche' => DB::raw('COALESCE(prix_place,0) * COALESCE(nb_places,0) + COALESCE(commission_plateforme,0)')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('covoiturages', function (Blueprint $table) {
            $table->dropColumn('prix_total_affiche');
        });
    }
};
