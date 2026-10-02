<?php

namespace App\Services\Site;

use App\Models\Branch;
use App\Models\BranchHour;
use App\Models\Market;
use App\Services\MasterData\HoursResolver;
use App\Services\MasterData\MasterData;
use App\Support\LocalTime;
use App\Support\PageUrl;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Public branches of a market for the home page, /locations/ and the branch pages (SI-B02, SI-M03, SI-M05/M06).
 * A branch without an approved name in the page language is not shown at all; hours, status and contact actions
 * appear only when approved (MasterData). Status is computed at request time in the market timezone.
 *
 * @phpstan-import-type Interval from BranchSummary
 * @phpstan-import-type DayRow from BranchSummary
 */
final class BranchDirectory
{
    /** Week order of the hours table: Saturday first, as the hours decision lists them (D-020). Carbon weekdays. */
    public const WEEK = [6, 0, 1, 2, 3, 4, 5];

    public function __construct(
        private readonly MasterData $data,
        private readonly ContactActions $contacts,
        private readonly OpenStatus $status,
    ) {}

    /** @return list<BranchSummary> */
    public function forMarket(Market $market, string $locale, ?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();

        return array_values(array_filter(
            $this->query($market)->get()->map(fn (Branch $b): ?BranchSummary => $this->summary($b, $market, $locale, $now))->all(),
        ));
    }

    public function find(Market $market, string $citySlug, string $branchSlug, string $locale, ?CarbonImmutable $now = null): ?BranchSummary
    {
        $branch = $this->query($market)
            ->where('slug', $branchSlug)
            ->whereHas('city', fn (Builder $q) => $q->where('slug', $citySlug))
            ->first();

        return $branch === null ? null : $this->summary($branch, $market, $locale, $now ?? CarbonImmutable::now());
    }

    /** @return Builder<Branch> */
    private function query(Market $market): Builder
    {
        return Branch::query()->public()->with('city')
            ->whereHas('city.country', fn (Builder $q) => $q->where('market_id', $market->id));
    }

    private function summary(Branch $branch, Market $market, string $locale, CarbonImmutable $now): ?BranchSummary
    {
        $name = $this->data->branchField($branch, 'name_'.$locale);
        if (! is_string($name) || $name === '') {
            return null;
        }
        $altLocale = $locale === 'ar' ? 'en' : 'ar';
        $altName = $this->data->branchField($branch, 'name_'.$altLocale);

        $week = $today = $status = null;
        $regular = $this->data->regularHours($branch);
        if ($regular !== null) {
            $local = $now->setTimezone($market->timezone);
            $resolver = new HoursResolver($branch, $market->timezone, $regular);
            $week = $this->week($regular, $local->dayOfWeek, $locale);
            $today = array_map(
                fn (array $i): array => self::interval($i[0]->format('H:i'), $i[1]->format('H:i'), $locale),
                $resolver->intervalsOn($local),
            );
            $status = $this->status->timeline($resolver, $now, $market->timezone, $locale);
        }

        return new BranchSummary(
            branch: $branch,
            name: $name,
            locale: $locale,
            altName: is_string($altName) && $altName !== '' ? $altName : null,
            altLocale: $altLocale,
            url: PageUrl::route('locations.branch', [
                'locale' => $locale, 'market' => $market->code, 'city' => $branch->city->slug, 'branch' => $branch->slug,
            ]),
            citySlug: $branch->city->slug,
            week: $week,
            today: $today,
            status: $status,
            phone: $this->contacts->phone($locale),
            whatsapp: $this->contacts->whatsapp($locale),
        );
    }

    /**
     * @param  Collection<int, BranchHour>  $rows
     * @return list<DayRow>
     */
    private function week(Collection $rows, int $todayWeekday, string $locale): array
    {
        return self::weekRows($rows->map(fn (BranchHour $h): array => [$h->weekday, substr($h->opens_at, 0, 5), substr($h->closes_at, 0, 5)])->all(), $todayWeekday, $locale);
    }

    /**
     * The week as the site shows it (Saturday first), from [weekday, "HH:MM", "HH:MM"] rows — also the dashboard's
     * preview of hours not published yet, so the Owner sees exactly what visitors will.
     *
     * @param  array<int, array{0: int, 1: string, 2: string}>  $rows
     * @return list<DayRow>
     */
    public static function weekRows(array $rows, ?int $todayWeekday, string $locale): array
    {
        return array_map(fn (int $weekday): array => [
            'weekday' => $weekday,
            'day' => LocalTime::weekday($weekday, $locale),
            'today' => $weekday === $todayWeekday,
            'intervals' => array_values(array_map(
                fn (array $r): array => self::interval($r[1], $r[2], $locale),
                collect($rows)->filter(fn (array $r): bool => $r[0] === $weekday)->sortBy(fn (array $r): string => $r[1])->all(),
            )),
        ], self::WEEK);
    }

    /** @return Interval */
    private static function interval(string $opens, string $closes, string $locale): array
    {
        return [
            'opens' => LocalTime::clock($opens, $locale),
            'closes' => LocalTime::clock($closes, $locale),
            'overnight' => $closes <= $opens,
            'opens_at' => $opens,
            'closes_at' => $closes,
        ];
    }
}
