<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = ['Hot', 'Sale', 'New'];
        $tagsToCreate = array_map(fn ($tag) => ['name' => $tag], $tags);

        Tag::query()->upsert($tagsToCreate, ['name']);

        $products = Product::all();
        $products->each(function (Product $product) {
           $tags = Tag::all();

           $product->tag()->associate($tags->random());
           $product->save();
        });
    }
}
