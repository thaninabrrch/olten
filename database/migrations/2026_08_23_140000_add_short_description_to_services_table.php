<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Courte accroche affichée sous le nom du service sur les tuiles de
     * l'accueil (ex. « Objets & matériel », « Trajets partagés »).
     * Volontairement distincte de `description`, qui reste le texte long
     * utilisé sur la page du service.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('services', 'short_description')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('short_description', 120)
                      ->nullable()
                      ->after('slug');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('services', 'short_description')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropColumn('short_description');
            });
        }
    }
};
