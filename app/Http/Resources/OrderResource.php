<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'recipient_name' => $this->recipient_name,
            'delivery' => $this->delivery->value,
            'payment' => $this->payment->value,
            'comment' => $this->whenNotNull('comment'),
            'sum' => $this->sum,
            'status' => $this->status->value,
            'address' => new AddressResource($this->whenLoaded('address')),
            'user' => new UserResource($this->whenLoaded('user')),
            'coupon' => new CouponResource($this->whenLoaded('coupon')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at
                ->setTimezone('Asia/Tashkent')
                ->format('Y-m-d H:i'),
        ];
    }
}
