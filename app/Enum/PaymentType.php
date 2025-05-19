<?php

namespace App\Enum;

enum PaymentType: int
{
    case PAYME = 1;
    case CLICK = 2;

    public function getLabel(): string
    {
        return match ($this->value) {
          1 => 'PAYME',
          2 => 'CLICK',
        };
    }
}
