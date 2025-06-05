<?php

namespace App\Http\Controllers\Admin;

use App\Action\CreateSkuVariantsAction;
use App\Action\SyncSkusWithProductAction;
use App\Enum\RemoveKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\Admin\ProductResource;
use App\Models\Product;
use App\Models\Sku;
use http\Env\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $products = Product::query()
            ->with(['allImages'])
            ->latest()
            ->orderBy('sort_order')
            ->get();

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request)
    {
        return DB::transaction(static function () use ($request) {
            Log::info('creating product', $request->validated());
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
                $productData['tag_id'] = $request->validated('tag_id');
            }

            $product = Product::query()->create($productData);

            if ($request->has('discount')) {
                $product->discount()->create($request->validated('discount'));
            }

            foreach ($request->validated('skus') as $skuToCreate) {
                /** @var Sku $sku */
                $sku = $product->skus()->create([
                    'sku' => str()->random(11),
                    'price' => $skuToCreate['price'],
                ]);
                if (empty($skuToCreate['combinations'])) {
                    // Нет выбранных атрибутов (combinations пустой)
                    $sku->variants()->create([
                        'stock' => $skuToCreate['stock'],
                        'photomodel_id' => null,
                    ]);
                } else {
                    (new CreateSkuVariantsAction())($skuToCreate['combinations'], $sku);
                }

                foreach ($skuToCreate['images'] as $imageToCreate) {
                    $file = UploadedFile::createFromBase($imageToCreate);
                    $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

                    $sku
                        ->addMedia($file)
                        ->usingName($fileName)
                        ->usingFileName(str($fileName)->slug() . '.' . $ext)
                        ->toMediaCollection();
                }
            }

            Log::info('product created', $product->toArray());
            return new ProductResource($product);
        });
    }

    public function show(string $locale, Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, string $locale, Product $product)
    {
        return DB::transaction(static function () use ($request, $product) {
            $validated = $request->validated();

            Log::info('updating product', $validated);

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
                'sort_order' => $validated['sort_order'],
                'is_visible' => $validated['is_visible'],
            ]);

            // Discount
            if ($request->has('discount')) {
                if ($validated['discount'] !== RemoveKey::REMOVE->value) {
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

            // SKU
            (new SyncSkusWithProductAction())($product, $validated['skus']);

            $product->refresh();

            Log::info('product updated', $product->toArray());
            return new ProductResource($product);
        });
    }

    public function updateIsVisible(\Illuminate\Http\Request $request, string $locale, Product $product): ProductResource
    {
        $validated = $request->validate([
            'is_visible' => ['required', 'boolean']
        ]);

        $product->update($validated);
        $product->refresh();

        return new ProductResource($product);
    }
    public function destroy(string $locale, Product $product): Response
    {
        $product->load('skus');

        return DB::transaction(static function () use ($product) {
//            $skuIds = $product->skus->pluck('id');
//             продумать whereNull('deleted_at', null)
//            DB::table('attribute_option_sku')->whereIn('sku_id', $skuIds)->delete();
//            Discount::query()->whereIn('discountable_id', $skuIds)->delete();
//            SkuVariant::query()->whereIn('sku_id', $skuIds)->delete();
//            Sku::query()->whereIn('id', $skuIds)->delete();
//
//            DB::table('collection_product')->where('product_id', $product->id)->delete();
//            ProductTranslation::query()->where('product_id', $product->id)->delete();

            $product->delete();

            return response()->noContent();
        });
    }

}
