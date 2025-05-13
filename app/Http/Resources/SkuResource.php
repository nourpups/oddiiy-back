<?php

namespace App\Http\Resources;

use App\Enum\AttributeId;
use App\Models\AttributeOption;
use App\Helper\SaleHelper;
use App\Models\SkuVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class   SkuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // пока что считаем только скидку продукта, у самого Sku нет.
        // Получаем скидку из переданного product_discount
        $discount = $this->getRelation('product_discount');

        // Вычисляем новую цену, если есть скидка
        $discountData = $discount ? [
            ...SaleHelper::formatSale($discount, $this->price),
            'starts_at' => $discount->starts_at,
            'expires_at' => $discount->expires_at,
        ] : null;
        $id = $this->id;
        $variants = $this->whenLoaded('variants');
        $attributeOptions = $variants->map(static function (SkuVariant $variant) use ($id) {
            return $variant->attributeOptions
                ->flatten();
        })->flatten()->unique('id');

        $colors = $attributeOptions->filter(static function (AttributeOption $option) {
            return $option->attribute_id === AttributeId::COLOR->value;
        });

        return [
            'id' => $this->id,
            'price' => $this->price,
            'sku' => $this->whenNotNull($this->sku),
            'discount' => $discountData,
            'colors' => AttributeOptionResource::collection($colors),
            'attributeOptions' => AttributeOptionResource::collection($attributeOptions),
            'variants' => SkuVariantResource::collection($variants),
            'images' => MediaResource::collection($this->whenLoaded('images')),
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
