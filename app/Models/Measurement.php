<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'model',
        'model_id',
        'notes',
        'status'
    ];

    public function dress()
    {
        return $this->belongsTo(Dress::class, 'model_id');
    }


    public function values()
    {
        return $this->hasMany(MeasurementValue::class, 'measurement_id');
    }

     
}
