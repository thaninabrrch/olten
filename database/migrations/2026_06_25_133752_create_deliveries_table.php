<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();

            $table->foreignId('product_sale_id')->nullable()->constrained('product_sales')->nullOnDelete();

            $table->foreignId('delivery_person_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('pickup_address');
            $table->string('delivery_address');

            $table->decimal('distance_km', 8, 2)->nullable();

            $table->decimal('base_price', 10, 2)->default(0);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);

            $table->enum('status', [
                'pending',
                'accepted',
                'picked_up',
                'in_transit',
                'delivered',
                'cancelled'
            ])->default('pending');

            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
