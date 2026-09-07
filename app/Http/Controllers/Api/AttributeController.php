<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttributeRequest;
use App\Http\Requests\UpdateAttributeRequest;
use App\Models\Attribute;
use App\Traits\ApiResponseTrait;
use Exception;

class AttributeController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $attributes = Attribute::with('values')->paginate(10);

            return $this->successResponse(
                $attributes,
                'Attributes retrieved successfully.'
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                'Failed to retrieve attributes.',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreAttributeRequest $request)
    {
        try {

            $attribute = Attribute::create($request->validated());

            return $this->successResponse(
                $attribute,
                'Attribute created successfully.',
                201
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                'Failed to create attribute.',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Attribute $attribute)
    {
        try {

            $attribute->load('values');

            return $this->successResponse(
                $attribute,
                'Attribute retrieved successfully.'
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                'Failed to retrieve attribute.',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateAttributeRequest $request, Attribute $attribute)
    {
        try {

            $attribute->update($request->validated());

            return $this->successResponse(
                $attribute,
                'Attribute updated successfully.'
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                'Failed to update attribute.',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Attribute $attribute)
    {
        try {

            $attribute->delete();

            return $this->successResponse(
                null,
                'Attribute deleted successfully.'
            );

        } catch (Exception $e) {

            return $this->errorResponse(
                'Failed to delete attribute.',
                500,
                $e->getMessage()
            );
        }
    }
}