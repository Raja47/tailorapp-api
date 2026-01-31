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


    public function answers()
    {
        return $this->hasOne(TailorCategoryAnswer::class, 'question_id', 'id');
    }

    /**
     * Check if the question type is a select type.
     * @return bool
     */
    public function isMulti() :bool
    {
        return $this->type === 'multi-select' || $this->type === 'multi-icon';
    }

    public function isCustom() :bool {
        return $this->question_id == null;
    }

}
