<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function show(string $locale, Product $product): array
    {
        $productIds = Product::query()->get()->pluck(['id'])->toArray();

        return [
          'product' => new ProductResource($product),
          'productIds' => $productIds,
        ];
    }

    public function recommended(): AnonymousResourceCollection
    {
        $recommendedProducts = Cache::remember('recommended_products', 5 * 60, function () {
            return Product::query()->get()->random(4);
        });;

        return ProductResource::collection($recommendedProducts);
    }
}
