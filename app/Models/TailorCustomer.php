<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TailorCustomer extends Model
{
    use HasFactory;
    
    protected $table = 'tailor_customers';

    protected $fillable = [
        'number',
        'name',
        'address',
        'tailor_id',
        'customer_id',
        'city_name',
        'gender',
        'picture'
    ];
}
