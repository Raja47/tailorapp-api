<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use App\Models\Parameter;

class InsertParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $filePath = public_path('/initialData/parameters.json');
        $parameters = json_decode(File::get($filePath), true);

        foreach ($parameters as $parameter) {
            Parameter::create([
                // 'id' => $parameter['id'],
                'name' => $parameter['name'],
                'label' => $parameter['label'],
                'image' => $parameter['image'],
                // 'is_suggested' => $parameter['is_suggested'],
                'status' => $parameter['status'],
                // 'createdAt' => $parameter['createdAt'],
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
