<?php

namespace App\Enums;

/**
 * Normalized view of a menu name status. The full inventory wording (e.g. "APPROVED — CORRECTED") is kept
 * in the *_status columns for traceability; only approved names reach display columns (D-091, D-133).
 */
enum NameStatus: string
{
    case Approved = 'approved';
    case Pending = 'pending';
    case Missing = 'missing';

    public static function fromInventory(string $status): self
    {
        $status = trim($status);

        return match (true) {
            str_starts_with($status, 'APPROVED') => self::Approved,
            $status === '' || $status === '—' || str_starts_with($status, 'MISSING') => self::Missing,
            default => self::Pending,
        };
    }
}
