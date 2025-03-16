<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function($table) {
            $table->integer('total_dress_amount');
            $table->integer('total_expenses');
            $table->integer('total_discount');
            $table->integer('total_payment');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function($table) {
            $table->dropColumn('total_dress_amount');
            $table->dropColumn('total_expenses');
            $table->dropColumn('total_discount');
            $table->dropColumn('total_payment');
        });
    }
}
