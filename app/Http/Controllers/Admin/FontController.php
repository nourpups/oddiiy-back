<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFontRequest;
use App\Http\Resources\Admin\FontResource;
use App\Models\Font;
use Illuminate\Http\Request;

class FontController extends Controller
{
    public function index(): array
    {
        $fonts = Font::all();
        $font = Font::query()->where('is_selected', true)->first();

        return [
            'fonts' => FontResource::collection($fonts),
            'font' => new FontResource($font),
        ];
    }

    public function update(string $locale, Font $font): FontResource
    {
        Font::query()->where('is_selected', true)->update([
           'is_selected' => false
        ]);
        $font->update(['is_selected' => true]);

        return new FontResource($font);
    }
}
