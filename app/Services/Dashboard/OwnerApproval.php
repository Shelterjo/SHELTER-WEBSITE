<?php

namespace App\Services\Dashboard;

use App\Enums\FactSource;
use App\Enums\FactStatus;
use App\Models\User;
use App\Services\MasterData\FactRegistry;

/**
 * The Owner's edit in the dashboard is the approval of the value (FACT-REGISTRY §3, MDH-025): one place that turns a
 * saved value into the APPROVED fact the site checks — registered and approved when the key is new, superseding an
 * approved value that changed (the old row stays, SUPERSEDED), approving a pending one. Never called for a value the
 * Owner did not save himself.
 */
final class OwnerApproval
{
    /** What `decision_ref` says for an approval given in the Owner Dashboard (the audit entry holds who and when). */
    public const DECISION_REF = 'OWNER-DASHBOARD';

    public function __construct(private readonly FactRegistry $facts) {}

    public function approve(string $key, mixed $value, User $owner, string $category, ?string $labelAr = null, ?string $labelEn = null): void
    {
        $fact = $this->facts->current($key);
        if ($fact === null) {
            $fact = $this->facts->register($key, $category, $value, FactStatus::PendingOwnerApproval, FactSource::OwnerDashboard, labelAr: $labelAr, labelEn: $labelEn);
            $this->facts->approve($fact, $owner, self::DECISION_REF);
        } elseif ($fact->status->isPublishable()) {
            if ($fact->value_hash !== FactRegistry::hash($value)) {
                $this->facts->supersede($fact, $value, $owner, self::DECISION_REF);
            }
        } else {
            $this->facts->approve($fact, $owner, self::DECISION_REF, replaceValue: true, value: $value);
        }
    }

    /** True when the current value is the approved one. */
    public function isApproved(string $key, mixed $value): bool
    {
        $fact = $this->facts->current($key);

        return $fact !== null && $fact->status->isPublishable() && $fact->value_hash === FactRegistry::hash($value);
    }
}
