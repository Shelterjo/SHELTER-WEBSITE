<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Base price history. Integer fils, VAT inclusive (D-122): 1 JOD = 1000 fils. valid_to NULL = current.
 *
 * @property int $id
 * @property int $product_id
 * @property int|null $menu_version_id
 * @property int $price_fils
 * @property string $currency
 * @property bool $tax_inclusive
 * @property Carbon $valid_from
 * @property Carbon|null $valid_to
 * @property string $source
 * @property int|null $created_by
 */
class ProductPrice extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['price_fils' => 'integer', 'tax_inclusive' => 'boolean', 'valid_from' => 'date', 'valid_to' => 'date'];
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
