<?php

namespace Tests\Unit;

use App\Enums\FactStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** FR-T1: the fact lifecycle transition matrix (FACT-REGISTRY.md §4). */
class FactStatusTest extends TestCase
{
    /** @return iterable<string, array{FactStatus, FactStatus, bool}> */
    public static function transitions(): iterable
    {
        yield 'missing → approved' => [FactStatus::Missing, FactStatus::Approved, true];
        yield 'pending approval → approved' => [FactStatus::PendingOwnerApproval, FactStatus::Approved, true];
        yield 'pending verification → rejected' => [FactStatus::PendingVerification, FactStatus::Rejected, true];
        yield 'approved → verified' => [FactStatus::Approved, FactStatus::Verified, true];
        yield 'approved → superseded' => [FactStatus::Approved, FactStatus::Superseded, true];
        yield 'verified → approved (expiry)' => [FactStatus::Verified, FactStatus::Approved, true];
        yield 'rejected → approved is final' => [FactStatus::Rejected, FactStatus::Approved, false];
        yield 'superseded → approved is final' => [FactStatus::Superseded, FactStatus::Approved, false];
        yield 'approved → rejected not allowed' => [FactStatus::Approved, FactStatus::Rejected, false];
        yield 'missing → verified skips approval' => [FactStatus::Missing, FactStatus::Verified, false];
    }

    #[DataProvider('transitions')]
    public function test_transition_matrix(FactStatus $from, FactStatus $to, bool $allowed): void
    {
        $this->assertSame($allowed, $from->canTransitionTo($to));
    }

    public function test_only_approved_and_verified_are_publishable(): void
    {
        $publishable = array_filter(FactStatus::cases(), fn (FactStatus $s): bool => $s->isPublishable());

        $this->assertSame([FactStatus::Approved, FactStatus::Verified], array_values($publishable));
    }
}
