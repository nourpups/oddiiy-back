<?php

namespace App\Rules;

use App\Enum\RemoveKey;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Validator;

class RemovedOr implements ValidationRule
{
    public function __construct(public string|array $rules = '')
    {
    }

    /**
     * Run the validation rule.
     *
     * @param \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === RemoveKey::REMOVE->value) {
            // Если значение указывает на удаление, просто принимаем
            return;
        }

        if (!empty($this->rules)) {
            $validator = Validator::make(
                ['value' => $value],
                ['value' => $this->rules]
            );

            if ($validator->fails()) {
                $fail($validator->errors()->first('value'));
            }
        }
    }
}
