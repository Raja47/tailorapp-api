<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopsTable extends Migration
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
            name Text,
            address Text Default Null,
            contact_number  String Not Null,
            contact_number2  String Default Null,
            address String 
            Integer Default 12345,
            services_to_gender Text Default 'male',
            picture Text Default null,
            status Integer Default 1,
            deleted_at Timestamp DATETIME DEFAULT Null,
            created_at Timestamp DATETIME DEFAULT CURRENT_TIMESTAMP)
         */

        Schema::create('shops', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('tailor_id')->index();
            $table->string('name' , 99);
            $table->string('country_code')->nullable();
            $table->string('city_name')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('contact_number2')->nullable();
            $table->string('address')->nullable();
            $table->string('picture' , 255)->nullable();
            $table->tinyInteger('services_to_gender')->nullable()->default(0);
            $table->tinyInteger('status')->nullable()->default(1);
            $table->json('attributes')->nullable();
            $table->integer('deleted_by')->nullable();
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
        Schema::dropIfExists('shops');
    }
}
