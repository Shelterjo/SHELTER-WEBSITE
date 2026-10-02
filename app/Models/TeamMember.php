<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A public SHELTER Family profile (DX-002…006, DX-036): what the employee agreed to show — nothing else. Not linked to
 * any HR record or job application; published only while the employee's recorded consent stands (G13-TF-01, OPS-034).
 *
 * @property int $id
 * @property string|null $display_name_ar
 * @property string|null $display_name_en
 * @property int|null $photo_media_id
 * @property string|null $job_title_ar
 * @property string|null $job_title_en
 * @property string|null $department
 * @property int|null $branch_id
 * @property Carbon|null $join_date
 * @property bool $show_join_date
 * @property string|null $bio_ar
 * @property string|null $bio_en
 * @property bool $show_bio
 * @property bool $is_published
 * @property int $sort_order
 * @property Carbon|null $publish_consent_at
 * @property string|null $publish_consent_version
 * @property Carbon|null $consent_withdrawn_at
 * @property Carbon|null $archived_at
 */
class TeamMember extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
            'show_join_date' => 'boolean',
            'show_bio' => 'boolean',
            'is_published' => 'boolean',
            'publish_consent_at' => 'datetime',
            'consent_withdrawn_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Media, $this> */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_media_id');
    }

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
