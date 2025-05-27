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
            'is_admin' => $this->is_admin,
            'name' => $this->name,
            'phone' => $this->phone,
            'birth_date' => $this->whenNotNull($this->birth_date->format('Y-m-d')),
//            'remember_token' =>$this->whenNotNull($this->remember_token),
            'cashback_wallet' => new CashbackWalletResource($this->whenLoaded('cashbackWallet')),
            'address' => new AddressResource($this->whenLoaded('address')),
        ];
    }
}
