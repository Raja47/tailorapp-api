<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class CreateStatusesTable extends Migration
{
    public function up()
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('name')->unique();            // internal key e.g. "in_progress"
            $table->string('title')->nullable();         // display text e.g. "In Progress"

            // Classification
            $table->string('type', 30)->nullable();      // order / dress / global
            $table->string('category', 50)->nullable();  // order_flow / dress_flow / payment_flow etc.

            // Behavior
            $table->boolean('is_default')->default(1);   // ON by default
            $table->integer('sort_order')->nullable();   // default ordering

            // UI / Style
            $table->string('color', 20)->nullable();             // text/icon color
            $table->string('background_color', 20)->nullable();  // chip/badge bg
            $table->string('card_color', 20)->nullable();        // card background
            $table->string('border_color', 20)->nullable();      // border color
            $table->string('icon')->nullable();                  // icon class/key e.g. "clock", "check"

            $table->timestamps();
        });

        // ==============================
        // 🚀 Seed Insert Default Statuses
        // ==============================

        $defaultStatuses = [

            // ORDER FLOW
            ['pending', 'Pending', 'order', 'order_flow', 1, '#FFF', '#FF9800', '#FFF3E0', '#E65100', 'clock'],
            ['in_progress', 'In Progress', 'order', 'order_flow', 4, '#FFF', '#2196F3', '#E3F2FD', '#0D47A1', 'loader'],
            ['trial_ready', 'Ready for Trial', 'order', 'order_flow', 5, '#FFF', '#673AB7', '#EDE7F6', '#311B92', 'user-check'],
            ['adjustments', 'Adjustments', 'order', 'order_flow', 6, '#FFF', '#FF5722', '#FBE9E7', '#BF360C', 'settings'],
            ['ready_for_delivery', 'Ready for Delivery', 'order', 'order_flow', 7, '#FFF', '#009688', '#E0F2F1', '#004D40', 'truck'],
            ['delivered', 'Delivered', 'order', 'order_flow', 8, '#FFF', '#4CAF50', '#E8F5E9', '#1B5E20', 'check'],
            ['cancelled', 'Cancelled', 'order', 'order_flow', 9, '#FFF', '#F44336', '#FFEBEE', '#B71C1C', 'x'],

            // DRESS FLOW
            ['dress_pending', 'Pending', 'dress', 'dress_flow', 1, '#FFF', '#FF9800', '#FFF3E0', '#E65100', 'clock'],
            ['cutting', 'Cutting', 'dress', 'dress_flow', 2, '#FFF', '#388E3C', '#E8F5E9', '#1B5E20', 'scissors'],
            ['stitching', 'Stitching', 'dress', 'dress_flow', 3, '#FFF', '#2196F3', '#E3F2FD', '#0D47A1', 'needle'],
            ['embroidery', 'Embroidery', 'dress', 'dress_flow', 4, '#FFF', '#9C27B0', '#F3E5F5', '#6A0080', 'sparkles'],
            ['handwork', 'Handwork', 'dress', 'dress_flow', 5, '#FFF', '#795548', '#EFEBE9', '#3E2723', 'hand'],
            ['outsource', 'Outsourced', 'dress', 'dress_flow', 6, '#FFF', '#607D8B', '#ECEFF1', '#37474F', 'send'],
            ['finishing', 'Finishing', 'dress', 'dress_flow', 7, '#FFF', '#FFC107', '#FFF8E1', '#FF8F00', 'check-circle'],
            ['ironing', 'Pressing / Ironing', 'dress', 'dress_flow', 8, '#FFF', '#8BC34A', '#F1F8E9', '#33691E', 'iron'],
            ['trial', 'Trial', 'dress', 'dress_flow', 9, '#FFF', '#673AB7', '#EDE7F6', '#311B92', 'user-check'],
            ['dress_adjustments', 'Adjustments', 'dress', 'dress_flow', 10, '#FFF', '#FF5722', '#FBE9E7', '#BF360C', 'settings'],
            ['done', 'Done', 'dress', 'dress_flow', 11, '#FFF', '#4CAF50', '#E8F5E9', '#1B5E20', 'check'],

            // PAYMENT FLOW
            ['unpaid', 'Unpaid', 'payment', 'payment_flow', 1, '#FFF', '#F44336', '#FFEBEE', '#B71C1C', 'alert-circle'],
            ['partial_paid', 'Partial Paid', 'payment', 'payment_flow', 3, '#FFF', '#03A9F4', '#E1F5FE', '#01579B', 'wallet'],
            ['paid', 'Paid', 'payment', 'payment_flow', 4, '#FFF', '#4CAF50', '#E8F5E9', '#1B5E20', 'check'],
            ['refunded', 'Refunded', 'payment', 'payment_flow', 5, '#FFF', '#9E9E9E', '#FAFAFA', '#616161', 'rotate-ccw'],
        ];

        foreach ($defaultStatuses as $s) {
            DB::table('statuses')->insert([
                'name' => $s[0],
                'title' => $s[1],
                'type' => $s[2],
                'category' => $s[3],
                'sort_order' => $s[4],
                'color' => $s[5],
                'background_color' => $s[6],
                'card_color' => $s[7],
                'border_color' => $s[8],
                'icon' => $s[9],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('statuses');
    }
};
