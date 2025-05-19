<?php

namespace App\Enum;

enum OrderStatus: int
{
    case PENDING = 1;
    case ACCEPTED = 2;
    case DELIVERY = 3;
    case COMPLETED = 4;
    case CANCELLED = 5;
}
