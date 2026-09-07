<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\PaymentProvider;

class PaymentRequest extends FormRequest
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
        'provider' => [
            'required',
            'string',
            Rule::enum(PaymentProvider::class),
        ],
    ];
}

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'provider.required' => 'وسيلة الدفع مطلوبة.',
            'provider.in'       => 'وسيلة الدفع غير مدعومة.',
        ];
    }

    /**
     * Custom attributes
     */
    public function attributes(): array
    {
        return [
            'provider' => 'وسيلة الدفع',
        ];
    }
}