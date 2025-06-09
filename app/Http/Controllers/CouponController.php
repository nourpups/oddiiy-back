<?php

namespace App\Http\Controllers;

use App\Enum\FirstOrderCoupon;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function firstOrder(): CouponResource
    {
        $firstOrderCoupon = Coupon::query()->find(FirstOrderCoupon::ID);

        return new CouponResource($firstOrderCoupon);
    }

    public function apply(Request $request): JsonResponse|CouponResource
    {
        $validator = Validator::make(
            data: $request->all(),
            rules: [
                'code' => 'required|string'
            ],
            messages: [
                'code.required' => __('messages.coupon.code')
            ]
        );

        $validated = $validator->validated();

        $coupon = Coupon::query()
            ->where('code', $validated['code'])
            ->first();

        if (!$coupon) {
            return response()->json([
                'message' => __('messages.coupon.notFound')
            ], 404);
        }

        if ($coupon->max_uses < 1) {
            return response()->json([
                'message' => __('messages.coupon.invalid')
            ], 400);
        }

        /** @var User $user */
        $user = auth()->user();
        $hasEverUsedCoupon = $user->orders()
            ->where('coupon_id', $coupon->id)
            ->exists();

        if ($hasEverUsedCoupon) {
            return response()->json([
                'message' => __('messages.coupon.used')
            ], 400);
        }

        $coupon->decrement('max_uses');

        return new CouponResource($coupon);
    }
}
