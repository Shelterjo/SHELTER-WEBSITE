<?php

namespace App\Enums;

/** Owner-facing priority (M35 §8, NOTIFICATIONS.md). */
enum Priority: string
{
    case Critical = 'CRITICAL';
    case ActionRequired = 'ACTION_REQUIRED';
    case Important = 'IMPORTANT';
    case Information = 'INFORMATION';
}
