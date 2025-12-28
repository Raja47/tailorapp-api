<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TailorStatusSetting extends Model
{
    protected $table = 'tailor_status_settings';

    protected $fillable = [
        'tailor_id', 'status_id', 'is_active', 'sort_order'
    ];

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function tailor()
    {
        return $this->belongsTo(Tailor::class);
    }
}
