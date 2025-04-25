<?php

namespace App\Http\Requests;

use App\Enum\CouponStatus;
use App\Enum\SaleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
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
            'code' => ['required', 'string'],
            'value' => ['required', 'integer'],
            'type' => ['required', Rule::enum(SaleType::class)],
            'status' => ['required', Rule::enum(CouponStatus::class)],
            'max_uses' => ['sometimes', 'integer'],
        ];
    }
}
