<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TailorCategoryParameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'tailor_id',
        'category_id',
        'parameter_id',
        'part',
        'status',
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function parameter()
    {
        return $this->belongsTo(Parameter::class);
    }
}
