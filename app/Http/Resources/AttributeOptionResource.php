<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeOptionResource extends JsonResource
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
            'value' => $this->value,
            'attribute' => new AttributeResource($this->whenLoaded('attribute')),
            'skus' => SkuResource::collection($this->whenLoaded('skus')),
            'translations' => AttributeOptionTranslationResource::collection($this->whenLoaded('translations'))
        ];
    }
}
