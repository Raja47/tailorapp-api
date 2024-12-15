<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TailorCategoryQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'tailor_id',
        'category_id',
        'question_id',
        'question',
        'type',
        'options',
        'status'
    ];
}
