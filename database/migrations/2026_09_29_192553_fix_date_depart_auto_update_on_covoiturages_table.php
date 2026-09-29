<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * `covoiturages` vient du dump SQL, où `date_depart` est un TIMESTAMP
     * NOT NULL sans valeur par défaut. MySQL et MariaDB lui ajoutent alors
     * d'office « ON UPDATE CURRENT_TIMESTAMP » : toute mise à jour du trajet
     * (prix, options, compteurs de places...) remplaçait sa date de départ
     * par l'heure courante. DATETIME n'a pas ce comportement.
     *
     * SQLite et PostgreSQL ne sont pas concernés : la table y est créée
     * autrement, sans cette mise à jour automatique.
     */
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement('ALTER TABLE covoiturages MODIFY date_depart DATETIME NOT NULL');
    }

    /**
     * Pas de retour au TIMESTAMP mis à jour automatiquement : ce
     * comportement effaçait les dates de départ.
     */
    public function down(): void
    {
    }
};
