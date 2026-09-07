<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\PaymentProvider;

class CheckoutRequest extends FormRequest
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
            'shipping_name' => [
                'required',
                'string',
                'max:255',
            ],

            'shipping_address' => [
                'required',
                'string',
                'max:255',
            ],

            'shipping_city' => [
                'required',
                'string',
                'max:255',
            ],

            'shipping_state' => [
                'nullable',
                'string',
                'max:255',
            ],

            'shipping_zipcode' => [
                'required',
                'string',
                'max:20',
            ],

            'shipping_country' => [
                'required',
                'string',
                'max:255',
            ],

            'shipping_phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s]+$/',
            ],

           'payment_method' => [
    'required',
    'in:' . implode(',', PaymentProvider::values()),
],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'shipping_name.required' => 'اسم المستلم مطلوب.',
            'shipping_address.required' => 'عنوان الشحن مطلوب.',
            'shipping_city.required' => 'المدينة مطلوبة.',
            'shipping_zipcode.required' => 'الرمز البريدي مطلوب.',
            'shipping_country.required' => 'الدولة مطلوبة.',
            'shipping_phone.required' => 'رقم الهاتف مطلوب.',

            'shipping_phone.regex' => 'رقم الهاتف غير صحيح.',

           'payment_method.required'
    => 'طريقة الدفع مطلوبة.',

'payment_method.enum'
    => 'طريقة الدفع غير مدعومة.',
        ];
    }

    /**
     * Custom attributes
     */
    public function attributes(): array
    {
        return [
            'shipping_name' => 'اسم المستلم',
            'shipping_address' => 'عنوان الشحن',
            'shipping_city' => 'المدينة',
            'shipping_state' => 'المحافظة/الولاية',
            'shipping_zipcode' => 'الرمز البريدي',
            'shipping_country' => 'الدولة',
            'shipping_phone' => 'رقم الهاتف',
            'payment_method' => 'طريقة الدفع',
            'notes' => 'ملاحظات',
        ];
    }
}