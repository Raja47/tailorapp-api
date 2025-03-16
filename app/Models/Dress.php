<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dress extends Model
{
    use HasFactory;

    protected $fillable = [
            'order_id',
            'tailor_id',
            'shop_id',
            'category_id',
            'name',
            'gender',
            'type',
            'quantity',
            'price',
            'delivery_date',
            'trial_date',
            'is_urgent',
            'notes',
            'status',
    ];

    protected $casts = [
        'delivery_date' => 'datetime:Y-m-d\TH:i:sP',
    ];
}
