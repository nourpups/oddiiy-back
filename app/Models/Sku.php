<?php

namespace App\Models;

use App\Enum\AvailabilityStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Sku extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\SkuFactory> */
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'in_stock',
    ];

    protected $with = [
        'discount',
        'attributeOptions',
        'images',
    ];

    protected function casts(): array
    {
        return [
            'in_stock' => AvailabilityStatus::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeOptions(): BelongsToMany
    {
        return $this->belongsToMany(AttributeOption::class);
    }

    public function discount(): MorphOne
    {
        return $this->morphOne(Discount::class, 'discountable');
    }

    public function images(): MorphMany
    {
        // https://github.com/spatie/laravel-medialibrary/issues/1047#issuecomment-853718949
        return $this->morphMany(Media::class, 'model')
            ->where('collection_name', 'default');
    }
}
