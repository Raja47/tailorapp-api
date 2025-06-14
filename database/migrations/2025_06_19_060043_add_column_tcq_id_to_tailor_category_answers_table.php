<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTcqIdToTailorCategoryAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tailor_category_answers', function (Blueprint $table) {
            $table->integer('tcq_id')->nullable()->index()->after('id');
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
              $table->dropColumn('tcq_id');
        });
    }
}
