<?php

namespace App\Services\Site;

use App\Enums\HoursExceptionKind;
use App\Support\PluralCategory;
use Carbon\CarbonImmutable;

/**
 * Open-state wording for now and the next hours (HOURS-009…012). The page renders the current segment; the browser
 * (resources/js/ui/open-status.ts) switches to the next segment when its time comes and counts "closing soon" down
 * every minute — no hours logic in JavaScript. Past the last known segment the status line is hidden (SPEC §20:
 * never an unconfirmed "open").
 */
final readonly class StatusTimeline
{
    /** @param non-empty-list<StatusSegment> $segments */
    public function __construct(
        public array $segments,
        public CarbonImmutable $now,
        public string $locale,
        public ?HoursExceptionKind $exception = null,
    ) {}

    public function state(): string
    {
        return $this->segments[0]->state;
    }

    public function text(): string
    {
        $current = $this->segments[0];

        return $current->closesAt !== null ? $this->closingText($this->minutesLeft($current->closesAt)) : $current->text;
    }

    public function isOpen(): bool
    {
        return $this->state() !== 'closed';
    }

    public function minutesLeft(CarbonImmutable $closesAt): int
    {
        return max(1, (int) ceil(($closesAt->getTimestamp() - $this->now->getTimestamp()) / 60));
    }

    public function closingText(int $minutes): string
    {
        return (string) __('site.status.closes_in.'.PluralCategory::for($minutes, $this->locale), ['n' => $minutes], $this->locale);
    }

    /** Data for the browser: segments with epoch-millisecond bounds + the plural templates of "closes in :n". */
    public function toJson(): string
    {
        $templates = __('site.status.closes_in', [], $this->locale);

        return (string) json_encode([
            'segments' => array_map(fn (StatusSegment $s): array => [
                'u' => $s->until?->getTimestampMs(),
                's' => $s->state,
                't' => $s->text,
                'c' => $s->closesAt?->getTimestampMs(),
            ], $this->segments),
            'closing' => is_array($templates) ? $templates : [],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
