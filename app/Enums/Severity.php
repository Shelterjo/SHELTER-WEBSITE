<?php

namespace App\Enums;

/** Five-level severity (M35 §5, INCIDENTS.md). */
enum Severity: string
{
    case Critical = 'CRITICAL';
    case High = 'HIGH';
    case Medium = 'MEDIUM';
    case Low = 'LOW';
    case Info = 'INFO';
}
