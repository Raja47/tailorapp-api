<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Status;

class AddStatusesIntoTailorSettingsTable extends Migration
{
    public function up()
    {
    
        Status::all()->each(function ($status) {
            // Assuming you have a way to get all tailors
            \App\Models\Tailor::all()->each(function ($tailor) use ($status) {
                \App\Models\TailorStatusSetting::firstOrCreate([
                    'tailor_id' => $tailor->id,
                    'status_id' => $status->id,
                ], [
                    'is_active' => 1,
                    'sort_order' => null,
                ]);
            });
        });
    
    }

    public function down()
    {
        // Schema::dropIfExists('tailor_status_settings');
    }
};
