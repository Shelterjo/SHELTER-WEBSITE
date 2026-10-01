<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable source lineage (SRC-#####, F-02, D-133). Never updated after import.
 *
 * @property int $id
 * @property string $code
 * @property int $product_id
 * @property int $menu_version_id
 * @property string $source_sheet
 * @property int $source_row
 * @property int $source_sequence_number
 * @property string $source_lineage
 * @property string $source_category_name
 * @property string $source_name_en
 * @property string|null $source_name_ar
 * @property string $source_price
 */
class MenuSourceRow extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::updating(fn (): bool => false);
        static::deleting(fn (): bool => false);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
