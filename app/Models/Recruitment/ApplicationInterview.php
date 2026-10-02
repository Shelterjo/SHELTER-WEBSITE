<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * An interview appointment (CAREERS-048): date, time, a listed location and internal notes. Rescheduling keeps the
 * earlier rows (is_current = false). Never shown to the applicant (tracking shows the public status only).
 *
 * @property int $id
 * @property int $application_id
 * @property Carbon $interview_date
 * @property string $interview_time
 * @property int $location_id
 * @property string|null $internal_notes
 * @property bool $is_current
 * @property int|null $created_by
 * @property Carbon|null $created_at
 */
class ApplicationInterview extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['interview_date' => 'date', 'is_current' => 'boolean'];
    }

    /** @return BelongsTo<InterviewLocation, $this> */
    public function location(): BelongsTo
    {
        return $this->belongsTo(InterviewLocation::class);
    }
}
