<?php

namespace App\Http\Controllers;

use App\Http\Resources\CollectionResource;
use App\Http\Resources\ProductResource;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): array
    {
        $collections = Collection::with([
            'products' => function (Builder $q) {
                $q->limit(2);
            }
        ])
            ->where('is_featured', true)
            ->get();
        $products = Product::all();

        $productResourceCollection = ProductResource::collection($products);

        $discountedProducts = $productResourceCollection->collection->where('discount', '!==', null);
        $groupedProducts = $productResourceCollection->collection
            ->where('tag', '!==', null)
            ->groupBy('tag.name');
        // ищем продукты со скидкой отдельно, так как продукты со скидкой, могут не иметь тег Saleы
        $groupedProducts['Sale'] = $discountedProducts;

        $productResourceCollection->collection = $groupedProducts;

        return [
            'collections' => (CollectionResource::collection($collections))
                ->response()
                ->getData(true),
            'products' => $productResourceCollection
                ->response()
                ->getData(true),
        ];
    }
}
