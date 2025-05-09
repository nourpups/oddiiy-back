<?php

namespace App\Http\Requests;

use App\Rules\UpdateMediaImage;
use Astrotomic\Translatable\Validation\RuleFactory;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
        return[
            ...RuleFactory::make([
                'translations.%name%' => ['required', 'string'],
            ]),
            'image' => ['sometimes', new UpdateMediaImage],
        ];
    }
}
