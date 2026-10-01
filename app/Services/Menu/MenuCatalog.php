<?php

namespace App\Services\Menu;

use App\Enums\Availability;
use App\Enums\NameStatus;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBranchOverride;
use App\Models\ProductPrice;
use App\Services\MasterData\MasterData;
use Carbon\CarbonImmutable;

/**
 * The one read API for menu data (website, schema and future channels use the same answers).
 * - Names: only approved display values; a pending Arabic name returns null (D-091).
 * - Price: branch override if set, otherwise the base price valid on the date (inherited).
 * - Availability: an explicit override wins; otherwise a branch shows the base availability only once that
 *   branch's availability data is approved — until then it is UNKNOWN and must not be shown as a claim (D-094).
 */
final class MenuCatalog
{
    public function __construct(private readonly MasterData $masterData) {}

    public function name(Product $product, string $locale): ?string
    {
        if ($locale === 'ar') {
            return NameStatus::fromInventory($product->name_ar_status) === NameStatus::Approved ? $product->display_name_ar : null;
        }

        return $product->display_name_en;
    }

    public function basePrice(Product $product, ?CarbonImmutable $on = null): ?ProductPrice
    {
        $day = ($on ?? CarbonImmutable::now())->toDateString();

        // Eager-loaded prices (the menu page loads all products at once) are filtered in memory: same rule, no N+1.
        if ($product->relationLoaded('prices')) {
            return $product->prices
                ->filter(fn (ProductPrice $p): bool => $p->valid_from->toDateString() <= $day && ($p->valid_to === null || $p->valid_to->toDateString() >= $day))
                ->sortByDesc(fn (ProductPrice $p): string => $p->valid_from->toDateString())
                ->first();
        }

        return $product->prices()
            ->whereDate('valid_from', '<=', $day)
            ->where(fn ($q) => $q->whereNull('valid_to')->orWhereDate('valid_to', '>=', $day))
            ->orderByDesc('valid_from')
            ->first();
    }

    public function price(Product $product, ?Branch $branch = null, ?CarbonImmutable $on = null): ?ResolvedPrice
    {
        $base = $this->basePrice($product, $on);
        if ($base === null) {
            return null;
        }
        $override = $branch !== null ? $this->override($product, $branch) : null;
        if ($override !== null && $override->price_fils !== null) {
            return new ResolvedPrice($override->price_fils, $base->currency, true);
        }

        return new ResolvedPrice($base->price_fils, $base->currency, false);
    }

    /**
     * @param  bool|null  $confirmed  whether the branch's availability data is approved, when the caller already knows
     *                                (one fact lookup per branch instead of one per product); null = look it up
     */
    public function availability(Product $product, Branch $branch, ?bool $confirmed = null): Availability
    {
        $override = $this->override($product, $branch);
        if ($override !== null && $override->availability !== null) {
            return $override->availability;
        }

        return ($confirmed ?? $this->availabilityConfirmed($branch)) ? $product->availability : Availability::Unknown;
    }

    /** True only when the owner approved this branch's availability data (fact menu.availability_confirmed.{code}). */
    public function availabilityConfirmed(Branch $branch): bool
    {
        return $this->masterData->value(self::availabilityFactKey($branch)) === true;
    }

    public static function availabilityFactKey(Branch $branch): string
    {
        return "menu.availability_confirmed.{$branch->code}";
    }

    private function override(Product $product, Branch $branch): ?ProductBranchOverride
    {
        if ($product->relationLoaded('branchOverrides')) {
            return $product->branchOverrides->firstWhere('branch_id', $branch->id);
        }

        return $product->branchOverrides()->where('branch_id', $branch->id)->first();
    }
}
