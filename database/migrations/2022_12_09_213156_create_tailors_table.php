<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTailorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        /**
         * id Integer not Null Primary Key AutoIncrement,
            external_id Integer not Null Unique,
            tailorName Text,
            address Text Default Null,
            tailorNumber Integer Not Null,
            password Integer Default 12345,
            servicesToGender Text Default 'male',
            picture Text Default null,
            status Integer Default 1,
            deletedAt Timestamp DATETIME DEFAULT Null,
            createdAt Timestamp DATETIME DEFAULT CURRENT_TIMESTAMP)
            
         */
        Schema::create('tailors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tailorName' , 99);
            $table->string('email' , 99)->nullable();
            $table->string('password')->nullable();
            $table->string('username' , 99)->unique();
            $table->tinyInteger('country_id')->nullable();
            $table->tinyInteger('city_id')->nullable();
            $table->string('address')->nullable();
            $table->string('tailorNumber')->nullable();
            $table->tinyInteger('servicesToGender')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->json('attributes')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
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
        Schema::dropIfExists('tailors');
    }
}
