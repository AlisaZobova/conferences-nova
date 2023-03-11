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
        Schema::table('users', function (Blueprint $table) {
            $table->string('firstname')->nullable()->change();
            $table->string('lastname')->nullable()->change();
            $table->date('birthdate')->nullable()->change();
            $table->string('phone')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('firstname')->nullable(false)->change();
            $table->string('lastname')->nullable(false)->change();
            $table->date('birthdate')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
        });
    }
};
