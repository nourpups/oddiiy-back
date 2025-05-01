<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\MediaResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Facades\Log;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => new MediaResource($this->whenLoaded('image')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'products_count' => $this->whenCounted('products'),
            'translations' => CategoryTranslationResource::collection($this->whenLoaded('translations')),
        ];
    }
}
