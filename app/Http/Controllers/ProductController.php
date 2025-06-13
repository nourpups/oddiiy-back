<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function show(string $locale, Product $product): array
    {
        $neighbors = [
            'prev' => Product::query()->where('id', '<', $product->id)->latest()->first()?->slug,
            'next' => Product::query()->where('id', '>', $product->id)->oldest()->first()?->slug,
        ];

        return [
            'neighbors' => $neighbors,
            'product' => new ProductResource($product),
        ];
    }

    public function recommended(): AnonymousResourceCollection
    {
        $recommendedProducts = Cache::remember('recommended_products', 5 * 60, function () {
            return Product::query()
                ->get()
                ->random(4);
        });;

        return ProductResource::collection($recommendedProducts);
    }
}
