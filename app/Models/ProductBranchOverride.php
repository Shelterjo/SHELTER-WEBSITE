<?php

namespace App\Models;

use App\Enums\Availability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Explicit per-branch override (M33 §5). A NULL column inherits the master value; no row = fully inherited.
 *
 * @property int $id
 * @property int $product_id
 * @property int $branch_id
 * @property int|null $price_fils
 * @property Availability|null $availability
 * @property string|null $reason
 * @property int|null $updated_by
 */
class ProductBranchOverride extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['price_fils' => 'integer', 'availability' => Availability::class];
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
