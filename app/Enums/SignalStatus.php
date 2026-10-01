<?php

namespace App\Enums;

enum SignalStatus: string
{
    case Open = 'OPEN';
    case Resolved = 'RESOLVED';
    case Dismissed = 'DISMISSED';
}
