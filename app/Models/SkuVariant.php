<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SkuVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sku_id',
        'photomodel_id',
        'stock',
    ];

    protected $with = ['attributeOptions'];

    public function sku(): BelongsTo
    {
        return $this->belongsTo(Sku::class);
    }

    public function photomodel(): BelongsTo
    {
        return $this->belongsTo(Photomodel::class);
    }

    public function attributeOptions(): BelongsToMany
    {
        return $this->belongsToMany(AttributeOption::class)
            ->withTimestamps();
    }

    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }
}
