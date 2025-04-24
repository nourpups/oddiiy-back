<?php

namespace Database\Seeders;

use App\Enum\FirstOrderCoupon;
use App\Enum\SaleType;
use App\Models\Coupon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Coupon::factory()->create([
            'id' => FirstOrderCoupon::ID,
            'code' => 'POCHIN',
            'value' => 10,
            'type' => SaleType::PERCENTAGE,
            'max_uses' => FirstOrderCoupon::MAX_USES
        ]);

        Coupon::factory()->create([
            'code' => "O'ZIGAXOS",
            'value' => 15,
            'type' => SaleType::PERCENTAGE
        ]);
    }
}
