<?php

namespace App\Services\Menu;

use App\Models\MenuCategory;
use Carbon\CarbonImmutable;

/**
 * When the seasonal section shows (Menu IA §10, CMS-009, MENU-044, F-17) — the one rule used by the menu page, the
 * search index and the dashboard. The Owner chooses: by its dates (shown from the first day to the last day, market
 * time, then it leaves by itself), shown now whatever the dates, or hidden. Ending never deletes anything. With no
 * choice and no dates the section shows while it is published — the state today (F-17: Active manually, dates
 * MISSING — M-04).
 */
final class MenuSeason
{
    public const MODES = ['dates', 'on', 'off'];

    /** The Owner's choice; a published season without dates or a choice counts as "shown now" (F-17). */
    public static function mode(MenuCategory $category): string
    {
        if (in_array($category->season_override, ['on', 'off'], true)) {
            return $category->season_override;
        }

        return $category->season_starts_on === null && $category->season_ends_on === null ? 'on' : 'dates';
    }

    /** @param  CarbonImmutable  $now  in the market's time zone */
    public static function active(MenuCategory $category, CarbonImmutable $now): bool
    {
        return in_array(self::state($category, $now), ['shown', 'live'], true);
    }

    /**
     * For the Owner and the rules: shown (by hand) · hidden (by hand) · live (inside its dates) · upcoming · ended ·
     * unpublished.
     *
     * @param  CarbonImmutable  $now  in the market's time zone
     */
    public static function state(MenuCategory $category, CarbonImmutable $now): string
    {
        if ($category->season_override === 'off') {
            return 'hidden';
        }
        if ($category->season_override === 'on') {
            return 'shown';
        }
        if (! in_array($category->status, ['active', 'published'], true)) {
            return 'unpublished';
        }
        $today = $now->toDateString();

        return match (true) {
            $category->season_starts_on !== null && $category->season_starts_on->toDateString() > $today => 'upcoming',
            $category->season_ends_on !== null && $category->season_ends_on->toDateString() < $today => 'ended',
            $category->season_starts_on === null && $category->season_ends_on === null => 'shown',
            default => 'live',
        };
    }

    /**
     * The next moment its dates change what customers see (the start of its first day, or the day after its last
     * day), or null when nothing is scheduled.
     *
     * @param  CarbonImmutable  $now  in the market's time zone
     */
    public static function nextChange(MenuCategory $category, CarbonImmutable $now): ?CarbonImmutable
    {
        if ($category->season_override !== null) {
            return null;
        }
        $zone = $now->getTimezone();
        $moments = [];
        if ($category->season_starts_on !== null) {
            $moments[] = CarbonImmutable::parse($category->season_starts_on->toDateString(), $zone)->startOfDay();
        }
        if ($category->season_ends_on !== null) {
            $moments[] = CarbonImmutable::parse($category->season_ends_on->toDateString(), $zone)->addDay()->startOfDay();
        }
        $future = array_values(array_filter($moments, fn (CarbonImmutable $m): bool => $m->greaterThan($now)));
        usort($future, fn (CarbonImmutable $a, CarbonImmutable $b): int => $a <=> $b);

        return $future[0] ?? null;
    }
}
