<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Subsection inside HOT / COLD / FIZZY (F-08). The current set is a proposal (P-04, PENDING OWNER APPROVAL):
 * status 'proposed' is never rendered publicly.
 *
 * @property int $id
 * @property string $code
 * @property int $menu_category_id
 * @property string|null $name_en
 * @property string|null $name_ar
 * @property string $status
 * @property int $sort
 */
class MenuSubcategory extends Model
{
    public const STATUS_PROPOSED = 'proposed';

    public const STATUS_APPROVED = 'approved';

    protected $guarded = ['id'];

    /** @return BelongsTo<MenuCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }
}
