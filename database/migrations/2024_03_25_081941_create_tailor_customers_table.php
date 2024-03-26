<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTailorCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tailor_customers', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('gender')->nullable();
            $table->string('picture')->nullable();
            $table->integer('tailor_id');
            $table->integer('customer_id');
            $table->string('city_name')->nullable();
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
        Schema::dropIfExists('tailor_customers');
    }
}
