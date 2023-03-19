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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->index('user_id', 'comment_user_idx');
            $table->foreign('user_id', 'comment_user_fk')->on('users')->references('id');
            $table->unsignedBigInteger('report_id');
            $table->index('report_id', 'comment_report_idx');
            $table->foreign('report_id', 'comment_report_fk')->on('reports')->references('id');
            $table->timestamp('publication_date');
            $table->text('content');
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
        Schema::dropIfExists('comments');
    }
};
