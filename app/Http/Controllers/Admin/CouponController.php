<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CouponController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $coupons = Coupon::query()->get();

        return CouponResource::collection($coupons);
    }

    public function store(StoreCouponRequest $request): CouponResource
    {
        $coupon = Coupon::query()->create($request->validated());

        return new CouponResource($coupon);
    }

    public function show(string $locale, Coupon $coupon)
    {
        return new CouponResource($coupon);
    }

    public function update(UpdateCouponRequest $request, string $locale, Coupon $coupon)
    {
        $coupon->update($request->validated());

        return new CouponResource($coupon);
    }

    public function destroy(string $locale, Coupon $coupon)
    {

    }
}
