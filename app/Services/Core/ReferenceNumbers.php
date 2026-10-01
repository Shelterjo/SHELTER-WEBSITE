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

    public function next(string $prefix, ?CarbonImmutable $now = null, string $timezone = 'Asia/Amman'): string
    {
        if (! in_array($prefix, self::PREFIXES, true)) {
            throw new InvalidArgumentException("Unknown reference prefix [{$prefix}].");
        }
        $year = ($now ?? CarbonImmutable::now())->setTimezone($timezone)->year;

        for ($attempt = 0; ; $attempt++) {
            try {
                $number = DB::transaction(function () use ($prefix, $year): int {
                    $sequence = ReferenceSequence::query()->where(['prefix' => $prefix, 'year' => $year])->lockForUpdate()->first()
                        ?? ReferenceSequence::query()->create(['prefix' => $prefix, 'year' => $year, 'last_number' => 0]);
                    $sequence->increment('last_number');

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
