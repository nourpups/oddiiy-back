<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'formatted' => $this->formatted,
            'region' => $this->whenNotNull($this->region),
            'city' => $this->city,
            'district' => $this->whenNotNull($this->district),
            'street' => $this->whenNotNull($this->street),
            'street_type' => $this->whenNotNull($this->street_type?->value),
            'street_type_number' => $this->whenNotNull($this->street_type_number),
            'house' => $this->house,
            'entrance' => $this->whenNotNull($this->entrance),
            'floor' => $this->whenNotNull($this->floor),
            'apartment' => $this->whenNotNull($this->apartment),
            'orientation' => $this->whenNotNull($this->orientation),
            'postal' => $this->whenNotNull($this->postal),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
