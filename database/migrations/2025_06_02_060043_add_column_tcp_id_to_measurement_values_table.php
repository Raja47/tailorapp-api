<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTcpIdToMeasurementValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('measurement_values', function (Blueprint $table) {
            $table->integer('tcp_id')->nullable()->index()->after('id');
        });

    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('measurement_values', function (Blueprint $table) {
              $table->dropColumn('tcp_id');
        });
    }
}
