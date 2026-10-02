<?php

namespace App\Services\Menu;

use App\Enums\Availability;
use App\Enums\PublishStatus;
use App\Models\Branch;
use App\Models\MenuCategory;
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

    /** New base price from a date; the current row is closed the day before (on the same day: replaced, kept in history). */
    public function changeBasePrice(Product $product, int $fils, User $owner, string $reason, ?CarbonImmutable $from = null): ProductPrice
    {
        $this->assertOwner($owner);
        if ($fils <= 0) {
            throw new InvalidArgumentException('Price must be positive.');
        }
        $from ??= CarbonImmutable::now('Asia/Amman')->startOfDay();

        return DB::transaction(function () use ($product, $fils, $owner, $reason, $from): ProductPrice {
            $current = $product->prices()->whereNull('valid_to')->lockForUpdate()->orderByDesc('valid_from')->first();
            if ($current !== null && $current->valid_from->toDateString() > $from->toDateString()) { // calendar days, not instants
                throw new InvalidArgumentException('A newer price already starts after that date.');
            }
            // A second change on the same start day: the earlier one keeps its row with an empty range (valid_to the
            // day before valid_from), so it never applies and the history still shows it — nothing is overwritten.
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

    /**
     * Sets a branch's values exactly: a null price = the base price, a null availability = the base availability.
     * Both null = "Reset to Master" (the override row is removed — the history stays in versions and the audit).
     */
    public function setBranchValues(Product $product, Branch $branch, User $owner, string $reason, ?int $priceFils, ?Availability $availability): void
    {
        $this->assertOwner($owner);
        if ($priceFils !== null && $priceFils <= 0) {
            throw new InvalidArgumentException('Price must be positive.');
        }
        if ($priceFils === null && $availability === null) {
            $this->resetToMaster($product, $branch, $owner, $reason);

            return;
        }
        DB::transaction(function () use ($product, $branch, $owner, $reason, $priceFils, $availability): void {
            $override = ProductBranchOverride::query()->firstOrNew(['product_id' => $product->id, 'branch_id' => $branch->id]);
            $before = $override->exists ? ['price_fils' => $override->price_fils, 'availability' => $override->availability?->value] : null;
            $override->forceFill(['price_fils' => $priceFils, 'availability' => $availability, 'reason' => $reason, 'updated_by' => $owner->id])->save();
            $after = ['price_fils' => $priceFils, 'availability' => $availability?->value];
            $this->versions->record($product, 'published', ['code' => $product->code, 'branch' => $branch->code, 'override' => $after], $reason, $owner);
            $this->audit->record('menu.branch_override_set', $product, ['before' => $before, 'after' => $after], ['branch' => $branch->code, 'reason' => $reason], self::CHANNELS, $owner);
        });
    }

    /**
     * The display names (D-082, D-091, D-137): the Owner's edit approves the English name; the Arabic name reaches the
     * site only when the Owner approves it. The source rows (menu_source_rows) are never touched.
     */
    public function setNames(Product $product, User $owner, string $nameEn, ?string $nameAr, bool $approveAr, ?string $reason = null): void
    {
        $this->assertOwner($owner);
        if (trim($nameEn) === '' || ($approveAr && ($nameAr === null || trim($nameAr) === ''))) {
            throw new InvalidArgumentException('An approved name cannot be empty.');
        }
        DB::transaction(function () use ($product, $owner, $nameEn, $nameAr, $approveAr, $reason): void {
            $before = $product->only(['display_name_en', 'display_name_ar', 'name_ar_status']);
            $stamp = 'OWNER-DASHBOARD '.CarbonImmutable::now('Asia/Amman')->toDateString();
            // Only an approved name reaches the display column (D-091, D-133); the source name stays in menu_source_rows.
            $changes = ['display_name_ar' => $approveAr ? $nameAr : null];
            if ($nameEn !== $product->display_name_en) {
                $changes += ['display_name_en' => $nameEn, 'name_en_status' => 'APPROVED — OWNER DASHBOARD', 'name_en_decision' => $stamp];
            }
            $wasApproved = str_starts_with($product->name_ar_status, 'APPROVED');
            if ($approveAr && (! $wasApproved || $nameAr !== $product->display_name_ar)) { // approved now, or corrected
                $changes += ['name_ar_status' => 'APPROVED — OWNER DASHBOARD', 'name_ar_decision' => $stamp];
            } elseif (! $approveAr && $wasApproved) {
                $changes += ['name_ar_status' => 'PENDING OWNER REVIEW — APPROVAL WITHDRAWN', 'name_ar_decision' => $stamp];
            }
            $product->forceFill($changes)->save();
            $after = $product->only(['display_name_en', 'display_name_ar', 'name_ar_status']);
            if ($after === $before) {
                return;
            }
            $this->versions->record($product, 'published', ['code' => $product->code] + $after, $reason, $owner);
            $this->audit->record('menu.names_saved', $product, ['before' => $before, 'after' => $after], $reason === null ? [] : ['reason' => $reason], self::CHANNELS, $owner);
        });
    }

    /**
     * The item's other fields (Menu IA §6, §7, §19): shown or hidden on the menu, description, main image, the "New"
     * badge and its end, the internal "featured" flag, the order inside its category and the spoken English name.
     *
     * @param  array{visible: bool, description_ar: ?string, description_en: ?string, media_id: ?int, is_new: bool, new_until: ?string, is_featured: bool, sort: int, aria_label_en: ?string}  $values
     */
    public function setDetails(Product $product, User $owner, array $values): void
    {
        $this->assertOwner($owner);
        DB::transaction(function () use ($product, $owner, $values): void {
            $fields = ['publish_status', 'description_ar', 'description_en', 'media_id', 'is_new', 'new_until', 'is_featured', 'sort', 'aria_label_en'];
            $before = self::details($product, $fields);
            $visible = $values['visible'];
            unset($values['visible']);
            $hidden = $product->publish_status === PublishStatus::Archived;
            $product->forceFill($values + ['publish_status' => $visible ? ($hidden ? PublishStatus::Published : $product->publish_status) : PublishStatus::Archived])->save();
            $after = self::details($product->refresh(), $fields);
            if ($after === $before) {
                return;
            }
            $this->versions->record($product, 'published', ['code' => $product->code] + $after, null, $owner);
            $this->audit->record('menu.details_saved', $product, ['before' => array_diff_assoc($before, $after), 'after' => array_diff_assoc($after, $before)], [], self::CHANNELS, $owner);
        });
    }

    /** A category's Arabic display name: shown only once approved (D-091, D-137 — P-01). */
    public function setCategoryName(MenuCategory $category, User $owner, ?string $nameAr, bool $approve): void
    {
        $this->assertOwner($owner);
        if ($approve && ($nameAr === null || trim($nameAr) === '')) {
            throw new InvalidArgumentException('An approved name cannot be empty.');
        }
        DB::transaction(function () use ($category, $owner, $nameAr, $approve): void {
            $before = $category->only(['name_ar', 'name_ar_status']);
            $wasApproved = str_starts_with($category->name_ar_status, 'APPROVED');
            $status = match (true) {
                $approve && (! $wasApproved || $nameAr !== $category->name_ar) => 'APPROVED — OWNER DASHBOARD',
                ! $approve && $wasApproved => 'PENDING OWNER REVIEW — APPROVAL WITHDRAWN',
                default => $category->name_ar_status,
            };
            $category->forceFill(['name_ar' => $approve ? $nameAr : null, 'name_ar_status' => $status])->save(); // null until approved (P-01)
            $after = $category->only(['name_ar', 'name_ar_status']);
            if ($after !== $before) {
                $this->versions->record($category, 'published', ['code' => $category->code] + $after, null, $owner);
                $this->audit->record('menu.category_name_saved', $category, ['before' => $before, 'after' => $after], [], self::CHANNELS, $owner);
            }
        });
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, mixed>
     */
    private static function details(Product $product, array $fields): array
    {
        $out = [];
        foreach ($fields as $field) {
            $value = $product->getAttribute($field);
            $out[$field] = match (true) {
                $value instanceof \BackedEnum => $value->value,
                $value instanceof \DateTimeInterface => $value->format('Y-m-d'),
                default => $value,
            };
        }

        return $out;
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
