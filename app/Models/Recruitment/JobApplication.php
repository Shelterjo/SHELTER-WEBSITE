<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * The careers fields of one application (RECRUITMENT-DATA-MODEL §2.1) — the number, status and dates live on the
 * Applications Core row; the identity number lives encrypted in application_identity_secure. Applicants cannot edit
 * after submitting; Owner corrections are audited.
 *
 * @property int $application_id
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
 * @property int|null $primary_attachment_id
 */
class JobApplication extends Model
{
    public const STATUSES = ['received', 'under_review', 'interview_shortlisted', 'interviewed', 'accepted', 'rejected', 'archived'];

    protected $primaryKey = 'application_id';

    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'same_field_experience' => 'boolean',
            'currently_employed' => 'boolean',
            'has_driving_license' => 'boolean',
            'expected_salary_jod' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Application, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /** @return BelongsTo<JordanCity, $this> */
    public function city(): BelongsTo
    {
        return $this->belongsTo(JordanCity::class);
    }
}
