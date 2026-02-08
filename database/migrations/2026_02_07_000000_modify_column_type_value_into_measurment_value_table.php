<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('measurement_values', function (Blueprint $table) {
            $table->string('value', 24)->change();
        });

        // Optional: format existing numeric values to string (prevents scientific notation issues)
        DB::statement("UPDATE measurement_values SET value = CAST(value AS CHAR(24))");
    }

    public function down()
    {
        Schema::table('measurement_values', function (Blueprint $table) {
            $table->double('value')->change();
        });
    }
};





