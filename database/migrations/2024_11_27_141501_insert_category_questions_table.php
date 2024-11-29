<?php

use App\Models\CategoryQuestion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class InsertCategoryQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $filePath = public_path('/questions/categoryquestions.json');
        $cat_questions = json_decode(File::get($filePath), true);

        foreach ($cat_questions as $cat_question) {
            CategoryQuestion::create([
                // 'id' => $cat_question['id'],
                'category_id' => $cat_question['category_id'],
                'question_id' => $cat_question['question_id'],
                'status' => $cat_question['status'],
                // 'createdAt' => $cat_question['createdAt'],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
