<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use function PHPUnit\Framework\stringContains;

class AttributeResource extends JsonResource
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
            'is_options_multiselect' => $this->is_options_multiselect,
            'options' => AttributeOptionResource::collection($this->whenLoaded('attributeOptions')),
            'translations' => AttributeTranslationResource::collection($this->whenLoaded('translations')),
        ];
    }
}
