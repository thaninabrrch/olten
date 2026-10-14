<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Propositions des prestataires sur une demande de prestation.
     *
     * La demande est une annonce (`ads`) publiée dans une catégorie du service
     * « Prestations de services » : le client y décrit son besoin, sa période
     * et son budget. Chaque prestataire répond par un prix total, une
     * disponibilité et un message ; le client compare puis en choisit une,
     * les autres sont déclinées.
     *
     * Une seule proposition par prestataire et par demande : il la modifie
     * ou la retire, il n'en empile pas plusieurs.
     */
    public function up(): void
    {
        Schema::create('service_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();         // la demande du client
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();       // le prestataire
            $table->decimal('price', 10, 2);                                      // prix total de la prestation
            $table->text('message');
            $table->date('available_on')->nullable();                             // disponible à partir du
            $table->string('status', 20)->default('pending');                     // pending | accepted | declined | withdrawn
            // dateTime et non timestamp : voir la migration de covoiturages.date_depart
            $table->dateTime('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['ad_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_proposals');
    }
};
