<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTailorStatusSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('tailor_status_settings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tailor_id'); // user or tailor table FK
            $table->unsignedBigInteger('status_id'); // FK to statuses

            $table->boolean('is_active')->default(1); // tailor ON/OFF
            $table->integer('sort_order')->nullable(); // tailor custom sorting override

            $table->timestamps();

            // Foreign Keys
            $table->foreign('tailor_id')->references('id')->on('tailors')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('cascade');

            // Ensure tailor cannot have duplicate entries for same status
            $table->unique(['tailor_id', 'status_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tailor_status_settings');
    }
};
