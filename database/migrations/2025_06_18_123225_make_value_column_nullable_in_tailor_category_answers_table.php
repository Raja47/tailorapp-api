<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeValueColumnNullableInTailorCategoryAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tailor_category_answers', function (Blueprint $table) {
            $table->string('value')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tailor_category_answers', function (Blueprint $table) {
            $table->integer('value')->nullable(false)->change();
        });
    }
}
