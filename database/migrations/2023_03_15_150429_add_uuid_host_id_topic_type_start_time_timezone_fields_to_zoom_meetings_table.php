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
        Schema::table('zoom_meetings', function (Blueprint $table) {
            $table->string('uuid')->after('id')->nullable();
            $table->string('host_id')->after('uuid')->nullable();
            $table->string('topic')->after('host_id')->nullable();
            $table->integer('type')->after('topic')->nullable();
            $table->dateTime('start_time')->after('type')->nullable();
            $table->string('timezone')->after('start_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('zoom_meetings', function (Blueprint $table) {
            $table->dropColumn('uuid');
            $table->dropColumn('host_id');
            $table->dropColumn('topic');
            $table->dropColumn('type');
            $table->dropColumn('start_time');
            $table->dropColumn('timezone');
        });
    }
};
