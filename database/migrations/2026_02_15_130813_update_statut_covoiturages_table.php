<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('covoiturages', function (Blueprint $table) {
            $table->enum('statut', [
                'actif',
                'inactif',
                'pending',
                'valide',
                'complet'
            ])->default('actif')->change();
        });
    }

    public function down(): void
    {
        Schema::table('covoiturages', function (Blueprint $table) {
            $table->enum('statut', [
                'actif',
                'complet',
                'annule'
            ])->default('actif')->change();
        });
    }
};