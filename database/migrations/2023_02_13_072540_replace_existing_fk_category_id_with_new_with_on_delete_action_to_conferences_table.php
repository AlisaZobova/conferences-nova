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
        Schema::table('conferences', function (Blueprint $table) {
            $table->dropForeign('conference_category_fk');
            $table->foreign('category_id', 'conference_category_fk')
                ->on('categories')
                ->references('id')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conferences', function (Blueprint $table) {
            $table->dropForeign('conference_category_fk');
            $table->foreign('category_id', 'conference_category_fk')
                ->on('categories')
                ->references('id');
        });
    }
};
