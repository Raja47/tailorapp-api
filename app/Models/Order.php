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
        'payment_status',
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

    protected static function boot()
    {
        parent::boot();
        
        // Generate unique order name before creating
        static::creating(function ($order) {
            $shopId = $order->shop_id;
            $prefix = $shopId . 'OR';

            $latest = self::where('name', 'like', $prefix . '%')
                ->where('shop_id', $order->shop_id)
                ->orderBy('id', 'desc')
                ->first();

            if ($latest && preg_match('/^' . $shopId . 'OR(\d+)$/', $latest->name, $matches)) {
                $last = (int)$matches[1];
            } else {
                $last = 0;
            }

            $next = $last + 1;
            $order->name = $prefix . $next;
        });
    }


    public function refreshFinancialStatus()
    {
        if ($this->total_payment === 0) {
            $this->payment_status = 19;
        }

        if ($this->total_payment > 0) {
            $this->payment_status = 20;
        }

        if ($this->total_dress_amount + $this->total_expenses - $this->total_discount - $this->total_payment <= 0) {
            $this->payment_status = 21;
        }

        return $this->save();
    }

    public function balanceAmount()
    {
        return $this->total_dress_amount + $this->total_expenses - $this->total_discount - $this->total_payment;
    }

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

   public function customer(){
    
        return $this->hasOne(TailorCustomer::class , 'id' , 'customer_id');
    }   
}
