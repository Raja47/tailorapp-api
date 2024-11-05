<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    //     id Integer not Null Primary Key AutoIncrement,  
    //     orderId Integer Not Null,
    //     tailor_id Integer Not Null,
    //     shop_id Integer Not Null,
    //     categoryId  Integer Not Null,
    //     name Text not Null,
    //     gender Text Default 'male',
    //     type Text not Null Default 'new',
    //     quantity Integer Not Null,
    //     price Int Not Null,
    //     deliveryDate  Timestamp DATETIME DEFAULT Null,
    //     trialDate Timestamp DATETIME DEFAULT Null,
    //     isUrgent Int Default 0,
    //     notes Text DEFAULT Null,
    //     status Integer Default 1,
    //     deletedAt Timestamp DATETIME DEFAULT Null,
    //     createdAt Timestamp DATETIME DEFAULT CURRENT_TIMESTAMP)
    public function up()
    {
        Schema::create('dresses', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->integer('tailor_id');
            $table->integer('shop_id');
            $table->integer('category_id');
            $table->string('name');
            $table->string('gender')->default('male');
            $table->string('type')->default('new');
            $table->integer('quantity');
            $table->integer('price');
            $table->timestamp('delivery_date')->nullable();
            $table->timestamp('trial_date')->nullable();
            $table->integer('is_urgent')->default(0);
            $table->string('notes')->nullable();
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
        Schema::dropIfExists('dresses');
    }
}
