<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Picture extends Model
{
    use HasFactory;

    protected $fillable = [
        'model',
        'model_id',
        'type',
        'path',
        'notes',
        'is_featured',
    ];
}
