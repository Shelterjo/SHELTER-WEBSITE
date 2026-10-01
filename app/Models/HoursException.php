<?php

namespace App\Models;

use App\Enums\HoursExceptionKind;
use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Special / holiday / temporary / emergency hours for a date range (D-021). Never changes the regular hours.
 *
 * @property int $id
 * @property int $branch_id
 * @property HoursExceptionKind $kind
 * @property Carbon $starts_on
 * @property Carbon $ends_on
 * @property bool $is_closed
 * @property string|null $opens_at
 * @property string|null $closes_at
 * @property string|null $reason_ar
 * @property string|null $reason_en
 * @property PublishStatus $status
 * @property int|null $created_by
 */
class HoursException extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'kind' => HoursExceptionKind::class,
            'status' => PublishStatus::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_closed' => 'boolean',
        ];
    }

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
