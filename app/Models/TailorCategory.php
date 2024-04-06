<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TailorCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'tailor_id',
        'category_id',
        'name',
        'label',
        'gender',
        'image',
        'is_suggested',
        'status',
    ];
}
