<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_sales', function (Blueprint $table) {
            $table->boolean('delivery_requested')->default(false)->after('phone');
            $table->decimal('delivery_cost', 8, 2)->default(0)->after('delivery_requested');
            $table->decimal('delivery_distance_km', 8, 2)->nullable()->after('delivery_cost');
            $table->string('delivery_address')->nullable()->after('delivery_distance_km');
        });
    }

    public function down(): void
    {
        Schema::table('product_sales', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_requested',
                'delivery_cost',
                'delivery_distance_km',
                'delivery_address',
            ]);
        });
    }
};
