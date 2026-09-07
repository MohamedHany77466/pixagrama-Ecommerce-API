<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ProductVariation;
use App\Traits\ApiResponseTrait;
use App\Http\Requests\ProductVariation\StoreProductVariationRequest;
use App\Http\Requests\ProductVariation\UpdateProductVariationRequest;

class ProductVariationController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display all variations.
     */
    public function index()
    {
        try {

            $variations = ProductVariation::with([
                'product',
                'attributeValues.attribute'
            ])->paginate(10);

            return $this->successResponse(
                $variations,
                'Product variations retrieved successfully.'
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                'Failed to retrieve product variations.',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Store variation.
     */
    public function store(StoreProductVariationRequest $request)
    {
        DB::beginTransaction();

        try {

            $variation = ProductVariation::create([
                'product_id' => $request->product_id,
                'sku' => $request->sku,
                'price' => $request->price,
                'sale_price' => $request->sale_price,
                'stock' => $request->stock,
                'image' => $request->image,
                'is_active' => $request->is_active ?? true,
            ]);

            $variation->attributeValues()->sync(
                $request->attribute_values
            );

            DB::commit();

            return $this->successResponse(
                $variation->load([
                    'product',
                    'attributeValues.attribute'
                ]),
                'Product variation created successfully.',
                201
            );

        } catch (Exception $e) {

            DB::rollBack();

            return $this->errorResponse(
                'Failed to create product variation.',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Show variation.
     */
    public function show(ProductVariation $productVariation)
    {
        try {

            return $this->successResponse(
                $productVariation->load([
                    'product',
                    'attributeValues.attribute'
                ]),
                'Product variation retrieved successfully.'
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                'Failed to retrieve product variation.',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Update variation.
     */
    public function update(UpdateProductVariationRequest $request, ProductVariation $productVariation)
    {
        DB::beginTransaction();

        try {

            $productVariation->update([
                'product_id' => $request->product_id,
                'sku' => $request->sku,
                'price' => $request->price,
                'sale_price' => $request->sale_price,
                'stock' => $request->stock,
                'image' => $request->image,
                'is_active' => $request->is_active ?? true,
            ]);

            $productVariation->attributeValues()->sync(
                $request->attribute_values
            );

            DB::commit();

            return $this->successResponse(
                $productVariation->load([
                    'product',
                    'attributeValues.attribute'
                ]),
                'Product variation updated successfully.'
            );

        } catch (Exception $e) {

            DB::rollBack();

            return $this->errorResponse(
                'Failed to update product variation.',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Delete variation.
     */
    public function destroy(ProductVariation $productVariation)
    {
        DB::beginTransaction();

        try {

            $productVariation->attributeValues()->detach();

            $productVariation->delete();

            DB::commit();

            return $this->successResponse(
                null,
                'Product variation deleted successfully.'
            );

        } catch (Exception $e) {

            DB::rollBack();

            return $this->errorResponse(
                'Failed to delete product variation.',
                500,
                $e->getMessage()
            );
        }
    }
}