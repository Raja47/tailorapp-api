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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($dress) {
            $shopId = $dress->shop_id;
            $prefix = $shopId . 'OR';

            $latest = self::where('name', 'like', $prefix . '%')
                        ->where('shop_id', $dress->shop_id)
                        ->orderBy('id', 'desc')
                        ->first();

            if ($latest && preg_match('/^' . $shopId . 'DR(\d+)$/', $latest->name, $matches)) {
                $last = (int)$matches[1];
            } else {
                $last = 0;
            }

            $next = $last + 1;
            $dress->name = $prefix . $next;
        });
    }

}
