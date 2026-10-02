<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * A job application (RECRUITMENT-DATA-MODEL §2.1) — without the identity number, which lives encrypted in
 * application_identity_secure. Applicants cannot edit after submitting; Owner corrections are audited.
 *
 * @property int $id
 * @property string $application_number
 * @property string $full_name
 * @property string $phone_raw
 * @property string $phone_normalized
 * @property string $email
 * @property string $email_normalized
 * @property string $gender
 * @property Carbon $birth_date
 * @property string $marital_status
 * @property string $nationality_type
 * @property string|null $nationality_text
 * @property int $city_id
 * @property string $area_text
 * @property string $job_title_text
 * @property string $education_level
 * @property string $experience_band
 * @property bool $same_field_experience
 * @property bool $currently_employed
 * @property string $expected_salary_jod
 * @property bool $has_driving_license
 * @property string $notes_text
 * @property string $status
 * @property int|null $primary_attachment_id
 * @property int $applicant_group_size
 * @property Carbon $submitted_at
 * @property string $idempotency_key
 * @property string $form_version
 */
class JobApplication extends Model
{
    public const STATUSES = ['received', 'under_review', 'interview_shortlisted', 'interviewed', 'accepted', 'rejected', 'archived'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'submitted_at' => 'datetime',
            'archived_at' => 'datetime',
            'first_viewed_at' => 'datetime',
            'same_field_experience' => 'boolean',
            'currently_employed' => 'boolean',
            'has_driving_license' => 'boolean',
            'applicant_group_size' => 'integer',
            'expected_salary_jod' => 'decimal:2',
        ];
    }

    /** @return HasMany<ApplicationAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(ApplicationAttachment::class, 'application_id')->orderBy('id');
    }

    /** @return BelongsTo<JordanCity, $this> */
    public function city(): BelongsTo
    {
        return $this->belongsTo(JordanCity::class);
    }

    /** @return HasOne<ApplicationIdentity, $this> */
    public function identity(): HasOne
    {
        return $this->hasOne(ApplicationIdentity::class, 'application_id');
    }
}
