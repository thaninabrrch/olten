<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un trajet appartient au service « covoiturage », au meme titre qu'une
     * annonce appartient a un service via sa categorie. Le rattachement se
     * faisait jusqu'ici de maniere implicite (la table `covoiturages` n'avait
     * aucun lien vers `services`) : il devient explicite, ce qui permet de
     * compter, filtrer et lister les trajets par service.
     *
     * Le service est renseigne automatiquement a la creation
     * (voir Covoiturage::booted), la colonne reste nullable pour ne pas
     * bloquer une insertion si le service venait a etre supprime.
     */
    public function up(): void
    {
        if (Schema::hasColumn('covoiturages', 'service_id')) {
            return;
        }

        Schema::table('covoiturages', function (Blueprint $table) {
            $table->foreignId('service_id')
                  ->nullable()
                  ->after('conducteur_id')
                  ->constrained('services')
                  ->nullOnDelete();
        });

        // Rattachement des trajets deja publies.
        $serviceId = DB::table('services')->where('slug', 'covoiturage')->value('id');

        if ($serviceId) {
            DB::table('covoiturages')->whereNull('service_id')->update(['service_id' => $serviceId]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('covoiturages', 'service_id')) {
            return;
        }

        Schema::table('covoiturages', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
        });
    }
};
