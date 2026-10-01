<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Presentation-only grouping (D-097, F-07: SWEETS / حلويات shows CAKE + COOKIES). Never changes data identity.
 *
 * @property int $id
 * @property string $code
 * @property string|null $name_en
 * @property string|null $name_ar
 * @property int $sort
 */
class MenuGroup extends Model
{
    protected $guarded = ['id'];

    /** @return HasMany<MenuCategory, $this> */
    public function categories(): HasMany
    {
        return $this->hasMany(MenuCategory::class);
    }
}
