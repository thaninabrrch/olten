<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->date('available_from')->nullable()->after('price_per_day');
            $table->date('available_until')->nullable()->after('available_from');

            $table->date('expires_at')->nullable()->after('available_until');
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn([
                'available_from',
                'available_until',
                'expires_at'
            ]);
        });
    }
};

