<?php

namespace App\Helper;

use App\Enum\SaleType;
use App\Models\Coupon;
use App\Models\Discount;

class SaleHelper
{
    public static function formatSale(Discount|Coupon $sale, int $price): array
    {
        return match ($sale->type) {
          SaleType::FIXED => self::calculateFixedSale($sale, $price),
          SaleType::PERCENTAGE => self::calculatePercentSale($sale, $price)
        };
    }

    public static function calculateFixedSale(Discount|Coupon $sale, int $sum): array
    {
        $saleAmount = $sale->value;
        $salePrice = $sum - $saleAmount;
        $salePercent = floor(100 - ($salePrice / $sum) * 100);

        return [
            'amount' => $saleAmount,
            'price' => max(0, $salePrice),
            'percent' => max(0, $salePercent),
        ];
    }

    public static function calculatePercentSale(Discount|Coupon $sale, int $sum): array
    {
        $salePercent = $sale->value;
        $saleAmount = $sum * ($salePercent / 100);
        $salePrice = $sum - $saleAmount;

        return [
            'amount' => $saleAmount,
            'price' => max(0, $salePrice),
            'percent' => max(0, $salePercent),
        ];
    }
}
