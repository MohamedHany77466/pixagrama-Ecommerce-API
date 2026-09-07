<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'sale_price',
        'stock',
        'image',
        'is_active',
    ];

    /**
     * Product relationship
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Attribute values
     */
    public function attributeValues()
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'product_variation_values',
            'product_variation_id',
            'attribute_value_id'
        );
    }
}