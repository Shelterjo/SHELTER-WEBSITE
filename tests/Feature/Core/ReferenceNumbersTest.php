<?php

namespace Tests\Feature\Core;

use App\Services\Core\ReferenceNumbers;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ReferenceNumbersTest extends TestCase
{
    use RefreshDatabase;

    public function test_numbers_are_sequential_per_prefix_and_year(): void
    {
        $refs = app(ReferenceNumbers::class);
        $now = CarbonImmutable::parse('2026-06-01 10:00', 'UTC');

        $this->assertSame('JOB-2026-00001', $refs->next('JOB', $now));
        $this->assertSame('JOB-2026-00002', $refs->next('JOB', $now));
        $this->assertSame('INQ-2026-00001', $refs->next('INQ', $now));
    }

    public function test_year_follows_the_market_timezone(): void
    {
        // 31 Dec 23:30 UTC is already 1 Jan in Amman (UTC+3).
        $ref = app(ReferenceNumbers::class)->next('FR', CarbonImmutable::parse('2026-12-31 23:30', 'UTC'));

        $this->assertSame('FR-2027-00001', $ref);
    }

    public function test_unknown_prefix_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        app(ReferenceNumbers::class)->next('XYZ');
    }
}
