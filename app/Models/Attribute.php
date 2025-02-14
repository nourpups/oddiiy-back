<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model implements TranslatableContract
{
    /** @use HasFactory<\Database\Factories\AttributeFactory> */
    use HasFactory, Translatable;

    public array $translatedAttributes = ['name'];

    public function attributeOptions(): HasMany
    {
        return $this->hasMany(AttributeOption::class);
    }
}
