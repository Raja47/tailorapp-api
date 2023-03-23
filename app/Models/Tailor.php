<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tailor extends Model
{
    use HasFactory;

    protected $table = 'tailors';
    
     /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function shops()
    {
        return $this->hasMany(\App\Models\Shop::class, 'tailor_id');
    }
}
