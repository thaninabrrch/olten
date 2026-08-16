<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('covoiturages', function (Blueprint $table) {
            $table->boolean('retour')->default(false)->after('statut');
            $table->json('itineraire')->nullable()->after('retour');
            $table->json('segments')->nullable()->after('itineraire');
            $table->string('photo_conducteur')->nullable()->after('segments');
            $table->text('message_conducteur')->nullable()->after('photo_conducteur');
        });
    }

    public function down(): void
    {
        Schema::table('covoiturages', function (Blueprint $table) {
            $table->dropColumn(['retour', 'itineraire', 'segments', 'photo_conducteur', 'message_conducteur']);
        });
    }
};
