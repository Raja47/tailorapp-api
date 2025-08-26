<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDressImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dress_images', function (Blueprint $table) {
            $table->id();
            $table->integer('tailor_id');
            $table->integer('dress_id');
            $table->integer('order_id');
            $table->string('type'); //cloth or design
            $table->string('path');
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
        Schema::dropIfExists('dress_images');
    }
}
