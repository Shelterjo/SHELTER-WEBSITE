<?php

namespace App\Services\Site;

use App\Services\MasterData\HoursResolver;
use App\Support\LocalTime;
use Carbon\CarbonImmutable;

/**
 * Builds the open-state wording from the effective hours (HoursResolver: regular + exceptions, past midnight, market
 * timezone). Texts follow HOURS-010: "Open now — until 2:00 AM", "Closes in 45 min" (last 60 minutes, HOURS-011),
 * "Closed now — opens 9:00 AM / tomorrow 9:00 AM / Saturday 9:00 AM". The closing time itself counts as closed.
 */
final class OpenStatus
{
    /** HOURS-011 (FROZEN): "closing soon" covers the last 60 minutes. */
    public const CLOSING_SOON_MINUTES = 60;

    private const HORIZON_HOURS = 24;

    private const MAX_SEGMENTS = 16;

    public function timeline(HoursResolver $hours, CarbonImmutable $now, string $timezone, string $locale): StatusTimeline
    {
        $at = $now->setTimezone($timezone);
        $horizon = $at->addHours(self::HORIZON_HOURS);
        $first = $hours->stateAt($at);
        $segments = [];

        while (count($segments) < self::MAX_SEGMENTS) {
            $state = $segments === [] ? $first : $hours->stateAt($at);
            if ($state->isOpen && $state->closesAt !== null) {
                $closes = $state->closesAt->setTimezone($timezone);
                $soon = $closes->subMinutes(self::CLOSING_SOON_MINUTES);
                if ($at->lessThan($soon)) {
                    $segments[] = new StatusSegment('open', $this->text('open_until', $closes, $locale), $soon);
                    $at = $soon;
                } else {
                    $segments[] = new StatusSegment('closing', '', $closes, $closes);
                    $at = $closes;
                }
            } else {
                $next = $state->nextOpensAt?->setTimezone($timezone);
                if ($next === null) {
                    $segments[] = new StatusSegment('closed', (string) __('site.status.closed', [], $locale), null);
                    break;
                }
                // Split at local midnight so "tomorrow" never becomes wrong while the page stays open.
                $midnight = $at->addDay()->startOfDay();
                $until = $next->lessThan($midnight) ? $next : $midnight;
                $segments[] = new StatusSegment('closed', $this->closedText($at, $next, $locale), $until);
                $at = $until;
            }
            if ($at->greaterThanOrEqualTo($horizon)) {
                break;
            }
        }

        return new StatusTimeline($segments, $now, $locale, $first->exception);
    }

    private function closedText(CarbonImmutable $at, CarbonImmutable $next, string $locale): string
    {
        $days = (int) $at->startOfDay()->diffInDays($next->startOfDay());

        return match ($days) {
            0 => $this->text('closed_opens', $next, $locale),
            1 => $this->text('closed_opens_tomorrow', $next, $locale),
            default => (string) __('site.status.closed_opens_day', [
                'day' => LocalTime::weekday($next->dayOfWeek, $locale),
                'time' => LocalTime::format($next, $locale),
            ], $locale),
        };
    }

    private function text(string $key, CarbonImmutable $time, string $locale): string
    {
        return (string) __('site.status.'.$key, ['time' => LocalTime::format($time, $locale)], $locale);
    }
}
