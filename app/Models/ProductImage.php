<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image',
        'thumbnail'
    ];

    protected $appends = [
        'image_url',
        'thumbnail_url'
    ];

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }

    public function getThumbnailUrlAttribute()
    {
        return asset('storage/' . $this->thumbnail);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}