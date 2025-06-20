<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInClothsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cloths', function (Blueprint $table) {
            $table->integer('order_id')->nullable();
            $table->integer('tailor_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cloths', function (Blueprint $table) {
            $table->dropColumn('order_id');
            $table->dropColumn('tailor_id');
        });
    }
}
