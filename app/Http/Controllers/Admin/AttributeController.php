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
        $attributes = Attribute::with(['translations', 'options'])->get();

        return AttributeResource::collection($attributes);
    }

    public function store(StoreAttributeRequest $request): AttributeResource
    {
        $data = [
            ...$request->validated('translations'),
            ...$request->safe()->except('translations')
        ];

        $attribute = Attribute::query()->create($data);

        $attribute->load(['options', 'translations']);

        return new AttributeResource($attribute);
    }

    public function show(string $locale, Attribute $attribute): AttributeResource
    {
        $attribute->load(['options', 'translations']);

        return new AttributeResource($attribute);
    }

    public function update(UpdateAttributeRequest $request, string $locale, Attribute $attribute): AttributeResource
    {
        $data = [
            ...$request->safe(['translations']),
            ...$request->safe()->except('translations')
        ];

        $attribute->update($data);

        $attribute->load(['options', 'translations']);

        return new AttributeResource($attribute);
    }
}
