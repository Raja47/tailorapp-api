<?php

use App\Models\Question;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class InsertQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $filePath = public_path('/questions/questions.json');
        $questions = json_decode(File::get($filePath), true);

        foreach ($questions as $question) {
            Question::create([
                // 'id' => $question['id'],
                'question' => $question['question'],
                'type' => $question['type'],
                'options' => $question['options'],
                'status' => $question['status'],
                // 'createdAt' => $question['createdAt'],
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
