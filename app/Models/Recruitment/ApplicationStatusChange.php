<?php

namespace App\Models\Recruitment;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One line of an application's status history (CAREERS-046): old → new, who, when, and an optional internal note.
 *
 * @property int $id
 * @property int $application_id
 * @property string|null $old_status
 * @property string $new_status
 * @property int|null $actor_id
 * @property Carbon $changed_at
 * @property string|null $internal_note
 * @property string|null $bulk_operation_id
 */
class ApplicationStatusChange extends Model
{
    protected $table = 'application_status_history';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['changed_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
