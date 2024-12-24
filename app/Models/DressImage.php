<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DressImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'tailor_id',
        'dress_id',
        'order_id',
        'type',
        'path'
    ];
}
