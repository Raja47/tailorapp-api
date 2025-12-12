<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToDressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dresses', function (Blueprint $table) {
            $table->string('order_name')->nullable();
            $table->string('category_name')->nullable();
            $table->unsignedBigInteger('tailor_customer_id')->index()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dresses', function (Blueprint $table) {
            $table->dropColumn('order_name');
            $table->dropColumn('category_name');
            $table->dropColumn('tailor_customer_id');
        });
    }
}
