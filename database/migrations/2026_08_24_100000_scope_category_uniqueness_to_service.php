<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Une categorie n'existe qu'a l'interieur d'un service : "Vehicules" doit
     * pouvoir exister a la fois sous Vente et sous Location, et
     * "Beaute & Bien-etre" sous Vente et sous Prestations de services.
     *
     * L'unicite globale sur `nom` et `slug` interdisait cette structure :
     * elle devient une unicite par service.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            foreach (['categories_nom_unique', 'categories_slug_unique'] as $index) {
                if ($this->hasIndex($index)) {
                    $table->dropUnique($index);
                }
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (! $this->hasIndex('categories_service_id_nom_unique')) {
                $table->unique(['service_id', 'nom']);
            }

            if (! $this->hasIndex('categories_service_id_slug_unique')) {
                $table->unique(['service_id', 'slug']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            foreach (['categories_service_id_nom_unique', 'categories_service_id_slug_unique'] as $index) {
                if ($this->hasIndex($index)) {
                    $table->dropUnique($index);
                }
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (! $this->hasIndex('categories_nom_unique')) {
                $table->unique('nom');
            }

            if (! $this->hasIndex('categories_slug_unique')) {
                $table->unique('slug');
            }
        });
    }

    /**
     * Introspection portable : la meme migration doit passer sur MySQL
     * (developpement) comme sur SQLite (tests).
     */
    private function hasIndex(string $name): bool
    {
        foreach (Schema::getIndexes('categories') as $index) {
            if (($index['name'] ?? null) === $name) {
                return true;
            }
        }

        return false;
    }
};
