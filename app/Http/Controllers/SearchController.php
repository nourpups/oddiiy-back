<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SearchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(SearchRequest $request): ResourceCollection
    {
        $search = $request->validated('search');
        $products = Product::query()->whereHas('translations', function (Builder $q) use ($search) {
            $q->whereLike('name', '%'.$search.'%');
        })->get();

        return ProductResource::collection($products);
    }
}
