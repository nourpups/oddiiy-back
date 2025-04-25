<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Category extends Model implements TranslatableContract
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory, Translatable;

    public array $translatedAttributes = [
        'name',
        'slug',
    ];

    protected $with = [
      'translations'
    ];

    public function resolveRouteBinding($value, $field = null): self
    {
        return $this->where('id', $value)
            ->orWhereHas(
                'translations',
                static fn(Builder $q) => $q->where('slug', $value)
            )->first();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function randomProductWithAllImages(): HasOne
    {
        return $this->hasOne(Product::class)
            ->with('allImages')
            ->inRandomOrder();
    }
}
