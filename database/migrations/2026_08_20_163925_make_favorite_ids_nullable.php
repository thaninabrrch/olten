<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->foreignId('ad_id')
                ->nullable()
                ->change();

            $table->foreignId('product_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->foreignId('ad_id')
                ->nullable(false)
                ->change();

            $table->foreignId('product_id')
                ->nullable(false)
                ->change();
        });
    }
};
