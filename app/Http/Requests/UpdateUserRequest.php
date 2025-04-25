<?php

namespace App\Http\Requests;

use App\Traits\Validation\AddressValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    use AddressValidationRules;
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
            'name' => ['required', 'string'],
            'phone' => ['required', 'phone'],
            'password' => ['nullable', 'string'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['sometimes', 'array'],
            ...$this->prefixedAddressRules()
        ];
    }

    private function prefixedAddressRules(): array
    {
        return collect($this->addressRules('address'))
            ->mapWithKeys(fn($rule, $key) => ["address.$key" => $rule])
            ->toArray();
    }
}
