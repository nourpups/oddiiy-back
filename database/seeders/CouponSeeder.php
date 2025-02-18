<?php

namespace Database\Seeders;

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
            'code' => 'OZIGAXOS',
            'value' => 15,
            'type' => SaleType::PERCENTAGE
        ]);

        Coupon::factory()->create([
            'code' => 'POCHIN',
            'value' => 10,
            'type' => SaleType::PERCENTAGE
        ]);
    }
}
