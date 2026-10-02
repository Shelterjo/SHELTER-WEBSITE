<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One customer's answer to "how was your visit?" for a branch (VOICE-OF-CUSTOMER §2) — no personal data by design.
 *
 * @property int $id
 * @property int $branch_id
 * @property int $rating_overall
 * @property int|null $rating_coffee
 * @property int|null $rating_service
 * @property int|null $rating_cleanliness
 * @property int|null $rating_speed
 * @property string|null $comment
 * @property string $locale
 * @property string $entry_point
 * @property array<int, array{tag: string, source: string}>|null $topics
 * @property int|null $inquiry_id
 * @property string $idempotency_key
 * @property string $form_version
 * @property Carbon $submitted_at
 * @property Carbon|null $reviewed_at
 * @property Carbon|null $archived_at
 */
class Feedback extends Model
{
    protected $table = 'feedback';

    protected $guarded = ['id'];

    /** The rating questions, in form order; overall is the only required one. */
    public const DIMENSIONS = ['overall', 'coffee', 'service', 'cleanliness', 'speed'];

    protected function casts(): array
    {
        return [
            'topics' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
