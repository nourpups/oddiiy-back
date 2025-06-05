<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCollectionRequest;
use App\Http\Requests\UpdateCollectionRequest;
use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CollectionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $collections = Collection::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();

        return CollectionResource::collection($collections);
    }

    public function store(StoreCollectionRequest $request): CollectionResource
    {
        return DB::transaction(static function () use ($request) {
            $collection = Collection::query()->create([
                ...$request->validated(),
                'slug' => Str::slug($request->validated('name')),
            ]);

            $collection->products()->sync($request->validated('product_ids'));

            return new CollectionResource($collection);
        });
    }

    public function show(string $locale, Collection $collection): CollectionResource
    {
        $collection->loadCount('products');

        return new CollectionResource($collection);
    }

    public function update(UpdateCollectionRequest $request, string $locale, Collection $collection): CollectionResource
    {
        DB::transaction(static function () use ($request, $collection) {
            $collection->update([
                ...$request->validated(),
                'slug' => Str::slug($request->validated('name')),
            ]);
            $collection->products()->sync($request->validated('product_ids'));
        });

        $collection->refresh();

        return new CollectionResource($collection);
    }

    public function destroy(string $locale, Collection $collection): Response
    {
        DB::transaction(static function () use ($collection) {
            $collection->products()->detach();
            $collection->delete();
        });

        return response()->noContent();
    }
}
