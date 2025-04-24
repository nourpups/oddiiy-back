<?php

namespace App\Models;

use App\Enum\SaleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class Discount extends Model
{
    /** @use HasFactory<\Database\Factories\DiscountFactory> */
    use HasFactory;

    protected $fillable = [
        'value',
        'type',
        'starts_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => SaleType::class,
        ];
    }

    protected function startsAt(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn (?string $value) => !empty($value) ? Carbon::parse($value) : null
        );
    }

    protected function expiresAt(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn (?string $value) => !empty($value) ? Carbon::parse($value) : null
        );
    }

    public function discountable(): MorphTo
    {
        return $this->morphTo();
    }
}
