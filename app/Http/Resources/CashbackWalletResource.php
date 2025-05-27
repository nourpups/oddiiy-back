<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashbackWalletResource extends JsonResource
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
            'balance' => $this->balance,
            'total_earned' => $this->total_earned,
            'total_used' => $this->total_used,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at
                ->setTimezone('Asia/Tashkent')
                ->format('Y-m-d H:i'),
        ];
    }
}
