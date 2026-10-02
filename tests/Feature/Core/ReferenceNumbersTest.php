<?php

namespace Tests\Feature\Core;

use App\Services\Core\ReferenceNumbers;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\TestCase;

class ReferenceNumbersTest extends TestCase
{
    use RefreshDatabase;

    public function test_numbers_are_sequential_per_prefix_and_year(): void
    {
        $refs = app(ReferenceNumbers::class);
        $now = CarbonImmutable::parse('2026-06-01 10:00', 'UTC');

        $this->assertSame('JOB-2026-00101', $refs->next('JOB', $now), 'D-324');
        $this->assertSame('JOB-2026-00102', $refs->next('JOB', $now));
        $this->assertSame('INQ-2026-00001', $refs->next('INQ', $now), 'inquiries unchanged');
    }

    public function test_partnership_numbers_start_after_00100(): void
    {
        // D-316: FR-2026-00100 is the starting point; the first application is 00101, the second 00102.
        $refs = app(ReferenceNumbers::class);
        $now = CarbonImmutable::parse('2026-10-02 10:00', 'UTC');

        $this->assertSame('FR-2026-00101', $refs->next('FR', $now));
        $this->assertSame('FR-2026-00102', $refs->next('FR', $now));
        $this->assertSame('JOB-2026-00101', $refs->next('JOB', $now), 'careers follow the same start (D-324)');
    }

    public function test_a_sequence_row_below_the_starting_point_moves_up_to_it(): void
    {
        DB::table('reference_sequences')->insert(['prefix' => 'FR', 'year' => 2026, 'last_number' => 3, 'created_at' => now(), 'updated_at' => now()]);

        $this->assertSame('FR-2026-00101', app(ReferenceNumbers::class)->next('FR', CarbonImmutable::parse('2026-10-02 10:00', 'UTC')));
    }

    public function test_year_follows_the_market_timezone(): void
    {
        // 31 Dec 23:30 UTC is already 1 Jan in Amman (UTC+3).
        $ref = app(ReferenceNumbers::class)->next('FR', CarbonImmutable::parse('2026-12-31 23:30', 'UTC'));

        $this->assertSame('FR-2027-00101', $ref, 'D-325: every new year starts again at 00101');
    }

    public function test_every_new_year_starts_again_at_00101(): void
    {
        $refs = app(ReferenceNumbers::class);
        foreach (['FR', 'JOB'] as $prefix) {
            $refs->next($prefix, CarbonImmutable::parse('2026-11-01 10:00', 'UTC'));
            $this->assertSame($prefix.'-2026-00102', $refs->next($prefix, CarbonImmutable::parse('2026-12-31 20:00', 'UTC')));
            $this->assertSame($prefix.'-2027-00101', $refs->next($prefix, CarbonImmutable::parse('2027-01-01 10:00', 'UTC')));
        }
    }

    public function test_unknown_prefix_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        app(ReferenceNumbers::class)->next('XYZ');
    }
}
