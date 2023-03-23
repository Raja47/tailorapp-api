<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Shop extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'shops';

    /**
     * one to one relation
     */
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function tailor()
    {
        return $this->belongsTo(\App\Models\Tailor::class, 'tailor_id');
    }
}
