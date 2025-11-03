<?php

namespace App\Http\Requests;

use App\Enum\SaleType;
use App\Rules\RemovedOr;
use App\Rules\UpdateMediaImage;
use Astrotomic\Translatable\Validation\RuleFactory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateProductRequest extends FormRequest
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
            'tag_id' => ['sometimes', new RemovedOr('integer')],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'is_visible' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer'],

            'discount' => ['sometimes', new RemovedOr('array')],
            'discount.value' => ['present_if:discount,array', 'integer'],
            'discount.type' => ['present_if:discount,array', Rule::enum(SaleType::class)],
            'discount.starts_at' => ['sometimes', new RemovedOr(['date']), ],
            'discount.expires_at' => ['sometimes', new RemovedOr(['date', 'after:discount.starts_at'])],

            'skus.*.id' => ['sometimes', 'numeric', 'exists:skus,id'],
            'skus.*.price' => ['required', 'numeric', 'min:1000'],
            'skus.*.stock' => ['sometimes', 'numeric'],

            'skus.*.combinations' => ['sometimes', new RemovedOr('array')],
            'skus.*.combinations.*.id' => ['required_with:skus.combinations', new RemovedOr('numeric')],
            'skus.*.combinations.*.options' => ['required_with:skus.combinations', 'array'],
            'skus.*.combinations.*.stock' => ['required_with:skus.combinations', 'integer'],

            'skus.*.images' => ['required', 'array'],
            'skus.*.images.*' => ['required', new UpdateMediaImage]
        ];
    }

    public function messages(): array
    {
        return [
            'skus.*.price' => __('messages.min.price'),
            'skus.*.images' => __('messages.images'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_visible' => filter_var($this->is_visible, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
