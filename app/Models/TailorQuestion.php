<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TailorQuestion extends Model
{
    use HasFactory;

    //types of category questions
    const TEXT = 1;
    const SINGLE_VALUE = 2;
    const MULTI_VALUE = 3;
    const SINGLE_ICON = 4;
    const MULTI_ICON = 5;

    protected $fillable = [
        'question_id',
        'tailor_id',
        'question',
        'type',
        'options',
        'status'
    ];
}
