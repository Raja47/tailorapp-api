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

    public function designs()
    {
        return $this->hasMany(DressImage::class, 'dress_id', 'id')->where('type', 'design'); 
    }
}
