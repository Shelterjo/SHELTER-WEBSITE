<?php

namespace App\Models\Recruitment;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * An internal note on an application (CAREERS-067, FRAN-058): many independent notes, never overwritten silently;
 * edits and removals are audited, removal is soft. Internal only — never shown to the applicant.
 *
 * @property int $id
 * @property int $application_id
 * @property string $body
 * @property int|null $author_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class ApplicationNote extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
