<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property \Illuminate\Support\Collection<Media> $allImages
 */
class Product extends Model implements TranslatableContract
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, Translatable;

    protected $fillable = [
        'category_id',
    ];

    protected $with = [
        'category',
        'tag',
        'translations',
        'skus',
        'discount'
    ];

    public array $translatedAttributes = [
        'name',
        'slug',
        'description',
    ];

    public function resolveRouteBinding($value, $field = null): self
    {
        return $this->where('id', $value)
            ->orWhereHas(
                'translations',
                static fn(Builder $q) => $q->where('slug', $value)
            )->first();
    }

    public function address(): HasOne
    {
        return $this->hasOne(Address::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class);
    }

    public function discount(): MorphOne
    {
        return $this->morphOne(Discount::class, 'discountable');
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    public function skus(): HasMany
    {
        return $this->hasMany(Sku::class);
    }

    public function allImages(): HasManyThrough
    {
        return $this->hasManyThrough(
            Media::class,
            Sku::class,
            'product_id',
            'model_id',
            'id',
            'id'
        );
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class)
            ->as('details')
            ->withPivot(['quantity', 'amount'])
            ->withTimestamps();
    }
}
