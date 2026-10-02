<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Another word customers may type for a menu item (CMS-018): added by the Owner only (= approved), archived, never
 * deleted. `normalized` is what the search compares.
 *
 * @property int $id
 * @property int $product_id
 * @property string $value
 * @property string $normalized
 * @property string $locale
 * @property string $status
 * @property int|null $created_by
 * @property Carbon|null $archived_at
 * @property Carbon|null $created_at
 * @property-read Product $product
 */
class SearchAlias extends Model
{
    public const STATUS_APPROVED = 'approved';

    public const STATUS_ARCHIVED = 'archived';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['archived_at' => 'datetime'];
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
