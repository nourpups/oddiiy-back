<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountResource extends JsonResource
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
            'type' => $this->type,
            'starts_at' => $this->starts_at->format('Y-m-d'),
            'expires_at' => $this->expires_at->format('Y-m-d'),
        ];
    }
}
