<?php

namespace App\Enum;

enum OrderStatus: int
{
    case PENDING = 1;
    case ACCEPTED = 2;
    case COMPLETED = 3;
    case CANCELLED = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => "Kutilmoqda ⏳",
            self::ACCEPTED => "Rasmiylashtirildi ✅",
            self::COMPLETED => "Bajarildi 🎉",
            self::CANCELLED => "Bekor qilindi ❌",
        };
    }
}
