<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'sku_id',
        'variant_id',
        'quantity',
        'price'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function sku(): HasOne
    {
        return $this->hasOne(Sku::class);
    }

    public function skuVariant(): HasOne
    {
        return $this->hasOne(SkuVariant::class);
    }
}
