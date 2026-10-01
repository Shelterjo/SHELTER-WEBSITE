<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\Severity;
use App\Enums\SignalCategory;
use App\Enums\SignalKind;
use App\Enums\SignalStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * One queue for notifications, needs-attention items and incidents (NOTIFICATIONS.md, INCIDENTS.md).
 *
 * @property int $id
 * @property SignalKind $kind
 * @property SignalCategory $category
 * @property Severity $severity
 * @property Priority $priority
 * @property SignalStatus $status
 * @property string|null $dedupe_key
 * @property string $title_ar
 * @property string $title_en
 * @property string|null $body_ar
 * @property string|null $body_en
 * @property string|null $recommended_action
 * @property string $source
 * @property array<string, mixed>|null $details
 * @property int $occurrences
 * @property Carbon|null $last_seen_at
 * @property Carbon|null $read_at
 * @property Carbon|null $resolved_at
 * @property int|null $resolved_by
 */
class Signal extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'kind' => SignalKind::class,
            'category' => SignalCategory::class,
            'severity' => Severity::class,
            'priority' => Priority::class,
            'status' => SignalStatus::class,
            'details' => 'array',
            'last_seen_at' => 'datetime',
            'read_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** @param Builder<Signal> $query */
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', SignalStatus::Open->value);
    }
}
