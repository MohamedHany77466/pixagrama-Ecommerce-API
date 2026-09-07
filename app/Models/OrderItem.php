<?php

namespace App\Models;
  use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariation;

class OrderItem extends Model
{
    use HasFactory;
   
    protected $fillable = [
    'order_id',
    'product_id',
    'product_variation_id',
    'product_name',
    'product_sku',
    'price',
    'quantity',
    'subtotal',
];

    // Define the relationship with the Order model
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    // Define the relationship with the Product model
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
  

public function variation(): BelongsTo
{
    return $this->belongsTo(ProductVariation::class, 'product_variation_id');
}
}