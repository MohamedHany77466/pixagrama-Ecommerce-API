<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'parent_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('validation.attributes.category_name'),
            'description' => __('validation.attributes.description'),
            'parent_id' => __('validation.attributes.parent_category'),
            'is_active' => __('validation.attributes.is_active'),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {

            $category = $this->route('category');

            if (
                $category instanceof \App\Models\Category &&
                $this->filled('parent_id') &&
                (int) $this->input('parent_id') === $category->id
            ) {
                $validator->errors()->add(
                    'parent_id',
                    __('validation.category_parent_self')
                );
            }
        });
    }
}