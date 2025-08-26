<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'method',
        'url',
        'user',
        'request_headers',
        'request_body',
        'response_status',
        'response_headers',
        'response_body',
    ];
}
