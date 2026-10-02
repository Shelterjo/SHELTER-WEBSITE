<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A partnership meeting (FRAN-059): when, how (in person · video · phone), where, internal notes and its state.
 * Internal only — never shown to the applicant.
 *
 * @property int $id
 * @property int $application_id
 * @property Carbon $meeting_at
 * @property string $channel
 * @property string|null $place
 * @property string|null $internal_notes
 * @property string $state
 * @property int|null $created_by
 */
class ApplicationMeeting extends Model
{
    public const CHANNELS = ['in_person', 'video', 'phone'];

    public const STATES = ['planned', 'done', 'cancelled'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['meeting_at' => 'datetime'];
    }
}
