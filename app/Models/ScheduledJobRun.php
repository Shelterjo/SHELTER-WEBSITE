<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $job
 * @property Carbon $started_at
 * @property Carbon|null $finished_at
 * @property string $status
 * @property int|null $duration_ms
 * @property string|null $summary
 * @property string|null $error
 */
class ScheduledJobRun extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'finished_at' => 'datetime', 'duration_ms' => 'integer'];
    }
}
