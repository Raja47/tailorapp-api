<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeDressImageIdNullableInClothsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cloths', function (Blueprint $table) {
            $table->integer('dress_image_id')->nullable()->change();
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
            $table->integer('dress_image_id')->nullable(false)->change();
        });
    }
}
