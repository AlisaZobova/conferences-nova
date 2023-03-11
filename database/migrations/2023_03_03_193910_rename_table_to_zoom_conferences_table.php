<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('zoom_conferences', function (Blueprint $table) {
            Schema::rename('zoom_conferences', 'zoom_meetings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('zoom_conferences', function (Blueprint $table) {
            Schema::rename('zoom_meetings', 'zoom_conferences');
        });
    }
};
