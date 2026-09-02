<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pendant des annonces : ad_visits garde le detail des consultations d'une
     * annonce, product_visits fait de meme pour un produit. Le compteur
     * products.views existait deja mais rien ne l'alimentait.
     */
    public function up(): void
    {
        Schema::create('product_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_visits');
    }
};
