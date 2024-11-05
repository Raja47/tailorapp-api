<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */


    //  id Integer Not Null Primary Key AutoIncrement,
    //         customerId Integer not Null,
    //         tailor_id Integer Not Null,
    //         shop_id Integer Not Null,
    //         name Text ,
    //         discount  Integer Not Null Default 0,
    //         notes Text Default NUll,
    //         status Integer Default 1,
    //         deletedAt Timestamp DATETIME DEFAULT Null,
    //         createdAt Timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->integer('tailor_id');
            $table->integer('shop_id');
            $table->string('name');
            $table->integer('discount')->default(0);
            $table->string('notes')->nullable();
            $table->integer('status')->default(1);
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
        Schema::dropIfExists('orders');
    }
}
