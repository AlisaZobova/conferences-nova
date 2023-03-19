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
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign('report_conference_fk');
            $table->foreign('conference_id', 'report_conference_fk')
                ->on('conferences')
                ->references('id')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign('report_conference_fk');
            $table->foreign('conference_id', 'report_conference_fk')
                ->on('conferences')
                ->references('id');
        });
    }
};
