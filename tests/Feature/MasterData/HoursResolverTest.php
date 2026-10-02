<?php

namespace Tests\Feature\MasterData;

use App\Enums\HoursExceptionKind;
use App\Enums\PublishStatus;
use App\Models\Branch;
use App\Services\MasterData\HoursResolver;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MasterDataFixtures;
use Tests\TestCase;

/**
 * Hours priority and overnight behaviour. The schedule below mirrors the shape of D-020 (Sat–Thu 07:00–02:00,
 * Fri 08:00–02:00) to exercise past-midnight intervals; it is fixture data, not a source of business values.
 */
class HoursResolverTest extends TestCase
{
    use MasterDataFixtures;
    use RefreshDatabase;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $hours = [];
        foreach ([6, 0, 1, 2, 3, 4] as $day) { // Sat … Thu
            $hours[] = [$day, '07:00', '02:00'];
        }
        $hours[] = [5, '08:00', '02:00']; // Fri
        $this->branch = $this->branch('BR-HOURS', $hours);
    }

    private function resolver(): HoursResolver
    {
        return new HoursResolver($this->branch->fresh() ?? $this->branch, 'Asia/Amman');
    }

    private function amman(string $time): CarbonImmutable
    {
        return CarbonImmutable::parse($time, 'Asia/Amman');
    }

    public function test_open_after_midnight_from_the_previous_day_interval(): void
    {
        // Friday 2026-10-02 01:00 belongs to Thursday's 07:00–02:00 interval.
        $state = $this->resolver()->stateAt($this->amman('2026-10-02 01:00'));

        $this->assertTrue($state->isOpen);
        $this->assertEquals($this->amman('2026-10-02 02:00'), $state->closesAt);
    }

    public function test_the_exact_minute_boundaries_and_the_year_change(): void
    {
        // FINAL-QA QA-045: the closing minute itself is closed, the opening minute itself is open, one second before
        // closing is still open; Thursday's late shift hands over to Friday's later opening; the year changes inside a shift.
        $this->assertTrue($this->resolver()->stateAt($this->amman('2026-10-02 01:59:59'))->isOpen);
        $this->assertFalse($this->resolver()->stateAt($this->amman('2026-10-02 02:00:00'))->isOpen, 'closes AT 02:00');
        $this->assertFalse($this->resolver()->stateAt($this->amman('2026-10-02 07:59:59'))->isOpen, 'Friday opens at 08:00, not 07:00');
        $this->assertTrue($this->resolver()->stateAt($this->amman('2026-10-02 08:00:00'))->isOpen, 'opens AT 08:00');
        $this->assertTrue($this->resolver()->stateAt($this->amman('2026-10-03 07:00:00'))->isOpen, 'Saturday back to 07:00');

        // 2026-12-31 is a Thursday: its 07:00–02:00 shift runs into 2027-01-01 (a Friday).
        $state = $this->resolver()->stateAt($this->amman('2027-01-01 00:30'));
        $this->assertTrue($state->isOpen);
        $this->assertEquals($this->amman('2027-01-01 02:00'), $state->closesAt);
        $this->assertEquals($this->amman('2027-01-01 08:00'), $this->resolver()->stateAt($this->amman('2027-01-01 03:00'))->nextOpensAt);
    }

    public function test_closed_between_intervals_reports_next_opening(): void
    {
        // Friday 02:30 → closed; Friday opens at 08:00.
        $state = $this->resolver()->stateAt($this->amman('2026-10-02 02:30'));

        $this->assertFalse($state->isOpen);
        $this->assertEquals($this->amman('2026-10-02 08:00'), $state->nextOpensAt);
        $this->assertEquals($state->nextOpensAt, $state->nextChange());
    }

    public function test_input_in_utc_is_evaluated_in_market_time(): void
    {
        // 2026-10-01 04:30 UTC = 07:30 Amman (Thursday) → open.
        $this->assertTrue($this->resolver()->stateAt(CarbonImmutable::parse('2026-10-01 04:30', 'UTC'))->isOpen);
    }

    public function test_holiday_closure_overrides_regular_hours(): void
    {
        $this->exception(HoursExceptionKind::Holiday, '2026-10-03', closed: true);

        $state = $this->resolver()->stateAt($this->amman('2026-10-03 12:00'));
        $this->assertFalse($state->isOpen);
        $this->assertSame(HoursExceptionKind::Holiday, $state->exception);
    }

    public function test_emergency_beats_special_hours_on_the_same_day(): void
    {
        $this->exception(HoursExceptionKind::Special, '2026-10-03', closed: false, opens: '10:00', closes: '23:00');
        $this->exception(HoursExceptionKind::Emergency, '2026-10-03', closed: true);

        $this->assertFalse($this->resolver()->stateAt($this->amman('2026-10-03 12:00'))->isOpen);
    }

    public function test_special_hours_replace_regular_hours_for_the_day(): void
    {
        $this->exception(HoursExceptionKind::Special, '2026-10-03', closed: false, opens: '10:00', closes: '23:00');

        $this->assertFalse($this->resolver()->stateAt($this->amman('2026-10-03 08:00'))->isOpen);
        $this->assertTrue($this->resolver()->stateAt($this->amman('2026-10-03 22:00'))->isOpen);
    }

    public function test_draft_exceptions_are_ignored(): void
    {
        $this->exception(HoursExceptionKind::Emergency, '2026-10-03', closed: true, status: PublishStatus::Draft);

        $this->assertTrue($this->resolver()->stateAt($this->amman('2026-10-03 12:00'))->isOpen);
    }

    private function exception(HoursExceptionKind $kind, string $date, bool $closed, ?string $opens = null, ?string $closes = null, PublishStatus $status = PublishStatus::Published): void
    {
        $this->branch->hoursExceptions()->create([
            'kind' => $kind, 'starts_on' => $date, 'ends_on' => $date, 'is_closed' => $closed,
            'opens_at' => $opens, 'closes_at' => $closes, 'status' => $status,
        ]);
    }
}
