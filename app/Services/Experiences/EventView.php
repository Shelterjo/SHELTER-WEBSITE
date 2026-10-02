<?php

namespace App\Services\Experiences;

use App\Services\Media\MediaImage;
use App\Services\Site\BranchSummary;
use Carbon\CarbonImmutable;

/**
 * A public event in one language, with its dates already in the event's time zone (DX-014, SI-M07/M08). One-evening
 * events have `timeText` (start – end); events over several days have `startText` / `endText` (day + time) instead.
 * `place` is the line visitors read; `branches` are our public branches it is at (their approved Master Data — the
 * Event structured data's location), empty when it is at another venue (`atVenue`) or nowhere said.
 */
final readonly class EventView
{
    /**
     * @param  list<string>  $paragraphs
     * @param  list<string>  $terms
     * @param  list<BranchSummary>  $branches
     */
    public function __construct(
        public string $slug,
        public string $title,
        public array $paragraphs,
        public array $terms,
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
        public string $dateText,
        public ?string $timeText,
        public ?string $startText,
        public ?string $endText,
        public string $state,
        public ?string $place,
        public ?string $ctaLabel,
        public ?string $ctaUrl,
        public string $url,
        public ?MediaImage $image = null,
        public array $branches = [],
        public bool $atVenue = false,
    ) {}

    public function isEnded(): bool
    {
        return $this->state === 'ended';
    }
}
