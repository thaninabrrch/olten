<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Le type de service disparaît définitivement : c'est le slug qui identifie
     * désormais un service (ex. /vente, /location, /covoiturage) et qui permet
     * au front de choisir le design à afficher.
     */
    public function up(): void
    {
        // 1. Ajout du slug, nullable le temps du backfill
        if (! Schema::hasColumn('services', 'slug')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('nom');
            });
        }

        // 2. Backfill : un slug unique dérivé du nom pour chaque service existant
        $used = [];

        foreach (DB::table('services')->orderBy('id')->get(['id', 'nom', 'slug']) as $service) {
            $base = Str::slug($service->slug ?: $service->nom) ?: 'service-' . $service->id;
            $slug = $base;
            $suffix = 2;

            while (in_array($slug, $used, true)) {
                $slug = $base . '-' . $suffix++;
            }

            $used[] = $slug;

            DB::table('services')->where('id', $service->id)->update(['slug' => $slug]);
        }

        // 3. Le slug devient obligatoire et unique
        Schema::table('services', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug');
        });

        // 4. Suppression définitive du type de service
        if (Schema::hasColumn('services', 'type_service_id')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropForeign(['type_service_id']);
                $table->dropColumn('type_service_id');
            });
        }

        Schema::dropIfExists('type_services');
    }

    public function down(): void
    {
        if (! Schema::hasTable('type_services')) {
            Schema::create('type_services', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('services', function (Blueprint $table) {
            if (! Schema::hasColumn('services', 'type_service_id')) {
                $table->foreignId('type_service_id')
                      ->nullable()
                      ->after('image')
                      ->constrained('type_services')
                      ->onDelete('cascade');
            }

            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
