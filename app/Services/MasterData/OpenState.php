<?php

namespace App\Services\MasterData;

use App\Enums\HoursExceptionKind;
use Carbon\CarbonImmutable;

/**
 * Result of a time-state computation, always made on the server at request time (CACHE-CDN.md).
 * nextChange lets the HTML cache expire exactly at the next open/close boundary.
 */
final readonly class OpenState
{
    public function __construct(
        public bool $isOpen,
        public ?CarbonImmutable $closesAt,
        public ?CarbonImmutable $nextOpensAt,
        public ?HoursExceptionKind $exception,
    ) {}

    public function nextChange(): ?CarbonImmutable
    {
        return $this->isOpen ? $this->closesAt : $this->nextOpensAt;
    }
}
