<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $collections = collect([
            "Yulduz bo'l",
            "Ooddiiy",
            "15 yorug', 15 qorong'u",
            "Sovuq",
            "Hozir yasha"
        ]);

        $collectionsToCreate = $collections->map(static function (string $item) {
           return [
               'title' => "$item sarlavhacha",
               'name' => $item,
               'slug' => str($item)->slug(language: 'uz'),
           ];
        });


        DB::transaction(function () use ($collectionsToCreate) {
            $products = Product::all();

            Collection::query()->upsert($collectionsToCreate->toArray(), 'name');

            Collection::all()->each(function (Collection $collection) use ($products) {
                $productIds = $products->random(rand(2, 5))->pluck('id')->toArray();
                $collection->products()->attach($productIds);
            });
        });
    }
}
