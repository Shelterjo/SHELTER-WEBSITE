<?php

namespace App\Enums;

/** Priority: Emergency > Temporary > Special/Holiday > Regular (M33, MDH, D-021). */
enum HoursExceptionKind: string
{
    case Emergency = 'emergency';
    case Temporary = 'temporary';
    case Special = 'special';
    case Holiday = 'holiday';

    public function rank(): int
    {
        return match ($this) {
            self::Emergency => 3,
            self::Temporary => 2,
            self::Special, self::Holiday => 1,
        };
    }
}
