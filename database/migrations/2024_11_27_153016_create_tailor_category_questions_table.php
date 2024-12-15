<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTailorCategoryQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tailor_category_questions', function (Blueprint $table) {
            $table->id();
            $table->integer('tailor_id');
            $table->integer('category_id'); //tailor category table
            $table->integer('question_id')->nullable(); //category question table
            $table->string('question');
            $table->integer('type');
            $table->text('options')->nullable();
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
        Schema::dropIfExists('tailor_category_questions');
    }
}
