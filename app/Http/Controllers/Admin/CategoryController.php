<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\Admin\CategoryResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sku;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    /**
     * Категории с рандомной фоткой рандомных продуктов этих категорий
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::with(['randomProductWithAllImages'])
            ->withCount('products')
            ->latest()
            ->get()
            ->map(function (Category $category) {
                $category['image'] = $category->randomProductWithAllImages?->allImages->random();

                return $category;
            });

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

        return new CategoryResource($category);
    }

    public function show(string $locale, Category $category): CategoryResource
    {
        $category->load('randomProductWithAllImages')->loadCount('products');

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

        return new CategoryResource($category);
    }

    public function destroy(string $locale, Category $category): Response
    {
        $category->deleteTranslations();
        $category->load('products.skus');
        $category->products->map(function (Product $product) {
            $product->skus->map(function (Sku $sku) {
                $sku->attributeOptions()->detach();
                $sku->discount()->delete();
                $sku->delete();
            });

            $product->discount()->delete();
            $product->deleteTranslations();
            $product->collections()->detach();
            $product->delete();
        });
        $category->delete();

        return response()->noContent();
    }
}
