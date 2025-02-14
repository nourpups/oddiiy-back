<?php

namespace App\Enum;

enum OrderStatus: int
{
    case ACCEPTED = 1;
    case CANCELLED = 2;
    case PENDING = 3;
    case DELIVERED = 4;
    case CLOSED = 5;
}
