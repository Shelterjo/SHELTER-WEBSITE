<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Regular weekly interval. weekday follows Carbon (0 = Sunday … 6 = Saturday).
 * closes_at <= opens_at means the interval runs past midnight into the next day.
 *
 * @property int $id
 * @property int $branch_id
 * @property int $weekday
 * @property string $opens_at
 * @property string $closes_at
 */
class BranchHour extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['weekday' => 'integer'];
    }

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
