<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('covoiturages', function (Blueprint $table) {
            $table->json('return_itinerary')->nullable()->after('return_time');
        });
    }

    public function down()
    {
        Schema::table('covoiturages', function (Blueprint $table) {
            $table->dropColumn('return_itinerary');
        });
    }
};
