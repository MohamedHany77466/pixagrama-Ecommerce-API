<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\AttributeValue;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttributeValue\StoreAttributeValueRequest;
use App\Http\Requests\AttributeValue\UpdateAttributeValueRequest;

class AttributeValueController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        try {

            $values = AttributeValue::with('attribute')->paginate(10);

            return $this->successResponse(
                $values,
                'Attribute values retrieved successfully.'
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                'Failed to retrieve attribute values.',
                500,
                $e->getMessage()
            );
        }
    }

    public function store(StoreAttributeValueRequest $request)
    {
        try {

            $value = AttributeValue::create($request->validated());

            return $this->successResponse(
                $value,
                'Attribute value created successfully.',
                201
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                'Failed to create attribute value.',
                500,
                $e->getMessage()
            );
        }
    }

    public function show(AttributeValue $attributeValue)
    {
        try {

            $attributeValue->load('attribute');

            return $this->successResponse(
                $attributeValue,
                'Attribute value retrieved successfully.'
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                'Failed to retrieve attribute value.',
                500,
                $e->getMessage()
            );
        }
    }

    public function update(UpdateAttributeValueRequest $request, AttributeValue $attributeValue)
    {
        try {

            $attributeValue->update($request->validated());

            return $this->successResponse(
                $attributeValue,
                'Attribute value updated successfully.'
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                'Failed to update attribute value.',
                500,
                $e->getMessage()
            );
        }
    }

    public function destroy(AttributeValue $attributeValue)
    {
        try {

            $attributeValue->delete();

            return $this->successResponse(
                null,
                'Attribute value deleted successfully.'
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                'Failed to delete attribute value.',
                500,
                $e->getMessage()
            );
        }
    }
}