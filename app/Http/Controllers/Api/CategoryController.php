<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\Category\CategoryService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected CategoryService $categoryService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | List Categories
    |--------------------------------------------------------------------------
    */

    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getAll();

        return $this->successResponse(
            CategoryResource::collection($categories),
            __('messages.categories_retrieved')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Category
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreCategoryRequest $request
    ): JsonResponse {

        $category = $this->categoryService->create(
            $request->validated()
        );

        return $this->successResponse(
            new CategoryResource($category),
            __('messages.category_created'),
            201
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Category
    |--------------------------------------------------------------------------
    */

    public function show(Category $category): JsonResponse
    {
        $category = $this->categoryService
            ->getById($category);

        return $this->successResponse(
            new CategoryResource($category),
            __('messages.category_retrieved')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Category
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateCategoryRequest $request,
        Category $category
    ): JsonResponse {

        $category = $this->categoryService->update(
            $category,
            $request->validated()
        );

        return $this->successResponse(
            new CategoryResource($category),
            __('messages.category_updated')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Category
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Category $category
    ): JsonResponse {

        $this->categoryService->delete($category);

        return $this->successResponse(
            null,
            __('messages.category_deleted')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Category Products
    |--------------------------------------------------------------------------
    */

    public function products(
        Category $category
    ): JsonResponse {

        $products = $this->categoryService
            ->getProducts($category);

        return $this->successResponse(
            $products,
            __('messages.category_products_retrieved')
        );
    }
}