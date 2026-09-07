<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\UploadImageTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;
use App\Models\ProductVariation;







class ProductController extends Controller
{
    use UploadImageTrait, ApiResponseTrait;
/**
 * Display a listing of the products.
 */
public function index(Request $request)
{
    $query = Product::with([
        'categories',
        'images',
        'variations.attributeValues.attribute',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */
    if ($request->filled('search')) {

        $search = $request->search;

        $query->where(function ($q) use ($search) {

            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    if ($request->filled('category_id')) {

        $query->whereHas('categories', function ($q) use ($request) {

            $q->where('categories.id', $request->category_id);

        });

    }

    if ($request->filled('is_active')) {

        $query->where('is_active', $request->boolean('is_active'));

    }

    if ($request->filled('has_variations')) {

        $query->where(
            'has_variations',
            $request->boolean('has_variations')
        );

    }

    if ($request->filled('price_min')) {

        $query->where('price', '>=', $request->price_min);

    }

    if ($request->filled('price_max')) {

        $query->where('price', '<=', $request->price_max);

    }

    /*
    |--------------------------------------------------------------------------
    | Sort
    |--------------------------------------------------------------------------
    */

    $sortBy = $request->get('sort_by', 'created_at');

    $sortDirection = $request->get('sort_direction', 'desc');

    $allowedSorts = [
        'id',
        'name',
        'price',
        'stock',
        'created_at',
    ];

    if (! in_array($sortBy, $allowedSorts)) {

        $sortBy = 'created_at';

    }

    $query->orderBy($sortBy, $sortDirection);

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $perPage = $request->integer('per_page', 10);

    $products = $query->paginate($perPage);

    return $this->successResponse(
        $products,
        'Products retrieved successfully.'
    );
}

    
/**
 * Display the specified product.
 */
public function show(Product $product)
{
    $product = Product::with([
        'categories',
        'images',
        'variations.attributeValues.attribute'
    ])->findOrFail($product->id);

    return $this->successResponse(
        $product,
        'Product retrieved successfully.'
    );
}
   /**
 * Store a newly created product.
 */
public function store(StoreProductRequest $request)
{
    DB::beginTransaction();

    try {

        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Generate Slug
        |--------------------------------------------------------------------------
        */

        $slug = Str::slug($data['name']);

        $count = Product::where('slug', $slug)->count();

        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $data['slug'] = $slug;

        /*
        |--------------------------------------------------------------------------
        | Upload Main Image
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {

            $uploaded = $this->uploadProductImage(
                $request->file('image')
            );

            $data['image'] = $uploaded['image'];
        }

        /*
        |--------------------------------------------------------------------------
        | Create Product
        |--------------------------------------------------------------------------
        */

        $product = Product::create($data);

        /*
        |--------------------------------------------------------------------------
        | Upload Gallery Images
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('images')) {

            foreach ($request->file('images') as $image) {

                $uploaded = $this->uploadProductImage($image);

                $product->images()->create([
                    'image' => $uploaded['image'],
                    'thumbnail' => $uploaded['thumbnail'],
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        if (!empty($data['categories'])) {

            $product->categories()->sync($data['categories']);
        }

        /*
        |--------------------------------------------------------------------------
        | Product Variations
        |--------------------------------------------------------------------------
        */

        if (
            $request->boolean('has_variations')
            && !empty($data['variations'])
        ) {

            foreach ($data['variations'] as $variationData) {

                $variation = $product->variations()->create([

                    'sku' => $variationData['sku'],

                    'price' => $variationData['price'],

                    'sale_price' => $variationData['sale_price'] ?? null,

                    'stock' => $variationData['stock'],

                    'image' => $variationData['image'] ?? null,

                    'is_active' => $variationData['is_active'] ?? true,

                ]);

                $variation->attributeValues()->sync(
                    $variationData['attribute_values']
                );
            }
        }

        DB::commit();

        Cache::forget('products');

        $product->load([
            'categories',
            'images',
            'variations.attributeValues.attribute',
        ]);

        return $this->successResponse(
            $product,
            'Product created successfully.',
            201
        );

    } catch (\Exception $e) {

        DB::rollBack();

        return $this->errorResponse(
            'Failed to create product.',
            500,
            $e->getMessage()
        );
    }
}
   /**
 * Update the specified product.
 */
public function update(
    UpdateProductRequest $request,
    Product $product
) {
    DB::beginTransaction();

    try {

        $data = $request->validated();

        // Update product
        $this->updateProduct($request, $product, $data);

        // Sync categories
        $this->syncCategories($product, $data);

        // Upload gallery images
        $this->uploadGalleryImages($request, $product);

        // Sync variations
        if (
            ($data['has_variations'] ?? false)
            && isset($data['variations'])
        ) {
            $this->syncVariations(
                $product,
                $data['variations']
            );
        }

        DB::commit();

        Cache::forget('products');
        Cache::forget('product_' . $product->id);

        $product->load([
            'categories',
            'images',
            'variations.attributeValues.attribute'
        ]);

        return $this->successResponse(
            $product,
            'Product updated successfully.'
        );

    } catch (\Exception $e) {

        DB::rollBack();

        return $this->errorResponse(
            'Failed to update product.',
            500,
            $e->getMessage()
        );
    }
}
private function updateProduct(
    Request $request,
    Product $product,
    array $data
) {

    if (isset($data['name'])) {

        $slug = Str::slug($data['name']);

        $count = Product::where('slug', $slug)
            ->where('id', '!=', $product->id)
            ->count();

        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $data['slug'] = $slug;
    }

    if ($request->hasFile('image')) {

        if ($product->image) {
            $this->deleteImage($product->image);
        }

        $uploaded = $this->uploadProductImage(
            $request->file('image')
        );

        $data['image'] = $uploaded['image'];
    }

    $product->update([
        'name' => $data['name'] ?? $product->name,
        'slug' => $data['slug'] ?? $product->slug,
        'description' => $data['description'] ?? $product->description,
        'price' => $data['price'] ?? $product->price,
        'stock' => $data['stock'] ?? $product->stock,
        'sku' => $data['sku'] ?? $product->sku,
        'image' => $data['image'] ?? $product->image,
        'is_active' => $data['is_active'] ?? $product->is_active,
        'has_variations' => $data['has_variations'] ?? $product->has_variations,
    ]);
}
/**
 * Create, update and delete product variations.
 */
private function syncVariations(
    Product $product,
    array $variations
): void {

    $existingIds = [];

    foreach ($variations as $variationData) {

        /*
        |--------------------------------------------------------------------------
        | Check Duplicate SKU
        |--------------------------------------------------------------------------
        */

        $skuExists = ProductVariation::where('sku', $variationData['sku'])
            ->when(
                !empty($variationData['id']),
                fn($query) => $query->where(
                    'id',
                    '!=',
                    $variationData['id']
                )
            )
            ->exists();

        if ($skuExists) {
            throw new \Exception(
                'Variation SKU already exists: ' .
                $variationData['sku']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Update Variation
        |--------------------------------------------------------------------------
        */

        if (!empty($variationData['id'])) {

            $variation = $product->variations()
                ->findOrFail($variationData['id']);

            $variation->update([
                'sku' => $variationData['sku'],
                'price' => $variationData['price'],
                'sale_price' => $variationData['sale_price'] ?? null,
                'stock' => $variationData['stock'],
                'image' => $variationData['image'] ?? null,
                'is_active' => $variationData['is_active'] ?? true,
            ]);

        } else {

            /*
            |--------------------------------------------------------------------------
            | Create Variation
            |--------------------------------------------------------------------------
            */

            $variation = $product->variations()->create([
                'sku' => $variationData['sku'],
                'price' => $variationData['price'],
                'sale_price' => $variationData['sale_price'] ?? null,
                'stock' => $variationData['stock'],
                'image' => $variationData['image'] ?? null,
                'is_active' => $variationData['is_active'] ?? true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Sync Attribute Values
        |--------------------------------------------------------------------------
        */

        $variation->attributeValues()->sync(
            $variationData['attribute_values']
        );

        $existingIds[] = $variation->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Removed Variations
    |--------------------------------------------------------------------------
    */

    $product->variations()
        ->whereNotIn('id', $existingIds)
        ->delete();
}

private function syncCategories(
    Product $product,
    array $data
): void {

    if (isset($data['categories'])) {
        $product->categories()->sync($data['categories']);
    }
} 
private function uploadGalleryImages(
    Request $request,
    Product $product
): void {

    if (!$request->hasFile('images')) {
        return;
    }

    foreach ($request->file('images') as $image) {

        $uploaded = $this->uploadProductImage($image);

        $product->images()->create([
            'image' => $uploaded['image'],
            'thumbnail' => $uploaded['thumbnail'],
        ]);
    }
}
    /**
     * Remove the specified resource from storage.
     */
  /**
 * Soft delete the specified product.
 */
public function destroy(Product $product)
{
    DB::beginTransaction();

    try {

        // Delete main image
        if ($product->image) {
            $this->deleteImage($product->image);
        }

        // Delete gallery images
        foreach ($product->images as $image) {

            $this->deleteImage($image->image);
            $this->deleteImage($image->thumbnail);
        }

        // Soft delete product
        $product->delete();

        Cache::forget('products');
        Cache::forget('product_' . $product->id);

        DB::commit();

        return $this->successResponse(
            null,
            'Product deleted successfully.'
        );

    } catch (\Exception $e) {

        DB::rollBack();

        return $this->errorResponse(
            'Failed to delete product.',
            500,
            $e->getMessage()
        );
    }
}

  
   /**
 * Restore a soft deleted product.
 */
public function undoDelete(Request $request, $id)
{
    if (!$request->user()->hasRole('admin')) {
        return $this->errorResponse(
            'You are not authorized to perform this action.',
            403
        );
    }

    $product = Product::withTrashed()->findOrFail($id);

    $product->restore();

    Cache::forget('products');
    Cache::forget('product_' . $product->id);

    return $this->successResponse(
        $product,
        'Product restored successfully.'
    );
}

/**
 * Permanently delete a product.
 */
public function permanentDelete(Request $request, $id)
{
    if (!$request->user()->hasRole('admin')) {
        return $this->errorResponse(
            'You are not authorized to perform this action.',
            403
        );
    }

    $product = Product::withTrashed()->findOrFail($id);

    // Delete main image
    if ($product->image) {
        $this->deleteImage($product->image);
    }

    // Delete gallery images
    foreach ($product->images as $image) {

        $this->deleteImage($image->image);
        $this->deleteImage($image->thumbnail);
    }

    $product->forceDelete();

    Cache::forget('products');
    Cache::forget('product_' . $product->id);

    return $this->successResponse(
        null,
        'Product permanently deleted successfully.'
    );
}
 /**
 * Display all products for admin.
 */
public function adminIndex(Request $request)
{
    if (!$request->user()->hasRole('admin')) {
        return $this->errorResponse(
            'You are not authorized to perform this action.',
            403
        );
    }

    $products = Product::withTrashed()
        ->with([
            'categories',
            'images',
            'variations.attributeValues.attribute'
        ])
        ->latest()
        ->paginate(15);

    return $this->successResponse(
        $products,
        'Products retrieved successfully.'
    );
}
/**
 * Get only soft deleted products.
 */
public function deletedProducts()
{
    $products = Product::onlyTrashed()
        ->with([
            'categories',
            'images',
            'variations.attributeValues.attribute'
        ])
        ->latest()
        ->paginate(15);

    return $this->successResponse(
        $products,
        'Deleted products retrieved successfully.'
    );
}
  /**
 * Filter products.
 */
public function filter(Request $request)
{
    $products = Product::with([
            'categories',
            'images',
            'variations.attributeValues.attribute'
        ])
        ->when(
            $request->filled('price_min'),
            fn($query) => $query->where('price', '>=', $request->price_min)
        )
        ->when(
            $request->filled('price_max'),
            fn($query) => $query->where('price', '<=', $request->price_max)
        )
        ->when(
            $request->filled('q'),
            function ($query) use ($request) {

                $query->where(function ($q) use ($request) {

                    $q->where('name', 'like', "%{$request->q}%")
                        ->orWhere('description', 'like', "%{$request->q}%")
                        ->orWhere('sku', 'like', "%{$request->q}%");

                });

            }
        )
        ->paginate(12);

    return $this->successResponse(
        $products,
        'Products retrieved successfully.'
    );
}
}
