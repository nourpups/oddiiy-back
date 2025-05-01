<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Category extends Model implements TranslatableContract, HasMedia
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory, Translatable, InteractsWithMedia;

    public array $translatedAttributes = [
        'name',
        'slug',
    ];

    protected $with = [
        'translations',
        'image'
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

    public function image(): MorphOne
    {
        // https://github.com/spatie/laravel-medialibrary/issues/1047#issuecomment-853718949
        return $this->morphOne(Media::class, 'model')
            ->where('collection_name', 'mainImage');
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('mainImage')
            ->singleFile();
    }
}
