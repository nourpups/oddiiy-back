<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Photomodel extends Model
{
    protected $fillable = [
        'height',
        'weight'
    ];

    public function skuVariant(): HasOne
    {
        return $this->hasOne(SkuVariant::class);
    }
}
