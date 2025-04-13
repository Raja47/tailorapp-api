<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'method',
        'amount',
        'date',
        'order_id',
        'tailor_id',
        'customer_id'
    ];
}
