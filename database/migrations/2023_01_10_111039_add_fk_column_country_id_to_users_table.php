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
            $table->unsignedBigInteger('country_id')->nullable()->after('birthdate');
            $table->index('country_id', 'user_country_idx');
            $table->foreign('country_id', 'user_country_fk')->on('countries')->references('id');
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
            $table->dropForeign('user_country_fk');
            $table->dropIndex('user_country_idx');
            $table->dropColumn('country_id');
        });
    }
};
