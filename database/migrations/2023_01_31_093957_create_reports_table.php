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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->index('user_id', 'report_user_idx');
            $table->foreign('user_id', 'report_user_fk')->on('users')->references('id');
            $table->unsignedBigInteger('conference_id');
            $table->index('conference_id', 'report_conference_idx');
            $table->foreign('conference_id', 'report_conference_fk')->on('conferences')->references('id');
            $table->string('topic');
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->text('description')->nullable();
            $table->string('presentation')->unique()->nullable();
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
};
