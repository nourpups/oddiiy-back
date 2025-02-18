<?php

namespace App\Models;

use App\Enum\SaleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
            'starts_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function discountable(): MorphTo
    {
        return $this->morphTo();
    }
}
