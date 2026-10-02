<?php

namespace App\Services\Experiences;

use App\Models\Experience;
use App\Models\Market;
use App\Services\Content\Pages;
use App\Services\Media\MediaLibrary;
use App\Services\Site\BranchDirectory;
use App\Support\LocalTime;
use App\Support\PageUrl;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Public events (SI-M07 listing, SI-M08 detail — DX-014). An event is public only when it is of type `event`,
 * scheduled or active (or ended, for its own page), not paused/cancelled/archived, not switched off by hand or by the
 * emergency switch, has valid dates (G-21), both languages for its title and body (LANGUAGE-PARITY) and no unconfirmed
 * AI text (G-20). The listing shows what is on now, then what is coming; ended events keep their page (marked ended,
 * out of the index) so shared links do not break. No media until approved (MEDIA PENDING OWNER APPROVAL).
 */
final class Events
{
    public function __construct(private readonly BranchDirectory $branches, private readonly MediaLibrary $media) {}

    /** @return list<EventView> */
    public function listed(Market $market, string $locale, ?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $events = $this->query($market)
            ->whereIn('status', ['scheduled', 'active'])
            ->where('ends_at', '>', $now->utc())
            ->orderBy('starts_at')
            ->get();

        $views = [];
        foreach ($events as $event) {
            $view = $this->view($event, $market, $locale, $now);
            if ($view !== null) {
                $views[] = $view;
            }
        }
        // On now first, then upcoming (both by start).
        usort($views, fn (EventView $a, EventView $b): int => [$a->state !== 'now', $a->startsAt] <=> [$b->state !== 'now', $b->startsAt]);

        return $views;
    }

    public function find(Market $market, string $slug, string $locale, ?CarbonImmutable $now = null): ?EventView
    {
        $event = $this->query($market)->whereIn('status', ['scheduled', 'active', 'ended'])->where('slug', $slug)->first();

        return $event === null ? null : $this->view($event, $market, $locale, $now ?? CarbonImmutable::now());
    }

    /**
     * What visitors would see for this event — whatever its status (the dashboard preview). Null when it could not be
     * shown at all (dates, one language only, no title): the same rules as the public pages.
     */
    public function preview(Experience $event, Market $market, string $locale, ?CarbonImmutable $now = null): ?EventView
    {
        return $event->slug === null ? null : $this->view($event, $market, $locale, $now ?? CarbonImmutable::now());
    }

    /** @return Builder<Experience> */
    private function query(Market $market): Builder
    {
        return Experience::query()
            ->where('type', 'event')
            ->where(fn (Builder $q) => $q->where('market_id', $market->id)->orWhereNull('market_id'))
            ->whereNull('archived_at')
            ->where('emergency_disabled', false)
            ->where(fn (Builder $q) => $q->whereNull('manual_state')->orWhere('manual_state', '!=', 'off'))
            ->where('origin', '!=', 'ai')
            ->whereNotNull('slug')
            ->whereNotNull('starts_at')
            ->whereNotNull('ends_at');
    }

