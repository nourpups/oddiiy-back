<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Category extends Model  implements TranslatableContract
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory, Translatable;

    public array $translatedAttributes = ['name'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function discount(): MorphOne
    {
        return $this->morphOne(Discount::class, 'discountable');
    }
}
