<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cloth extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'dress_image_id',
        'length',
        'provided_by',
        'price'
    ];


    protected $frontendMap = [
        'path' => fn($cloth) => $cloth->image ? $cloth->image->path : null,
        'emailAddress' => 'email',
        'isVerified' => fn($user) => (bool) $user->email_verified_at,
    ];

    public function toFrontend(): array
    {
        return $this->mapAttributes($this->frontendMap);
    }



    public function image()
    {
        return $this->belongsTo(DressImage::class , 'dress_image_id');
    }
}
