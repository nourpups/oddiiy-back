<?php

namespace App\Models;

use App\Enum\StreetType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Address extends Model
{
    /** @use HasFactory<\Database\Factories\AddressFactory> */
    use HasFactory;

    protected $fillable = [
        'formatted',
        'region',
        'city',
        'district',
        'locality',
        'street',
        'street_type',
        'street_type_number',
        'house',
        'entrance',
        'floor',
        'apartment',
        'orientation',
        'postal',
    ];

    protected function casts(): array
    {
        return [
            'street_type' => StreetType::class,
        ];
    }

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }
}
