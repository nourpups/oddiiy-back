<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Font extends Model
{
    protected $fillable = [
        'name',
        'class',
        'is_selected'
    ];

    protected $casts = [
      'is_selected' => 'boolean'
    ];
}
