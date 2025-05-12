<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\Admin\CategoryResource;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Sku;
use App\Models\SkuVariant;
use App\Models\Stock;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * Категории с рандомной фоткой рандомных продуктов этих категорий
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::with(['image'])
            ->withCount('products')
            ->latest()
            ->get();

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): CategoryResource
    {
        $validated = $request->validated();
        $translations = $validated['translations'];

        foreach ($translations as $locale => $translationArray) {
            $name = $translationArray['name'];
            $translations[$locale]['slug'] = str($name)->slug(language: $locale);
        }

        $category = Category::query()->create([...$translations]);

        $file = UploadedFile::createFromBase($validated['image']);
        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

        $category->addMedia($file)
            ->usingName($fileName)
            ->usingFileName(str($fileName)->slug() . '.' . $ext)
            ->toMediaCollection('mainImage');

        return new CategoryResource($category);
    }

    public function show(string $locale, Category $category): CategoryResource
    {
        $category->loadCount('products');

        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, string $locale, Category $category): CategoryResource
    {
        $validated = $request->validated();
        $translations = $validated['translations'];

        foreach ($translations as $locale => $translationArray) {
            $name = $translationArray['name'];
            $translations[$locale]['slug'] = str($name)->slug(language: $locale);
        }

        $category->update([...$translations]);


        if ($request->has('image')) {
            $file = UploadedFile::createFromBase($validated['image']);
            $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

            $category->addMedia($file)
                ->usingName($fileName)
                ->usingFileName(str($fileName)->slug() . '.' . $ext)
                ->toMediaCollection('mainImage');
        }

        return new CategoryResource($category);
    }

    public function destroy(string $locale, Category $category): Response
    {
        return DB::transaction(static function () use ($category) {
            $productIds = $category->products()->pluck('id');
            $skuIds = Sku::query()->whereIn('product_id', $productIds)->pluck('id');

            Discount::query()->whereIn('discountable_id', [...$skuIds, ...$productIds])->delete();

            DB::table('attribute_option_sku')->whereIn('sku_id', $skuIds)->delete();
            SkuVariant::query()->whereIn('sku_id', $skuIds)->delete();
            Sku::query()->whereIn('product_id', $productIds)->delete();

            DB::table('collection_product')->whereIn('product_id', $productIds)->delete();
            ProductTranslation::query()->whereIn('product_id', $productIds);
            Product::query()->whereIn('id', $productIds)->delete();

            CategoryTranslation::query()->where('category_id', $category->id)->delete();
            $category->image()->delete();

            $category->delete();

            return response()->noContent();
        });
    }
}
