<?php

namespace App\Services\MasterData;

use App\Enums\ContactKind;
use App\Models\Branch;
use App\Models\BranchHour;
use App\Models\ContactPoint;
use App\Models\Market;
use App\Services\Core\Settings;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * The single public read API over the Master Data Hub (FACT-REGISTRY §6: one function decides visibility).
 * Every value is returned only if its fact is APPROVED/VERIFIED and the live value still matches the approved one.
 * Otherwise it returns null and the component must not render (no empty boxes, no "MISSING" text in production).
 */
final class MasterData
{
    public function __construct(private readonly FactRegistry $facts, private readonly Settings $settings) {}

    /** Standalone facts (claims, brand facts without an operational table). */
    public function value(string $key): mixed
    {
        $fact = $this->facts->current($key);

        return $fact !== null && $fact->status->isPublishable() ? $fact->value : null;
    }

    /** Brand / global-website settings (`brand.*`, `website.*`) backed by facts. */
    public function setting(string $key): mixed
    {
        $live = $this->settings->get($key);

        return $live !== null && $this->facts->isPublishable($key, $live) ? $live : null;
    }

    public function branchField(Branch $branch, string $field): mixed
    {
        $live = $branch->getAttribute($field);

        return $live !== null && $this->facts->isPublishable($branch->factKey($field), $live) ? $live : null;
    }

    /** Brand-level contact point for an intent, or null if not approved / not public (D-057, D-059). */
    public function contact(ContactKind $kind): ?ContactPoint
    {
        $point = ContactPoint::query()->where('scope', 'brand')->where('kind', $kind->value)->where('is_public', true)->first();

        return $point !== null && $point->value !== null && $this->facts->isPublishable($point->factKey(), $point->value) ? $point : null;
    }

    /**
     * Approved regular hours, or null when the hours fact is not publishable (then the page shows no hours at all).
     *
     * @return Collection<int, BranchHour>|null
     */
    public function regularHours(Branch $branch): ?Collection
    {
        $rows = $branch->hours()->get();
        if ($rows->isEmpty() || ! $this->facts->isPublishable("hours.{$branch->code}.regular", self::normalizeHours($rows))) {
            return null;
        }

        return $rows;
    }

    /** Open / closed now, computed server-side in the market timezone. Null when hours are not publishable. */
    public function openState(Branch $branch, Market $market, ?CarbonImmutable $at = null): ?OpenState
    {
        $regular = $this->regularHours($branch);
        if ($regular === null) {
            return null;
        }

        return (new HoursResolver($branch, $market->timezone, $regular))->stateAt($at ?? CarbonImmutable::now());
    }

    /**
     * Canonical form of a branch's regular hours used for the fact hash.
     *
     * @param  Collection<int, BranchHour>  $rows
     * @return list<array{0: int, 1: string, 2: string}>
     */
    public static function normalizeHours(Collection $rows): array
    {
        return array_values($rows
            ->map(fn (BranchHour $h): array => [$h->weekday, substr($h->opens_at, 0, 5), substr($h->closes_at, 0, 5)])
            ->sortBy(fn (array $r): string => sprintf('%d-%s', $r[0], $r[1]))
            ->all());
    }
}
