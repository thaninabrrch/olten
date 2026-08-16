<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('demande_livreur', function (Blueprint $table) {
            $table->id('id_demande');
            $table->foreignId('id_livreur')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('id_annonce')
                ->constrained('ads')
                ->cascadeOnDelete();
            $table->enum('statut', ['en_attente', 'acceptee', 'refusee', 'terminee'])
                ->default('en_attente');
            $table->timestamp('date_demande')->useCurrent();
            $table->timestamps();
            $table->unique(['id_livreur', 'id_annonce']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('demande_livreur');
    }
};
