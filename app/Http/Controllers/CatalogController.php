<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttributeResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\ProductResource;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __invoke()
    {
        $collections = Collection::query()->get();
        $categories = Category::with('translations')->get();
        $products = Product::query()->with(['tag', 'allImages'])->get();
        $attributes = Attribute::with('options')->get();

        return [
            'collections' => (CollectionResource::collection($collections))
                ->response()
                ->getData(true),
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
}
