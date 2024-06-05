<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use App\Models\Category;

class InsertCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        // @todo we need to add this file in public folder , not in storage  
        // first storage are mostly for user uploaded files , and are always git ignored 
        // app related file are to be maintianed in public e.g css , js , icons , images , jsons
        $filePath = storage_path('app/categories.json');
        $categories = json_decode(File::get($filePath), true);

        foreach ($categories as $category) {
            Category::create([
                // 'id' => $category['id'],
                'name' => $category['name'],
                'label' => $category['label'],
                'gender' => $category['gender'],
                'image' => $category['image'],
                'status' => $category['status'],
                // 'createdAt' => $category['createdAt'],
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
