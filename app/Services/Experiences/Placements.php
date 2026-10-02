<?php

namespace App\Services\Experiences;

use App\Models\Experience;
use App\Models\Market;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

/**
 * The resolver of the dynamic experience engine (DYNAMIC-EXPERIENCE-ENGINE §3, DX-010…013, DX-021, DX-037): for each
 * placement — the top bar on every page, the feature block on the home page — ONE experience at most, the highest
 * priority among those live now (Emergency/Urgent > Major event > Campaign > Seasonal > Recognition > Announcement,
 * the Owner may override). Live = published (scheduled/active), inside its dates (or switched on by hand), not paused,
 * cancelled, archived or stopped by "Disable now", with its title in both languages and no unconfirmed AI text.
 * Nothing live → null → nothing is drawn (DX-012). A failure here never breaks the page (DX-037): it returns null.
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

    public function current(Market $market, string $placement, string $locale, ?CarbonImmutable $now = null): ?PlacedExperience
    {
        $key = $market->id.'|'.$placement.'|'.$locale.'|'.($now?->getTimestamp() ?? 'now');
        if (array_key_exists($key, $this->resolved)) {
            return $this->resolved[$key];
        }
        try {
            $winner = $this->live($market, $now)->first(fn (Experience $e): bool => in_array($placement, $e->placements ?? [], true));

            return $this->resolved[$key] = $winner === null ? null : self::present($winner, $placement, $locale, $market);
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

    /** Titles in both languages; a text and a button only as pairs (LANGUAGE-PARITY). */
    private static function complete(Experience $e): bool
    {
        if ($e->text('title', 'ar') === null || $e->text('title', 'en') === null) {
            return false;
        }

        return ($e->text('body', 'ar') === null) === ($e->text('body', 'en') === null);
    }

    private static function present(Experience $e, string $placement, string $locale, Market $market): PlacedExperience
    {
        $url = $e->cta_url !== null && preg_match('#^(https://|/(?!/))#', $e->cta_url) === 1 ? $e->cta_url : null; // G-08
        $url = $url === null ? null : (preg_replace('#^/(ar|en)/#', '/'.$locale.'/', $url) ?? $url);
        $label = $e->text('cta_label', $locale);
        $timezone = $e->timezone !== '' ? $e->timezone : $market->timezone;

        return new PlacedExperience(
            id: $e->id,
            type: $e->type,
            placement: $placement,
            title: (string) $e->text('title', $locale),
            text: $e->text('body', $locale),
            ctaLabel: $label !== null && $url !== null ? $label : null,
            ctaUrl: $label !== null ? $url : null,
            urgent: (($e->details ?? [])['urgent'] ?? false) === true,
            endsAt: CarbonImmutable::instance($e->ends_at ?? CarbonImmutable::now()->addDay())->setTimezone($timezone),
        );
    }
}
