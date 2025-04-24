<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
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

            'address' => ['required', 'array'],
            'address.region' => ['required', 'string', 'max:255'],
            'address.city' => ['required', 'string', 'max:255'],
            'address.district' => ['nullable', 'string', 'max:255'],
            'address.street' => ['required', 'string', 'max:255'],
            'address.house' => ['required', 'string', 'max:10'],
            'address.entrance' => ['nullable', 'string', 'max:10'],
            'address.floor' => ['nullable', 'integer', 'min:1', 'max:100'],
            'address.apartment' => ['nullable', 'string', 'max:10'],
            'address.orientation' => ['nullable', 'string', 'max:255'],
            'address.postal' => ['nullable', 'string', 'max:10'],

            'cart' => ['required', 'array'],
            'cart.*.product_id' => ['required', 'exists:products,id'],
            'cart.*.sku_id' => ['required', 'exists:skus,id'],
            'cart.*.amount' => ['required', 'integer'],
            'cart.*.quantity' => ['required', 'integer', 'min:1'],

            'total_price' => ['required', 'integer', 'min:1'],
            'comment' => ['nullable', 'string']
        ];
    }
}
