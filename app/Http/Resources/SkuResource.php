<?php

namespace App\Http\Resources;

use App\Enum\AttributeOption;
use App\Helper\SaleHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
        $discountData = null;
        if ($discount) {
            $discountData = [
                ...SaleHelper::formatSale($discount, $this->price),
                'starts_at' => $discount->starts_at,
                'expires_at' => $discount->expires_at,
            ];
        }

        $attributeOptions = $this->whenLoaded('attributeOptions');

        $colors = null;
        if ($attributeOptions) {
            /** @var Collection $attributeOptions */
            $colors = $attributeOptions
                ->filter(fn ($option) => $option->attribute->id === AttributeOption::COLOR->value);
        }
        return [
            'id' => $this->id,
            'price' => $this->price,
            'sku' => $this->whenNotNull($this->sku),
            'in_stock' => $this->in_stock,
            'discount' => $discountData,
            'attributeOptions' => AttributeOptionResource::collection($attributeOptions),
            'colors' => AttributeOptionResource::collection($this->whenLoaded('attributeOptions', $colors)),
            'images' => MediaResource::collection($this->whenLoaded('images')),
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
