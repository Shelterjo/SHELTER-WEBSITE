<?php

namespace App\Services\Site;

use Carbon\CarbonImmutable;

/**
 * One stretch of time with the same open-state wording. `until` = when the next segment starts (null = no known
 * change). `closesAt` is set only for "closing soon", whose text counts the minutes down.
 */
final readonly class StatusSegment
{
    public function __construct(
        public string $state,
        public string $text,
        public ?CarbonImmutable $until,
        public ?CarbonImmutable $closesAt = null,
    ) {}
}
