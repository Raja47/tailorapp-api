<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TailorParameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'tailor_id',
        'parameter_id',
        'name',
        'label',
        'image',
    ];
}
