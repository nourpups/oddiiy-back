<?php

namespace Database\Seeders;

use App\Enum\SaleType;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $saleTag = Tag::query()->where('name', 'Sale')->first();
        $products = Product::query()->where('tag_id', $saleTag->id)->get();

        $products->each(function (Product $product) {
            if (mt_rand(0,1)) {
                $product->discount()->create([
                    'value' => round(mt_rand(25_000, 50_000), -3),
                    'type' => SaleType::FIXED,
                    'starts_at' => now(),
                    'expires_at' => now()->addMonth(),
                ]);
            } else {
                $product->discount()->create([
                    'value' => mt_rand(10, 35),
                    'type' => SaleType::PERCENTAGE,
                    'starts_at' => now(),
                    'expires_at' => now()->addMonth(),
                ]);
            }

        });
    }
}
