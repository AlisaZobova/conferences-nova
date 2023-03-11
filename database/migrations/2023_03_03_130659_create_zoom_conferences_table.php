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
        Schema::create('zoom_conferences', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('report_id')->nullable();
            $table->text('start_url')->nullable();
            $table->string('join_url')->nullable();

            $table->index('report_id', 'report_zoom_conference_idx');
            $table->foreign('report_id', 'report_zoom_conference_fk')->on('reports')->references('id')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zoom_conferences');
    }
};
