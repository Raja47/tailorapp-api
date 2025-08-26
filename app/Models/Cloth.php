<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cloth extends Model
{
    use HasFactory;

    protected $fillable = [
        'dress_id',
        'order_id',
        'tailor_id',
        'title',
        'dress_image_id',
        'length',
        'provided_by',
        'price'
    ];


    public function frontendMap(): array
    {
        return  [
            'id' => 'id',
            'dress_id' => 'dress_id',
            'order_id' => 'order_id',
            'tailor_id' => 'tailor_id',
            'dress_image_id' => fn($cloth) => $cloth->image ? $cloth->image->path : null,
            'title' => 'title',
            'length' => 'length',
            'provided_by' => 'provided_by',
            'price' => 'price',
        ];
    }

    
    public function toFrontend(): array
    {
        return $this->mapAttributes($this->frontendMap());
    }



    public function image()
    {
        return $this->belongsTo(DressImage::class , 'dress_image_id');
    }
}
