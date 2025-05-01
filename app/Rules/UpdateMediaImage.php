<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\File as FileRule;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UpdateMediaImage implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value)) {
            // UUID: проверка на существование в media
            $exists = Media::query()->where('uuid', $value)->exists();
            if (!$exists) {
                $fail(__('validation.exists', ['attribute' => $value]));
            }
        } elseif ($value instanceof UploadedFile) {
            // Новый файл — валидируем как изображение
            $validator = Validator::make(
                ['file' => $value],
                ['file' => FileRule::image()->max(4096)]
            );

            if ($validator->fails()) {
                $fail(__('validation.image'));
            }
        } else {
            $fail(__('validation.unknown'));
        }
    }
}
