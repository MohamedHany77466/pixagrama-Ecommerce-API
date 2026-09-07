<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            'name' => 'required|string|max:255',

            'description' => 'nullable|string',

            'price' => 'nullable|numeric|min:0|required_if:has_variations,false',

            'stock' => 'nullable|integer|min:0|required_if:has_variations,false',

            'sku' => 'nullable|string|max:255|unique:products,sku|required_if:has_variations,false',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'is_active' => 'boolean',

            'has_variations' => 'boolean',

            /*
            |--------------------------------------------------------------------------
            | Categories
            |--------------------------------------------------------------------------
            */

            'categories' => 'nullable|array',

            'categories.*' => 'exists:categories,id',

            /*
            |--------------------------------------------------------------------------
            | Gallery
            |--------------------------------------------------------------------------
            */

            'images' => 'nullable|array',

            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',

            /*
            |--------------------------------------------------------------------------
            | Variations
            |--------------------------------------------------------------------------
            */

            'variations' => 'required_if:has_variations,true|array|min:1',

            'variations.*.sku' => 'required|string|max:255|unique:product_variations,sku',

            'variations.*.price' => 'required|numeric|min:0',

            'variations.*.sale_price' => 'nullable|numeric|lte:variations.*.price',

            'variations.*.stock' => 'required|integer|min:0',

            'variations.*.image' => 'nullable|string',

            'variations.*.is_active' => 'nullable|boolean',

            'variations.*.attribute_values' => 'required|array|min:1',

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

            'image.image' => 'الملف المرفوع يجب أن يكون صورة.',
            'image.mimes' => 'الصورة الرئيسية يجب أن تكون بصيغة JPG أو JPEG أو PNG.',
            'image.max' => 'حجم الصورة الرئيسية يجب ألا يتجاوز 2MB.',

            'images.array' => 'معرض الصور يجب أن يكون مصفوفة.',
            'images.*.image' => 'جميع الملفات يجب أن تكون صورًا.',
            'images.*.mimes' => 'الصور يجب أن تكون بصيغة JPG أو JPEG أو PNG أو WEBP.',
            'images.*.max' => 'حجم كل صورة يجب ألا يتجاوز 4MB.',
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