<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTailorCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tailor_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('tailor_id');
            $table->integer('category_id')->nullable();
            $table->string('name');
            $table->string('label')->nullable();
            $table->string('gender')->nullable();
            $table->string('image')->nullable();
            $table->integer('is_suggested')->nullable()->default(0);
            $table->integer('status')->nullable()->default(1);
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
        Schema::dropIfExists('tailor_categories');
    }
}
