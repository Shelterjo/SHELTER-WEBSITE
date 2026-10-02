<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A private attachment (RECRUITMENT-DATA-MODEL §2.3, RECRUITMENT-SECURITY §3–§4). Stored under a random key on the
 * private `careers` disk; the original name is display metadata only and never builds a path.
 *
 * @property int $id
 * @property int|null $application_id
 * @property string|null $upload_session_id
 * @property string $storage_key
 * @property string $storage_path
 * @property string $original_filename
 * @property string $extension
 * @property string|null $declared_mime
 * @property string $detected_mime
 * @property string $file_family
 * @property int $size_bytes
 * @property string $sha256
 * @property int $cv_score
 * @property string $cv_detection
 * @property string $scan_status
 */
class ApplicationAttachment extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['size_bytes' => 'integer', 'cv_score' => 'integer'];
    }

    /** @return BelongsTo<JobApplication, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }
}
