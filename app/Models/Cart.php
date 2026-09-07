<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    //fillable properties
    protected $fillable = [
        'user_id',
    'product_id',
    'product_variation_id',
    'quantity',
    ];
    
    protected $casts = [
        'quantity' => 'integer',
    ];

    protected $appends = [
        'subtotal',
    ];
    protected $with = ['product:id,name,price,image'];
    // relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // relationship with Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function variation()
{
    return $this->belongsTo(ProductVariation::class, 'product_variation_id');
}
    //

public function getSubtotalAttribute()
{
    $price = $this->variation
        ? ($this->variation->sale_price ?? $this->variation->price)
        : $this->product->price;

    return $price * $this->quantity;
}
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}