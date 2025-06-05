<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'description' => $this->whenNotNull($this->description),
            'slug' => $this->slug,
            'is_visible' => $this->is_visible,
            'sort_order' => $this->sort_order,
            'skus' => SkuResource::collection(
                $this->whenLoaded(
                    'skus',
                    fn() => $this->skus->map(fn($sku) => $sku->setRelation('product_discount', $this->discount))
                )
            ),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'discount' => new DiscountResource($this->whenLoaded('discount')),
            'tag' => new TagResource($this->whenLoaded('tag')),
            'orders' => OrderResource::collection($this->whenLoaded('orders')),
            'images' => MediaResource::collection($this->whenLoaded('allImages'))
        ];
    }
}
