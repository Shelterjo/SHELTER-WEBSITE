<?php

namespace App\Services\MasterData;

use App\Enums\HoursExceptionKind;
use App\Enums\PublishStatus;
use App\Models\Branch;
use App\Models\BranchHour;
use App\Models\HoursException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Effective opening hours (D-021, M33 MDH): Emergency > Temporary > Special/Holiday > Regular.
 * Exceptions never modify the regular hours. Intervals that close at or before they open run past midnight.
 * All computation happens in the market timezone (Asia/Amman for JO).
 */
final class HoursResolver
{
    /** Lookahead for "next opening" when a branch is closed (covers long temporary closures). */
    private const LOOKAHEAD_DAYS = 14;

    /**
     * @param  Collection<int, BranchHour>|null  $regular  preloaded regular rows (defaults to the branch's rows)
     * @param  Collection<int, HoursException>|null  $exceptions  preloaded published exceptions
     */
    public function __construct(
        private readonly Branch $branch,
        private readonly string $timezone,
        private ?Collection $regular = null,
        private ?Collection $exceptions = null,
    ) {}

    /**
     * Intervals that START on the given local date.
     *
     * @return list<array{0: CarbonImmutable, 1: CarbonImmutable}>
     */
    public function intervalsOn(CarbonImmutable $date): array
    {
        $day = $date->setTimezone($this->timezone)->startOfDay();
        $exception = $this->exceptionFor($day);

        if ($exception !== null) {
            if ($exception->is_closed || $exception->opens_at === null || $exception->closes_at === null) {
                return [];
            }

            return [$this->interval($day, $exception->opens_at, $exception->closes_at)];
        }

        return array_values($this->regular()
            ->filter(fn (BranchHour $h): bool => $h->weekday === $day->dayOfWeek)
            ->sortBy('opens_at')
            ->map(fn (BranchHour $h): array => $this->interval($day, $h->opens_at, $h->closes_at))
            ->all());
    }

    /**
     * The days in the next $days (from $from's local date) whose hours come from a published exception — holiday,
     * special hours, emergency or temporary closure — with the hours that apply then (none = closed). The same rule as
     * the page (one priority order), so search engines never get a second copy of the hours (M57 §19, §21).
     *
     * @return list<array{date: string, intervals: list<array{opens: string, closes: string}>}>
     */
    public function specialDays(CarbonImmutable $from, int $days): array
    {
        $start = $from->setTimezone($this->timezone)->startOfDay();
        $out = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $start->addDays($i);
            if ($this->exceptionFor($day) === null) {
                continue;
            }
            $out[] = ['date' => $day->format('Y-m-d'), 'intervals' => array_map(
                fn (array $interval): array => ['opens' => $interval[0]->format('H:i'), 'closes' => $interval[1]->format('H:i')],
                $this->intervalsOn($day),
            )];
        }

        return $out;
    }

    public function stateAt(CarbonImmutable $at): OpenState
    {
        $local = $at->setTimezone($this->timezone);

        // An interval from yesterday may still be running after midnight.
        foreach ([$local->subDay(), $local] as $day) {
            foreach ($this->intervalsOn($day) as [$start, $end]) {
                if ($local->greaterThanOrEqualTo($start) && $local->lessThan($end)) {
                    return new OpenState(true, $end, null, $this->exceptionFor($day->startOfDay())?->kind);
                }
            }
        }

        return new OpenState(false, null, $this->nextOpening($local), $this->exceptionFor($local->startOfDay())?->kind);
    }

    private function nextOpening(CarbonImmutable $from): ?CarbonImmutable
    {
        for ($i = 0; $i <= self::LOOKAHEAD_DAYS; $i++) {
            foreach ($this->intervalsOn($from->addDays($i)) as [$start]) {
                if ($start->greaterThan($from)) {
                    return $start;
                }
            }
        }

        return null;
    }

    private function exceptionFor(CarbonImmutable $day): ?HoursException
    {
        return $this->exceptions()
            ->filter(fn (HoursException $e): bool => $day->betweenIncluded(
                CarbonImmutable::parse($e->starts_on->format('Y-m-d'), $this->timezone)->startOfDay(),
                CarbonImmutable::parse($e->ends_on->format('Y-m-d'), $this->timezone)->endOfDay(),
            ))
            ->sortByDesc(fn (HoursException $e): int => $e->kind->rank())
            ->first();
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    private function interval(CarbonImmutable $day, string $opens, string $closes): array
    {
        $start = self::at($day, $opens);
        $end = self::at($day, $closes);

        return [$start, $end->lessThanOrEqualTo($start) ? $end->addDay() : $end];
    }

    private static function at(CarbonImmutable $day, string $time): CarbonImmutable
    {
        [$hour, $minute] = array_map('intval', explode(':', $time) + [0, 0]);

        return $day->setTime($hour, $minute);
    }

    /** @return Collection<int, BranchHour> */
    private function regular(): Collection
    {
        return $this->regular ??= $this->branch->hours()->get();
    }

    /** @return Collection<int, HoursException> */
    private function exceptions(): Collection
    {
        return $this->exceptions ??= $this->branch->hoursExceptions()
            ->where('status', PublishStatus::Published->value)
            ->get();
    }

    /** Highest-priority kind used when two exceptions overlap (exposed for the dashboard conflict view). */
    public static function winner(HoursExceptionKind ...$kinds): ?HoursExceptionKind
    {
        usort($kinds, fn (HoursExceptionKind $a, HoursExceptionKind $b): int => $b->rank() <=> $a->rank());

        return $kinds[0] ?? null;
    }
}
