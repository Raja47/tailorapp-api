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

        static::updating(function ($order) {
            if ($order === null) {
                throw new \InvalidArgumentException('Argument $order cannot be null');
            }

            try {
                if ($order->total_payment === 0) {
                    $order->payment_status = 19;
                }

                if ($order->total_payment > 0) {
                    $order->payment_status = 20;
                }

                if (($order->total_payment + $order->total_discount) === ($order->total_dress_amount + $order->total_expenses)) {
                    $order->payment_status = 21;
                }
            } catch (\Throwable $th) {
                throw new \RuntimeException('Error updating order payment status: ' . $th->getMessage());
            }
        });
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

        return $this->hasOne(TailorCustomer::class , 'customer_id' ,'id');
    }   
}
