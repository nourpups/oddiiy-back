<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttributeRequest;
use App\Http\Requests\UpdateAttributeRequest;
use App\Http\Resources\AttributeResource;
use App\Models\Attribute;

class AttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $attributes = Attribute::with(['translations', 'attributeOptions'])->get();

        return AttributeResource::collection($attributes);
    }

    public function store(StoreAttributeRequest $request): AttributeResource
    {
        $attribute = Attribute::query()->create([...$request->validated('translations')]);

        $attribute->load(['attributeOptions', 'translations']);

        return new AttributeResource($attribute);
    }

    public function show(string $locale, Attribute $attribute): AttributeResource
    {
        $attribute->load(['attributeOptions', 'translations']);

        return new AttributeResource($attribute);
    }

    public function update(UpdateAttributeRequest $request, string $locale, Attribute $attribute): AttributeResource
    {
        $attribute->update([...$request->validated('translations')]);

        $attribute->load(['attributeOptions', 'translations']);

        return new AttributeResource($attribute);
    }
}
