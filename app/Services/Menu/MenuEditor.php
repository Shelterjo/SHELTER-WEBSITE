<?php

namespace App\Services\Menu;

use App\Enums\Availability;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBranchOverride;
use App\Models\ProductPrice;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Services\Core\Versions;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Owner write operations on menu master data (M33 §5, §16–§18). Every change: owner only, reason required for
 * prices, versioned in content_versions, audited with the channels it affects. Price history is never overwritten.
 */
final class MenuEditor
{
    /** Channels a menu change reaches (used by the audit trail and, later, change-impact preview). */
    private const CHANNELS = ['website.menu', 'schema.menu'];

    public function __construct(private readonly AuditLogger $audit, private readonly Versions $versions) {}

    /** New base price from a date; the current row is closed the day before. */
    public function changeBasePrice(Product $product, int $fils, User $owner, string $reason, ?CarbonImmutable $from = null): ProductPrice
    {
        $this->assertOwner($owner);
        if ($fils <= 0) {
            throw new InvalidArgumentException('Price must be positive.');
        }
        $from ??= CarbonImmutable::now('Asia/Amman')->startOfDay();

        return DB::transaction(function () use ($product, $fils, $owner, $reason, $from): ProductPrice {
            $current = $product->prices()->whereNull('valid_to')->lockForUpdate()->orderByDesc('valid_from')->first();
            if ($current !== null && $current->valid_from->greaterThanOrEqualTo($from)) {
                throw new InvalidArgumentException('A newer price already starts on or after that date.');
            }
            $current?->forceFill(['valid_to' => $from->subDay()->toDateString()])->save();

            $price = $product->prices()->create([
                'price_fils' => $fils,
                'currency' => $current->currency ?? 'JOD',
                'tax_inclusive' => true,
                'valid_from' => $from->toDateString(),
                'source' => 'owner_dashboard',
                'created_by' => $owner->id,
            ]);
            $before = $current?->price_fils;
            $this->versions->record($product, 'published', ['code' => $product->code, 'price_fils' => $fils, 'valid_from' => $from->toDateString()], $reason, $owner);
            $this->audit->record('menu.price_changed', $product, ['before' => ['price_fils' => $before], 'after' => ['price_fils' => $fils]], ['reason' => $reason], self::CHANNELS, $owner);

            return $price;
        });
    }

    public function overrideForBranch(Product $product, Branch $branch, User $owner, string $reason, ?int $priceFils = null, ?Availability $availability = null): ProductBranchOverride
    {
        $this->assertOwner($owner);
        if ($priceFils === null && $availability === null) {
            throw new InvalidArgumentException('An override needs a price or an availability.');
        }
        if ($priceFils !== null && $priceFils <= 0) {
            throw new InvalidArgumentException('Price must be positive.');
        }

        return DB::transaction(function () use ($product, $branch, $owner, $reason, $priceFils, $availability): ProductBranchOverride {
            $override = ProductBranchOverride::query()->firstOrNew(['product_id' => $product->id, 'branch_id' => $branch->id]);
            $before = $override->exists ? ['price_fils' => $override->price_fils, 'availability' => $override->availability?->value] : null;
            $override->fill([
                'price_fils' => $priceFils ?? $override->price_fils,
                'availability' => $availability ?? $override->availability,
                'reason' => $reason,
                'updated_by' => $owner->id,
            ])->save();
            $after = ['price_fils' => $override->price_fils, 'availability' => $override->availability?->value];
            $this->versions->record($product, 'published', ['code' => $product->code, 'branch' => $branch->code, 'override' => $after], $reason, $owner);
            $this->audit->record('menu.branch_override_set', $product, ['before' => $before, 'after' => $after], ['branch' => $branch->code, 'reason' => $reason], self::CHANNELS, $owner);

            return $override;
        });
    }

    /** "Reset to Master": the branch inherits the base price and availability again. */
    public function resetToMaster(Product $product, Branch $branch, User $owner, string $reason): void
    {
        $this->assertOwner($owner);
        DB::transaction(function () use ($product, $branch, $owner, $reason): void {
            $override = ProductBranchOverride::query()->where(['product_id' => $product->id, 'branch_id' => $branch->id])->first();
            if ($override === null) {
                return;
            }
            $before = ['price_fils' => $override->price_fils, 'availability' => $override->availability?->value];
            $override->delete();
            $this->versions->record($product, 'published', ['code' => $product->code, 'branch' => $branch->code, 'override' => null], $reason, $owner);
            $this->audit->record('menu.branch_override_reset', $product, ['before' => $before, 'after' => null], ['branch' => $branch->code, 'reason' => $reason], self::CHANNELS, $owner);
        });
    }

    private function assertOwner(User $user): void
    {
        if (! $user->isOwner()) {
            throw new AuthorizationException('Only the owner can change menu master data.');
        }
    }
}
