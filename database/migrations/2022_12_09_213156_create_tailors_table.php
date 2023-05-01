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
            $table->string('name' , 99);
            $table->string('email' , 99)->nullable();
            $table->string('username' , 99)->unique();
            $table->string('number' , 99)->unique();
            $table->string('password')->nullable();
            $table->string('picture' , 255)->nullable();
            $table->string('country_code')->nullable();
            $table->tinyInteger('city_id')->nullable();
            $table->tinyInteger('status')->nullable()->default(1);
            $table->json('attributes')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
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
