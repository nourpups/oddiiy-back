<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'phone' => $this->phone,
            'birth_date' => $this->whenNotNull($this->birth_date->format('Y-m-d')),
//            'remember_token' =>$this->whenNotNull($this->remember_token),
            'address' => new AddressResource($this->whenLoaded('address')),
        ];
    }
}
