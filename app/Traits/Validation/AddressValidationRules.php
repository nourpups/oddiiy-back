<?php

namespace App\Traits\Validation;

trait AddressValidationRules
{
    /**
     * @param ?string $requiredWithArrayKey - при использовании правил в других сущностях,
     * правила приходят в массиве (пр. address.formatted), и тогда
     * нам нужно передавать этот самый ключ
     *
     * @return array[]
     */
    private function addressRules(?string $requiredWithArrayKey = null): array
    {
        $rwak = $requiredWithArrayKey;
        return [
            'formatted' => [$rwak ? "required_with:$rwak" : 'required', 'string'],
            'region' => ['nullable', 'string'],
            'city'=> [$rwak ? "required_with:$rwak" : 'nullable', 'string'],
            'district' => ['nullable', 'string'],
//            'street' => [$rwak ? "required_with:$rwak" : 'nullable', 'string'],
//            'house' => [$rwak ? "required_with:$rwak" : 'nullable', 'string'],
            'street' => ['nullable', 'string'],
            'house' => ['nullable', 'string'],
            'entrance' => ['nullable', 'numeric'],
            'floor' => ['nullable', 'numeric'],
            'apartment' => ['nullable', 'string'],
            'orientation' => ['nullable', 'string'],
            'postal' => ['nullable', 'string'],
            'latitude' => ['required', 'string'],
            'longitude' => ['required', 'string'],
        ];
    }
}
