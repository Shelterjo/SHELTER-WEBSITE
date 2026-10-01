<?php

namespace App\Enums;

/** Where a fact value came from. LEGACY_SITE / EXTERNAL_LISTING are information only, never enough to publish. */
enum FactSource: string
{
    case OwnerDecision = 'OWNER_DECISION';
    case OwnerDashboard = 'OWNER_DASHBOARD';
    case Document = 'DOCUMENT';
    case OfficialSource = 'OFFICIAL_SOURCE';
    case LegacySite = 'LEGACY_SITE';
    case ExternalListing = 'EXTERNAL_LISTING';
}
