<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('covoiturages', 'covoiturage_id')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();          // le passager
            $table->json('legs');                                 // ["aller","retour"]
            $table->string('phone');
            $table->decimal('driver_amount', 8, 2);               // part du conducteur (ex. 20 €)
            $table->decimal('commission', 8, 2);                  // part plateforme (ex. 4 €)
            $table->decimal('total_price', 8, 2);                 // payé par le passager (ex. 24 €)
            $table->decimal('commission_rate', 5, 2);             // taux appliqué à ce moment-là
            $table->string('stripe_intent')->unique();            // unique : évite les doublons
            $table->string('status')->default('paid');            // paid | cancelled
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['trip_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_bookings');
    }
};