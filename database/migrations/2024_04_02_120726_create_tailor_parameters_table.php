<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTailorParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tailor_parameters', function (Blueprint $table) {
            $table->id();
            $table->integer('tailor_id');
            $table->integer('parameter_id')->nullable();
            $table->string('name');
            $table->string('label')->nullable();
            $table->string('image')->nullable();
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
        Schema::dropIfExists('tailor_parameters');
    }
}
