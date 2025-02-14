<?php

namespace App\Enum;

enum CouponType: int
{
    case FIXED = 1;
    case PERCENTAGE = 2;
}