    private function view(Experience $event, Market $market, string $locale, CarbonImmutable $now): ?EventView
    {
        if ($event->starts_at === null || $event->ends_at === null || ! $event->ends_at->greaterThan($event->starts_at)) {
            return null; // G-21
        }
        foreach (['title', 'body'] as $field) {
            if (($event->text($field, 'ar') === null) !== ($event->text($field, 'en') === null)) {
                return null; // one language only → not public
            }
        }
        $title = $event->text('title', $locale);
        if ($title === null) {
            return null;
        }

        $timezone = $event->timezone !== '' ? $event->timezone : $market->timezone;
        $starts = CarbonImmutable::instance($event->starts_at)->setTimezone($timezone);
        $ends = CarbonImmutable::instance($event->ends_at)->setTimezone($timezone);
        $state = $event->status === 'ended' || ! $ends->greaterThan($now) ? 'ended' : ($starts->lessThanOrEqualTo($now) ? 'now' : 'upcoming');
        $oneDay = $this->isOneDay($starts, $ends);
        $ctaLabel = $event->text('cta_label', $locale);
        $ctaUrl = $event->cta_url !== null && preg_match('#^(https://|/)#', $event->cta_url) === 1 ? $event->cta_url : null; // G-08
        // A page of this site opens in the page's language (/ar/jo/menu/ on the English page → /en/jo/menu/).
        $ctaUrl = $ctaUrl === null ? null : (preg_replace('#^/(ar|en)/#', '/'.$locale.'/', $ctaUrl) ?? $ctaUrl);

        return new EventView(
            slug: (string) $event->slug,
            title: $title,
            paragraphs: Pages::paragraphs((string) $event->text('body', $locale)),
            terms: Pages::paragraphs((string) $event->text('terms', $locale)),
            startsAt: $starts,
            endsAt: $ends,
            dateText: $this->dates($starts, $ends, $locale),
            timeText: $oneDay ? LocalTime::format($starts, $locale).' – '.LocalTime::format($ends, $locale) : null,
            startText: $oneDay ? null : $this->moment($starts, $locale),
            endText: $oneDay ? null : $this->moment($ends, $locale),
            state: $state,
            place: $this->place($event, $market, $locale),
            ctaLabel: $ctaLabel !== null && $ctaUrl !== null ? $ctaLabel : null,
            ctaUrl: $ctaLabel !== null ? $ctaUrl : null,
            url: PageUrl::route('events.show', ['locale' => $locale, 'market' => $market->code, 'slug' => $event->slug]),
            image: $this->media->image($event->media, $locale, $title),
        );
    }

    private function dates(CarbonImmutable $starts, CarbonImmutable $ends, string $locale): string
    {
        $carbonLocale = $locale === 'ar' ? 'ar_JO' : 'en';
        $start = $this->localized($starts, $carbonLocale);
        if ($this->isOneDay($starts, $ends)) {
            return $start->isoFormat('dddd D MMMM YYYY');
        }
        $end = $this->localized($ends, $carbonLocale);

        return $starts->isSameMonth($ends)
            ? $start->isoFormat('D').' – '.$end->isoFormat('D MMMM YYYY')
            : $start->isoFormat('D MMMM').' – '.$end->isoFormat('D MMMM YYYY');
    }

    /** An event that ends by the next morning (e.g. 20:00 → 01:00) is still one evening. */
    private function isOneDay(CarbonImmutable $starts, CarbonImmutable $ends): bool
    {
        return $starts->isSameDay($ends) || ($ends->diffInHours($starts, true) < 24 && $ends->hour < 6);
    }

    private function moment(CarbonImmutable $at, string $locale): string
    {
        return $this->localized($at, $locale === 'ar' ? 'ar_JO' : 'en')->isoFormat('dddd D MMMM').($locale === 'ar' ? '، ' : ', ').LocalTime::format($at, $locale);
    }

    /** Levantine month names on Arabic pages (تشرين الأول), Latin digits in both languages (D-065). */
    private function localized(CarbonImmutable $date, string $carbonLocale): CarbonImmutable
    {
        $localized = $date->locale($carbonLocale);

        return $localized instanceof CarbonImmutable ? $localized : $date;
    }

    /** The venue: an approved venue text in both languages, else the approved names of the event's branches. */
    private function place(Experience $event, Market $market, string $locale): ?string
    {
        $details = $event->details ?? [];
        $venueAr = is_string($details['venue_ar'] ?? null) ? trim($details['venue_ar']) : '';
        $venueEn = is_string($details['venue_en'] ?? null) ? trim($details['venue_en']) : '';
        if ($venueAr !== '' && $venueEn !== '') {
            return $locale === 'ar' ? $venueAr : $venueEn;
        }
        $ids = array_map('intval', $event->branch_ids ?? []);
        if ($ids === []) {
            return null;
        }
        $names = [];
        foreach ($this->branches->forMarket($market, $locale) as $branch) {
            if (in_array($branch->branch->id, $ids, true)) {
                $names[] = $branch->name;
            }
        }

        return $names === [] ? null : implode(' · ', $names);
    }
}
