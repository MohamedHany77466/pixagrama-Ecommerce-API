<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Authorize the request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        $product = $this->route('product');

        return [

            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            'name' => 'sometimes|string|max:255',

            'description' => 'nullable|string',

            'price' => 'nullable|numeric|min:0',

            'stock' => 'nullable|integer|min:0',

            'sku' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'sku')->ignore($product),
            ],

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'is_active' => 'nullable|boolean',

            'has_variations' => 'nullable|boolean',

            /*
            |--------------------------------------------------------------------------
            | Categories
            |--------------------------------------------------------------------------
            */

            'categories' => 'nullable|array',

            'categories.*' => 'exists:categories,id',

            /*
            |--------------------------------------------------------------------------
            | Gallery Images
            |--------------------------------------------------------------------------
            */

            'images' => 'nullable|array',

            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',

            /*
            |--------------------------------------------------------------------------
            | Product Variations
            |--------------------------------------------------------------------------
            */

            'variations' => 'nullable|array|min:1',

            'variations.*.id' => 'nullable|exists:product_variations,id',

            'variations.*.sku' => 'required_with:variations|string|max:255',

            'variations.*.price' => 'required_with:variations|numeric|min:0',

            'variations.*.sale_price' => 'nullable|numeric|min:0|lte:variations.*.price',

            'variations.*.stock' => 'required_with:variations|integer|min:0',

            'variations.*.image' => 'nullable|string|max:255',

            'variations.*.is_active' => 'nullable|boolean',

            'variations.*.attribute_values' => 'required_with:variations|array|min:1',

            'variations.*.attribute_values.*' => 'exists:attribute_values,id',
        ];
    }


    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم المنتج مطلوب.',
            'name.max' => 'اسم المنتج يجب ألا يزيد عن 255 حرفًا.',

            'slug.required' => 'الرابط المختصر مطلوب.',
            'slug.unique' => 'الرابط المختصر مستخدم بالفعل.',

            'price.required' => 'السعر مطلوب.',
            'price.numeric' => 'السعر يجب أن يكون رقمًا.',
            'price.min' => 'السعر يجب أن يكون أكبر من أو يساوي صفر.',

            'stock.integer' => 'المخزون يجب أن يكون رقمًا صحيحًا.',
            'stock.min' => 'المخزون يجب أن يكون أكبر من أو يساوي صفر.',

            'sku.required' => 'SKU مطلوب.',
            'sku.unique' => 'SKU مستخدم بالفعل.',

            'categories.array' => 'التصنيفات يجب أن تكون مصفوفة.',
            'categories.*.exists' => 'أحد التصنيفات المحددة غير موجود.',

            'image.image' => 'الملف يجب أن يكون صورة.',
            'image.mimes' => 'الصورة يجب أن تكون بصيغة JPG أو JPEG أو PNG.',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 2MB.',

            'images.array' => 'معرض الصور يجب أن يكون مصفوفة.',
            'images.*.image' => 'جميع الملفات يجب أن تكون صورًا.',
            'images.*.mimes' => 'الصور يجب أن تكون بصيغة JPG أو JPEG أو PNG أو WEBP.',
            'images.*.max' => 'حجم كل صورة يجب ألا يتجاوز 4MB.',
            'variations.required_with' =>
                'Product variations are required.',

            'variations.*.sku.required_with' =>
                'Variation SKU is required.',

            'variations.*.price.required_with' =>
                'Variation price is required.',

            'variations.*.stock.required_with' =>
                'Variation stock is required.',

            'variations.*.attribute_values.required_with' =>
                'Select at least one attribute value.',
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم المنتج',
            'slug' => 'الرابط المختصر',
            'description' => 'الوصف',
            'price' => 'السعر',
            'stock' => 'المخزون',
            'sku' => 'SKU',
            'is_active' => 'الحالة',
            'categories' => 'التصنيفات',
            'image' => 'الصورة الرئيسية',
            'images' => 'معرض الصور',
        ];
    }
}