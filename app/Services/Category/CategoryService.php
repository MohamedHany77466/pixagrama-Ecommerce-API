<?php

namespace App\Services\Category;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryService
{
    /*
    |--------------------------------------------------------------------------
    | Get Categories
    |--------------------------------------------------------------------------
    */

public function getAll()
{
    return Category::query()
        ->whereNull('parent_id')
        ->with('children')
        ->withCount('children')
        ->get();
}

    /*
    |--------------------------------------------------------------------------
    | Get Category
    |--------------------------------------------------------------------------
    */

    public function getById(Category $category): Category
    {
        $category->load([
            'parent',
            'children.products',
            'products',
        ])->loadCount('children');

        return $category;
    }


    /*
    |--------------------------------------------------------------------------
    | Create Category
    |--------------------------------------------------------------------------
    */

    public function create(array $data): Category
    {
        $data['slug'] = $this->generateUniqueSlug(
            $data['name']
        );

        $data['is_active'] = $data['is_active'] ?? true;

        return Category::create($data);
    }


    /*
    |--------------------------------------------------------------------------
    | Update Category
    |--------------------------------------------------------------------------
    */

    public function update(
        Category $category,
        array $data
    ): Category {

        if (
            isset($data['name']) &&
            $data['name'] !== $category->name
        ) {
            $data['slug'] = $this->generateUniqueSlug(
                $data['name'],
                $category->id
            );
        }

        $category->update($data);

        return $category->fresh();
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Category
    |--------------------------------------------------------------------------
    */

    public function delete(Category $category): void
    {
        DB::transaction(function () use ($category) {

            $category->children()->update([
                'parent_id' => $category->parent_id,
            ]);

            $category->delete();
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Get Category Products
    |--------------------------------------------------------------------------
    */

    public function getProducts(Category $category)
    {
        return $category
            ->products()
            ->get();
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Unique Slug
    |--------------------------------------------------------------------------
    */

    private function generateUniqueSlug(
        string $name,
        ?int $ignoreId = null
    ): string {

        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'category';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            Category::query()
                ->where('slug', $slug)
                ->when(
                    $ignoreId,
                    fn ($query) => $query->where(
                        'id',
                        '!=',
                        $ignoreId
                    )
                )
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}