<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
        $recommendedProducts = Product::query()->get()->random(4);

        return ProductResource::collection($recommendedProducts);
    }
}
