<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TailorCategoryAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'tailor_id',
        'dress_id',
        'question_id', //tailor_catgeory_question ID
        'value'
    ];
}
