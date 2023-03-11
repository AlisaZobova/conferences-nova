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
        Schema::create('conferences', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->dateTime('conf_date');
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->index('country_id', 'conference_country_idx');
            $table->foreign('country_id', 'conference_country_fk')->on('countries')->references('id');
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
        Schema::dropIfExists('conferences');
    }
};
