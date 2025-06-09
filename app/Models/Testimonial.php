<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    /** @use HasFactory<\Database\Factories\TestimonialFactory> */
    use HasFactory, Translatable;

    protected $fillable = [
      'author_name'
    ];

    protected $with = [
      'translations'
    ];

    public array $translatedAttributes = [
        'title',
        'text',
    ];
}
