<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Applications Core (PLATFORM-ARCHITECTURE §3.4): one row per submission of any type — JOB careers, FR partnerships,
 * INQ inquiries — with the server-generated reference number (PREFIX-YYYY-NNNNN), the internal status, the assignee and
 * the dates. Each type keeps its own fields in a detail table; notes, status history, attachments, consents and links
 * are shared and point here. Reading state ("new") is separate from the status.
 *
 * @property int $id
 * @property string $type JOB | FR | INQ
 * @property string $reference_number
 * @property string $status
 * @property string|null $status_before_archive
 * @property int|null $assigned_to
 * @property Carbon|null $first_viewed_at
 * @property int $applicant_group_size
 * @property Carbon $submitted_at
 * @property Carbon|null $archived_at
 * @property string $idempotency_key
 * @property string $form_version
 * @property string $locale
 */
class Application extends Model
{
    public const TYPES = ['JOB', 'FR', 'INQ'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'archived_at' => 'datetime',
            'first_viewed_at' => 'datetime',
            'applicant_group_size' => 'integer',
        ];
    }

    /** @return HasOne<JobApplication, $this> */
    public function job(): HasOne
    {
        return $this->hasOne(JobApplication::class, 'application_id');
    }

    /** @return HasOne<PartnershipApplication, $this> */
    public function partnership(): HasOne
    {
        return $this->hasOne(PartnershipApplication::class, 'application_id');
    }

    /** @return HasMany<ApplicationAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(ApplicationAttachment::class, 'application_id')->orderBy('id');
    }

    /** @return HasOne<ApplicationIdentity, $this> */
    public function identity(): HasOne
    {
        return $this->hasOne(ApplicationIdentity::class, 'application_id');
    }

    /** @return HasMany<ApplicationNote, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(ApplicationNote::class, 'application_id')->orderByDesc('id');
    }

    /** @return HasMany<ApplicationStatusChange, $this> */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(ApplicationStatusChange::class, 'application_id')->orderByDesc('changed_at')->orderByDesc('id');
    }

    /** @return HasMany<ApplicationInterview, $this> */
    public function interviews(): HasMany
    {
        return $this->hasMany(ApplicationInterview::class, 'application_id')->orderByDesc('id');
    }

    /** @return HasMany<ApplicationMeeting, $this> */
    public function meetings(): HasMany
    {
        return $this->hasMany(ApplicationMeeting::class, 'application_id')->orderByDesc('meeting_at');
    }

    public function isNew(): bool
    {
        return $this->first_viewed_at === null;
    }
}
