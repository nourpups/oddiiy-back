<?php

namespace App\Enum;

enum DeliveryType: int
{
    case BTS = 1;
    case YANDEX_DELIVERY = 2;
    case PICKUP = 3;

    public function getLabel(): string
    {
        return match ($this->value) {
            1 => 'BTS',
            2 => 'Yandex Delivery',
            3 => 'Do\'kondan olib ketish',
        };
    }
}
