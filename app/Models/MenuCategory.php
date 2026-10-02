<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Menu category (CAT-001…CAT-011, frozen IDs — F-05). name_en is the official source name (D-089);
 * name_ar is null until approved (P-01); suggested_name_ar keeps the proposal without publishing it.
 *
 * @property int $id
 * @property string $code
 * @property int|null $menu_group_id
 * @property string $source_name
 * @property string|null $name_en
 * @property string|null $name_ar
 * @property string $name_ar_status
 * @property string|null $suggested_name_ar
 * @property string|null $slug
 * @property string $type
 * @property Carbon|null $season_starts_on
 * @property Carbon|null $season_ends_on
 * @property string|null $season_override null = by the dates · on · off (CMS-009)
 * @property int $sort
 * @property string $status
 */
class MenuCategory extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['season_starts_on' => 'date', 'season_ends_on' => 'date', 'sort' => 'integer'];
    }

    /** @return BelongsTo<MenuGroup, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(MenuGroup::class, 'menu_group_id');
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** @return HasMany<MenuSubcategory, $this> */
    public function subcategories(): HasMany
    {
        return $this->hasMany(MenuSubcategory::class);
    }
}
