<?php

namespace App\Services\Experiences;

use App\Models\Experience;
use App\Models\Market;
use App\Services\Core\FeatureFlags;
use App\Services\Media\MediaLibrary;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

/**
 * The resolver of the dynamic experience engine (DYNAMIC-EXPERIENCE-ENGINE §3, DX-010…013, DX-021, DX-037): for each
 * placement — the top bar on every page, the feature block on the home page — ONE experience at most, the highest
 * priority among those live now (Emergency/Urgent > Major event > Campaign > Seasonal > Recognition > Announcement,
 * the Owner may override). Live = published (scheduled/active), inside its dates (or switched on by hand), not paused,
 * cancelled, archived or stopped by "Disable now", with its title in both languages and no unconfirmed AI text.
 * One that names branches (CAMP-004) stays off the pages of the other branches; no branch named = every branch.
 * Its image shows only while MediaRights allows it (approved, website rights). Nothing live → null → nothing is drawn
 * (DX-012). A failure here never breaks the page (DX-037): it returns null.
 */
final class Placements
{
    public const TOP_BAR = 'top_bar';

    public const HOME_FEATURE = 'home_feature';

    public const ALL = [self::TOP_BAR, self::HOME_FEATURE];

    /** The experience types that may take a placement. */
    public const TYPES = ['announcement', 'campaign', 'event'];

    /** Default priority by type (M32 §10); an urgent notice outranks everything. */
    public const DEFAULT_PRIORITY = ['urgent' => 100, 'event' => 80, 'campaign' => 60, 'seasonal_theme' => 40, 'recognition' => 30, 'announcement' => 20];

    /** @var array<string, PlacedExperience|null> one answer per request */
    private array $resolved = [];

    public function __construct(private readonly FeatureFlags $flags, private readonly MediaLibrary $media) {}

    /** @param  int|null  $branchId  the branch the page is about (its own page, its menu) — null on every other page */
    public function current(Market $market, string $placement, string $locale, ?CarbonImmutable $now = null, ?int $branchId = null): ?PlacedExperience
    {
        $key = $market->id.'|'.$placement.'|'.$locale.'|'.($now?->getTimestamp() ?? 'now').'|'.($branchId ?? 'all');
        if (array_key_exists($key, $this->resolved)) {
            return $this->resolved[$key];
        }
        try {
            // Safe Mode (SAFE-MODE §2): the dynamic layer stops; only an urgent notice still shows in the top bar, as text.
            $safe = $this->flags->enabled(FeatureFlags::SAFE_MODE);
            $winner = $this->live($market, $now)->first(fn (Experience $e): bool => in_array($placement, $e->placements ?? [], true)
                && self::targets($e, $branchId)
                && (! $safe || ($placement === self::TOP_BAR && ($e->details['urgent'] ?? false) === true)));

            return $this->resolved[$key] = $winner === null ? null : $this->present($winner, $placement, $locale, $market);
        } catch (Throwable $e) {
            report($e);

            return $this->resolved[$key] = null; // the base site never depends on the engine
        }
    }

    /**
     * Everything live now that takes a placement, highest priority first (the Active Now screen reads the same list).
     *
     * @return Collection<int, Experience>
     */
    public function live(Market $market, ?CarbonImmutable $now = null): Collection
    {
        $now ??= CarbonImmutable::now();

        return Experience::query()
            ->whereIn('type', self::TYPES)
            ->where(fn ($q) => $q->where('market_id', $market->id)->orWhereNull('market_id'))
            ->whereIn('status', ['scheduled', 'active'])
            ->whereNull('archived_at')
            ->where('emergency_disabled', false)
            ->where(fn ($q) => $q->whereNull('manual_state')->orWhere('manual_state', '!=', 'off'))
            ->where('origin', '!=', 'ai')
            ->whereNotNull('placements')
            ->where(fn ($q) => $q->where('manual_state', 'on')
                ->orWhere(fn ($t) => $t->where('starts_at', '<=', $now->utc())->where('ends_at', '>', $now->utc())))
            ->get()
            ->filter(fn (Experience $e): bool => self::complete($e))
            ->sortBy([fn (Experience $a, Experience $b): int => self::priority($b) <=> self::priority($a), fn (Experience $a, Experience $b): int => ($b->starts_at?->getTimestamp() ?? 0) <=> ($a->starts_at?->getTimestamp() ?? 0)])
            ->values();
    }

    public static function priority(Experience $experience): int
    {
        if ($experience->priority > 0) {
            return $experience->priority; // the Owner's own choice
        }
        $urgent = ($experience->details ?? [])['urgent'] ?? false;

        return $urgent === true ? self::DEFAULT_PRIORITY['urgent'] : (self::DEFAULT_PRIORITY[$experience->type] ?? 0);
    }

    /**
     * Whether it may show on a page about this branch (CAMP-004): no branch named = every branch; a page about no
     * branch in particular (null) shows it as before.
     */
    public static function targets(Experience $experience, ?int $branchId): bool
    {
        $branches = array_map('intval', $experience->branch_ids ?? []);

        return $branchId === null || $branches === [] || in_array($branchId, $branches, true);
    }

    /** Titles in both languages; a text and a button only as pairs (LANGUAGE-PARITY). */
    private static function complete(Experience $e): bool
    {
        if ($e->text('title', 'ar') === null || $e->text('title', 'en') === null) {
            return false;
        }

        return ($e->text('body', 'ar') === null) === ($e->text('body', 'en') === null);
    }

    private function present(Experience $e, string $placement, string $locale, Market $market): PlacedExperience
    {
        $url = $e->cta_url !== null && preg_match('#^(https://|/(?!/))#', $e->cta_url) === 1 ? $e->cta_url : null; // G-08
        $url = $url === null ? null : (preg_replace('#^/(ar|en)/#', '/'.$locale.'/', $url) ?? $url);
        $label = $e->text('cta_label', $locale);
        $timezone = $e->timezone !== '' ? $e->timezone : $market->timezone;
        $title = (string) $e->text('title', $locale);

        return new PlacedExperience(
            id: $e->id,
            type: $e->type,
            placement: $placement,
            title: $title,
            text: $e->text('body', $locale),
            ctaLabel: $label !== null && $url !== null ? $label : null,
            ctaUrl: $label !== null ? $url : null,
            urgent: (($e->details ?? [])['urgent'] ?? false) === true,
            endsAt: CarbonImmutable::instance($e->ends_at ?? CarbonImmutable::now()->addDay())->setTimezone($timezone),
            // The top bar is one line of text; only the home block draws the image (approved, website rights — MEDIA-RIGHTS).
            image: $placement === self::HOME_FEATURE ? $this->media->image($e->media, $locale, $title) : null,
        );
    }
}
