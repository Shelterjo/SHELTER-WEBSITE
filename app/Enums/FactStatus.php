<?php

namespace App\Enums;

/** D-224 vocabulary + VERIFIED (G14-CF-12, FACT-REGISTRY.md §3). Only APPROVED / VERIFIED are publishable. */
enum FactStatus: string
{
    case Approved = 'APPROVED';
    case Verified = 'VERIFIED';
    case PendingOwnerApproval = 'PENDING OWNER APPROVAL';
    case PendingVerification = 'PENDING VERIFICATION';
    case Missing = 'MISSING';
    case Rejected = 'REJECTED';
    case Superseded = 'SUPERSEDED';

    public function isPublishable(): bool
    {
        return $this === self::Approved || $this === self::Verified;
    }

    public function isFinal(): bool
    {
        return $this === self::Rejected || $this === self::Superseded;
    }

    /** Allowed lifecycle transitions (FACT-REGISTRY.md §4). */
    public function canTransitionTo(self $to): bool
    {
        return match ($this) {
            self::Missing, self::PendingOwnerApproval, self::PendingVerification => in_array($to, [self::Approved, self::Rejected, self::PendingOwnerApproval, self::PendingVerification], true),
            self::Approved => in_array($to, [self::Verified, self::Superseded], true),
            self::Verified => in_array($to, [self::Approved, self::Superseded], true),
            self::Rejected, self::Superseded => false,
        };
    }
}
