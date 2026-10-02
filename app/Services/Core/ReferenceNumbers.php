<?php

namespace App\Services\Core;

use App\Models\ReferenceSequence;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Server-generated reference numbers PREFIX-YYYY-NNNNN (JOB careers, FR partnerships, INQ inquiries).
 * The year follows the market timezone; numbers are never reused. Row lock serialises concurrent submissions.
 */
final class ReferenceNumbers
{
    public const PREFIXES = ['JOB', 'FR', 'INQ'];

    /**
     * Where a sequence starts counting from (the first number issued is this + 1). D-316 (Owner, 2026-10-02): the
     * partnership numbers start at FR-2026-00100, so the first application is FR-2026-00101. Each year's sequence
     * starts the same way — PENDING OWNER APPROVAL (PO-075: restart at 00101 every year, or continue the count).
     */
    public const START_AFTER = ['FR' => 100];

    public function next(string $prefix, ?CarbonImmutable $now = null, string $timezone = 'Asia/Amman'): string
    {
        if (! in_array($prefix, self::PREFIXES, true)) {
            throw new InvalidArgumentException("Unknown reference prefix [{$prefix}].");
        }
        $year = ($now ?? CarbonImmutable::now())->setTimezone($timezone)->year;

        for ($attempt = 0; ; $attempt++) {
            try {
                $number = DB::transaction(function () use ($prefix, $year): int {
                    $start = self::START_AFTER[$prefix] ?? 0;
                    $sequence = ReferenceSequence::query()->where(['prefix' => $prefix, 'year' => $year])->lockForUpdate()->first()
                        ?? ReferenceSequence::query()->create(['prefix' => $prefix, 'year' => $year, 'last_number' => $start]);
                    // A row created before the starting point was set moves up to it — numbers only ever go forward.
                    $sequence->last_number = max($sequence->last_number, $start) + 1;
                    $sequence->save();

                    return $sequence->last_number;
                });

                return sprintf('%s-%d-%05d', $prefix, $year, $number);
            } catch (UniqueConstraintViolationException $e) {
                // Two first submissions of a new year raced to create the row: retry once with the row present.
                if ($attempt >= 1) {
                    throw $e;
                }
            }
        }
    }
}
