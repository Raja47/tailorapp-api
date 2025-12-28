<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;


class Tailor extends Model
{
    use HasFactory, HasApiTokens;

    protected $table = 'tailors';
    
     /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function shops()
    {
        return $this->hasMany(\App\Models\Shop::class, 'tailor_id');
    }


    public function statusSettings()
    {
        return $this->hasMany(TailorStatusSetting::class);
    }

    public function activeStatusSettings()
    {
        return $this->hasMany(TailorStatusSetting::class)
                    ->where('is_active', true)
                    ->orderBy('sort_order');
    }

    // To get final status list tailored for this tailor
    public function getAvailableStatuses()
    {
        return Status::with(['tailorSettings' => function($q) {
                        $q->where('tailor_id', $this->id);
                    }])
                    ->get();
    }
}
