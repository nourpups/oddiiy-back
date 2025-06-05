<?php

namespace App\Http\Requests;

use App\Enum\SaleType;
use Astrotomic\Translatable\Validation\RuleFactory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...RuleFactory::make([
                'translations.%name%' => ['required', 'string'],
                'translations.%description%' => ['required', 'string'],
            ]),
            'tag_id' => ['sometimes', 'integer', 'exists:tags,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'is_visible' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer'],

            'discount' => ['sometimes', 'array:value,type,starts_at,expires_at'],
            'discount.value' => ['required_with:discount', 'integer'],
            'discount.type' => ['required_with:discount', Rule::enum(SaleType::class)],
            'discount.starts_at' => ['sometimes', 'date', 'after:yesterday'],
            'discount.expires_at' => ['sometimes', 'date', 'after:discount.starts_at'],

            'skus.*.price' => ['required', 'numeric', 'min:1000'],
            'skus.*.stock' => ['sometimes', 'numeric'],

            'skus.*.combinations' => ['sometimes', 'array'],
            'skus.*.combinations.*.options' => ['required_with:skus.combinations', 'array'],
            'skus.*.combinations.*.stock' => ['required_with:skus.combinations', 'integer'],

            'skus.*.images' => ['required', 'array'],
            'skus.*.images.*' => ['required', File::image()->max(4096)]
        ];
    }

    public function messages(): array
    {
        return [
            'skus.*.price' => __('messages.min.price'),
            'skus.*.combinations.*.stock' => __('messages.min.price')
        ];
    }
}
