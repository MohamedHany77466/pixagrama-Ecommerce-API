<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCartRequest extends FormRequest
{
    /**
     * Authorization
     */
    public function authorize(): bool
    {
        
        return true;
    }

    /**
     * Validation Rules
     */
public function rules(): array
{
    return [
        'product_id' => [
            'required',
            'exists:products,id',
        ],

        'product_variation_id' => [
            'nullable',
            'exists:product_variations,id',
        ],

        'quantity' => [
            'required',
            'integer',
            'min:1',
        ],
    ];
}
    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'المنتج مطلوب.',
            'product_id.integer'  => 'معرّف المنتج يجب أن يكون رقمًا.',
            'product_id.exists'   => 'المنتج غير موجود.',

            'quantity.required' => 'الكمية مطلوبة.',
            'quantity.integer'  => 'الكمية يجب أن تكون رقمًا صحيحًا.',
            'quantity.min'      => 'أقل كمية يمكن إضافتها هي 1.',
            'quantity.max'      => 'أقصى كمية يمكن إضافتها هي 100.',
        ];
    }

    /**
     * Custom attribute names
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'المنتج',
            'quantity'   => 'الكمية',
        ];
    }
}