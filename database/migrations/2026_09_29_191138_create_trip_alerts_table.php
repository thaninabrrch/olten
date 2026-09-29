<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alertes trajet : « prévenez-moi quand un Paris → Lyon est publié ».
     *
     * La ville est gardée deux fois : telle que le membre l'a saisie, pour
     * l'afficher, et en slug, pour la comparer aux trajets publiés
     * (covoiturages.depart_slug / destination_slug).
     */
    public function up(): void
    {
        Schema::create('trip_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('depart');
            $table->string('destination');
            $table->string('depart_slug');
            $table->string('destination_slug');
            // Un jour précis, ou n'importe quel jour si vide
            $table->date('date')->nullable();
            $table->timestamps();

            $table->index(['depart_slug', 'destination_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_alerts');
    }
};
