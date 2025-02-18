<?php

namespace App\Models;

use App\Enum\SaleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    /** @use HasFactory<\Database\Factories\CouponFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'value',
        'type',
        'max_uses',
    ];

    protected function casts(): array
    {
        return [
            'type' => SaleType::class
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
