<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttributeOptionCollection;
use App\Http\Resources\AttributeOptionResource;
use App\Http\Resources\AttributeResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::with('translations')->get();
        $products = Product::query()->with(['tag'])->get();
        $attributes = Attribute::with('attributeOptions')->get();

        return [
            'categories' => (CategoryResource::collection($categories))
                ->response()
                ->getData(true),
            'attributes' => (AttributeResource::collection($attributes))
                ->response()
                ->getData(true),
            'products' => ProductResource::collection($products)
                ->response()
                ->getData(true),
        ];
    }


    /**
     * Display the specified resource.
     */
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
