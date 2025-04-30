<?php

namespace App\Http\Controllers\Admin;

use App\Action\SyncSkusWithProductAction;
use App\Enum\Locale;
use App\Enum\RemoveKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\Admin\ProductResource;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Sku;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::query()
            ->with(['allImages'])
            ->latest()
            ->get();

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): ProductResource
    {
        return DB::transaction(static function () use ($request) {
            $translations = $request->validated(['translations']);
            foreach ($translations as $locale => $translationArray) {
                $name = $translationArray['name'];
                $translations[$locale]['slug'] = str($name)->slug(language: $locale);
            }
            $productData = [
                ...$translations,
                ...$request->safe(['category_id']),
            ];

            if ($request->has('tag_id')) {
                $productData = [
                    ...$productData,
                    ...$request->safe(['tag_id'])
                ];
            }

            $product = Product::query()->create($productData);

            if ($request->has('discount')) {
                $product->discount()->create($request->validated('discount'));
            }

            foreach ($request->validated('skus') as $key => $skuToCreate) {
                /** @var Sku $sku */
                $sku = $product->skus()->create([
                    'sku' => str()->random(11),
                    'price' => $skuToCreate['price'],
                ]);

                if ($request->has("skus.$key.attributes")) {
                    $attributeIds = $skuToCreate['attributes'];
                    $sku->attributeOptions()->attach($attributeIds);
                }

                foreach ($skuToCreate['images'] as $imageToCreate) {
                    $file = UploadedFile::createFromBase($imageToCreate);
                    $fileName = $file->getClientOriginalName();

                    $sku
                        ->addMedia($file)
                        ->usingName($fileName)
                        ->usingFileName(str($fileName)->slug() . $file->getExtension())
                        ->toMediaCollection();
                }
            }

            return new ProductResource($product);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $locale, Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, string $locale, Product $product)
    {
        return DB::transaction(static function () use ($request, $product) {
            $validated = $request->validated();
            $translations = $request->validated(['translations']);

            foreach ($translations as $locale => $translationArray) {
                $name = $translationArray['name'];
                $translations[$locale]['slug'] = str($name)->slug(language: $locale);
            }

            // Product
            $product->update([
                ...$translations,
                'category_id' => $validated['category_id'],
                'tag_id' => $validated['tag_id'] !== RemoveKey::REMOVE->value ? $validated['tag_id'] : null,
            ]);

            // Discount
            if (array_key_exists('discount', $validated)) {
                if (is_array($validated['discount'])) {
                    $product->discount()->updateOrCreate([], [
                        'value' => $validated['discount']['value'],
                        'type' => $validated['discount']['type'],
                        'starts_at' => $validated['discount']['starts_at'] === RemoveKey::REMOVE->value
                            ? null
                            : $validated['discount']['starts_at'],
                        'expires_at' => $validated['discount']['expires_at'] === RemoveKey::REMOVE->value
                            ? null
                            : $validated['discount']['expires_at'],
                    ]);
                } else {
                    $product->discount()?->delete();
                }
            }

            // SKU (синхронизация + очистка атрибутов если не переданы)
            (new SyncSkusWithProductAction())($product, $validated['skus']);

            $product->refresh();
            return new ProductResource($product);
        });
    }

    public function destroy(string $locale, Product $product): Response
    {
        $product->load('skus');

        $product->skus->map(function (Sku $sku) {
            $sku->attributeOptions()->detach();
            $sku->discount()->delete();
            $sku->delete();
        });

        $product->discount()->delete();
        $product->deleteTranslations();
        $product->collections()->detach();
        $product->delete();

        return response()->noContent();
    }

}
