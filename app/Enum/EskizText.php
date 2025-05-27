<?php

namespace App\Enum;

enum EskizText: int
{
    case REGISTER = 1;
    case CONFIRM_ORDER = 2;
    case RESET_PASSWORD = 3;

    public function getText(): string
    {
        return match ($this) {
            self::REGISTER => "ooddiiy.uz saytida buyurtma rasmiylashtirish uchun tasdiqlash kodi: %d",
            self::CONFIRM_ORDER => "ooddiiy.uz saytiga ro‘yxatdan o‘tish uchun tasdiqlash kodi: %d",
            self::RESET_PASSWORD => "ooddiiy.uz saytida parolni tiklash uchun tasdiqlash kodi: %d",
        };
    }
}
