<?php

namespace App\Enum;

enum FirstOrderCoupon: int
{
    case ID = 1;
    case MAX_USES = 80085; // нет лимита
}
