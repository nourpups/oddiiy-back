<?php

namespace App\Http\Controllers;

use App\Enum\FirstOrderCoupon;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function firstOrder(): CouponResource
    {
        $firstOrderCoupon = Coupon::query()->find(FirstOrderCoupon::ID);

        return new CouponResource($firstOrderCoupon);
    }

    public function apply()
    {
        // @todo - grok3
    }
}
