<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTailorCategoryParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tailor_category_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->integer('tailor_id');
            $table->integer('category_id');
            $table->integer('parameter_id');
            $table->string('part')->nullable();
            $table->integer('status')->default(1);
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
        Schema::dropIfExists('tailor_category_parameters');
    }
}
