<?php

namespace App\Models;

use App\Enums\Availability;
use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * One product identity for every channel (M33 §4). Codes PRD-##### are frozen and never reused (D-136).
 * The public site reads names/prices/availability only through App\Services\Menu\MenuCatalog.
 *
 * @property int $id
 * @property string $code
 * @property int $menu_category_id
 * @property int|null $menu_subcategory_id
 * @property string $status
 * @property int|null $merged_into_id
 * @property string|null $normalized_name_en
 * @property string|null $normalized_name_ar
 * @property string|null $display_name_en
 * @property string|null $display_name_ar
 * @property string $name_en_status
 * @property string|null $name_en_decision
 * @property string $name_ar_status
 * @property string|null $name_ar_decision
 * @property string|null $suggested_name_ar
 * @property Availability $availability
 * @property PublishStatus $publish_status
 * @property string|null $size_info_status
 * @property bool $show_addons
 * @property string|null $description_ar
 * @property string|null $description_en
 * @property int|null $media_id
 * @property Carbon|null $new_until
 * @property string|null $aria_label_en
 * @property bool $is_featured
 * @property bool $is_new
 * @property bool $is_seasonal
 * @property string|null $data_quality_status
 * @property string|null $data_quality_flags
 * @property int $sort
 * @property-read MenuCategory $category
 */
class Product extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_RETIRED = 'retired';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'availability' => Availability::class,
            'publish_status' => PublishStatus::class,
            'show_addons' => 'boolean',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'new_until' => 'date',
            'is_seasonal' => 'boolean',
            'sort' => 'integer',
        ];
    }

    /** @return BelongsTo<MenuCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    /** @return BelongsTo<MenuSubcategory, $this> */
    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(MenuSubcategory::class, 'menu_subcategory_id');
    }

    /** @return BelongsTo<Media, $this> */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /** True while the "New" badge applies: switched on and its end date (if any) not passed (Amman day). */
    public function isNewOn(string $day): bool
    {
        return $this->is_new && ($this->new_until === null || $this->new_until->toDateString() >= $day);
    }

    /** @return HasMany<ProductPrice, $this> */
    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    /** @return HasMany<ProductBranchOverride, $this> */
    public function branchOverrides(): HasMany
    {
        return $this->hasMany(ProductBranchOverride::class);
    }

    /** @return HasMany<SearchAlias, $this> the approved search words (archived ones stay in the table) */
    public function searchAliases(): HasMany
    {
        return $this->hasMany(SearchAlias::class)->where('status', SearchAlias::STATUS_APPROVED)->orderBy('id');
    }

    /** @return HasMany<MenuSourceRow, $this> */
    public function sourceRows(): HasMany
    {
        return $this->hasMany(MenuSourceRow::class);
    }

    /** @return MorphMany<ExternalReference, $this> */
    public function externalReferences(): MorphMany
    {
        return $this->morphMany(ExternalReference::class, 'entity');
    }

    /** @param Builder<Product> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }
}
