<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('covoiturages', function (Blueprint $table) {
            $table->string('booking_mode')->default('instant');
            // instant | manual
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('covoiturages', function (Blueprint $table) {
            //
        });
    }
};
