<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\Admin\CategoryResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sku;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
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
        $category->deleteTranslations();
        $category->image()->delete();
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
