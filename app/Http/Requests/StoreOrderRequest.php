<?php

namespace App\Http\Requests;

use App\Traits\Validation\AddressValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'user_id' => ['required', 'numerical', 'exists:users,id'],

            ...$this->prefixedAddressRules(),

            'cart' => ['required', 'array'],
            'cart.*.product_id' => ['required', 'exists:products,id'],
            'cart.*.sku_id' => ['required', 'exists:skus,id'],
            'cart.*.amount' => ['required', 'integer'],
            'cart.*.quantity' => ['required', 'integer', 'min:1'],

            'total_price' => ['required', 'integer', 'min:1'],
            'comment' => ['nullable', 'string']
        ];
    }

    private function prefixedAddressRules(): array
    {
        return collect($this->addressRules('address'))
            ->mapWithKeys(fn($rule, $key) => ["address.$key" => $rule])
            ->toArray();
    }
}
