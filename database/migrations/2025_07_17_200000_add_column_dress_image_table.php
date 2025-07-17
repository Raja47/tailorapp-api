<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnDressImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dress_images', function (Blueprint $table) {
            $table->string('high_res_path')->nullable();
            $table->string('low_res_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dress_images', function (Blueprint $table) {
            $table->dropColumn('high_res_path');
            $table->dropColumn('low_res_path');
        });
    }
}
