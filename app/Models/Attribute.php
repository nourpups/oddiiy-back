<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property Collection<AttributeOption> $attributeOptions
 */
class Attribute extends Model implements TranslatableContract
{
    /** @use HasFactory<\Database\Factories\AttributeFactory> */
    use HasFactory, Translatable;
    protected $fillable = ['is_options_multiselect'];

    public array $translatedAttributes = ['name'];

    protected $casts = [
        'is_options_multiselect' => 'boolean'
    ];

    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class);
    }
}
