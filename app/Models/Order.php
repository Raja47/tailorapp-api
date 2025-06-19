<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'tailor_id',
        'shop_id',
        'name',
        'discount',
        'notes',
        'status',
        'total_dress_amount',
        'total_expenses',
        'total_discount',
        'total_payment'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s.v\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s.v\Z',
    ];

    public function discounts()
    {
        return $this->hasMany(Discount::class, 'order_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'order_id');
    }
}
