<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'amount',
        'order_id',
        'tailor_id'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s.v\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s.v\Z',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
