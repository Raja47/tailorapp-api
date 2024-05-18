<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeasurementValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'measurement_id',
        'parameter_id',
        'value'
    ];
}
