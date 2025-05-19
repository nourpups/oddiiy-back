<?php

namespace App\Http\Resources;

use App\Models\SkuVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'quantity' => $this->quantity,
            'price' => $this->price,
            'sku' => new SkuResource($this->whenLoaded('sku')),
            'variant' => $this->whenNotNull(
                new SkuVariantResource($this->whenLoaded('skuVariant'))
            ),
            'order' => new OrderResource($this->whenLoaded('order')),
        ];
    }
}
