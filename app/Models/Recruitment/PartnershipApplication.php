<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The partnership fields of one FR application (docs/franchise/03-APPLICATION-FIELD-MATRIX.md 1–11). The number,
 * status and dates live on the Applications Core row. Submitting is an expression of interest — never an approval,
 * a franchise right, a territory or a contractual commitment (PF-02, D-319). The interest type records what the
 * applicant is interested in — never a format, right or territory SHELTER offers.
 *
 * @property int $application_id
 * @property string $full_name
 * @property string $phone_raw
 * @property string $phone_normalized
 * @property string $email
 * @property string $email_normalized
 * @property string $country_code
 * @property string $city_text
 * @property string $market_interest
 * @property string $partnership_interest_type single_location · multi_location · market_development · proposed_location · general_interest · other (PF-06)
 * @property string|null $partnership_interest_other
 * @property string $experience_band
 * @property string|null $experience_text
 * @property bool $owns_business
 * @property string $location_status
 * @property string $introduction
 */
class PartnershipApplication extends Model
{
    protected $primaryKey = 'application_id';

    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['owns_business' => 'boolean'];
    }

    /** @return BelongsTo<Application, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
