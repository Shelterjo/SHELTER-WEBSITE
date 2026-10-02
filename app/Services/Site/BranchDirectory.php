<?php

namespace App\Services\Site;

use App\Enums\BranchType;
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

    /** How far ahead exception days (holidays, special hours, closures) go into the branch structured data. */
    private const SPECIAL_DAYS = 60;

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

    /**
     * The branch as its card and page will show it once the Owner publishes these details (BRANCH-010 preview, nothing
     * saved): the values typed in the dashboard stand in for the approved ones — publishing approves them — while the
     * hours, the state now, the contacts and the city stay the approved ones. Hidden or shown is the caller's to say.
     *
     * @param  array<string, string|null>  $values  BranchEditor::FIELDS => value
     * @param  array<string, list<string>>  $said  group => the keys answered yes
     */
    public function preview(Branch $branch, Market $market, string $locale, array $values, array $said, ?CarbonImmutable $now = null): ?BranchSummary
    {
        $branch->loadMissing(['city', 'branchAttributes']);

        return $this->summary($branch, $market, $locale, $now ?? CarbonImmutable::now(), $values, $said);
    }

    /** @return Builder<Branch> */
    private function query(Market $market): Builder
    {
        return Branch::query()->public()->with(['city', 'branchAttributes'])
            ->whereHas('city.country', fn (Builder $q) => $q->where('market_id', $market->id));
    }

    /**
     * @param  array<string, string|null>|null  $draft  unsaved details standing in for the approved ones (preview only)
     * @param  array<string, list<string>>|null  $said  unsaved services / payments answered yes (preview only)
     */
    private function summary(Branch $branch, Market $market, string $locale, CarbonImmutable $now, ?array $draft = null, ?array $said = null): ?BranchSummary
    {
        $field = fn (string $name): mixed => $draft !== null && array_key_exists($name, $draft) ? $draft[$name] : $this->data->branchField($branch, $name);
        $name = $field('name_'.$locale);
        if (! is_string($name) || $name === '') {
            return null;
        }
        $altLocale = $locale === 'ar' ? 'en' : 'ar';
        $altName = $field('name_'.$altLocale);

        $week = $today = $status = null;
        $special = [];
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
            $special = $resolver->specialDays($local, self::SPECIAL_DAYS);
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
            address: self::text($field('address_'.$locale)),
            mapsUrl: self::text($field('maps_url')),
            latitude: self::text($field('latitude')),
            longitude: self::text($field('longitude')),
            services: $said['service'] ?? $this->said($branch, 'service'),
            payments: $said['payment'] ?? $this->said($branch, 'payment'),
            kind: self::kind($branch->type, $locale),
            titleKind: self::kind($branch->type, $locale, 'site.branch.title_kinds'),
            city: $this->data->cityName($branch->city, $locale),
            landmark: self::text($field('landmark_'.$locale)),
            special: $special,
        );
    }

    private static function kind(?BranchType $type, string $locale, string $labels = 'site.branch.kinds'): ?string
    {
        if ($type === null) {
            return null;
        }
        $key = $labels.'.'.$type->value;
        $label = __($key, [], $locale);

        return is_string($label) && $label !== $key ? $label : null;
    }

    private static function text(mixed $value): ?string
    {
        return is_scalar($value) && (string) $value !== '' ? (string) $value : null;
    }

    /** @return list<string> the keys of a group the Owner said yes to (unknown and no are not shown) */
    private function said(Branch $branch, string $group): array
    {
        return array_values($branch->branchAttributes->where('group', $group)->where('value', true)->pluck('key')->map(fn ($k): string => (string) $k)->all());
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
