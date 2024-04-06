<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryParameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'category_id',
        'parameter_id',
        'part',
        'status',
    ];
}
