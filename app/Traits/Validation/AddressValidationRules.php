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
            'region' => ['sometimes', 'string'],
            'city'=> [$rwak ? "required_with:$rwak" : 'required', 'string'],
            'district' => ['sometimes', 'string'],
            'street' => [$rwak ? "required_with:$rwak" : 'required', 'string'],
            'house' => [$rwak ? "required_with:$rwak" : 'required', 'string'],
            'entrance' => ['sometimes', 'numeric'],
            'floor' => ['sometimes', 'numeric'],
            'apartment' => ['sometimes', 'string'],
            'orientation' => ['sometimes', 'string'],
            'postal' => ['sometimes', 'string'],
        ];
    }
}
