<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyCouponRequest extends FormRequest
{
    /**
     * Authorization
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'coupon' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9\-]+$/',
            ],
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'coupon.required' => 'كود الكوبون مطلوب.',
            'coupon.string'   => 'كود الكوبون يجب أن يكون نصًا.',
            'coupon.max'      => 'كود الكوبون طويل جدًا.',
            'coupon.regex'    => 'كود الكوبون غير صحيح.',
        ];
    }

    /**
     * Custom attributes
     */
    public function attributes(): array
    {
        return [
            'coupon' => 'كود الخصم',
        ];
    }
}