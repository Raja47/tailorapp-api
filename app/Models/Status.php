<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $fillable = [
        'type', 'title', 'category', 'sort_order',
        'color', 'background_color', 'border_color',
        'card_color', 'icon'
    ];

    // Tailor specific customization
    public function tailorSettings()
    {
        return $this->hasMany(TailorStatusSetting::class);
    }
}
