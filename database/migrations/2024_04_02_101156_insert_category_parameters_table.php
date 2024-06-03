<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use App\Models\CategoryParameter;

class InsertCategoryParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $filePath = storage_path('app/categoryparameters.json');
        $categoryParameters = json_decode(File::get($filePath), true);

        foreach ($categoryParameters as $categoryParameter) {
            CategoryParameter::create([
                // 'id' => $categoryParameter['id'],
                'label' => $categoryParameter['label'],
                'category_id' => $categoryParameter['categoryId'],
                'parameter_id' => $categoryParameter['parameterId'],
                'status' => $categoryParameter['status'],
                // 'createdAt' => $categoryParameter['createdAt'],
                'part' => $categoryParameter['part'],
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
