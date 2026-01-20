<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dress extends Model
{
    use HasFactory;

    protected $fillable = [
            'order_id',
            'order_name',
            'tailor_id',
            'shop_id',
            'category_id',
            'category_name',
            'order_name',
            'tailor_customer_id',
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
        'delivery_date' => 'datetime:Y-m-d\TH:i:s.v\Z',
        'trial_date' => 'datetime:Y-m-d\TH:i:s.v\Z',
        'created_at' => 'datetime:Y-m-d\TH:i:s.v\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s.v\Z',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function clothes()
    {
        return $this->hasMany(Cloth::class);
    }

    public function measurement()
    {
        return $this->hasOne(Measurement::class , 'model_id', 'id');
    }

    public function measurement_values()
    {
        return $this->hasManyThrough(MeasurementValue::class, Measurement::class, 'model_id' ,'measurement_id' , 'id' , 'id');
    }

    public function customer(){

        return $this->hasOne(TailorCustomer::class , 'id' , 'tailor_customer_id');
    }   

    public function designs()
    {
        return $this->hasMany(DressImage::class, 'dress_id', 'id')->where('type', 'design'); 
    }

    public function category() 
    { 
        return $this->belongsTo(TailorCategory::class , 'category_id', 'id'); 
    } 

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $shopId = $order->shop_id;
            $prefix = $shopId . 'DR';

            $latest = self::where('name', 'like', $prefix . '%')
                        ->where('shop_id', $shopId)
                        ->orderBy('id', 'desc')
                        ->first();

            if ($latest && preg_match('/^' . $shopId . 'DR(\d+)$/', $latest->name, $matches)) {
                $last = (int)$matches[1];
            } else {
                $last = 0;
            }

            $next = $last + 1;
            $order->name = $prefix . $next;
        });
    }
}
