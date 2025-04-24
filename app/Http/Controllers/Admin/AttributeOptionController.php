<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttributeOptionRequest;
use App\Http\Requests\UpdateAttributeOptionRequest;
use App\Http\Resources\AttributeOptionResource;
use App\Models\Attribute;
use App\Models\AttributeOption;

class AttributeOptionController extends Controller
{
    public function store(StoreAttributeOptionRequest $request, string $locale, Attribute $attribute): AttributeOptionResource
    {
        $data = [...$request->validated('translations')];

        $option = $attribute
            ->attributeOptions()
            ->create($data);

        return new AttributeOptionResource($option);
    }

    public function show(string $locale, AttributeOption $attributeOption)
    {
        $attributeOption->load(['translations']);

        return new AttributeOptionResource($attributeOption);
    }

    public function update(UpdateAttributeOptionRequest $request, string $locale, AttributeOption $attributeOption)
    {
        $data = [...$request->validated('translations')];
        $attributeOption->update($data);

        $attributeOption->load(['translations']);

        return new AttributeOptionResource($attributeOption);
    }

    public function destroy(string $locale, AttributeOption $attributeOption)
    {
        $attributeOption->deleteTranslations();
        $attributeOption->skus()->detach();
        $attributeOption->delete();
    }
}
