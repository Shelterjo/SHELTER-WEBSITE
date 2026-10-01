<?php

namespace App\Services\Site;

use App\Models\Branch;

/**
 * What a public page may show about one branch: approved values only (FACT-REGISTRY §6). A null field is not
 * rendered at all — no empty box, no "MISSING" text. Hours rows: ['weekday', 'day', 'today', 'intervals' =>
 * [['opens', 'closes', 'overnight', 'opens_at', 'closes_at']]] with display times and raw HH:MM.
 *
 * @phpstan-type Interval array{opens: string, closes: string, overnight: bool, opens_at: string, closes_at: string}
 * @phpstan-type DayRow array{weekday: int, day: string, today: bool, intervals: list<Interval>}
 */
final readonly class BranchSummary
{
    /**
     * @param  list<DayRow>|null  $week  regular weekly hours, week order (null = hours not approved)
     * @param  list<Interval>|null  $today  effective intervals starting today (exceptions applied)
     */
    public function __construct(
        public Branch $branch,
        public string $name,
        public string $locale,
        public ?string $altName,
        public string $altLocale,
        public string $url,
        public string $citySlug,
        public ?array $week,
        public ?array $today,
        public ?StatusTimeline $status,
        public ?ContactAction $phone,
        public ?ContactAction $whatsapp,
    ) {}
}
