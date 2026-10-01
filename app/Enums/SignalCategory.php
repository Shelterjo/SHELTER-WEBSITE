<?php

namespace App\Enums;

/** The 9 health categories + OPERATIONS (PLATFORM-ARCHITECTURE §3.1, MONITORING.md). */
enum SignalCategory: string
{
    case Content = 'CONTENT';
    case Seo = 'SEO';
    case Performance = 'PERFORMANCE';
    case Accessibility = 'ACCESSIBILITY';
    case Security = 'SECURITY';
    case Privacy = 'PRIVACY';
    case DataConsistency = 'DATA_CONSISTENCY';
    case Integrations = 'INTEGRATIONS';
    case Backups = 'BACKUPS';
    case Operations = 'OPERATIONS';
    case Business = 'BUSINESS';
}
