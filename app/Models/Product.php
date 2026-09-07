<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'sku',
        'has_variations',
        'is_active',
        'image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'has_variations' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'image_url',
    ];

    
    protected static function booted(): void
    {
        static::addGlobalScope('active', function (Builder $query) {
            $query->where('is_active', true);
        });
    }

    

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'category_product'
        );
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /* Scopes */

    public function scopePriceBetween(
        Builder $query,
        float $min,
        float $max
    ): Builder {
        return $query->whereBetween('price', [$min, $max]);
    }

    /* Accessors */

    public function getFormattedNameAttribute(): string
    {
        return ucwords($this->name);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : null;
    }

    /* Helpers */

    public function inStock(): bool
    {
        return $this->stock > 0;
    }
}