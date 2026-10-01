<?php

namespace App\Enums;

/** One signals source, two views: EVENT → Notifications, ISSUE → Needs attention / incidents (NOTIFICATIONS.md). */
enum SignalKind: string
{
    case Event = 'EVENT';
    case Issue = 'ISSUE';
}
