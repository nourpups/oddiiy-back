<?php

namespace App\Http\Requests;

use App\Enum\DeliveryType;
use App\Enum\PaymentType;
use App\Traits\Validation\AddressValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    use AddressValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'numeric', 'exists:users,id'],
            'coupon_id' => ['nullable', 'numeric', 'exists:coupons,id'],
            'cashback_wallet_option_id' => ['nullable', 'numeric', 'exists:cashback_wallet_options,id'],
            'recipient_name' => ['required', 'string'],
            'telegram_contact' => ['required', 'string'],
            'delivery' => ['required', Rule::enum(DeliveryType::class)],
            'payment' => ['required', 'numeric', Rule::enum(PaymentType::class)],

// отключаем адресс временно (или нет)
//            ...$this->prefixedAddressRules(),

            'items' => ['required', 'array'],
            'items.*.sku_id' => ['required', 'exists:skus,id'],
            'items.*.sku_variant_id' => ['required', 'exists:sku_variants,id'],
            'items.*.quantity' => ['required', 'integer'],
            'items.*.price' => ['required', 'integer'],

            'sum' => ['required', 'integer'],
            'comment' => ['nullable', 'string']
        ];
    }

    private function prefixedAddressRules(): array
    {
        return collect($this->addressRules())
            ->mapWithKeys(fn($rule, $key) => ["address.$key" => $rule])
            ->toArray();
    }
}
